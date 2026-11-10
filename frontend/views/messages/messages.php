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
                      // Automatically generate initials for the avatar (e.g., "Justin Adrian" -> "JA")
                      $nameParts = explode(' ', trim($conv['other_user_name']));
                      $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                  ?>
                  <a href="/messages?c=<?= $conv['conversation_id'] ?>" class="conv-item <?= ($activeConvId == $conv['conversation_id']) ? 'active' : '' ?>">
                      <div class="conv-avatar"><?= htmlspecialchars($initials) ?></div>
                      <div class="conv-details">
                          <div class="conv-header">
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
            <strong><?= htmlspecialchars($activeUserName) ?></strong>
        </div>
      </header>

      <div class="chat-history" id="chat-history" style="padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; height: 400px;">
          <?php if (!$activeConvId): ?>
              <div style="text-align: center; color: #999; margin-top: 40px;">Select a chat from the left to start messaging.</div>
          <?php elseif (empty($activeChat)): ?>
              <div style="text-align: center; color: #999; margin-top: 40px;">No messages yet. Say hello!</div>
          <?php else: ?>
              <?php foreach ($activeChat as $msg): ?>
                  <?php $isMine = ($msg['sender_id'] === $currentUserId); ?>
                  <div style="align-self: <?= $isMine ? 'flex-end' : 'flex-start' ?>; max-width: 70%; background: <?= $isMine ? '#2563eb' : '#f1f5f9' ?>; color: <?= $isMine ? '#fff' : '#333' ?>; padding: 10px 14px; border-radius: 8px;">
                      <div style="margin-bottom: 4px;"><?= nl2br(htmlspecialchars($msg['content'])) ?></div>
                      <small style="font-size: 0.7em; color: <?= $isMine ? '#bfdbfe' : '#94a3b8' ?>;">
                          <?= date('h:i A', strtotime($msg['created_at'])) ?>
                      </small>
                  </div>
              <?php endforeach; ?>
          <?php endif; ?>
      </div>

      <form class="chat-input-bar" id="chat-form" method="POST" action="/messages">
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