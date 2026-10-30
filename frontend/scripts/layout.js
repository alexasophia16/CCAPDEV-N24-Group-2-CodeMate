(function () {
  const sidebar = document.getElementById("sidebar");
  const backdrop = document.getElementById("sidebar-backdrop");
  const hamburger = document.getElementById("topbar-hamburger");
  const collapseBtn = document.getElementById("sidebar-collapse-btn");
  const userTrigger = document.getElementById("user-menu-trigger");
  const userDropdown = document.getElementById("user-menu-dropdown");
  const bellBtn = document.getElementById("btn-notifications");
  const notifPanel = document.getElementById("notif-panel");
  const themeBtn = document.getElementById("btn-theme");

  // sidebar
  if (localStorage.getItem("codemate_sidebar_collapsed") === "true" && window.innerWidth > 880) {
    document.body.classList.add("sidebar-collapsed");
  }

  if (collapseBtn) {
    collapseBtn.addEventListener("click", function () {
      document.body.classList.toggle("sidebar-collapsed");
      localStorage.setItem(
        "codemate_sidebar_collapsed",
        String(document.body.classList.contains("sidebar-collapsed"))
      );
    });
  }

  function closeSidebar() {
    sidebar.classList.remove("open");
    backdrop.classList.remove("show");
  }

  if (hamburger) {
    hamburger.addEventListener("click", function () {
      sidebar.classList.add("open");
      backdrop.classList.add("show");
    });
  }
  if (backdrop) backdrop.addEventListener("click", closeSidebar);
  window.addEventListener("resize", function () {
    if (window.innerWidth > 880) closeSidebar();
  });

  // dropdowns
  function setOpen(button, panel, open) {
    if (!button || !panel) return;
    panel.hidden = !open;
    button.setAttribute("aria-expanded", String(open));
  }

  function closeMenus() {
    setOpen(userTrigger, userDropdown, false);
    setOpen(bellBtn, notifPanel, false);
  }

  function toggleMenu(button, panel) {
    if (!button || !panel) return;
    button.addEventListener("click", function (e) {
      e.stopPropagation();
      const opening = panel.hidden;
      closeMenus();
      setOpen(button, panel, opening);
    });
    panel.addEventListener("click", function (e) {
      e.stopPropagation();
    });
  }

  toggleMenu(userTrigger, userDropdown);
  toggleMenu(bellBtn, notifPanel);
  document.addEventListener("click", closeMenus);
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") closeMenus();
  });

  // dark mode
  if (themeBtn) {
    function applyTheme(dark) {
      document.body.classList.toggle("dark-mode", dark);
      themeBtn.innerHTML = dark ? "<span>&#9728;</span>" : "<span>&#127769;</span>";
    }

    applyTheme(sessionStorage.getItem("codemate_theme") === "dark");

    themeBtn.addEventListener("click", function () {
      const dark = !document.body.classList.contains("dark-mode");
      sessionStorage.setItem("codemate_theme", dark ? "dark" : "light");
      applyTheme(dark);
    });
  }
})();
