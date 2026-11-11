<main class="role-page">
  <div class="role-page-header">
    <div>
      <h1>My Tutor Profile</h1>
      <p>Manage the profile students see when choosing a tutor.</p>
    </div>
  </div>
  
  <div class="role-grid">
    <form class="role-card" method="POST" action="index.php?page=tutor-profile-manage" id="profile-form">
      <h2>Profile Information</h2>
      
      <div class="role-field">
        <label>Bio</label>
        <textarea name="bio" id="bio" style="resize:none;"><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea>
      </div>
      
      <div class="role-field">
        <label>Experience</label>
        <textarea name="experience" id="experience" style="resize:none;"><?= htmlspecialchars($profile['experience'] ?? '') ?></textarea>
      </div>
      
      <div class="role-field">
        <label>Subjects (comma separated)</label>
        <input type="text" name="subjects" id="subjects" value="<?= htmlspecialchars($profile['subjects'] ?? '') ?>" />
      </div>
      
      <button type="submit" class="role-btn primary">Save changes</button>
    </form>
    
    <div class="role-card">
      <h2>Verification & Credibility</h2>
      <div id="credibility">
        <div style="margin-bottom: 20px;">
          <span class="status-pill status-confirmed">Verified</span>
        </div>
        <p style="font-size: 14px; margin: 0; line-height: 1.5; color: var(--ink);">
          <strong><?= htmlspecialchars($profile['rating'] ?? '4.8') ?>/5</strong> credibility · <?= htmlspecialchars($profile['completed_sessions'] ?? '32') ?> completed sessions
        </p>
      </div>
    </div>
  </div>
</main>