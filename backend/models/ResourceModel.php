<?php

// Study resources. Each one is a link (url) to a file, video or page, tagged with a course.
// New uploads start as 'pending' until an admin approves them.
class ResourceModel extends Model {

    // same values as the resource_type ENUM; also the options in the upload form
    public const TYPES = ['PDF', 'Video', 'Link', 'Notes'];

    // Everyone sees approved resources; uploaders also see their own pending/rejected ones;
    // admins see everything.
    public function getResourcesForUser(int $userId, string $role): array {
        return $this->fetchAll(
            "SELECT r.resource_id AS id, r.title, r.description, r.url, r.status,
                    r.resource_type AS type, c.course_code AS course, u.full_name AS author, r.uploader_id
               FROM resources r
               JOIN users u ON u.user_id = r.uploader_id
               JOIN courses c ON c.course_id = r.course_id
              WHERE r.status = 'approved' OR r.uploader_id = ? OR ? = 'admin'
              ORDER BY r.created_at DESC",
            [$userId, $role]
        );
    }

    // Saves a new resource. The status column defaults to 'pending' in the database, so it
    // waits for an admin in Content Moderation. An empty description is stored as NULL.
    // The controller has already checked the title, course, type and that url is http(s).
    public function createResource(int $uploaderId, int $courseId, string $title, string $type, string $url, string $description): void {
        $this->execute(
            "INSERT INTO resources (uploader_id, course_id, title, description, resource_type, url)
             VALUES (?, ?, ?, ?, ?, ?)",
            [$uploaderId, $courseId, $title, $description === '' ? null : $description, $type, $url]
        );
    }
}
