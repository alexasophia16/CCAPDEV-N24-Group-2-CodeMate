// shows Admin-only nav items based on whichever mock user is "logged in" (stored in localStorage by the login/signup page once that's wired up).

(function () {

  const sidebar = document.getElementById("sidebar");
  const backdrop = document.getElementById("sidebar-backdrop");
  const hamburger = document.getElementById("topbar-hamburger");

  function openSidebar() {
    sidebar.classList.add("open");
    backdrop.classList.add("show");
  }

  function closeSidebar() {
    sidebar.classList.remove("open");
    backdrop.classList.remove("show");
  }

  if (hamburger) {
    hamburger.addEventListener("click", openSidebar);
  }
  if (backdrop) {
    backdrop.addEventListener("click", closeSidebar);
  }

  window.addEventListener("resize", function () {
    if (window.innerWidth > 880) closeSidebar();
  });

  // admin role
  async function applySidebarRole() {
    try {
      const currentUserId = localStorage.getItem("codemate_current_user") || "u1";
      const res = await fetch("mock-users.json");
      const users = await res.json();
      const currentUser = users.find(function (u) { return u.id === currentUserId; }) || users[0];

      const isAdmin = currentUser.role === "admin";
      document.querySelectorAll(".sidebar-item-admin").forEach(function (item) {
        item.hidden = !isAdmin;
      });

      const badge = document.getElementById("sidebar-messages-badge");
      if (badge) {
        if (currentUser.unreadMessages > 0) {
          badge.textContent = currentUser.unreadMessages;
          badge.hidden = false;
        } else {
          badge.hidden = true;
        }
      }
    } catch (err) {
      console.error("sidebar.js could not load mock-users.json:", err);
    }
  }

  applySidebarRole();

})();