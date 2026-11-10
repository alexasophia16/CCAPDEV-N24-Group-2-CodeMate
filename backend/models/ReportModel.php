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

    // Fetch reports by specific user. "target" reads like "User #3 (Justin ...)".
    public function getReportsByUser(int $userId): array {
        return $this->fetchAll(
            "SELECT r.report_id AS id, u.full_name AS reporter, r.reason, r.details, r.status, r.created_at,
                    CONCAT(r.target_type, ' #', r.target_id,
                           IF(t.full_name IS NULL, '', CONCAT(' (', t.full_name, ')'))) AS target
               FROM reports r
               JOIN users u ON u.user_id = r.reporter_id
               LEFT JOIN users t ON r.target_type = 'User' AND t.user_id = r.target_id
              WHERE r.reporter_id = ?
              ORDER BY r.created_at DESC",
            [$userId]
        );
    }

    // Is there a row with this id in the target's table?
    public function targetExists(string $targetType, int $targetId): bool {
        if (!isset(self::TARGETS[$targetType])) {
            return false;
        }
        [$table, $idColumn] = self::TARGETS[$targetType];
        return (bool) $this->fetchValue("SELECT COUNT(*) FROM $table WHERE $idColumn = ?", [$targetId]);
    }

    // Submit a new report (status starts as 'pending')
    public function createReport(int $reporterId, string $targetType, int $targetId, string $reason, string $details): void {
        $this->execute(
            "INSERT INTO reports (reporter_id, target_type, target_id, reason, details) VALUES (?, ?, ?, ?, ?)",
            [$reporterId, $targetType, $targetId, $reason, $details === '' ? null : $details]
        );
    }
}
