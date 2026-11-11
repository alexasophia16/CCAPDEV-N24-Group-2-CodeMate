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
}
