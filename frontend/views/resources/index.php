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
            <h1>Study Resources</h1>
            <p>Student-made tutoring materials organized by course.</p>
          </div>
          <?php if ($currentUser['role'] === 'tutor'): ?>
            <button class="role-btn primary" id="upload-btn">Upload Resource</button>
          <?php endif; ?>
        </div>
        
        <div class="role-grid" id="resource-list">
          <?php if (empty($resources)): ?>
            <p style="color: var(--ink-faint);">No resources available right now.</p>
          <?php else: ?>
            <?php foreach ($resources as $res): ?>
              <div class="role-card">
                <span class="role-pill"><?= e($res['course']) ?></span>
                <?php if ($res['status'] === 'pending'): ?>
                   <span class="role-pill pending" style="margin-left: 8px;">Pending</span>
                <?php elseif ($res['status'] === 'rejected'): ?>
                   <span class="role-pill danger" style="margin-left: 8px;">Rejected</span>
                <?php endif; ?>
                <h2 style="margin-top:8px"><?= e($res['title']) ?></h2>
                <p><?= e($res['type']) ?> &middot; <?= e($res['author']) ?></p>
                <?php if ($res['description'] !== null && $res['description'] !== ''): ?>
                  <p><?= e($res['description']) ?></p>
                <?php endif; ?>
                <a class="role-btn secondary" style="margin-top:10px; display:inline-block; text-decoration:none"
                   href="<?= e($res['url']) ?>" target="_blank" rel="noopener noreferrer">Open</a>
                    <button type="button" class="role-btn btn-open-report-modal" style="margin-top:10px; background: transparent; color: var(--ink-faint); border: 1px solid #ccc;" data-target-type="Resource" data-target-id="<?= $res['id'] ?>">Report</button>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <?php if ($currentUser['role'] === 'tutor'): ?>
        <div class="role-card" id="upload-card" hidden style="margin-top: 18px">
          <h2>Upload Resource</h2>
          <form id="upload-form" method="POST" action="<?= url('store-resource') ?>">
            
            <div class="role-field"><label for="title">Title</label><input name="title" id="title" maxlength="200" required /></div>
            <div class="role-field">
              <label for="course">Course</label>
              <select name="course_id" id="course" required>
                <option value="">Choose a course</option>
                <?php foreach ($courses as $course): ?>
                  <option value="<?= (int) $course['course_id'] ?>"><?= e($course['course_code'] . ' - ' . $course['course_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="role-field">
              <label for="type">Type</label>
              <select name="type" id="type">
                <?php foreach ($types as $type): ?>
                  <option value="<?= e($type) ?>"><?= e($type) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="role-field">
              <label for="url">Link to the file or page</label>
              <input type="url" name="url" id="url" maxlength="500" placeholder="https://drive.google.com/..." required />
            </div>
            <div class="role-field">
              <label for="description">Description (optional)</label>
              <textarea name="description" id="description" rows="3" maxlength="1000"></textarea>
            </div>
            <button type="submit" class="role-btn primary">Submit for moderation</button>
          </form>
        </div>
        <?php endif; ?>
      </main>

<!-- Report Form Modal  -->
<div class="modal-backdrop" id="report-modal-backdrop" hidden>
  <div class="report-modal">
    <h3>File a Report</h3>
    <form id="file-report-form" method="POST" action="<?= url('store-report') ?>">
      
      <!-- HIDDEN INPUTS (Auto-filled by JS) -->
      <input type="hidden" name="target_id" id="report-target-id" value="" required />
      <input type="hidden" name="target_type" id="report-target-type" value="" required />
      
      <div class="form-group">
        <label>You are reporting a <strong id="display-target-type">...</strong></label>
      </div>
      
      <div class="form-group">
        <label for="reason-input">Reason</label>
        <input type="text" name="reason" id="reason-input" maxlength="255" placeholder="e.g. Inappropriate content" required />
      </div>
      <div class="form-group">
        <label for="details-input">Details (optional)</label>
        <textarea name="details" id="details-input" rows="4" maxlength="1000" placeholder="Describe the issue..."></textarea>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn-cancel" id="btn-close-report-modal">Cancel</button>
        <button type="submit" class="btn-submit">Submit</button>
      </div>
    </form>
  </div>
</div>

<script>
// Find all report buttons on the page
const reportButtons = document.querySelectorAll('.btn-open-report-modal');
const reportModal = document.getElementById('report-modal-backdrop');
const closeReportBtn = document.getElementById('btn-close-report-modal');

// The hidden inputs inside the form
const inputTargetId = document.getElementById('report-target-id');
const inputTargetType = document.getElementById('report-target-type');
const displayTargetType = document.getElementById('display-target-type');

// Attach click event to every report button
reportButtons.forEach(button => {
  button.addEventListener('click', (e) => {
    // Grab the ID and Type from the button that was clicked
    const targetId = button.getAttribute('data-target-id');
    const targetType = button.getAttribute('data-target-type');
    
    // Inject them into the hidden form inputs
    inputTargetId.value = targetId;
    inputTargetType.value = targetType;
    displayTargetType.textContent = targetType;
    
    // Show the modal
    reportModal.removeAttribute('hidden');
  });
});

// Close modal logic
if (closeReportBtn) {
  closeReportBtn.addEventListener('click', () => {
    reportModal.setAttribute('hidden', 'true');
  });
}
reportModal.addEventListener('click', (e) => {
  if (e.target === reportModal) {
    reportModal.setAttribute('hidden', 'true');
  }
});
</script>
