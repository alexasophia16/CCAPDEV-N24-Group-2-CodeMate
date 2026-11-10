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
              <p style="padding: 15px; text-align: center; color: var(--ink-faint);">No active conversations.</p>
          <?php else: ?>
              <?php foreach ($conversations as $conv): ?>
                  <?php 
                      // Generate Avatar Initials
                      $nameParts = explode(' ', trim($conv['other_user_name']));
                      $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                      
                      // Check for unread status (assuming 'is_unread' or 'unread_count' is in your DB SELECT)
                      $isUnread = !empty($conv['is_unread']) || !empty($conv['unread_count']);
                  ?>
                  
                  <a href="index.php?page=messages&c=<?= $conv['conversation_id'] ?>" 
                     class="conversation-item <?= ($activeConvId == $conv['conversation_id']) ? 'active' : '' ?> <?= $isUnread ? 'unread' : '' ?>">
                      
                      <div class="conversation-avatar">
                          <?= htmlspecialchars($initials) ?>
                      </div>
                      
                      <div class="conversation-info">
                          <div class="conv-title-row">
                              <span class="conv-name" <?= $isUnread ? 'style="font-weight: 700; color: var(--ink);"' : '' ?>>
                                  <?= htmlspecialchars($conv['other_user_name']) ?>
                              </span>
                              <span class="conv-time" <?= $isUnread ? 'style="font-weight: 600; color: var(--green);"' : '' ?>>
                                  <?= $conv['last_message_time'] ? date('M j, g:i A', strtotime($conv['last_message_time'])) : '' ?>
                              </span>
                          </div>
                          <div class="conv-preview" <?= $isUnread ? 'style="font-weight: 600; color: var(--ink);"' : '' ?>>
                              <?php if ($isUnread): ?>
                                  <span style="display: inline-block; width: 8px; height: 8px; background: var(--green); border-radius: 50%; margin-right: 4px;"></span>
                              <?php endif; ?>
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
            <span class="recipient-name" style="font-weight: 600; font-size: 16px;">
                <?= htmlspecialchars($activeUserName ?? 'Select a conversation') ?>
            </span>
        </div>
      </header>

      <div class="chat-history" id="chat-history">
          <?php if (!$activeConvId): ?>
              <div style="text-align: center; color: var(--ink-faint); margin-top: 40px;">Select a chat from the left to start messaging.</div>
          <?php elseif (empty($activeChat)): ?>
              <div style="text-align: center; color: var(--ink-faint); margin-top: 40px;">No messages yet. Say hello!</div>
          <?php else: ?>
              <?php foreach ($activeChat as $msg): ?>
                  <?php $isMine = ($msg['sender_id'] === $currentUserId); ?>
                  <div class="message-bubble <?= $isMine ? 'sent' : 'received' ?>">
                      <p style="margin:0;"><?= nl2br(htmlspecialchars($msg['content'])) ?></p>
                      <span class="msg-time" style="font-size: 11px; opacity: 0.7; margin-top: 4px; display: block;">
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

  <!-- Compose Modal (Preserved exactly as requested) -->
  <div class="compose-modal-backdrop" id="compose-modal" hidden>
    <div class="compose-modal">
      <div class="compose-modal-header">
        <h3>New Message</h3>
        <button class="btn-close-modal" id="btn-close-compose" type="button">&times;</button>
      </div>
      <div class="compose-modal-body">
        <input type="text" class="compose-user-search" placeholder="Search users to message...">
        <div class="compose-user-list">
          <p style="text-align: center; color: var(--ink-faint); font-size: 12px; margin-top: 20px;">
            User list goes here...
          </p>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Compose Modal Logic
    const btnCompose = document.getElementById('btn-compose');
    const btnClose = document.getElementById('btn-close-compose');
    const composeModal = document.getElementById('compose-modal');

    if (btnCompose && btnClose && composeModal) {
      btnCompose.addEventListener('click', () => {
        composeModal.removeAttribute('hidden');
      });

      btnClose.addEventListener('click', () => {
        composeModal.setAttribute('hidden', 'true');
      });

      composeModal.addEventListener('click', (e) => {
        if (e.target === composeModal) {
          composeModal.setAttribute('hidden', 'true');
        }
      });
    }

    // Search Filter Logic for Conversations
    const chatSearch = document.getElementById('conv-search');
    const chatItems = document.querySelectorAll('.conversation-item');
    
    if (chatSearch) {
      chatSearch.addEventListener('input', (e) => {
        const term = e.target.value.toLowerCase();
        chatItems.forEach(item => {
          const name = item.querySelector('.conv-name').textContent.toLowerCase();
          item.style.display = name.includes(term) ? 'flex' : 'none';
        });
      });
    }

    // Auto-scroll chat history to bottom on load
    const chatHistory = document.getElementById('chat-history');
    if (chatHistory) {
        chatHistory.scrollTop = chatHistory.scrollHeight;
    }
  </script>
</main>