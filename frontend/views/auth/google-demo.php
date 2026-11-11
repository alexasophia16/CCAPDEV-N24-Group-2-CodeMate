<?php $isSignup = $mode === 'signup'; ?>
<h1><?= $isSignup ? 'Sign up with Google' : 'Log in with Google' ?></h1>

<p class="demo-note">Demo mode: this page simulates Google sign-in and does not contact Google.</p>

<?php if ($error): ?>
  <p class="form-error" role="alert"><?= e($error) ?></p>
<?php endif; ?>

<form method="post" action="<?= e(url('google-demo')) ?>">
  <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>" />
  <input type="hidden" name="mode" value="<?= e($mode) ?>" />

  <?php if ($isSignup): ?>
    <div>
      <label for="google-name-input">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#1f1f1f">
          <path
            d="M367-527q-47-47-47-113t47-113q47-47 113-47t113 47q47 47 47 113t-47 113q-47 47-113 47t-113-47ZM160-160v-112q0-34 17.5-62.5T224-378q62-31 126-46.5T480-440q66 0 130 15.5T736-378q29 15 46.5 43.5T800-272v112H160Z"
          />
        </svg>
      </label>
      <input
        type="text"
        name="full_name"
        id="google-name-input"
        placeholder="Name on your Google account"
        maxlength="100"
        value="<?= e($old['full_name']) ?>"
        required
      />
    </div>
  <?php endif; ?>

  <div>
    <label for="google-email-input"><span> @ </span></label>
    <input
      type="email"
      name="email"
      id="google-email-input"
      placeholder="Google email"
      maxlength="150"
      value="<?= e($old['email']) ?>"
      required
    />
  </div>

  <button type="submit">Continue</button>
</form>

<p>
  <a href="<?= e(url($isSignup ? 'signup' : 'login')) ?>">Back</a>
</p>
