<?php

class Message extends Model
{
    // conversations with unread messages (sidebar badge)
    public function unreadConversationCount(int $userId): int
    {
        return (int) $this->fetchValue(
            'SELECT COUNT(DISTINCT m.conversation_id)
             FROM messages m
             JOIN conversations c ON c.conversation_id = m.conversation_id
             WHERE (c.user_one_id = ? OR c.user_two_id = ?)
               AND m.sender_id <> ?
               AND m.is_read = FALSE',
            [$userId, $userId, $userId]
        );
    }

    // Get all conversations for the sidebar
    public function getConversations(int $userId): array
    {
        return $this->fetchAll(
            'SELECT 
                c.conversation_id, 
                u.user_id AS other_user_id, 
                u.full_name AS other_user_name,
                (SELECT content FROM messages WHERE conversation_id = c.conversation_id ORDER BY sent_at DESC LIMIT 1) AS last_message_text,
                (SELECT sent_at FROM messages WHERE conversation_id = c.conversation_id ORDER BY sent_at DESC LIMIT 1) AS last_message_time,
                (SELECT COUNT(*) FROM messages WHERE conversation_id = c.conversation_id AND sender_id <> ? AND is_read = FALSE) AS unread_count
             FROM conversations c
             JOIN users u ON u.user_id = IF(c.user_one_id = ?, c.user_two_id, c.user_one_id)
             WHERE c.user_one_id = ? OR c.user_two_id = ?
             ORDER BY last_message_time DESC',
            [$userId, $userId, $userId, $userId]
        );
    }

    // Get the chat history for a specific conversation ID
    public function getChatHistory(int $conversationId): array
    {
        return $this->fetchAll(
            'SELECT m.*, u.full_name AS sender_name 
               FROM messages m
               JOIN users u ON m.sender_id = u.user_id
              WHERE m.conversation_id = ?
              ORDER BY m.sent_at ASC',
            [$conversationId]
        );
    }

    // Insert a new message into an existing conversation
    public function sendMessage(int $conversationId, int $senderId, string $content): void
    {
        $this->execute(
            'INSERT INTO messages (conversation_id, sender_id, content, is_read, sent_at) 
             VALUES (?, ?, ?, FALSE, NOW())',
            [$conversationId, $senderId, $content]
        );
    }
    
    // Mark messages in a specific conversation as read
    public function markAsRead(int $conversationId, int $userId): void
    {
        $this->execute(
            'UPDATE messages 
             SET is_read = TRUE 
             WHERE conversation_id = ? 
               AND sender_id <> ? 
               AND is_read = FALSE',
            [$conversationId, $userId]
        );
    }
}
