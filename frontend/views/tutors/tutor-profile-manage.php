<main class="role-page">
  <div class="role-page-header">
    <div>
      <h1>My Tutor Profile</h1>
      <p>Manage the profile students see when choosing a tutor.</p>
    </div>
  </div>
  <div class="role-grid">
    <form class="role-card" id="profile-form" method="POST" action="/tutor-profile-manage">
      <h2>Profile Information</h2>
      
      <div class="role-field">
          <label>Bio</label>
          <textarea name="bio" id="bio" rows="4" required><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea>
      </div>
      
      <div class="role-field">
          <label>Experience</label>
          <textarea name="experience" id="experience" rows="4" required><?= htmlspecialchars($profile['experience'] ?? '') ?></textarea>
      </div>
      
      <button type="submit" class="role-btn primary">Save changes</button>
    </form>
    
    <div class="role-card">
      <h2>Verification & Credibility</h2>
      <div id="credibility" style="margin-top: 15px;">
          <p><strong>Status:</strong> 
              <span style="color: <?= ($profile['verification_status'] ?? '') === 'verified' ? '#065f46' : '#92400e' ?>; font-weight: bold;">
                  <?= ucfirst(htmlspecialchars($profile['verification_status'] ?? 'pending')) ?>
              </span>
          </p>
          <p style="color: #666; font-size: 0.9em; margin-top: 10px;">
              Verified tutors appear higher in search results and can accept bookings without administrative review.
          </p>
      </div>
    </div>
  </div>
</main>