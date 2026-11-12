<h1>Log In</h1>

<?php if ($success): ?>
  <p class="form-success" role="status"><?= e($success) ?></p>
<?php endif; ?>
<?php if ($error): ?>
  <p class="form-error" role="alert"><?= e($error) ?></p>
<?php endif; ?>

<a class="btn-google" href="<?= e(url('google-start', ['mode'=>'login'])) ?>">
  <svg class="google-logo" width="18" height="18" viewBox="0 0 48 48" aria-hidden="true" focusable="false">
    <path fill="#4285F4" d="M43.6 24.5c0-1.4-.1-2.8-.4-4.1H24v7.8h11c-.5 2.5-1.9 4.6-4 6l6.5 5C41.3 35.4 43.6 30.4 43.6 24.5z"/>
    <path fill="#34A853" d="M24 44c5.5 0 10.1-1.8 13.5-4.8l-6.5-5c-1.8 1.2-4.1 2-7 2-5.4 0-10-3.6-11.6-8.5l-6.7 5.2C9.1 39.5 16 44 24 44z"/>
    <path fill="#FBBC05" d="M12.4 27.7c-.4-1.2-.7-2.4-.7-3.7s.2-2.5.7-3.7l-6.7-5.2C4.6 17.7 3.6 20.8 3.6 24s1 6.3 2.8 8.9l6-5.2z"/>
    <path fill="#EA4335" d="M24 11.6c3 0 5.7 1 7.8 3.1l5.8-5.8C34.1 5.7 29.5 4 24 4 16 4 9.1 8.5 5.7 15.1l6.7 5.2C14 15.2 18.6 11.6 24 11.6z"/>
</svg> Sign in with Google
</a>
<div class="divider"><span>or sign in with email</span></div>
<form method="post" action="<?= e(url('login')) ?>">
  
  <div>
    <label for="email-input"><span> @ </span></label>
    <input
      type="email"
      name="email"
      id="email-input"
      placeholder="Email"
      value="<?= e($email) ?>"
      required
    />
  </div>
  <div>
    <label for="password-input">
      <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000">
        <path
          d="M240-80q-33 0-56.5-23.5T160-160v-400q0-33 23.5-56.5T240-640h40v-80q0-83 58.5-141.5T480-920q83 0 141.5 58.5T680-720v80h40q33 0 56.5 23.5T800-560v400q0 33-23.5 56.5T720-80H240Zm296.5-223.5Q560-327 560-360t-23.5-56.5Q513-440 480-440t-56.5 23.5Q400-393 400-360t23.5 56.5Q447-280 480-280t56.5-23.5ZM360-640h240v-80q0-50-35-85t-85-35q-50 0-85 35t-35 85v80Z"
        />
      </svg>
    </label>
    <input type="password" name="password" id="password-input" placeholder="Password" autocomplete="current-password" required />
  </div>
  <button type="submit">Log In</button>
</form>

<p>Don't have an account? <a href="<?= e(url('signup')) ?>">Sign Up</a></p>
