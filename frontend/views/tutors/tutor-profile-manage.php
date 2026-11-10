<main class="role-page">
  <div class="role-page-header">
    <div>
      <h1>My Tutor Profile</h1>
      <p>Manage the profile students see when choosing a tutor.</p>
    </div>
  </div>
  <div class="tutor-profile-grid" style="display: flex; gap: 24px; flex-wrap: wrap;">
    <div class="tutor-profile-card" style="flex: 2; min-width: 300px; background: white; padding: 28px; border-radius: 8px; border: 1px solid #e5e7eb; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
      <h3 style="margin-top: 0; margin-bottom: 24px; font-size: 18px; color: #111827;">Profile Information</h3>
      
      <!-- FIXED FORM ACTION -->
      <form method="POST" action="index.php?page=tutor-profile-manage" style="margin: 0;">
        <div style="margin-bottom: 20px;">
          <label style="display: block; font-size: 13px; margin-bottom: 8px; font-weight: 600; color: #374151;">Bio</label>
          <textarea name="bio" style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; min-height: 120px; font-family: inherit; font-size: 14px;"><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea>
        </div>
        <div style="margin-bottom: 20px;">
          <label style="display: block; font-size: 13px; margin-bottom: 8px; font-weight: 600; color: #374151;">Experience</label>
          <textarea name="experience" style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; min-height: 120px; font-family: inherit; font-size: 14px;"><?= htmlspecialchars($profile['experience'] ?? '') ?></textarea>
        </div>
        <div style="margin-bottom: 24px;">
          <label style="display: block; font-size: 13px; margin-bottom: 8px; font-weight: 600; color: #374151;">Subjects (comma separated)</label>
          <input type="text" name="subjects" value="<?= htmlspecialchars($profile['subjects'] ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; box-sizing: border-box;">
        </div>
        <button type="submit" style="background: #1f4e3d; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px;">Save changes</button>
      </form>

    </div>
    <div class="tutor-cred-card" style="flex: 1; min-width: 250px; background: white; padding: 28px; border-radius: 8px; border: 1px solid #e5e7eb; height: fit-content; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
      <h3 style="margin-top: 0; margin-bottom: 24px; font-size: 18px; color: #111827;">Verification & Credibility</h3>
      <div style="margin-bottom: 20px;">
        <span style="background: #d1fae5; color: #065f46; padding: 6px 14px; border-radius: 16px; font-size: 13px; font-weight: 600; display: inline-block;">Verified</span>
      </div>
      <p style="color: #4b5563; font-size: 14px; margin: 0; line-height: 1.5;"><strong><?= htmlspecialchars($profile['rating'] ?? '4.8') ?>/5</strong> credibility · <?= htmlspecialchars($profile['completed_sessions'] ?? '32') ?> completed sessions</p>
    </div>
  </div>
</main>