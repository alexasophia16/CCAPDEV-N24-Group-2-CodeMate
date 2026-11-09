<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CodeMate - Messages</title>
    <link rel="stylesheet" href="/css/base.css" />
    <link rel="stylesheet" href="/css/sidebar.css" />
    <link rel="stylesheet" href="/css/topbar.css" />
    <link rel="stylesheet" href="/css/messages.css" />
  </head>
  <body class="app-layout">
    <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

    <aside class="sidebar" id="sidebar">
      <div class="sidebar-logo">
        <div class="sidebar-logo-mark">CM</div>
        <div class="sidebar-logo-text">
          <span class="sidebar-title">CodeMate</span>
          <span class="sidebar-subtitle">CCS - DLSU</span>
        </div>
        <button type="button" class="sidebar-collapse-btn" id="sidebar-collapse-btn" title="Toggle sidebar">&#10094;</button>
      </div>

      <nav class="sidebar-nav">
        <ul class="sidebar-list">
          <li class="sidebar-item"><a href="/dashboard" class="sidebar-link"><span class="sidebar-icon">&#9679;</span><span>Dashboard</span></a></li>
          <li class="sidebar-item"><a href="/find-tutor" class="sidebar-link"><span class="sidebar-icon">&#9678;</span><span>Find a Tutor</span></a></li>
          <li class="sidebar-item"><a href="/my-bookings" class="sidebar-link"><span class="sidebar-icon">&#9636;</span><span>My Bookings</span></a></li>
          <li class="sidebar-item"><a href="/discussions" class="sidebar-link"><span class="sidebar-icon">&#9671;</span><span>Discussions</span></a></li>
          <li class="sidebar-item"><a href="/messages" class="sidebar-link active"><span class="sidebar-icon">&#9993;</span><span>Messages</span><span class="sidebar-badge" id="sidebar-messages-badge" hidden></span></a></li>
          <li class="sidebar-item"><a href="/reports" class="sidebar-link"><span class="sidebar-icon">&#9873;</span><span>Reports</span></a></li>
        </ul>
      </nav>
      <div class="sidebar-foot">CCAPDEV &middot; Group 2<br />CodeMate prototype</div>
    </aside>

    <div class="main-column">
      <header class="topbar">
        <button type="button" class="topbar-hamburger" id="topbar-hamburger" aria-label="Open menu">&#9776;</button>
        <form class="topbar-search" role="search" id="topbar-search-form">
          <span class="topbar-search-icon">&#8981;</span>
          <input type="search" id="global-search" name="q" placeholder="Search tutors, subjects, discussions..." />
        </form>
        <div class="topbar-actions">
          <button type="button" class="icon-btn" id="btn-notifications" aria-label="Notifications">
            <span>&#128276;</span><span class="icon-btn-badge" id="notifications-badge" hidden></span>
          </button>
          <button type="button" class="icon-btn" id="btn-bookmarks" aria-label="Saved tutors"><span>&#9733;</span></button>
          <div class="user-menu">
            <button type="button" class="user-menu-trigger" id="user-menu-trigger" aria-haspopup="true" aria-expanded="false">
              <span class="user-avatar" id="user-avatar"></span>
            </button>
            <div class="user-menu-dropdown" id="user-menu-dropdown" hidden>
              <div class="user-menu-identity">
                <span class="user-menu-dot" id="user-menu-dot"></span>
                <span class="user-menu-name-role" id="user-menu-name-role"></span>
              </div>
              <a href="/login" class="user-menu-item user-menu-signout" id="btn-signout">Signout</a>
            </div>
          </div>
        </div>
      </header>

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
                        <a href="/messages?c=<?= $conv['conversation_id'] ?>" style="display: block; padding: 15px; border-bottom: 1px solid #eee; text-decoration: none; color: #333; background: <?= ($activeConvId == $conv['conversation_id']) ? '#f8fafc' : 'transparent' ?>;">
                            <strong><?= htmlspecialchars($conv['other_user_name']) ?></strong>
                            <div style="font-size: 0.8em; color: #888; margin-top: 4px;">
                                <?= $conv['last_message_time'] ? date('M d, h:i A', strtotime($conv['last_message_time'])) : 'New Conversation' ?>
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
                    <div style="text-align: center; color: #999; margin-top: 40px;">Select a conversation to start messaging.</div>
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
    </div>

    <script src="/js/session.js"></script>
    <script src="/js/sidebar.js"></script>
    <script src="/js/topbar.js"></script>
  </body>
</html>