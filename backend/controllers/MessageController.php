<?php

class MessageController extends Controller
{
    public function index(): void
    {
        $user = $this->requireLogin(['tutee', 'tutor']);
        $userId = (int) $user['user_id'];
        $messageModel = new Message();
        $contacts = $messageModel->contacts($userId);

        // if a user was selected from the "New Message" modal
        if (isset($_GET['new_user'])) {
            $newUserId = (int) $_GET['new_user'];

            // only people from the contact list (active tutees/tutors, not yourself)
            if (!in_array($newUserId, array_map('intval', array_column($contacts, 'user_id')), true)) {
                flash('error', 'You can\'t start a conversation with that user.');
                redirect('messages');
            }

            // generate or find the conversation ID, then open it
            $convId = $messageModel->findOrCreateConversation($userId, $newUserId);
            redirect('messages', ['c' => $convId]);
        }

        $activeConvId = isset($_GET['c']) ? (int) $_GET['c'] : null;

        // a conversation you're not part of is treated as not found
        if ($activeConvId !== null && !$messageModel->isParticipant($activeConvId, $userId)) {
            flash('error', 'That conversation could not be found.');
            redirect('messages');
        }

        if ($activeConvId) {
            $messageModel->markAsRead($activeConvId, $userId);
        }

        // fetch all conversations for the sidebar
        $conversations = $messageModel->getConversations($userId);

        $activeChat = [];
        $activeUserName = 'Select a conversation';
        $activeUserId = null;

        // if a chat is clicked in the sidebar, fetch its messages
        if ($activeConvId) {
            $activeChat = $messageModel->getChatHistory($activeConvId);

            // find the name of the person we are chatting with
            foreach ($conversations as $conv) {
                if ((int) $conv['conversation_id'] === $activeConvId) {
                    $activeUserName = $conv['other_user_name'];

                    $activeUserId = $conv['other_user_id'] ?? null;
                    
                    break;
                }
            }
        }
        $totalUnread = 0;
        foreach ($conversations as $conv) {
            $totalUnread += (int)($conv['unread_count'] ?? 0);
        }

        $this->render('messages/messages', [
            'conversations'  => $conversations,
            'activeChat'     => $activeChat,
            'activeConvId'   => $activeConvId,
            'activeUserName' => $activeUserName,
            'activeUserId'   => $activeUserId,
            'currentUserId'  => $userId,
            'allUsers'       => $contacts,
            'totalUnread'    => $totalUnread,
            'styles'         => ['messages']
        ]);
    }

    // handles the form submission when sending a new message
    public function send(): void
    {
        $user = $this->requireLogin(['tutee', 'tutor']);
        $senderId = (int) $user['user_id'];
        $conversationId = (int) ($_POST['conversation_id'] ?? 0);
        $content = input('content');
        $messageModel = new Message();

        // you can only post in your own conversations
        if (!$messageModel->isParticipant($conversationId, $senderId)) {
            flash('error', 'That conversation could not be found.');
            redirect('messages');
        }
        if ($content === '' || mb_strlen($content) > 1000) {
            flash('error', 'Messages must be between 1 and 1000 characters.');
            redirect('messages', ['c' => $conversationId]);
        }

        $messageModel->sendMessage($conversationId, $senderId, $content);

        // redirect back to the same chat
        redirect('messages', ['c' => $conversationId]);
    }
}
