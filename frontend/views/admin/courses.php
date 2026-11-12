<?php
// Admin Course Management page. Variables come from CourseController::manage():
// $courses (every course with its tutor count), $old (form values to refill after an error)
?>
<main class="role-page">
  <div class="role-page-header">
    <div>
      <h1>Course Management</h1>
      <p>Maintain courses used throughout CodeMate. Archived courses are hidden from tutor search and booking.</p>
    </div>
  </div>

  <div class="role-card">
    <h2>Add a course</h2>
    <?php // POST form handled by CourseController::store() ?>
    <form method="post" action="<?= e(url('course-create')) ?>">
      
      <div class="role-grid">
        <div class="role-field">
          <label for="course-code">Course code</label>
          <input id="course-code" name="course_code" value="<?= e($old['course_code']) ?>" maxlength="10" placeholder="e.g. CCPROG3" required />
        </div>
        <div class="role-field">
          <label for="course-name">Course name</label>
          <input id="course-name" name="course_name" value="<?= e($old['course_name']) ?>" maxlength="100" placeholder="e.g. Object-Oriented Programming" required />
        </div>
      </div>
      <button type="submit" class="role-btn primary">Add course</button>
    </form>
  </div>

  <div class="role-table-wrap course-table">
    <table class="role-table">
      <thead>
        <tr>
          <th>Code</th>
          <th>Name</th>
          <th>Tutors</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($courses as $course): ?>
          <?php $isActive = $course['status'] === 'active'; ?>
          <tr>
            <td><strong><?= e($course['course_code']) ?></strong></td>
            <td><?= e($course['course_name']) ?></td>
            <td><?= (int) $course['tutor_count'] ?></td>
            <td><span class="role-pill <?= $isActive ? '' : 'pending' ?>"><?= e($course['status']) ?></span></td>
            <td>
              <div class="role-actions">
                <?php // clicking "Edit" opens a small form to rename the course (course-update) ?>
                <details class="course-edit">
                  <summary class="role-btn secondary">Edit</summary>
                  <form method="post" action="<?= e(url('course-update')) ?>">
                    
                    <input type="hidden" name="course_id" value="<?= (int) $course['course_id'] ?>" />
                    <div class="role-field">
                      <label>Code</label>
                      <input name="course_code" value="<?= e($course['course_code']) ?>" maxlength="10" required />
                    </div>
                    <div class="role-field">
                      <label>Name</label>
                      <input name="course_name" value="<?= e($course['course_name']) ?>" maxlength="100" required />
                    </div>
                    <button type="submit" class="role-btn primary">Save</button>
                  </form>
                </details>
                <?php // one button that archives an active course or restores an archived one ?>
                <form method="post" action="<?= e(url('course-status')) ?>">
                  
                  <input type="hidden" name="course_id" value="<?= (int) $course['course_id'] ?>" />
                  <input type="hidden" name="status" value="<?= $isActive ? 'archived' : 'active' ?>" />
                  <button type="submit" class="role-btn <?= $isActive ? 'danger' : 'primary' ?>">
                    <?= $isActive ? 'Archive' : 'Restore' ?>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>
