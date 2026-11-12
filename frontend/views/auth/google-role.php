<?php
?>
<h1>Choose Your Account Type</h1>
<p>Welcome, <?= e($googleUser['full_name']) ?>. How would you like to use CodeMate?</p>

<?php if ($error): ?>
  <p class="form-error" role="alert"><?= e($error) ?></p>
<?php endif; ?>

<form method="post" action="<?= e(url('google-role')) ?>">
  
  <fieldset class="signup-role-choice">
    <legend>Select one option</legend>
    <label class="role-option">
      <input type="radio" name="role" value="tutee" <?= $oldRole === 'tutee' ? 'checked' : '' ?> required />
      <span><strong>Tutee</strong><small>Find tutors and learn.</small></span>
    </label>
    <label class="role-option">
      <input type="radio" name="role" value="tutor" <?= $oldRole === 'tutor' ? 'checked' : '' ?> required />
      <span><strong>Tutor</strong><small>Teach and share your knowledge.</small></span>
    </label>
  </fieldset>
  <button type="submit">Create Account</button>
</form>
<p><a href="<?= e(url('signup')) ?>">Cancel and go back</a></p>
