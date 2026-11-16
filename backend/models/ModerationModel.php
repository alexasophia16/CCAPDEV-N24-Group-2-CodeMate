<?php

// Content Moderation queue: student resources (to approve or reject) plus discussions and
// replies that someone reported. Every item gets one of three queue statuses:
//   pending - waiting for an admin
//   kept    - resource approved, or reported post left up
//   hidden  - resource rejected, or reported post removed
class ModerationModel extends Model
{
    public const TYPES = ['Resource', 'Discussion', 'Reply'];
    public const STATUSES = ['pending', 'kept', 'hidden'];
    public const FILTERS = ['pending', 'kept', 'hidden', 'all'];

    public function getItemsByStatus(string $filter): array
    {
        // A post can be reported more than once; only its latest report is joined,
        // so each post shows up once in the queue.
        $sql = "
            SELECT * FROM (
                SELECT 'Resource' AS type, r.resource_id AS id,
                       CASE r.status WHEN 'approved' THEN 'kept'
                                     WHEN 'rejected' THEN 'hidden'
                                     ELSE 'pending' END AS status,
                       r.title, r.description AS content, c.course_name AS course,
                       u.full_name AS author, NULL AS reason, NULL AS flaggedBy,
                       NULL AS flaggedAt, r.created_at AS sort_date
                  FROM resources r
                  JOIN users u ON u.user_id = r.uploader_id
                  JOIN courses c ON c.course_id = r.course_id

                UNION ALL

                SELECT 'Discussion', d.discussion_id,
                       CASE WHEN d.status = 'removed' THEN 'hidden'
                            WHEN rep.status = 'pending' THEN 'pending'
                            ELSE 'kept' END,
                       d.title, d.content, c.course_name, u.full_name,
                       rep.reason, ru.full_name, rep.created_at, rep.created_at
                  FROM discussions d
                  JOIN users u ON u.user_id = d.author_id
                  JOIN courses c ON c.course_id = d.course_id
                  JOIN reports rep ON rep.report_id = (
                         SELECT MAX(report_id) FROM reports
                          WHERE target_type = 'Discussion' AND target_id = d.discussion_id)
                  JOIN users ru ON ru.user_id = rep.reporter_id

                UNION ALL

                SELECT 'Reply', dr.reply_id,
                       CASE WHEN dr.status = 'removed' THEN 'hidden'
                            WHEN rep.status = 'pending' THEN 'pending'
                            ELSE 'kept' END,
                       CONCAT('Reply in: ', d.title), dr.content, c.course_name, u.full_name,
                       rep.reason, ru.full_name, rep.created_at, rep.created_at
                  FROM discussion_replies dr
                  JOIN discussions d ON d.discussion_id = dr.discussion_id
                  JOIN users u ON u.user_id = dr.author_id
                  JOIN courses c ON c.course_id = d.course_id
                  JOIN reports rep ON rep.report_id = (
                         SELECT MAX(report_id) FROM reports
                          WHERE target_type = 'Reply' AND target_id = dr.reply_id)
                  JOIN users ru ON ru.user_id = rep.reporter_id
            ) AS moderation_queue";

        $params = [];
        if ($filter !== 'all') {
            $sql .= ' WHERE status = ?';
            $params[] = $filter;
        }
        return $this->fetchAll($sql . ' ORDER BY sort_date DESC', $params);
    }

    // Who posted or uploaded the item, and a short name for it (for notifications), or null.
    public function ownerOf(string $type, int $id): ?array
    {
        $sql = [
            'Resource'   => 'SELECT uploader_id AS user_id, title FROM resources WHERE resource_id = ?',
            'Discussion' => 'SELECT author_id AS user_id, title FROM discussions WHERE discussion_id = ?',
            'Reply'      => 'SELECT dr.author_id AS user_id, d.title FROM discussion_replies dr
                               JOIN discussions d ON d.discussion_id = dr.discussion_id WHERE dr.reply_id = ?',
        ][$type] ?? null;
        return $sql === null ? null : $this->fetchOne($sql, [$id]);
    }

    // Returns false if the type/status is unknown or the item doesn't exist.
    public function updateStatus(string $type, int $id, string $status, int $adminId): bool
    {
        if (!in_array($type, self::TYPES, true) || !in_array($status, self::STATUSES, true)) {
            return false;
        }

        if ($type === 'Resource') {
            $dbStatus = ['kept' => 'approved', 'hidden' => 'rejected', 'pending' => 'pending'][$status];
            if (!$this->fetchValue('SELECT COUNT(*) FROM resources WHERE resource_id = ?', [$id])) {
                return false;
            }
            $this->execute('UPDATE resources SET status = ? WHERE resource_id = ?', [$dbStatus, $id]);
            return true;
        }

        // table and column names come from this fixed list, never from the form
        [$table, $idColumn] = $type === 'Discussion'
            ? ['discussions', 'discussion_id']
            : ['discussion_replies', 'reply_id'];
        if (!$this->fetchValue("SELECT COUNT(*) FROM $table WHERE $idColumn = ?", [$id])) {
            return false;
        }

        // show or hide the post, and close (or reopen, for Undo) every report about it, together
        $this->db->beginTransaction();
        try {
            $this->execute(
                "UPDATE $table SET status = ? WHERE $idColumn = ?",
                [$status === 'hidden' ? 'removed' : 'visible', $id]
            );
            $reportStatus = ['kept' => 'dismissed', 'hidden' => 'resolved', 'pending' => 'pending'][$status];
            $this->execute(
                'UPDATE reports SET status = ?, resolved_by = ?, resolved_at = IF(? = "pending", NULL, NOW())
                  WHERE target_type = ? AND target_id = ?',
                [$reportStatus, $status === 'pending' ? null : $adminId, $status, $type, $id]
            );
            $this->db->commit();
            return true;
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }
}
