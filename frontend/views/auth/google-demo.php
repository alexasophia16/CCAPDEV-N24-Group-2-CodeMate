<?php $isSignup = $mode === 'signup'; ?>
<h1><?= $isSignup ? 'Sign up with Google' : 'Log in with Google' ?></h1>
<p class="demo-note"><strong>Demo mode:</strong> this form simulates Google sign-in and does not verify ownership of the email address. Do not use on a public deployment.</p>

<?php if ($error): ?>
  <p class="form-error" role="alert"><?= e($error) ?></p>
<?php endif; ?>

<form method="post" action="<?= e(url('google-demo')) ?>">
  <input type="hidden" name="mode" value="<?= e($mode) ?>" />
  <?php if ($isSignup): ?>
    <div>
      <label for="google-name-input">Name</label>
      <input type="text" name="full_name" id="google-name-input" placeholder="Name on your Google account" maxlength="100" value="<?= e($old['full_name']) ?>" required />
    </div>
  <?php endif; ?>
  <div>
    <label for="google-email-input">Email</label>
    <input type="email" name="email" id="google-email-input" placeholder="Google email" maxlength="150" value="<?= e($old['email']) ?>" required />
  </div>
  <button type="submit">Continue</button>
</form>
<p><a href="<?= e(url($isSignup ? 'signup' : 'login')) ?>">Back</a></p>
