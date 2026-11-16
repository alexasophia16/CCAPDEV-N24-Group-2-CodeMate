<?php

// Discussion threads (the discussions table).
// extends Model: $this->db is set up by Model's constructor.
// Every query only shows status = 'visible', so a thread hidden by a moderator disappears
// from the list and its page without being deleted (an admin can undo it).
// These use named placeholders (:id) instead of ?; both are prepared statements.
class DiscussionModel extends Model {

    // All visible threads, newest first, with the author and reply count.
    // $courseCodeFilter is a course code like 'CCPROG2', or 'ALL' for every course.
    public function getDiscussions($courseCodeFilter = 'ALL') {
        $sql = "
            SELECT 
                d.discussion_id AS id,
                c.course_code AS course,
                d.title,
                d.content,
                u.full_name AS author,
                d.author_id,
                u.role AS authorRole,
                d.created_at
            FROM discussions d
            JOIN users u ON d.author_id = u.user_id
            JOIN courses c ON d.course_id = c.course_id
            WHERE d.status = 'visible'
        ";

        if ($courseCodeFilter !== 'ALL') {
            $sql .= " AND c.course_code = :course_code";
        }

        $sql .= " ORDER BY d.created_at DESC";

        $stmt = $this->db->prepare($sql);

        if ($courseCodeFilter !== 'ALL') {
            $stmt->execute(['course_code' => $courseCodeFilter]);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // One visible thread for its page, or false if it doesn't exist or is hidden
    public function getDiscussionById($id) {
        $sql = "
            SELECT 
                d.discussion_id AS id,
                c.course_code AS course,
                d.title,
                d.content,
                u.full_name AS author,
                d.author_id,
                u.role AS authorRole,
                d.created_at
            FROM discussions d
            JOIN users u ON d.author_id = u.user_id
            JOIN courses c ON d.course_id = c.course_id
            WHERE d.discussion_id = :id AND d.status = 'visible'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Saves a new thread as 'visible'. The form sends a course code, so it is looked up
    // first; an unknown code throws an Exception, which the controller shows as an error.
    public function createDiscussion($authorId, $courseCode, $title, $content) {
        // First get the course_id from the course_code
        $courseStmt = $this->db->prepare("SELECT course_id FROM courses WHERE course_code = :code");
        $courseStmt->execute(['code' => $courseCode]);
        $course = $courseStmt->fetch(PDO::FETCH_ASSOC);

        if (!$course) {
            throw new Exception("Invalid course code.");
        }

        $sql = "
            INSERT INTO discussions (author_id, course_id, title, content, status, created_at) 
            VALUES (:author_id, :course_id, :title, :content, 'visible', NOW())
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'author_id' => $authorId,
            'course_id' => $course['course_id'],
            'title'     => $title,
            'content'   => $content
        ]);
    }
}
