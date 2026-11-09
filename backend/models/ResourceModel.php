<?php

class ResourceModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function getResourcesForUser(int $userId, string $role) {
        // If admin, see all
        // If not, see approved or user's own pending resources.
        $sql = "
            SELECT 
                r.resource_id AS id, 
                r.title, 
                r.course, 
                r.type, 
                r.status, 
                u.full_name AS author
            FROM resources r
            JOIN users u ON r.author_id = u.user_id
            WHERE r.status = 'approved' 
               OR r.author_id = :user_id 
               OR :role = 'admin'
            ORDER BY r.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'role'    => $role
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createResource(int $authorId, string $title, string $course, string $type) {
        $sql = "
            INSERT INTO resources (author_id, title, course, type, status, created_at)
            VALUES (:author_id, :title, :course, :type, 'pending', NOW())
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'author_id' => $authorId,
            'title'     => $title,
            'course'    => $course,
            'type'      => $type
        ]);
    }
}
