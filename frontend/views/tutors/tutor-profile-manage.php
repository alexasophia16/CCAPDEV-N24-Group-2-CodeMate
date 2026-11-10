<main class="role-page">
  <div class="role-page-header">
    <div>
      <h1>My Tutor Profile</h1>
      <p>Manage the profile students see when choosing a tutor.</p>
    </div>
  </div>
  <div class="tutor-profile-grid" style="display: flex; gap: 24px; flex-wrap: wrap;">
    
   <div class="profile-card" style="flex: 2; min-width: 300px;">
      <h3 style="margin-top: 0; margin-bottom: 24px; font-size: 18px;">Profile Information</h3>
      <form method="POST" action="index.php?page=tutor-profile-manage" style="margin: 0;">
        <div style="margin-bottom: 20px;">
          <label style="display: block; font-size: 13px; margin-bottom: 8px; font-weight: 600;">Bio</label>
          <textarea name="bio" style="width: 100%; padding: 12px; border: 1px solid var(--line); background: transparent; color: inherit; border-radius: 8px; min-height: 120px; font-family: inherit; font-size: 14px;"><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea>
        </div>
        <div style="margin-bottom: 20px;">
          <label style="display: block; font-size: 13px; margin-bottom: 8px; font-weight: 600;">Experience</label>
          <textarea name="experience" style="width: 100%; padding: 12px; border: 1px solid var(--line); background: transparent; color: inherit; border-radius: 8px; min-height: 120px; font-family: inherit; font-size: 14px;"><?= htmlspecialchars($profile['experience'] ?? '') ?></textarea>
        </div>
        <div style="margin-bottom: 24px;">
          <label style="display: block; font-size: 13px; margin-bottom: 8px; font-weight: 600;">Subjects (comma separated)</label>
          <input type="text" name="subjects" value="<?= htmlspecialchars($profile['subjects'] ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--line); background: transparent; color: inherit; border-radius: 8px; font-size: 14px; box-sizing: border-box;">
        </div>
        <button type="submit" class="booking-btn primary">Save changes</button>
      </form>
    </div>

     <div class="info-card" style="flex: 1; min-width: 250px; height: fit-content;">
      <h3 style="margin-top: 0; margin-bottom: 24px; font-size: 18px;">Verification & Credibility</h3>
      <div style="margin-bottom: 20px;">
        <span class="status-pill status-confirmed">Verified</span>
      </div>
      <p style="font-size: 14px; margin: 0; line-height: 1.5;"><strong><?= htmlspecialchars($profile['rating'] ?? '4.8') ?>/5</strong> credibility · <?= htmlspecialchars($profile['completed_sessions'] ?? '32') ?> completed sessions</p>
    </div>

  </div>
</main>