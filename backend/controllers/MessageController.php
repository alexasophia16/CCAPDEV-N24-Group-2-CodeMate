<?php

class MessageController extends Controller
{
    public function index(): void
    {
        $this->requireLogin(['tutee', 'tutor']);
        
        $userId = $_SESSION['user_id'];
        $messageModel = new Message();

        // if a user was selected from the "New Message" modal
        if (isset($_GET['new_user'])) {
            $newUserId = (int)$_GET['new_user'];
            
            // generate or find the conversation ID
            $convId = $messageModel->findOrCreateConversation($userId, $newUserId);
            
            // redirect to the actual conversation view
            header('Location: index.php?page=messages&c=' . $convId);
            exit;
        }

        $activeConvId = $_GET['c'] ?? null; 
        
        if ($activeConvId) {
            $messageModel->markAsRead((int)$activeConvId, $userId);
        }
        
        // fetch all conversations for the sidebar
        $conversations = $messageModel->getConversations($userId);
        
        $activeChat = [];
        $activeUserName = 'Select a conversation';

        // if a chat is clicked in the sidebar, fetch its messages
        if ($activeConvId) {

            $activeChat = $messageModel->getChatHistory((int)$activeConvId);
            
            // find the name of the person we are chatting with
            foreach ($conversations as $conv) {
                if ($conv['conversation_id'] == $activeConvId) {
                    $activeUserName = $conv['other_user_name'];
                    break;
                }
            }
        }
        $totalUnread = 0;
        foreach ($conversations as $conv) {
            $totalUnread += (int)($conv['unread_count'] ?? 0);
        }
        $userModel = new User();
        $allUsers = $userModel->all();

        // pass the data to the new PHP view
$this->render('messages/messages', [
            'conversations'  => $conversations,
            'activeChat'     => $activeChat,
            'activeConvId'   => $activeConvId,
            'activeUserName' => $activeUserName,
            'currentUserId'  => $userId,
            'allUsers'       => $allUsers,
            'totalUnread'    => $totalUnread,
            'styles'         => ['messages']
        ]);
    }

    // handles the form submission when sending a new message
    public function send(): void
    {
        $this->requireLogin(['tutee', 'tutor']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $senderId = $_SESSION['user_id'];
            $conversationId = (int)$_POST['conversation_id'];
            $content = trim($_POST['content']);

            if ($conversationId && !empty($content)) {
                $messageModel = new Message();
                $messageModel->sendMessage($conversationId, $senderId, $content);
            }

            // redirect back to the same chat
            header('Location: index.php?page=messages&c=' . $conversationId);
            exit;
        }
    }
}
