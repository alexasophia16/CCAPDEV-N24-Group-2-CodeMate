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

  // unread messages badge
  function renderBadge(count) {
    const badge = document.getElementById("sidebar-messages-badge");
    if (!badge) return;

    if (count > 0) {
      badge.textContent = count;
      badge.hidden = false;
      badge.removeAttribute("hidden");
    } else {
      badge.textContent = "";
      badge.hidden = true;
      badge.setAttribute("hidden", "");
    }
  }

  async function updateBadge() {
    const storedCount = localStorage.getItem("codemate_unread_count");
    if (storedCount !== null) {
      renderBadge(parseInt(storedCount, 10));
      return;
    }

    try {
      const res = await fetch("mock-messages.json");
      const msgs = await res.json();
      const readIds = JSON.parse(localStorage.getItem("codemate_read_conv_ids") || "[]");
      const totalUnread = msgs.reduce(function (sum, c) {
        return sum + (readIds.includes(c.id) ? 0 : (Number(c.unread) || 0));
      }, 0);

      localStorage.setItem("codemate_unread_count", totalUnread);
      renderBadge(totalUnread);
    } catch (err) {
      renderBadge(0);
    }
  }

  updateBadge();

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
    } catch (err) {
      console.error("sidebar.js could not load mock-users.json:", err);
    }
  }

  applySidebarRole();

})();