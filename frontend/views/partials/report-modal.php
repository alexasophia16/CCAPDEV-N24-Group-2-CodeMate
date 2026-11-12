<?php
// Shared "File a Report" pop-up, included at the bottom of every page that has Report buttons
// (Tutor Profile, discussion thread, Resources, Messages). Each button carries
// data-target-type and data-target-id; report-modal.js copies them into the hidden inputs.
// Pages that include this also load report-modal.css and report-modal.js.
?>
<div class="report-modal-backdrop" id="report-modal-backdrop" hidden>
  <div class="report-modal" role="dialog" aria-modal="true" aria-labelledby="report-modal-title">
    <h3 id="report-modal-title">File a Report</h3>
    <form method="POST" action="<?= e(url('store-report')) ?>">
      <input type="hidden" name="target_type" id="report-target-type" value="" />
      <input type="hidden" name="target_id" id="report-target-id" value="" />
      <!-- the page to come back to after submitting (checked on the server) -->
      <input type="hidden" name="back" value="<?= e($_SERVER['QUERY_STRING'] ?? '') ?>" />

      <p class="report-modal-target">You are reporting: <strong id="report-target-label"></strong></p>

      <div class="form-group">
        <label for="report-reason">Reason</label>
        <input type="text" name="reason" id="report-reason" maxlength="255"
               placeholder="e.g. Inappropriate content" required />
      </div>
      <div class="form-group">
        <label for="report-details">Details (optional)</label>
        <textarea name="details" id="report-details" rows="4" maxlength="1000"
                  placeholder="Describe the issue..."></textarea>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn-cancel" id="report-cancel">Cancel</button>
        <button type="submit" class="btn-submit">Submit report</button>
      </div>
    </form>
  </div>
</div>
