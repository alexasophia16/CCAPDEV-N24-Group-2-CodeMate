<?php $canReport = in_array($currentUser['role'], ['tutee', 'tutor'], true); // admins moderate instead of reporting ?>
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
            <?php if ($canReport && (int) $thread['author_id'] !== (int) $currentUser['user_id']): ?>
              <button type="button" class="btn-report btn-open-report-modal" data-target-type="Discussion"
                      data-target-id="<?= (int) $thread['id'] ?>" data-target-label="<?= e('Discussion: ' . $thread['title']) ?>">Report</button>
            <?php endif; ?>
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
                    <?php if ($canReport && (int) $reply['author_id'] !== (int) $currentUser['user_id']): ?>
                      <button type="button" class="btn-report btn-open-report-modal" data-target-type="Reply"
                              data-target-id="<?= (int) $reply['id'] ?>" data-target-label="<?= e('Reply by ' . $reply['author']) ?>">Report</button>
                    <?php endif; ?>
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

<?php require APP_ROOT . '/frontend/views/partials/report-modal.php'; ?>
