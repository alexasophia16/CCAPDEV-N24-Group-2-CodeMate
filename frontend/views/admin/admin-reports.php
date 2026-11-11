
      
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
                      <form method="POST" action="<?= url('admin-reports/update') ?>" style="display:inline;">
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
   
