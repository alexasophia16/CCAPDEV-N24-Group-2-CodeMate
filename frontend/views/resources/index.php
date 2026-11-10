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
                   <span class="role-pill" style="background: #fef08a; color: #854d0e; margin-left: 8px;">Pending</span>
                <?php endif; ?>
                <h2 style="margin-top:8px"><?= e($res['title']) ?></h2>
                <p><?= e($res['type']) ?> &middot; <?= e($res['author']) ?></p>
                <button class="role-btn secondary" style="margin-top:10px">Preview</button>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <?php if ($currentUser['role'] === 'tutor'): ?>
        <div class="role-card" id="upload-card" hidden style="margin-top: 18px">
          <h2>Upload Resource</h2>
          <form id="upload-form" method="POST" action="<?= url('store-resource') ?>" enctype="multipart/form-data">
            <input type="hidden" name="_csrf" value="<?= csrf_token() ?>" />
            <div class="role-field"><label>Title</label><input name="title" id="title" required /></div>
            <div class="role-field"><label>Course</label><input name="course" id="course" required /></div>
            <div class="role-field">
              <label>Type</label>
              <select name="type" id="type">
                <option value="PDF">PDF</option>
                <option value="Video">Video</option>
                <option value="Notes">Notes</option>
              </select>
            </div>
            <div class="role-field">
              <label>File (front-end prototype)</label><input type="file" name="file" />
            </div>
            <button type="submit" class="role-btn primary">Submit for moderation</button>
          </form>
        </div>
        <?php endif; ?>
      </main>
