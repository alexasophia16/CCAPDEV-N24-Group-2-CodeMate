<?php
$searchPage = in_array($activePage, ['discussions', 'discussions-thread'], true) ? 'discussions' : 'find-tutor';
?>
<header class="topbar">
  <button type="button" class="topbar-hamburger" id="topbar-hamburger" aria-label="Open menu">
    &#9776;
  </button>

  <form class="topbar-search" role="search" method="get" action="index.php">
    <input type="hidden" name="page" value="<?= e($searchPage) ?>" />
    <span class="topbar-search-icon">&#8981;</span>
    <input
      type="search"
      id="global-search"
      name="q"
      value="<?= e($_GET['q'] ?? '') ?>"
      placeholder="Search tutors, subjects, discussions..."
    />
  </form>

  <div class="topbar-actions">
    <div class="notif-menu">
      <button
        type="button"
        class="icon-btn"
        id="btn-notifications"
        aria-label="Notifications"
        aria-haspopup="true"
        aria-expanded="false"
      >
        <span>&#128276;</span>
        <span class="icon-btn-badge" <?= $unreadNotifications ? '' : 'hidden' ?>></span>
      </button>

      <div class="notif-panel" id="notif-panel" hidden>
        <div class="notif-header">
          <span>Notifications</span>
          <?php if ($unreadNotifications): ?>
            <form method="post" action="<?= e(url('notifications-read-all')) ?>">
              
              <input type="hidden" name="return_page" value="<?= e($activePage) ?>" />
              <button type="submit" class="notif-mark-all">Mark all as read</button>
            </form>
          <?php endif; ?>
        </div>

        <?php if ($notifications): ?>
          <ul class="notif-list">
            <?php foreach ($notifications as $notification): ?>
              <li>
                <form method="post" action="<?= e(url('notification-open')) ?>">
                  
                  <input type="hidden" name="notification_id" value="<?= (int) $notification['notification_id'] ?>" />
                  <input type="hidden" name="return_page" value="<?= e($activePage) ?>" />
                  <button type="submit" class="notif-item<?= $notification['is_read'] ? '' : ' unread' ?>">
                    <span class="notif-text"><?= e($notification['message']) ?></span>
                    <span class="notif-time"><?= e(time_ago($notification['created_at'])) ?></span>
                  </button>
                </form>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="notif-empty">No notifications yet.</p>
        <?php endif; ?>
      </div>
    </div>

    <button type="button" class="icon-btn" id="btn-theme" aria-label="Toggle dark mode">
      <span>&#127769;</span>
    </button>

    <div class="user-menu">
      <button
        type="button"
        class="user-menu-trigger"
        id="user-menu-trigger"
        aria-haspopup="true"
        aria-expanded="false"
      >
        <span class="user-avatar"><?= e(initials($currentUser['full_name'])) ?></span>
      </button>

      <div class="user-menu-dropdown" id="user-menu-dropdown" hidden>
        <div class="user-menu-identity">
          <span class="user-menu-dot"></span>
          <span class="user-menu-name-role">
            <?= e($currentUser['full_name']) ?> - <?= e(role_label($currentUser['role'])) ?>
          </span>
        </div>
        <form method="post" action="<?= e(url('logout')) ?>">
          
          <button type="submit" class="user-menu-item user-menu-signout">Sign out</button>
        </form>
      </div>
    </div>
  </div>
</header>
