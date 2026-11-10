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
          <button type="button" class="btn-file-report" id="btn-open-modal">+ File a report</button>
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

    <!-- Report Form Modal -->
    <div class="modal-backdrop" id="modal-backdrop" hidden>
      <div class="report-modal">
        <h3>File a Report</h3>
        <form id="file-report-form" method="POST" action="<?= url('store-report') ?>">
          <input type="hidden" name="_csrf" value="<?= csrf_token() ?>" />
          
          <div class="form-group">
            <label for="target-type">What are you reporting?</label>
            <select name="target_type" id="target-type" required>
              <?php foreach ($targetTypes as $type): ?>
                <option value="<?= e($type) ?>"><?= e($type) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="target-id">ID</label>
            <input type="number" name="target_id" id="target-id" min="1" placeholder="e.g. 3" required />
            <small>The number after <code>id=</code> in the page's address, e.g. a tutor profile or discussion thread.</small>
          </div>
          <div class="form-group">
            <label for="reason-input">Reason</label>
            <input type="text" name="reason" id="reason-input" maxlength="255"
                   placeholder="e.g. No-show for scheduled consultation" required />
          </div>
          <div class="form-group">
            <label for="details-input">Details (optional)</label>
            <textarea name="details" id="details-input" rows="4" maxlength="1000"
                      placeholder="Describe the issue..."></textarea>
          </div>
          <div class="modal-actions">
            <button type="button" class="btn-cancel" id="btn-close-modal">Cancel</button>
            <button type="submit" class="btn-submit">Submit</button>
          </div>
        </form>
      </div>
    </div>
