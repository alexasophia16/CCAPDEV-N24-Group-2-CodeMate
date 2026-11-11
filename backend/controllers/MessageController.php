<?php

class MessageController extends Controller
{
    public function index(): void
    {
        $this->requireLogin(); 
        
        $userId = $_SESSION['user_id'];
        $messageModel = new Message();
        
        // fetch all conversations for the sidebar
        $conversations = $messageModel->getConversations($userId);
        
        $activeChat = [];
        $activeConvId = $_GET['c'] ?? null; 
        $activeUserName = 'Select a conversation';

        // if a chat is clicked in the sidebar, fetch its messages
        if ($activeConvId) {
            
            $messageModel->markAsRead((int)$activeConvId, $userId);
            $activeChat = $messageModel->getChatHistory((int)$activeConvId);
            
            // find the name of the person we are chatting with
            foreach ($conversations as $conv) {
                if ($conv['conversation_id'] == $activeConvId) {
                    $activeUserName = $conv['other_user_name'];
                    break;
                }
            }
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
        $this->requireLogin();

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
