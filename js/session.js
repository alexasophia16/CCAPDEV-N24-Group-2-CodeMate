// Shared by every page. There is no real backend yet, so "being logged in is simulated with localStorage:
//   codemate_session_user   - full user object, set by sign-up.html or
//                              login.html once a (mock) account is created
//                              or matched. Dashboard/sidebar/topbar read
//                              this first.
//   codemate_current_user   - just a user id (u1/u2/u3), a manual override
//                              for testing roles from the browser console,
//                              e.g. localStorage.setItem("codemate_current_user","u3")

window.CodeMateSession = (function () {
  const KEEP_KEYS = ["codemate_session_user", "codemate_current_user", "codemate_registered_users"];

  function resetDemoState() {
    Object.keys(localStorage).forEach(function (key) {
      if (key.indexOf("codemate_") === 0 && !KEEP_KEYS.includes(key)) {
        localStorage.removeItem(key);
      }
    });
  }

  function isReload() {
    try {
      const nav = performance.getEntriesByType("navigation")[0];
      return nav ? nav.type === "reload" : performance.navigation.type === 1;
    } catch (e) {
      return false;
    }
  }

  try {
    if (isReload() || !sessionStorage.getItem("codemate_demo_started")) {
      resetDemoState();
      sessionStorage.setItem("codemate_demo_started", "1");
    }
  } catch (e) {
    
  }

  async function loadMockUsers() {
    const res = await fetch("data/mock-users.json");
    if (!res.ok) throw new Error("Failed to load mock-users.json");
    return res.json();
  }

  async function getCurrentUser() {
    // 1. A real session from sign up / log in
    const stored = localStorage.getItem("codemate_session_user");
    if (stored) {
      try {
        return JSON.parse(stored);
      } catch (e) {
        // fall through to the other methods if this got corrupted somehow
      }
    }

    // 2. A manual role override (for testing in the console)
    const users = await loadMockUsers();
    const overrideId = localStorage.getItem("codemate_current_user");
    if (overrideId) {
      const match = users.find(function (u) { return u.id === overrideId; });
      if (match) return match;
    }

    // 3. Default demo account
    return users[0];
  }

  function setSessionUser(user) {
    localStorage.setItem("codemate_session_user", JSON.stringify(user));
    localStorage.setItem("codemate_current_user", user.id);
  }

  function clearSession() {
    localStorage.removeItem("codemate_session_user");
    localStorage.removeItem("codemate_current_user");
  }

  function getRegisteredUsers() {
    try {
      return JSON.parse(localStorage.getItem("codemate_registered_users")) || [];
    } catch (e) {
      return [];
    }
  }

  function addRegisteredUser(userWithPassword) {
    const list = getRegisteredUsers();
    list.push(userWithPassword);
    localStorage.setItem("codemate_registered_users", JSON.stringify(list));
  }

  return {
    getCurrentUser: getCurrentUser,
    setSessionUser: setSessionUser,
    clearSession: clearSession,
    getRegisteredUsers: getRegisteredUsers,
    addRegisteredUser: addRegisteredUser,
    loadMockUsers: loadMockUsers
  };

})();