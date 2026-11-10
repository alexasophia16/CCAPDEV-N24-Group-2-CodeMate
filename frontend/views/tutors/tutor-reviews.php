<main class="role-page">
  <div class="role-page-header">
    <div>
      <h1>Reviews</h1>
      <p>Feedback submitted after completed tutoring sessions.</p>
    </div>
  </div>
  
  <div class="role-card" id="summary" style="display: flex; gap: 20px; align-items: center; padding: 20px;">
      <div style="font-size: 2.5em; font-weight: bold; color: #f59e0b;">
          <?= $averageRating > 0 ? number_format($averageRating, 1) : '0.0' ?> <span style="font-size: 0.6em;">&#9733;</span>
      </div>
      <div>
          <h3 style="margin: 0; font-size: 1.2em;">Average Rating</h3>
          <p style="margin: 0; color: #666;">Based on <?= $totalReviews ?> student review<?= $totalReviews !== 1 ? 's' : '' ?></p>
      </div>
  </div>

  <div id="review-list" style="margin-top: 24px;">
      <?php if (empty($reviews)): ?>
          <div class="role-card" style="text-align: center; color: #666; padding: 30px;">
              No reviews yet. Complete more sessions to receive feedback!
          </div>
      <?php else: ?>
          <?php foreach ($reviews as $review): ?>
              <div class="role-card" style="margin-bottom: 16px; padding: 20px;">
                  <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                      <div>
                          <h4 style="margin: 0; font-size: 1.1em;"><?= htmlspecialchars($review['reviewer']) ?></h4>
                          <small style="color: #666;"><?= htmlspecialchars($review['course_name']) ?> &middot; <?= date('M d, Y', strtotime($review['created_at'])) ?></small>
                      </div>
                      <div style="color: #f59e0b; font-weight: bold;">
                          <?= htmlspecialchars($review['rating']) ?> / 5.0
                      </div>
                  </div>
                  <p style="margin: 0; line-height: 1.5; color: #333;">
                      <?= nl2br(htmlspecialchars($review['comment'])) ?>
                  </p>
              </div>
          <?php endforeach; ?>
      <?php endif; ?>
  </div>
</main>