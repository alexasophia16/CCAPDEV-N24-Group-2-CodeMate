      <main class="discussions-content">
        <div class="discussions-header-row">
          <div>
            <h1>Discussions</h1>
            <p class="discussions-subtitle">Ask or Answer a Question.</p>
          </div>
          <button type="button" class="btn-create-post" id="btn-toggle-modal">
            + Ask Question
          </button>
        </div>

        <div class="course-tabs-wrapper">
          <a href="<?= url('discussions', ['course' => 'ALL']) ?>" 
          class="course-tab <?= $selectedFilter === 'ALL' ? 'active' : '' ?>">All Subjects</a>

          <?php foreach ($courses as $course): ?>
          <a href="<?= url('discussions', ['course' => $course['course_code']]) ?>" 
          class="course-tab <?= $selectedFilter === $course['course_code'] ? 'active' : '' ?>">
          <?= e($course['course_code']) ?>
          </a>
          <?php endforeach; ?>
        </div>

        <div class="threads-list" id="threads-container">
          <?php if (empty($threads)): ?>
            <p class="discussions-subtitle">No discussions found.</p>
          <?php else: ?>
            <?php foreach ($threads as $thread): ?>
              <div class="thread-preview-card">
                <div class="thread-card-top">
                  <span class="thread-badge-blue"><?= e($thread['course']) ?></span>
                </div>
                <div class="thread-card-main">
                  <h3 class="thread-title">
                    <a href="<?= url('discussions-thread', ['id' => $thread['id']]) ?>"><?= e($thread['title']) ?></a>
                  </h3>
                  <p class="thread-snippet"><?= e(mb_strimwidth($thread['content'], 0, 140, '...')) ?></p>
                  <div class="thread-author">
                    <div class="thread-avatar"><?= e(initials($thread['author'])) ?></div>
                    <div>
                      <span class="author-name"><?= e($thread['author']) ?></span>
                      <span class="author-role"><?= e(role_label($thread['authorRole'])) ?> &middot; <?= time_ago($thread['created_at']) ?></span>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </main>

    <div class="modal-overlay hidden" id="post-modal">
      <div class="modal-box">
        <div class="modal-top">
          <h2>Post a Discussion Question</h2>
          <button type="button" class="btn-close-modal" id="btn-close-modal">&times;</button>
        </div>
        <form id="new-post-form" action="<?= url('store-discussion') ?>" method="POST">
    
    <input type="hidden" name="_csrf" value="<?= csrf_token() ?>" />
    
    <div class="form-group">
        <label for="post-course">Course Subject</label>
        <select id="post-course" name="course" required>
            <?php foreach ($courses as $course): ?>
                <option value="<?= e($course['course_code']) ?>"><?= e($course['course_code']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="form-group">
      <label for="post-title">Question Title</label>
      <input type="text" id="post-title" name="title" placeholder="What is your question?" required />
    </div>

    <div class="form-group">
      <label for="post-content">Details</label>
      <textarea id="post-content" name="content" rows="5" placeholder="Elaborate on the issue or topic you need help with" required></textarea>
    </div>
    
    <div class="modal-actions">
        <button type="button" class="btn-cancel" id="btn-cancel-post">Cancel</button>
        <button type="submit" class="btn-submit">Publish Question</button>
    </div>
</form>
      </div>
    </div>
