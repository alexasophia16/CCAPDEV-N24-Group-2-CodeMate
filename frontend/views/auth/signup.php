<h1>Sign Up</h1>

<?php if ($error): ?>
  <p class="form-error" role="alert"><?= e($error) ?></p>
<?php endif; ?>

<a class="btn-google" href="<?= e(url('google-demo', ['mode'=>'signup'])) ?>">
  <span class="google-g">G</span> Sign up with Google
</a>
<div class="divider"><span>or sign up with email</span></div>
<form method="post" action="<?= e(url('signup')) ?>">
  <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>" />
  <div>
    <label for="fullname-input">
      <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#1f1f1f">
        <path
          d="M367-527q-47-47-47-113t47-113q47-47 113-47t113 47q47 47 47 113t-47 113q-47 47-113 47t-113-47ZM160-160v-112q0-34 17.5-62.5T224-378q62-31 126-46.5T480-440q66 0 130 15.5T736-378q29 15 46.5 43.5T800-272v112H160Z"
        />
      </svg>
    </label>
    <input
      type="text"
      name="full_name"
      id="fullname-input"
      placeholder="Full Name"
      value="<?= e($old['full_name']) ?>"
      required
    />
  </div>
  <div>
    <label for="email-input"><span> @ </span></label>
    <input
      type="email"
      name="email"
      id="email-input"
      placeholder="Email"
      value="<?= e($old['email']) ?>"
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
    <input
      type="password"
      name="password"
      id="password-input"
      placeholder="Password (at least 8 characters)"
      minlength="8" autocomplete="new-password"
      required
    />
  </div>
  <div>
    <label for="repeat-password-input">
      <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000">
        <path
          d="M240-80q-33 0-56.5-23.5T160-160v-400q0-33 23.5-56.5T240-640h40v-80q0-83 58.5-141.5T480-920q83 0 141.5 58.5T680-720v80h40q33 0 56.5 23.5T800-560v400q0 33-23.5 56.5T720-80H240Zm296.5-223.5Q560-327 560-360t-23.5-56.5Q513-440 480-440t-56.5 23.5Q400-393 400-360t23.5 56.5Q447-280 480-280t56.5-23.5ZM360-640h240v-80q0-50-35-85t-85-35q-50 0-85 35t-35 85v80Z"
        />
      </svg>
    </label>
    <input
      type="password"
      name="repeat_password"
      id="repeat-password-input"
      placeholder="Repeat Password" autocomplete="new-password"
      required
    />
  </div>

  <fieldset class="signup-role-choice">
    <legend>How would you like to use CodeMate?</legend>
    <label><input type="radio" name="role" value="tutee" <?= ($old['role'] ?? 'tutee') === 'tutee' ? 'checked' : '' ?> required><span><strong>Tutee</strong><small>Find tutors and learn.</small></span></label>
    <label><input type="radio" name="role" value="tutor" <?= ($old['role'] ?? '') === 'tutor' ? 'checked' : '' ?> required><span><strong>Tutor</strong><small>Teach and share your knowledge.</small></span></label>
  </fieldset>
  <button type="submit">Sign Up</button>
</form>

<p>Already have an account? <a href="<?= e(url('login')) ?>">Log In</a></p>
