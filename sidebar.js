(function () {

  const sidebar = document.getElementById("sidebar");
  const backdrop = document.getElementById("sidebar-backdrop");
  const hamburger = document.getElementById("topbar-hamburger");
  const collapseBtn = document.getElementById("sidebar-collapse-btn");

  const isCollapsed = localStorage.getItem("codemate_sidebar_collapsed") === "true";
  if (isCollapsed && window.innerWidth > 880) {
    document.body.classList.add("sidebar-collapsed");
  }

  if (collapseBtn) {
    collapseBtn.addEventListener("click", function () {
      document.body.classList.toggle("sidebar-collapsed");
      const current = document.body.classList.contains("sidebar-collapsed");
      localStorage.setItem("codemate_sidebar_collapsed", current.toString());
    });
  }

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

  const PARENT_PAGE = {
    "discussions-thread.html": "discussions.html",
    "book-consultation.html": "find-tutor.html",
    "tutor-profile.html": "find-tutor.html",
    "index.html": "dashboard.html",
    "": "dashboard.html"
  };

  function setActiveLink() {
    const page = window.location.pathname.split("/").pop();
    const target = PARENT_PAGE[page] || page;
    document.querySelectorAll(".sidebar-link").forEach(function (link) {
      link.classList.toggle("active", link.getAttribute("href") === target);
    });
  }

  setActiveLink();

  const LINK_ICONS = {
    "dashboard.html": "&#9679;",
    "find-tutor.html": "&#9678;",
    "my-bookings.html": "&#9636;",
    "discussions.html": "&#9671;",
    "messages.html": "&#9993;",
    "resources.html": "&#9638;",
    "tutor-profile-manage.html": "&#9680;",
    "tutor-slots.html": "&#9719;",
    "tutor-bookings.html": "&#9635;",
    "tutor-reviews.html": "&#9733;",
    "admin-verify-tutors.html": "&#10003;",
    "admin-course-management.html": "&#9637;",
    "admin-moderation.html": "&#9873;",
    "admin-reports.html": "&#9650;",
    "admin-users.html": "&#9672;"
  };

  function setLinkIcons() {
    document.querySelectorAll(".sidebar-link").forEach(function (link) {
      const icon = link.querySelector(".sidebar-icon");
      const symbol = LINK_ICONS[link.getAttribute("href")];
      if (icon && symbol) icon.innerHTML = symbol;
    });
  }

  setLinkIcons();

  async function applySidebarRole() {
    try {
      const currentUser = await CodeMateSession.getCurrentUser();

      const isAdmin = currentUser.role === "admin";
      const isTutor = currentUser.role === "tutor";

      document.querySelectorAll(".sidebar-item-admin").forEach(function (item) {
        item.hidden = !isAdmin;
      });

      document.querySelectorAll(".sidebar-link").forEach(function (link) {
        const href = link.getAttribute("href");
        if (["find-tutor.html", "my-bookings.html"].includes(href)) {
          link.closest(".sidebar-item").hidden = isTutor || isAdmin;
        }
      });

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
    } catch (err) {
      console.error("sidebar.js could not resolve the current user:", err);
    } finally {
      [
        ["tutor-reviews.html", "Reviews"],
        ["resources.html", "Resources"]
      ].forEach(function (entry) {
        if (document.querySelector('a[href="' + entry[0] + '"]')) return;
        const list = document.querySelector(".sidebar-list");
        const li = document.createElement("li");
        li.className = "sidebar-item";
        li.innerHTML = '<a href="' + entry[0] + '" class="sidebar-link"><span class="sidebar-icon"></span><span>' + entry[1] + '</span></a>';
        list.appendChild(li);
      });

      setLinkIcons();
      setActiveLink();
    }
  }

  applySidebarRole();

})();