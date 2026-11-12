<h1><?= $mode === 'signup' ? 'Sign Up with Google' : 'Sign In with Google' ?></h1>
<p class="google-demo-warning">Google demo flow: enter the account details below. This is a classroom simulation and does not verify ownership through Google.</p>
<?php if ($error): ?><p class="form-error" role="alert"><?= e($error) ?></p><?php endif; ?>
<form method="post" action="<?= e(url('google-demo')) ?>">
  
  <input type="hidden" name="mode" value="<?= e($mode) ?>">
  <?php if ($mode === 'signup'): ?>
    <div class="stacked-field"><label for="google-name">Full Name</label><input id="google-name" type="text" name="full_name" placeholder="Full Name" maxlength="100" value="<?= e($old['full_name']) ?>" required></div>
  <?php endif; ?>
  <div class="stacked-field"><label for="google-email">Email</label><input id="google-email" type="email" name="email" maxlength="150" value="<?= e($old['email']) ?>" required></div>
  <?php if ($mode === 'signup'): ?>
    <fieldset class="signup-role-choice">
      <legend>Choose your account type</legend>
      <label><input type="radio" name="role" value="tutee" <?= ($old['role'] ?? 'tutee') === 'tutee' ? 'checked' : '' ?> required><span><strong>Tutee</strong><small>Find tutors and learn.</small></span></label>
      <label><input type="radio" name="role" value="tutor" <?= ($old['role'] ?? '') === 'tutor' ? 'checked' : '' ?> required><span><strong>Tutor</strong><small>Teach and share your knowledge.</small></span></label>
    </fieldset>
  <?php endif; ?>
  <button type="submit"><?= $mode === 'signup' ? 'Create Account' : 'Continue' ?></button>
</form>
<p><a href="<?= e(url($mode === 'signup' ? 'signup' : 'login')) ?>">Back</a></p>
