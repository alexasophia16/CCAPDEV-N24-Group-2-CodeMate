<?php // Variables come from AdminUserController::reports(): $reports, $counts, $status (current filter) ?>
      <main class="role-page">
        <div class="role-page-header">
          <div>
            <h1>Reports</h1>
            <p>Reports filed by tutees and tutors. <strong>Remove / Deactivate</strong> takes the reported post, resource or account down and resolves the report; <strong>Dismiss</strong> leaves it up. Closed reports leave the Pending list; use Show to see them again.</p>
          </div>
          <form method="GET" action="index.php" class="role-field">
            <input type="hidden" name="page" value="admin-reports" />
            <label for="report-filter">Show</label>
            <select name="status" id="report-filter" onchange="this.form.submit()">
              <?php foreach (['pending' => 'Pending', 'resolved' => 'Resolved', 'dismissed' => 'Dismissed', 'all' => 'All'] as $value => $label): ?>
                <option value="<?= $value ?>" <?= $status === $value ? 'selected' : '' ?>>
                  <?= $label ?><?= $value === 'all' ? '' : ' (' . (int) $counts[$value] . ')' ?>
                </option>
              <?php endforeach; ?>
            </select>
            <noscript><button type="submit" class="role-btn secondary">Show</button></noscript>
          </form>
        </div>

        <div class="role-table-wrap">
          <table class="role-table">
            <thead>
              <tr>
                <th>Reporter</th>
                <th>Reported</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($reports)): ?>
                <tr>
                  <td colspan="5" style="text-align: center; padding: 24px; color: var(--ink-faint);">
                    <?= $status === 'pending' ? 'No reports waiting for review.' : 'No reports to show.' ?>
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($reports as $report): ?>
                  <tr>
                    <td>
                      <?= e($report['reporter']) ?><br>
                      <small style="color: var(--ink-faint);"><?= e(time_ago($report['created_at'])) ?></small>
                    </td>
                    <td>
                      <?= e($report['target']) ?>
                      <?php if ($report['link']): ?>
                        <br><a href="<?= e(link_url($report['link'])) ?>" target="_blank" rel="noopener">View</a>
                      <?php endif; ?>
                    </td>
                    <td>
                      <strong><?= e($report['reason']) ?></strong>
                      <?php if ($report['details'] !== null && $report['details'] !== ''): ?>
                        <br><small style="color: var(--ink-faint);"><?= e($report['details']) ?></small>
                      <?php endif; ?>
                    </td>
                    <td>
                      <span class="role-pill <?= $report['status'] === 'pending' ? 'pending' : ($report['status'] === 'dismissed' ? 'danger' : '') ?>">
                        <?= e(ucfirst($report['status'])) ?>
                      </span>
                      <?php if ($report['status'] !== 'pending' && $report['resolved_by_name']): ?>
                        <br><small style="color: var(--ink-faint);">by <?= e($report['resolved_by_name']) ?>, <?= e(time_ago($report['resolved_at'])) ?></small>
                      <?php endif; ?>
                    </td>
                    <td>
                      <form method="POST" action="<?= e(url('admin-report-update')) ?>" class="role-actions">
                        <input type="hidden" name="report_id" value="<?= (int) $report['id'] ?>">
                        <input type="hidden" name="filter" value="<?= e($status) ?>">
                        <?php if ($report['status'] === 'pending'): ?>
                          <?php
                            // Resolve also acts on what was reported (AdminUserController::resolveReport)
                            $action = [
                                'Discussion' => ['Remove post', 'Remove this discussion from the site and resolve the report?'],
                                'Reply'      => ['Remove reply', 'Remove this reply from the site and resolve the report?'],
                                'Resource'   => ['Remove resource', 'Hide this resource from everyone and resolve the report?'],
                                'User'       => ['Deactivate user', 'Deactivate this account? They will not be able to log in.'],
                            ][$report['target_type']] ?? ['Resolve', 'Mark this report as resolved?'];
                          ?>
                          <button type="submit" name="status" value="resolved" class="role-btn danger"
                                  onclick="return confirm('<?= e($action[1]) ?>')"><?= e($action[0]) ?></button>
                          <button type="submit" name="status" value="dismissed" class="role-btn secondary" title="Nothing wrong was found; leave it as it is">Dismiss</button>
                        <?php else: ?>
                          <button type="submit" name="status" value="pending" class="role-btn secondary">Reopen</button>
                        <?php endif; ?>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </main>
