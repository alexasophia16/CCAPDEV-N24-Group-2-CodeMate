// user menu dropdown open/close, and the notification bell.

(function () {

  const trigger = document.getElementById("user-menu-trigger");
  const dropdown = document.getElementById("user-menu-dropdown");
  const bellBtn = document.getElementById("btn-notifications");
  const bellBadge = document.getElementById("notifications-badge");
  const searchForm = document.getElementById("topbar-search-form");

  if (trigger && dropdown) {
    trigger.addEventListener("click", function (e) {
      e.stopPropagation();
      const isOpen = !dropdown.hidden;
      dropdown.hidden = isOpen;
      trigger.setAttribute("aria-expanded", String(!isOpen));
    });

    document.addEventListener("click", function (e) {
      if (!dropdown.hidden && !dropdown.contains(e.target) && e.target !== trigger) {
        dropdown.hidden = true;
        trigger.setAttribute("aria-expanded", "false");
      }
    });
  }

  if (bellBtn) {
    bellBtn.addEventListener("click", function () {
      if (bellBadge) bellBadge.hidden = true;
      alert("No new notifications.");
    });
  }

  if (searchForm) {
    searchForm.addEventListener("submit", function (e) {
      e.preventDefault();
      const query = document.getElementById("global-search").value.trim();
      if (query) {
        window.location.href = "find-tutor.html?q=" + encodeURIComponent(query);
      }
    });
  }

  const themeBtn = document.getElementById("btn-bookmarks");

  if (themeBtn) {

    const currentTheme = sessionStorage.getItem("codemate_theme") || "light";

    if (currentTheme === "dark") {
      document.body.classList.add("dark-mode");
      themeBtn.innerHTML = "<span>&#9728;</span>"; // Sun icon
    }
    else {
    themeBtn.innerHTML = "<span>&#127769;</span>"; // Moon icon
    }

    themeBtn.addEventListener("click", function () {

      document.body.classList.toggle("dark-mode");
      const isDark = document.body.classList.contains("dark-mode");
      sessionStorage.setItem("codemate_theme", isDark ? "dark" : "light");
      themeBtn.innerHTML = isDark ? "<span>&#9728;</span>" : "<span>&#127769;</span>";
    });
  }

  async function applyTopbarIdentity() {
    try {
      // FIX: same as sidebar.js - use the shared session helper so a
      // freshly signed-up or Google-logged-in user shows their own name
      // and initials, not whichever mock-users.json entry happens to be
      // first.
      const currentUser = await CodeMateSession.getCurrentUser();

      const avatarEl = document.getElementById("user-avatar");
      const nameRoleEl = document.getElementById("user-menu-name-role");
      if (avatarEl) avatarEl.textContent = currentUser.initials;
      if (nameRoleEl) {
        const roleLabel = currentUser.role === "tutee" ? "Student / Tutee"
          : currentUser.role === "tutor" ? "Tutor"
          : "Admin";
        nameRoleEl.textContent = currentUser.name + " - " + roleLabel;
      }
    } catch (err) {
      console.error("topbar.js could not resolve the current user:", err);
    }
  }

  applyTopbarIdentity();

})();