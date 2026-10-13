// Handles both sign-up.html (index.html) and login.html. There's no real backend yet, so:
//   - "Sign up" creates a mock account and stores it in localStorage
//     (see session.js) so it can be "logged into" later in this browser.
//   - "Log in" checks the typed email/password against the demo accounts
//     in mock-users.json and any accounts created via Sign Up.
//   - Both "Continue with Google" buttons just simulate success after a
//     short delay, since real Google OAuth needs a backend this project
//     doesn't have yet.

(function () {

  const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  function showError(message) {
    const errorEl = document.getElementById("form-error");
    if (!errorEl) return;
    errorEl.textContent = message;
    errorEl.hidden = false;
  }

  function clearError() {
    const errorEl = document.getElementById("form-error");
    if (!errorEl) return;
    errorEl.hidden = true;
    errorEl.textContent = "";
  }

  function markInvalid(input, isInvalid) {
    if (!input) return;
    input.classList.toggle("invalid", !!isInvalid);
  }

  function goToDashboard() {
    window.location.href = "dashboard.html";
  }

  // simulated google auth
  function simulateGoogleAuth(button) {
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = "Connecting to Google...";

    setTimeout(function () {
      const googleUser = {
        id: "u_google_" + Date.now(),
        name: "Google User",
        initials: "GU",
        role: "tutee",
        email: "google.user@gmail.com",
        unreadMessages: 0
      };
      CodeMateSession.setSessionUser(googleUser);
      goToDashboard();
    }, 700);
  }

  const googleSignupBtn = document.getElementById("google-signup-btn");
  if (googleSignupBtn) {
    googleSignupBtn.addEventListener("click", function () {
      simulateGoogleAuth(googleSignupBtn);
    });
  }

  const googleLoginBtn = document.getElementById("google-login-btn");
  if (googleLoginBtn) {
    googleLoginBtn.addEventListener("click", function () {
      simulateGoogleAuth(googleLoginBtn);
    });
  }

  // sign up form
  const signupForm = document.getElementById("signup-form");
  if (signupForm) {
    signupForm.addEventListener("submit", async function (e) {
      e.preventDefault();
      clearError();

      const firstnameInput = document.getElementById("firstname-input");
      const emailInput = document.getElementById("email-input");
      const passwordInput = document.getElementById("password-input");
      const repeatPasswordInput = document.getElementById("repeat-password-input");

      const firstname = firstnameInput.value.trim();
      const email = emailInput.value.trim();
      const password = passwordInput.value;
      const repeatPassword = repeatPasswordInput.value;

      markInvalid(firstnameInput, false);
      markInvalid(emailInput, false);
      markInvalid(passwordInput, false);
      markInvalid(repeatPasswordInput, false);

      if (!firstname) {
        markInvalid(firstnameInput, true);
        showError("Please enter your first name.");
        return;
      }
      if (!EMAIL_PATTERN.test(email)) {
        markInvalid(emailInput, true);
        showError("Please enter a valid email address.");
        return;
      }
      if (password.length < 6) {
        markInvalid(passwordInput, true);
        showError("Password must be at least 6 characters.");
        return;
      }
      if (password !== repeatPassword) {
        markInvalid(repeatPasswordInput, true);
        markInvalid(passwordInput, true);
        showError("Passwords do not match.");
        return;
      }

      const submitBtn = signupForm.querySelector("button[type='submit']");
      submitBtn.disabled = true;
      submitBtn.textContent = "Creating account...";

      try {
        // reject duplicate emails against both demo accounts and anyone who already signed up in this browser.
        const mockUsers = await CodeMateSession.loadMockUsers();
        const registered = CodeMateSession.getRegisteredUsers();
        const taken = mockUsers.concat(registered).some(function (u) {
          return u.email.toLowerCase() === email.toLowerCase();
        });

        if (taken) {
          submitBtn.disabled = false;
          submitBtn.textContent = "Sign Up";
          markInvalid(emailInput, true);
          showError("An account with this email already exists. Try logging in instead.");
          return;
        }

        const newUser = {
          id: "u_" + Date.now(),
          name: firstname,
          initials: firstname.charAt(0).toUpperCase(),
          role: "tutee",
          email: email,
          unreadMessages: 0
        };

        // store the full record (with password) 
        CodeMateSession.addRegisteredUser(Object.assign({}, newUser, { password: password }));
        // store the session copy (no password) for the dashboard to read
        CodeMateSession.setSessionUser(newUser);

        goToDashboard();

      } catch (err) {
        console.error("Sign up failed:", err);
        submitBtn.disabled = false;
        submitBtn.textContent = "Sign Up";
        showError("Something went wrong loading account data. Check the browser console.");
      }
    });
  }

  // log in form
  const loginForm = document.getElementById("login-form");
  if (loginForm) {
    loginForm.addEventListener("submit", async function (e) {
      e.preventDefault();
      clearError();

      const emailInput = document.getElementById("email-input");
      const passwordInput = document.getElementById("password-input");

      const email = emailInput.value.trim();
      const password = passwordInput.value.trim();

      markInvalid(emailInput, false);
      markInvalid(passwordInput, false);

      if (!EMAIL_PATTERN.test(email)) {
        markInvalid(emailInput, true);
        showError("Please enter a valid email address.");
        return;
      }
      if (!password) {
        markInvalid(passwordInput, true);
        showError("Please enter your password.");
        return;
      }

      const submitBtn = loginForm.querySelector("button[type='submit']");
      submitBtn.disabled = true;
      submitBtn.textContent = "Logging in...";

      try {
        const mockUsers = await CodeMateSession.loadMockUsers();
        const registered = CodeMateSession.getRegisteredUsers();
        const allAccounts = mockUsers.concat(registered);

        const byEmail = allAccounts.find(function (u) {
          return u.email.toLowerCase() === email.toLowerCase();
        });
        const match = byEmail && byEmail.password === password ? byEmail : null;

        if (!match) {
          submitBtn.disabled = false;
          submitBtn.textContent = "Log In";
          markInvalid(emailInput, true);
          markInvalid(passwordInput, true);
          showError("Invalid email or password.");

          if (!byEmail) {
            console.warn(
              "[auth.js] No account found with that email. Loaded emails:",
              allAccounts.map(function (u) { return u.email; })
            );
          } else {
            console.warn(
              "[auth.js] Email matched but password did not. " +
              "Check mock-users.json actually has a \"password\" field for this account, " +
              "and that the file was saved/reloaded (try a hard refresh)."
            );
          }
          return;
        }

        const sessionUser = Object.assign({}, match);
        delete sessionUser.password;

        CodeMateSession.setSessionUser(sessionUser);
        goToDashboard();

      } catch (err) {
        console.error("Log in failed:", err);
        submitBtn.disabled = false;
        submitBtn.textContent = "Log In";
        showError("Something went wrong loading account data. Check the browser console.");
      }
    });
  }

})();