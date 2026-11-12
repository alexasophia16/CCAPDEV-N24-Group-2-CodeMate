<?php

// extends Model: $this->db is set up by Model's constructor
class ReplyModel extends Model {

    // Fetches all visible replies for a specific thread ID
    public function getRepliesByDiscussionId($discussionId) {
        $sql = "
            SELECT 
                r.reply_id AS id,
                r.discussion_id AS thread_id,
                r.content,
                u.full_name AS author,
                r.author_id,
                r.created_at
            FROM discussion_replies r
            JOIN users u ON r.author_id = u.user_id
            WHERE r.discussion_id = :discussion_id 
              AND r.status = 'visible'
            ORDER BY r.created_at ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['discussion_id' => $discussionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Submits a new reply to a thread
    public function createReply($discussionId, $authorId, $content) {
        $sql = "
            INSERT INTO discussion_replies (discussion_id, author_id, content, status, created_at)
            VALUES (:discussion_id, :author_id, :content, 'visible', NOW())
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'discussion_id' => $discussionId,
            'author_id'     => $authorId,
            'content'       => $content
        ]);
    }
}
