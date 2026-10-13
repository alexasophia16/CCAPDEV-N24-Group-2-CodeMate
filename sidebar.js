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

  // role-based navigation (extends the original Admin-only behavior)
  async function applySidebarRole() {
    try {
      // FIX: use the shared session helper (checks the real signed-in session
      // first) instead of re-deriving identity from mock-users.json alone -
      // that old approach couldn't recognize anyone who signed up or logged
      // in with Google, since those accounts aren't in mock-users.json.
      const currentUser = await CodeMateSession.getCurrentUser();

      const isAdmin = currentUser.role === "admin";
      const isTutor = currentUser.role === "tutor";

      document.querySelectorAll(".sidebar-item-admin").forEach(function (item) {
        item.hidden = !isAdmin;
      });

      // Keep the original tutee links exactly as they are, but hide tutee-only
      // pages when a tutor/admin is signed in.
      document.querySelectorAll(".sidebar-link").forEach(function (link) {
        const href = link.getAttribute("href");
        if (["find-tutor.html", "my-bookings.html"].includes(href)) {
          link.closest(".sidebar-item").hidden = isTutor || isAdmin;
        }
      });

      // Tutor pages are added only when needed, so the original sidebar markup
      // and visual styling remain untouched.
      if (isTutor) {
        const list = document.querySelector(".sidebar-list");
        const messagesItem = Array.from(list.children).find(function (li) {
          const a = li.querySelector("a"); return a && a.getAttribute("href") === "messages.html";
        });
        [
          ["tutor-profile-manage.html", "My Tutor Profile"],
          ["tutor-slots.html", "Tutoring Slots"],
          ["tutor-bookings.html", "Booking Requests"],
          ["tutor-reviews.html", "Reviews"],
          ["resources.html", "Resources"]
        ].forEach(function (entry) {
          if (document.querySelector('a[href="' + entry[0] + '"]')) return;
          const li = document.createElement("li");
          li.className = "sidebar-item sidebar-item-tutor";
          li.innerHTML = '<a href="' + entry[0] + '" class="sidebar-link"><span class="sidebar-icon">&#9670;</span><span>' + entry[1] + '</span></a>';
          list.insertBefore(li, messagesItem);
        });
      }

      // Resources are also available to tutees/admins without replacing their current links.
      if (!document.querySelector('a[href="resources.html"]')) {
        const list = document.querySelector(".sidebar-list");
        const li = document.createElement("li");
        li.className = "sidebar-item";
        li.innerHTML = '<a href="resources.html" class="sidebar-link"><span class="sidebar-icon">&#9638;</span><span>Resources</span></a>';
        list.appendChild(li);
      }
    } catch (err) {
      console.error("sidebar.js could not resolve the current user:", err);
    }
  }

  applySidebarRole();

})();