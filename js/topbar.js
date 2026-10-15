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
      if (!isOpen) closeNotifPanel();
      trigger.setAttribute("aria-expanded", String(!isOpen));
    });

    document.addEventListener("click", function (e) {
      if (!dropdown.hidden && !dropdown.contains(e.target) && e.target !== trigger) {
        dropdown.hidden = true;
        trigger.setAttribute("aria-expanded", "false");
      }
    });
  }

  // notifications: the current user's entries from mock-notifications.json, with read state kept in localStorage since there's no backend yet
  const NOTIF_READ_KEY = "codemate_read_notifications";
  let notifications = [];
  let notifPanel = null;

  function getReadNotifIds() {
    try {
      return JSON.parse(localStorage.getItem(NOTIF_READ_KEY)) || [];
    } catch (e) {
      return [];
    }
  }

  function markNotifsRead(ids) {
    const readIds = getReadNotifIds();
    ids.forEach(function (id) {
      if (!readIds.includes(id)) readIds.push(id);
    });
    localStorage.setItem(NOTIF_READ_KEY, JSON.stringify(readIds));
  }

  function isUnread(n) {
    return !n.read && !getReadNotifIds().includes(n.id);
  }

  function escapeHtml(s) {
    return String(s == null ? "" : s).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" }[c];
    });
  }

  function updateBellBadge() {
    if (bellBadge) bellBadge.hidden = !notifications.some(isUnread);
  }

  function renderNotifPanel() {
    if (!notifPanel) return;
    const unreadCount = notifications.filter(isUnread).length;

    const items = notifications.map(function (n) {
      return '<li><a class="notif-item' + (isUnread(n) ? " unread" : "") + '" href="' + escapeHtml(n.link || "#") + '" data-id="' + escapeHtml(n.id) + '">' +
        '<span class="notif-text">' + escapeHtml(n.text) + '</span>' +
        (n.time ? '<span class="notif-time">' + escapeHtml(n.time) + '</span>' : "") +
        '</a></li>';
    }).join("");

    notifPanel.innerHTML =
      '<div class="notif-header"><span>Notifications</span>' +
      (unreadCount ? '<button type="button" class="notif-mark-all">Mark all as read</button>' : "") +
      '</div>' +
      (notifications.length
        ? '<ul class="notif-list">' + items + '</ul>'
        : '<p class="notif-empty">No notifications yet.</p>');
  }

  function closeNotifPanel() {
    if (!notifPanel || notifPanel.hidden) return;
    notifPanel.hidden = true;
    bellBtn.setAttribute("aria-expanded", "false");
  }

  async function loadNotifications() {
    try {
      const currentUser = await CodeMateSession.getCurrentUser();
      const res = await fetch("data/mock-notifications.json");
      const all = await res.json();
      notifications = all.filter(function (n) { return n.userId === currentUser.id; });
    } catch (err) {
      notifications = [];
    }
    updateBellBadge();
    renderNotifPanel();
  }

  if (bellBtn) {
    // wrap the bell so the panel can be positioned under it
    const wrap = document.createElement("div");
    wrap.className = "notif-menu";
    bellBtn.parentNode.insertBefore(wrap, bellBtn);
    wrap.appendChild(bellBtn);

    notifPanel = document.createElement("div");
    notifPanel.className = "notif-panel";
    notifPanel.hidden = true;
    wrap.appendChild(notifPanel);

    bellBtn.setAttribute("aria-haspopup", "true");
    bellBtn.setAttribute("aria-expanded", "false");

    bellBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      const opening = notifPanel.hidden;
      if (opening) {
        renderNotifPanel();
        if (dropdown) dropdown.hidden = true;
      }
      notifPanel.hidden = !opening;
      bellBtn.setAttribute("aria-expanded", String(opening));
    });

    notifPanel.addEventListener("click", function (e) {
      e.stopPropagation();

      if (e.target.closest(".notif-mark-all")) {
        markNotifsRead(notifications.map(function (n) { return n.id; }));
        updateBellBadge();
        renderNotifPanel();
        return;
      }

      // opening a notification marks it read, then follows its link
      const item = e.target.closest(".notif-item");
      if (item) {
        markNotifsRead([item.dataset.id]);
        updateBellBadge();
        if (item.getAttribute("href") === "#") {
          e.preventDefault();
          renderNotifPanel();
        }
      }
    });

    document.addEventListener("click", closeNotifPanel);
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape") closeNotifPanel();
    });

    loadNotifications();
  }

  // search: stay in the section you are already in (discussions or tutors);
  // from any other page, go to tutors unless only discussions match
  async function searchDestination(query, page) {
    const suffix = "?q=" + encodeURIComponent(query);

    if (page === "discussions.html" || page === "discussions-thread.html") {
      return "discussions.html" + suffix;
    }
    if (page === "find-tutor.html") {
      return "find-tutor.html" + suffix;
    }

    const lower = query.toLowerCase();
    let tutorHits = 0;
    let discussionHits = 0;

    try {
      const tutors = await (await fetch("data/mock-tutors.json")).json();
      tutorHits = tutors.filter(function (t) {
        return (t.name + " " + t.subjects.join(" ")).toLowerCase().includes(lower);
      }).length;
    } catch (err) {
      tutorHits = 0;
    }

    try {
      const mockThreads = await (await fetch("data/mock-discussions.json")).json();
      let newThreads = [];
      try {
        newThreads = JSON.parse(localStorage.getItem("codemate_discussions")) || [];
      } catch (err) {
        newThreads = [];
      }
      discussionHits = mockThreads.concat(newThreads).filter(function (d) {
        return [d.title, d.content, d.author, d.subject, d.course].join(" ").toLowerCase().includes(lower);
      }).length;
    } catch (err) {
      discussionHits = 0;
    }

    return (tutorHits === 0 && discussionHits > 0 ? "discussions.html" : "find-tutor.html") + suffix;
  }

  window.CodeMateSearch = { destination: searchDestination };

  if (searchForm) {
    searchForm.addEventListener("submit", async function (e) {
      e.preventDefault();
      const query = document.getElementById("global-search").value.trim();
      if (!query) return;
      const page = window.location.pathname.split("/").pop();
      window.location.href = await searchDestination(query, page);
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