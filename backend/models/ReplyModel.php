<?php

// Replies inside a discussion thread (the discussion_replies table).
// extends Model: $this->db is set up by Model's constructor.
// These queries use named placeholders (:discussion_id) instead of ?; both are prepared
// statements and work the same way.
class ReplyModel extends Model {

    // All visible replies of one thread, oldest first (like a conversation).
    // Only status = 'visible' is shown, so a reply hidden by a moderator disappears here
    // without being deleted. author_id is used by the view to hide the Report button on your own reply.
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

    // Saves a new reply; it starts as 'visible'. The controller checks the thread exists,
    // the user isn't an admin and the reply isn't empty before calling this.
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
