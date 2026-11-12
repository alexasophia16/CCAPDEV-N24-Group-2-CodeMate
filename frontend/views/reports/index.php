<main class="reports-layout">
        
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

        <div class="reports-header-row">
          <div>
            <h1>Reports</h1>
            <p>Review reports submitted to the moderation team.</p>
          </div>
          
        </div>

        <div class="reports-card">
          <table class="reports-table">
            <thead>
              <tr>
                <th>REPORTER</th>
                <th>TARGET</th>
                <th>REASON</th>
                <th>STATUS</th>
              </tr>
            </thead>
            <tbody id="rows">
              <?php if (empty($reports)): ?>
                <tr>
                  <td colspan="4" style="text-align: center; color: var(--ink-faint); padding: 32px">
                    You haven't submitted any reports yet.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($reports as $report): ?>
                  <tr>
                    <td><?= e($report['reporter']) ?></td>
                    <td><?= e($report['target']) ?></td>
                    <td>
                      <?= e($report['reason']) ?>
                      <?php if ($report['details'] !== null && $report['details'] !== ''): ?>
                        <br /><small><?= e($report['details']) ?></small>
                      <?php endif; ?>
                    </td>
                    <td>
                      <span class="role-pill <?= $report['status'] === 'pending' ? 'pending' : ($report['status'] === 'dismissed' ? 'danger' : '') ?>">
                        <?= e(ucfirst($report['status'])) ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </main>
