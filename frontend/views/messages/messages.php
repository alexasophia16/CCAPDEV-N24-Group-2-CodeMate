<main class="messages-page-content">
  <div class="messages-header">
    <h1>Messages</h1>
    <p class="messages-subtitle">Chat directly with peers and tutors.</p>
  </div>

  <div class="messages-card">
    <aside class="conversations-pane">
      <div class="pane-header">
        <input type="search" placeholder="Search chats..." class="chat-search" id="conv-search" />
        <button type="button" class="btn-compose" id="btn-compose" title="New Message">&#9998;</button>
      </div>
      
      <div class="conversations-list" id="conversations-list">
          <?php if (empty($conversations)): ?>
              <p style="padding: 15px; text-align: center; color: #666;">No active conversations.</p>
          <?php else: ?>
              <?php foreach ($conversations as $conv): ?>
                  <?php 
                      $nameParts = explode(' ', trim($conv['other_user_name']));
                      $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                  ?>
                  
                  <a href="index.php?page=messages&c=<?= $conv['conversation_id'] ?>" 
                     class="conversation-item <?= ($activeConvId == $conv['conversation_id']) ? 'active' : '' ?>">
                      
                      <div class="conversation-avatar">
                          <?= htmlspecialchars($initials) ?>
                      </div>
                      
                      <div class="conversation-info">
                          <div class="conv-title-row">
                              <span class="conv-name"><?= htmlspecialchars($conv['other_user_name']) ?></span>
                              <span class="conv-time">
                                  <?= $conv['last_message_time'] ? date('m/d/Y', strtotime($conv['last_message_time'])) : '' ?>
                              </span>
                          </div>
                          <div class="conv-preview">
                              <?= htmlspecialchars($conv['last_message_text'] ?? 'Click to view messages') ?>
                          </div>
                      </div>
                  </a>
              <?php endforeach; ?>
          <?php endif; ?>
      </div>
    </aside>

    <section class="chat-pane">
      <header class="chat-header">
        <div class="chat-recipient-info" id="chat-recipient-info">
            <span class="recipient-name"><?= htmlspecialchars($activeUserName) ?></span>
        </div>
      </header>

      <div class="chat-history" id="chat-history">
          <?php if (!$activeConvId): ?>
              <div style="text-align: center; color: #999; margin-top: 40px;">Select a chat from the left to start messaging.</div>
          <?php elseif (empty($activeChat)): ?>
              <div style="text-align: center; color: #999; margin-top: 40px;">No messages yet. Say hello!</div>
          <?php else: ?>
              <?php foreach ($activeChat as $msg): ?>
                  <?php $isMine = ($msg['sender_id'] === $currentUserId); ?>
                  <div class="message-bubble <?= $isMine ? 'sent' : 'received' ?>">
                      <p style="margin:0;"><?= nl2br(htmlspecialchars($msg['content'])) ?></p>
                      <span class="msg-time">
                          <?= date('h:i A', strtotime($msg['sent_at'])) ?>
                      </span>
                  </div>
              <?php endforeach; ?>
          <?php endif; ?>
      </div>

      <form class="chat-input-bar" id="chat-form" method="POST" action="index.php?page=messages">
        <input type="hidden" name="conversation_id" value="<?= htmlspecialchars($activeConvId ?? '') ?>">
        <input 
          type="text" 
          name="content" 
          id="message-input" 
          placeholder="Write a message..." 
          autocomplete="off" 
          required 
          <?= !$activeConvId ? 'disabled' : '' ?>
        />
        <button type="submit" class="btn-send" id="btn-send" <?= !$activeConvId ? 'disabled' : '' ?>>Send</button>
      </form>
    </section>
  </div>
</main>
