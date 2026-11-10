<div class="role-field">
      <label>Bio</label>
      <textarea name="bio" id="bio" rows="4" required><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea>
  </div>
  
  <div class="role-field">
      <label>Experience</label>
      <textarea name="experience" id="experience" rows="4" required><?= htmlspecialchars($profile['experience'] ?? '') ?></textarea>
  </div>
  
  <!-- Courses managed via DB relationships; text input hidden or made read-only for safety unless you add tutor_courses pivot logic -->
  
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
