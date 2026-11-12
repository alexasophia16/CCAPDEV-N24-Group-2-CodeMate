      <main class="role-page">
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
                      <span class="role-pill <?= $report['status'] === 'pending' ? 'pending' : ($report['status'] === 'dismissed' ? 'danger' : '') ?>">
                        <?= e(ucfirst($report['status'])) ?>
                      </span>
                    </td>
                    <td>
                      <form method="POST" action="<?= url('admin-report-update') ?>" style="display:inline;">
                        
                        <input type="hidden" name="report_id" value="<?= e($report['id']) ?>">
                        <select name="status" onchange="this.form.submit()" style="padding: 4px; border-radius: 4px; border: 1px solid var(--line);">
                          <?php foreach ($statuses as $status): ?>
                            <option value="<?= e($status) ?>" <?= $report['status'] === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                          <?php endforeach; ?>
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
