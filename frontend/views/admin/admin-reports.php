
    
    <div class="main-column">
      <header class="topbar">
        <button type="button" class="topbar-hamburger" id="topbar-hamburger" aria-label="Open menu">&#9776;</button>
        <form class="topbar-search" role="search" id="topbar-search-form" action="<?= url('search') ?>" method="GET">
          <span class="topbar-search-icon">&#8981;</span>
          <input type="search" id="global-search" name="q" placeholder="Search tutors, subjects, discussions..." />
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
            <button type="button" class="user-menu-trigger" id="user-menu-trigger" aria-haspopup="true" aria-expanded="false">
              <span class="user-avatar" id="user-avatar"></span>
            </button>
            <div class="user-menu-dropdown" id="user-menu-dropdown" hidden>
              <div class="user-menu-identity">
                <span class="user-menu-dot" id="user-menu-dot"></span>
                <span class="user-menu-name-role" id="user-menu-name-role"></span>
              </div>
              <a href="<?= url('logout') ?>" class="user-menu-item user-menu-signout" id="btn-signout">Signout</a>
            </div>
          </div>
        </div>
      </header>
      
      <main class="role-page">
        <?php if (!empty($error)): ?>
            <div class="alert alert-error" style="margin-bottom: 20px; padding: 12px; background: #fee2e2; color: #991b1b; border-radius: 6px;">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success" style="margin-bottom: 20px; padding: 12px; background: #dcfce7; color: #166534; border-radius: 6px;">
                <?= e($success) ?>
            </div>
        <?php endif; ?>

        <div class="role-page-header">
          <div>
            <h1>Reports</h1>
            <p>Review reports submitted by CodeMate users.</p>
          </div>
        </div>
        
        <div class="role-table-wrap">
          <table class="role-table">
            <thead>
              <tr>
                <th>Reporter</th>
                <th>Target</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="rows">
              <?php if (empty($reports)): ?>
                <tr>
                  <td colspan="5" style="text-align: center; padding: 24px; color: var(--ink-faint);">
                    No reports to review at this time.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($reports as $report): ?>
                  <tr>
                    <td><?= e($report['reporter']) ?></td>
                    <td><?= e($report['target']) ?></td>
                    <td>
                      <strong><?= e($report['reason']) ?></strong><br>
                      <small style="color: var(--ink-faint);"><?= e($report['details'] ?? '') ?></small>
                    </td>
                    <td>
                      <span class="role-pill <?= $report['status'] === 'pending' ? 'pending' : '' ?>">
                        <?= e(ucfirst($report['status'])) ?>
                      </span>
                    </td>
                    <td>
                      <form method="POST" action="<?= url('admin/update-report') ?>" style="display:inline;">
                        <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                        <input type="hidden" name="report_id" value="<?= $report['id'] ?>">
                        <select name="status" onchange="this.form.submit()" style="padding: 4px; border-radius: 4px; border: 1px solid var(--border-color);">
                          <option value="pending" <?= $report['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                          <option value="resolved" <?= $report['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                          <option value="dismissed" <?= $report['status'] === 'dismissed' ? 'selected' : '' ?>>Dismissed</option>
                        </select>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </main>
    </div>
    
   
  </body>
</html>
