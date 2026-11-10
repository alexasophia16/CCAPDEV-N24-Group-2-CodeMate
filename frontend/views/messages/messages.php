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


  <div class="compose-modal-backdrop" id="compose-modal" hidden>
    <div class="compose-modal">
      <div class="compose-modal-header">
        <h3>New Message</h3>
        <button class="btn-close-modal" id="btn-close-compose" type="button">&times;</button>
      </div>
      <div class="compose-modal-body">
        <input type="text" class="compose-user-search" placeholder="Search users to message...">
        <div class="compose-user-list">

          <p style="text-align: center; color: #999; font-size: 12px; margin-top: 20px;">
            User list goes here...
          </p>
        </div>
      </div>
    </div>
  </div>


  <script>
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

      // close modal
      composeModal.addEventListener('click', (e) => {
        if (e.target === composeModal) {
          composeModal.setAttribute('hidden', 'true');
        }
      });
    }

    const chatSearch = document.getElementById('conv-search');
  const chatItems = document.querySelectorAll('.conversation-item');
  
  if (chatSearch) {
    chatSearch.addEventListener('input', (e) => {
      const term = e.target.value.toLowerCase();
      chatItems.forEach(item => {
        const name = item.querySelector('.conv-name').textContent.toLowerCase();
        // Toggle display based on whether the name matches the search term
        item.style.display = name.includes(term) ? 'flex' : 'none';
      });
    });
  }

  const userSearch = document.querySelector('.compose-user-search');
  const userItems = document.querySelectorAll('.compose-user-item');
  
  if (userSearch) {
    userSearch.addEventListener('input', (e) => {
      const term = e.target.value.toLowerCase();
      userItems.forEach(item => {
        const name = item.querySelector('.compose-user-name').textContent.toLowerCase();
        item.style.display = name.includes(term) ? 'flex' : 'none';
      });
    });
  }

  </script>

</main>
