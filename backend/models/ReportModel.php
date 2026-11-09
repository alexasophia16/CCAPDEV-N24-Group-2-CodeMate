<?php

class ReportModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    // Fetch reports by specific user
    public function getReportsByUser(int $userId) {
        $sql = "
            SELECT 
                r.report_id AS id,
                u.full_name AS reporter,
                r.target,
                r.reason,
                r.status,
                r.created_at
            FROM reports r
            JOIN users u ON r.reporter_id = u.user_id
            WHERE r.reporter_id = :reporter_id
            ORDER BY r.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['reporter_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Submit a new report
    public function createReport(int $reporterId, string $target, string $reason) {
        $sql = "
            INSERT INTO reports (reporter_id, target, reason, status, created_at)
            VALUES (:reporter_id, :target, :reason, 'pending', NOW())
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'reporter_id' => $reporterId,
            'target'      => $target,
            'reason'      => $reason
        ]);
    }
}
