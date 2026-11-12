<?php

// Reports filed by tutees and tutors. target_type says which table the report is about,
// and target_id is the id of the row in that table.
class ReportModel extends Model {

    // target_type => [table, id column]. Also a whitelist, since table names can't be ? parameters.
    public const TARGETS = [
        'User'       => ['users', 'user_id'],
        'Discussion' => ['discussions', 'discussion_id'],
        'Reply'      => ['discussion_replies', 'reply_id'],
        'Message'    => ['messages', 'message_id'],
        'Resource'   => ['resources', 'resource_id'],
    ];

    public const STATUSES = ['pending', 'resolved', 'dismissed'];
    // the "Show" filter on the admin Reports page
    public const FILTERS = ['pending', 'resolved', 'dismissed', 'all'];

    // Shared SELECT for both report lists.
    // - target: readable name of what was reported, e.g. "Discussion: Why is my merge sort..."
    // - link:   the page where the admin can look at it (null if there is none)
    // The LEFT JOINs only match the table that target_type points to.
    private const LIST_QUERY = "
        SELECT r.report_id AS id, r.reporter_id, u.full_name AS reporter, r.target_type, r.target_id,
               r.reason, r.details, r.status, r.created_at, r.resolved_at, admin.full_name AS resolved_by_name,
               CONCAT(r.target_type, ': ', COALESCE(tu.full_name, d.title,
                      CONCAT('\"', LEFT(dr.content, 60), '\" by ', dru.full_name), res.title,
                      CONCAT('#', r.target_id))) AS target,
               CASE r.target_type
                   WHEN 'Discussion' THEN CONCAT('discussions-thread&id=', r.target_id)
                   WHEN 'Reply'      THEN CONCAT('discussions-thread&id=', dr.discussion_id)
                   WHEN 'Resource'   THEN 'resources'
                   WHEN 'User'       THEN IF(tp.verification_status = 'verified',
                                             CONCAT('tutor-profile&id=', r.target_id), 'admin-users')
               END AS link
          FROM reports r
          JOIN users u ON u.user_id = r.reporter_id
          LEFT JOIN users admin ON admin.user_id = r.resolved_by
          LEFT JOIN users tu ON r.target_type = 'User' AND tu.user_id = r.target_id
          LEFT JOIN tutor_profiles tp ON r.target_type = 'User' AND tp.tutor_id = r.target_id
          LEFT JOIN discussions d ON r.target_type = 'Discussion' AND d.discussion_id = r.target_id
          LEFT JOIN discussion_replies dr ON r.target_type = 'Reply' AND dr.reply_id = r.target_id
          LEFT JOIN users dru ON dru.user_id = dr.author_id
          LEFT JOIN resources res ON r.target_type = 'Resource' AND res.resource_id = r.target_id";

    // Reports filed by one user (their Reports page)
    public function getReportsByUser(int $userId): array {
        return $this->fetchAll(self::LIST_QUERY . ' WHERE r.reporter_id = ? ORDER BY r.created_at DESC', [$userId]);
    }

    // Admin Reports page. $status is one of FILTERS; pending ones are shown first by default.
    public function getAllReports(string $status = 'pending'): array {
        if ($status === 'all') {
            return $this->fetchAll(self::LIST_QUERY . ' ORDER BY r.created_at DESC');
        }
        return $this->fetchAll(self::LIST_QUERY . ' WHERE r.status = ? ORDER BY r.created_at DESC', [$status]);
    }

    // How many reports have each status, for the counts in the filter
    public function countsByStatus(): array {
        $counts = array_fill_keys(self::STATUSES, 0);
        foreach ($this->fetchAll('SELECT status, COUNT(*) AS total FROM reports GROUP BY status') as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }
        return $counts;
    }

    // One report with its readable target, or null
    public function find(int $reportId): ?array {
        return $this->fetchOne(self::LIST_QUERY . ' WHERE r.report_id = ?', [$reportId]);
    }

    // Who reported this item and is still waiting (used to notify them when an admin decides)
    public function pendingReporterIds(string $targetType, int $targetId): array {
        return array_map('intval', array_column($this->fetchAll(
            'SELECT DISTINCT reporter_id FROM reports WHERE target_type = ? AND target_id = ? AND status = "pending"',
            [$targetType, $targetId]
        ), 'reporter_id'));
    }

    // Is there a row with this id in the target's table?
    public function targetExists(string $targetType, int $targetId): bool {
        if (!isset(self::TARGETS[$targetType])) {
            return false;
        }
        [$table, $idColumn] = self::TARGETS[$targetType];
        return (bool) $this->fetchValue("SELECT COUNT(*) FROM $table WHERE $idColumn = ?", [$targetId]);
    }

    // Submit a new report (status starts as 'pending'). Returns the new report id.
    public function createReport(int $reporterId, string $targetType, int $targetId, string $reason, string $details): int {
        $this->execute(
            "INSERT INTO reports (reporter_id, target_type, target_id, reason, details) VALUES (?, ?, ?, ?, ?)",
            [$reporterId, $targetType, $targetId, $reason, $details === '' ? null : $details]
        );
        return $this->lastInsertId();
    }

    // Closes every still-pending report about one item at once (used when the admin acts on it),
    // recording which admin did it and when
    public function closeAllFor(string $targetType, int $targetId, string $status, int $adminId): void {
        $this->execute(
            'UPDATE reports SET status = ?, resolved_by = ?, resolved_at = NOW()
              WHERE target_type = ? AND target_id = ? AND status = "pending"',
            [$status, $adminId, $targetType, $targetId]
        );
    }

    // Admin changes a report's status. Returns false for an unknown status or report.
    // resolved_by / resolved_at record which admin closed it and when (cleared again for pending).
    public function updateStatus(int $reportId, string $status, int $adminId): bool {
        if (!in_array($status, self::STATUSES, true)
            || !$this->fetchValue("SELECT COUNT(*) FROM reports WHERE report_id = ?", [$reportId])) {
            return false;
        }
        $pending = $status === 'pending';
        $this->execute(
            "UPDATE reports SET status = ?, resolved_by = ?, resolved_at = " . ($pending ? "NULL" : "NOW()") . "
              WHERE report_id = ?",
            [$status, $pending ? null : $adminId, $reportId]
        );
        return true;
    }
}
