

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
            <h1>Content Moderation</h1>
            <p>Review flagged discussions, replies, and student-made resources.</p>
          </div>
          <div class="role-field">
            <form method="GET" action="<?= url('admin-moderation') ?>" id="filter-form">
              <label for="moderation-filter" style="margin-right: 8px;">Show</label>
              <select name="filter" id="moderation-filter" onchange="this.form.submit()">
                <option value="pending" <?= ($filter ?? 'pending') === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="kept" <?= ($filter ?? '') === 'kept' ? 'selected' : '' ?>>Kept / Approved</option>
                <option value="hidden" <?= ($filter ?? '') === 'hidden' ? 'selected' : '' ?>>Hidden</option>
                <option value="all" <?= ($filter ?? '') === 'all' ? 'selected' : '' ?>>All</option>
              </select>
            </form>
          </div>
        </div>
        
        <div id="items">
          <?php if (empty($items)): ?>
            <div class="role-card">Nothing to show here.</div>
          <?php else: ?>
            <?php foreach ($items as $x): ?>
              <?php 
                $statusLabel = ['pending' => 'Pending', 'kept' => 'Kept', 'hidden' => 'Hidden'][$x['status']] ?? 'Unknown';
                $statusClass = ['pending' => 'pending', 'kept' => '', 'hidden' => 'danger'][$x['status']] ?? '';
                $keepLabel   = ($x['type'] === 'Resource') ? 'Approve' : 'Keep';
              ?>
              <div class="role-card" style="margin-bottom: 10px">
                  <span class="role-pill"><?= e($x['type']) ?></span>
                  <span class="role-pill <?= $statusClass ?>"><?= $statusLabel ?></span>
                  
                  <h2 style="margin-top: 8px"><?= e($x['title'] ?? '') ?></h2>
                  <p><?= e($x['content'] ?? '') ?></p>
                  
                  <p style="margin-top: 8px">
                      <small><?= e($x['course'] ?? 'N/A') ?> &middot; by <?= e($x['author'] ?? 'Unknown') ?></small>
                  </p>
                  
                  <?php if (!empty($x['reason'])): ?>
                  <p style="margin-top: 4px">
                      <small>
                          <strong>Flagged:</strong> <?= e($x['reason']) ?> 
                          &middot; <?= e($x['flaggedBy']) ?> 
                          &middot; <?= e($x['flaggedAt']) ?>
                      </small>
                  </p>
                  <?php endif; ?>
                  
                  <div class="role-actions" style="margin-top: 10px">
                      <form method="POST" action="<?= url('admin-moderation/update') ?>" style="display:inline;">
                          <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                          <input type="hidden" name="item_id" value="<?= e($x['id']) ?>">
                          <input type="hidden" name="item_type" value="<?= e($x['type']) ?>">
                          
                          <?php if ($x['status'] === 'pending'): ?>
                              <button type="submit" name="status" value="kept" class="role-btn primary"><?= $keepLabel ?></button>
                              <button type="submit" name="status" value="hidden" class="role-btn danger">Hide</button>
                          <?php else: ?>
                              <button type="submit" name="status" value="pending" class="role-btn secondary">Undo</button>
                          <?php endif; ?>
                      </form>
                  </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </main>
    
