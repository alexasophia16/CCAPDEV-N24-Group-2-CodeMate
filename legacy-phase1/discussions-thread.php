<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CodeMate - Discussion Thread</title>

    <link rel="stylesheet" href="<?= url('styles/base.css') ?>" />
    <link rel="stylesheet" href="<?= url('styles/sidebar.css') ?>" />
    <link rel="stylesheet" href="<?= url('styles/topbar.css') ?>" />
    <link rel="stylesheet" href="<?= url('styles/discussions.css') ?>" />
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
        <button
          type="button"
          class="sidebar-collapse-btn"
          id="sidebar-collapse-btn"
          title="Toggle sidebar"
        >
          &#10094;
        </button>
      </div>

      <nav class="sidebar-nav">
        <ul class="sidebar-list">
          <li class="sidebar-item">
            <a href="<?= url('dashboard') ?>" class="sidebar-link">
              <span class="sidebar-icon">&#9679;</span>
              <span>Dashboard</span>
            </a>
          </li>

          <li class="sidebar-item">
            <a href="<?= url('find-tutor') ?>" class="sidebar-link">
              <span class="sidebar-icon">&#9678;</span>
              <span>Find a Tutor</span>
            </a>
          </li>

          <li class="sidebar-item">
            <a href="<?= url('my-bookings') ?>" class="sidebar-link">
              <span class="sidebar-icon">&#9636;</span>
              <span>My Bookings</span>
            </a>
          </li>

          <li class="sidebar-item">
            <a href="<?= url('discussions') ?>" class="sidebar-link active">
              <span class="sidebar-icon">&#9671;</span>
              <span>Discussions</span>
            </a>
          </li>

          <li class="sidebar-item">
            <a href="<?= url('messages') ?>" class="sidebar-link">
              <span class="sidebar-icon">&#9993;</span>
              <span>Messages</span>
              <span class="sidebar-badge" id="sidebar-messages-badge" hidden></span>
            </a>
          </li>

          <li class="sidebar-item">
            <a href="<?= url('reports') ?>" class="sidebar-link">
              <span class="sidebar-icon">&#9873;</span>
              <span>Reports</span>
            </a>
          </li>

          <li class="sidebar-item sidebar-item-admin" hidden>
            <a href="<?= url('admin-verify-tutors') ?>" class="sidebar-link">
              <span class="sidebar-icon">&#9670;</span>
              <span>Verify Tutors</span>
            </a>
          </li>

          <li class="sidebar-item sidebar-item-admin" hidden>
            <a href="<?= url('admin-course-management') ?>" class="sidebar-link">
              <span class="sidebar-icon">&#9670;</span>
              <span>Course Management</span>
            </a>
          </li>

          <li class="sidebar-item sidebar-item-admin" hidden>
            <a href="<?= url('admin-moderation') ?>" class="sidebar-link">
              <span class="sidebar-icon">&#9670;</span>
              <span>Content Moderation</span>
            </a>
          </li>

          <li class="sidebar-item sidebar-item-admin" hidden>
            <a href="<?= url('admin-reports') ?>" class="sidebar-link">
              <span class="sidebar-icon">&#9670;</span>
              <span>Reports</span>
            </a>
          </li>

          <li class="sidebar-item sidebar-item-admin" hidden>
            <a href="<?= url('admin-users') ?>" class="sidebar-link">
              <span class="sidebar-icon">&#9670;</span>
              <span>User Management</span>
            </a>
          </li>
        </ul>
      </nav>

      <div class="sidebar-foot">CCAPDEV &middot; Group 2<br />CodeMate prototype</div>
    </aside>

    <div class="main-column">
      <header class="topbar">
        <button type="button" class="topbar-hamburger" id="topbar-hamburger" aria-label="Open menu">
          &#9776;
        </button>

        <form class="topbar-search" role="search" id="topbar-search-form">
          <span class="topbar-search-icon">&#8981;</span>
          <input
            type="search"
            id="global-search"
            name="q"
            placeholder="Search tutors, subjects, discussions..."
          />
        </form>

        <div class="topbar-actions">
          <button type="button" class="icon-btn" id="btn-notifications" aria-label="Notifications">
            <span>&#128276;</span>
            <span class="icon-btn-badge" id="notifications-badge" hidden></span>
          </button>

          <button type="button" class="icon-btn" id="btn-bookmarks" aria-label="Saved tutors">
            <span>&#9733;</span>
          </button>

          <div class="user-menu">
            <button
              type="button"
              class="user-menu-trigger"
              id="user-menu-trigger"
              aria-haspopup="true"
              aria-expanded="false"
            >
              <span class="user-avatar" id="user-avatar"></span>
            </button>

            <div class="user-menu-dropdown" id="user-menu-dropdown" hidden>
              <div class="user-menu-identity">
                <span class="user-menu-dot" id="user-menu-dot"></span>
                <span class="user-menu-name-role" id="user-menu-name-role"></span>
              </div>
              <a href="<?= url('login') ?>" class="user-menu-item user-menu-signout" id="btn-signout"
                >Signout</a
              >
            </div>
          </div>
        </div>
      </header>

      <main class="discussions-content">
        <div class="thread-navigation">
          <a href="<?= url('discussions') ?>" class="btn-back">&larr; Back to all discussions</a>
        </div>

        <article class="thread-main-card">
          <div class="thread-card-top">
            <span class="thread-badge-blue"><?= e($thread['course']) ?></span>
          </div>
          <h2><?= e($thread['title']) ?></h2>
          <div class="thread-author" style="margin-bottom: 1rem;">
            <span class="author-name"><strong><?= e($thread['author']) ?></strong></span>
            <span class="author-role">&middot; <?= time_ago($thread['created_at']) ?></span>
          </div>
          <div class="thread-body">
            <?= nl2br(e($thread['content'])) ?>
          </div>
        </article>

        <section class="replies-section">
          <h3><?= count($replies) ?> Replies</h3>
          <div class="replies-list">
            <?php if (empty($replies)): ?>
              <p>No replies yet. Be the first to answer!</p>
            <?php else: ?>
              <?php foreach ($replies as $reply): ?>
                <div class="reply-card">
                  <div class="thread-author">
                    <span class="author-name"><strong><?= e($reply['author']) ?></strong></span>
                    <span class="author-role">&middot; <?= time_ago($reply['created_at']) ?></span>
                  </div>
                  <div class="reply-body" style="margin-top: 0.5rem;">
                    <?= nl2br(e($reply['content'])) ?>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </section>

        <section class="reply-composer-card">
          <h4>Leave a Reply</h4>
          <form action="<?= url('store-reply') ?>" method="POST">
            <input type="hidden" name="_csrf" value="<?= csrf_token() ?>" />
            <input type="hidden" name="thread_id" value="<?= $thread['id'] ?>" />
            
            <textarea
              name="content"
              rows="4"
              placeholder="Write your explanation or answer..."
              required
            ></textarea>
            <div class="composer-footer">
              <button type="submit" class="btn-submit">Post Reply</button>
            </div>
          </form>
        </section>
      </main>
    </div>

    <script src="<?= url('scripts/session.js') ?>"></script>
    <script src="<?= url('scripts/sidebar.js') ?>"></script>
    <script src="<?= url('scripts/topbar.js') ?>"></script>
    <script src="<?= url('scripts/discussions.js') ?>"></script>
  </body>
</html>
