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
            <!-- REPORT BUTTON: Placed on the main discussion question -->
            <button type="button" class="btn-report-text btn-open-report-modal" style="margin-left: 10px; font-size: 12px; color: var(--ink-faint); cursor: pointer; border: none; background: none;" data-target-type="Discussion" data-target-id="<?= $thread['id'] ?>">Report</button>
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
                    <!--  REPORT BUTTON: Placed inside the loop for each individual reply -->
                    <button type="button" class="btn-report-text btn-open-report-modal" style="margin-left: 10px; font-size: 12px; color: var(--ink-faint); cursor: pointer; border: none; background: none;" data-target-type="Reply" data-target-id="<?= $reply['id'] ?>">Report</button>
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

<!-- Report Form Modal (Place at the bottom of the page or in a global footer) -->
<div class="modal-backdrop" id="report-modal-backdrop" hidden>
  <div class="report-modal">
    <h3>File a Report</h3>
    <form id="file-report-form" method="POST" action="<?= url('store-report') ?>">
      <input type="hidden" name="_csrf" value="<?= csrf_token() ?>" />
      
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
