document.addEventListener("DOMContentLoaded", async () => {
  const container = document.getElementById("threads-container");

  if (!container) return;

  const NEW_THREADS_KEY = "codemate_discussions";
  const NEW_REPLIES_KEY = "codemate_discussion_replies";

  const user = await CodeMateSession.getCurrentUser().catch(() => null);
  const readOnly = !!user && user.role === "admin";
  if (readOnly) {
    const askBtn = document.getElementById("btn-toggle-modal");
    if (askBtn) askBtn.hidden = true;
    const subtitle = document.querySelector(".discussions-subtitle");
    if (subtitle) subtitle.textContent = "View-only: admins can read discussions but can't post.";
  }

  // tutors start discussions rather than ask questions, so reword the button and the post form for them
  if (user && user.role === "tutor") {
    const setText = (selector, text) => {
      const el = document.querySelector(selector);
      if (el) el.textContent = text;
    };
    setText("#btn-toggle-modal", "+ Post a Discussion");
    setText(".discussions-subtitle", "Start a discussion or answer a question.");
    setText("#post-modal .modal-top h2", "Post a Discussion");
    setText('label[for="post-title"]', "Discussion Title");
    setText("#post-modal .btn-submit", "Publish Discussion");
    const titleInput = document.getElementById("post-title");
    if (titleInput) titleInput.placeholder = "What do you want to discuss?";
    const contentInput = document.getElementById("post-content");
    if (contentInput) contentInput.placeholder = "Share a tip, explanation, or topic for students to discuss";
  }

  const searchInput = document.getElementById("global-search");
  const tabsWrapper = document.querySelector(".course-tabs-wrapper");
  const courseSelect = document.getElementById("post-course");

  let currentFilter = "ALL";
  let courses = [];
  let threads = [];
  let replies = [];

  function readStored(key) {
    try {
      return JSON.parse(localStorage.getItem(key)) || [];
    } catch (e) {
      return [];
    }
  }

  function loadJSON(path) {
    return fetch(path).then(res => {
      if (!res.ok) throw new Error("Failed to load " + path);
      return res.json();
    });
  }

  function escapeHtml(text) {
    return String(text == null ? "" : text).replace(/[&<>"']/g, c => {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" }[c];
    });
  }

  function initialsOf(name) {
    return String(name).split(" ").slice(0, 2).map(w => w[0]).join("").toUpperCase();
  }

  function formatDate(iso) {
    return new Date(iso + "T00:00:00").toLocaleDateString("en-US", {
      month: "short",
      day: "numeric",
      year: "numeric"
    });
  }

  function snippetOf(text) {
    return text.length > 140 ? text.slice(0, 140).trim() + "..." : text;
  }

  function myRoleLabel() {
    return user && user.role === "tutor" ? "Tutor" : "Student";
  }

  function replyCountFor(threadId) {
    return replies.filter(r => r.discussionId === threadId).length;
  }

  function countText(threadId) {
    const n = replyCountFor(threadId);
    return n + (n === 1 ? " reply" : " replies");
  }

  function matchesSearch(thread) {
    const search = searchInput ? searchInput.value.trim().toLowerCase() : "";
    if (!search) return true;
    return [thread.title, thread.content, thread.author, thread.subject, thread.course]
      .join(" ")
      .toLowerCase()
      .includes(search);
  }

  function buildCourseControls() {
    const active = courses.filter(c => c.status === "active");

    tabsWrapper.innerHTML =
      '<button type="button" class="course-tab active" data-course="ALL">All Subjects</button>' +
      active.map(c => '<button type="button" class="course-tab" data-course="' + escapeHtml(c.code) + '">' + escapeHtml(c.code) + "</button>").join("");

    courseSelect.innerHTML = active.map(c => '<option value="' + escapeHtml(c.code) + '">' + escapeHtml(c.code) + "</option>").join("");
  }

  function renderThreads() {
    const shown = threads
      .filter(t => currentFilter === "ALL" || t.course === currentFilter)
      .filter(matchesSearch)
      .sort((a, b) => b.date.localeCompare(a.date) || b.id - a.id);

    if (shown.length === 0) {
      container.innerHTML = '<p class="discussions-subtitle">No discussions found.</p>';
      return;
    }

    container.innerHTML = shown.map(thread => `
      <div class="thread-preview-card${readOnly ? " read-only" : ""}" data-id="${thread.id}">
        <div class="thread-card-top">
          <span class="thread-badge-blue">${escapeHtml(thread.course)}</span>
        </div>

        <div class="thread-card-main">
          <h3 class="thread-title"><a href="discussions-thread.html?id=${thread.id}">${escapeHtml(thread.title)}</a></h3>
          <p class="thread-snippet">${escapeHtml(snippetOf(thread.content))}</p>
          <div class="thread-author">
            <div class="thread-avatar">${escapeHtml(initialsOf(thread.author))}</div>
            <div>
              <span class="author-name">${escapeHtml(thread.author)}</span>
              <span class="author-role">${escapeHtml(thread.authorRole)} &middot; ${escapeHtml(formatDate(thread.date))}</span>
            </div>
          </div>
        </div>

        ${readOnly ? "" : `<div class="thread-interactive-area">
          <textarea class="thread-quick-reply" placeholder="Type here..."></textarea>
          <div class="thread-reply-actions">
            <button type="button" class="btn-quick-reply">Post</button>
          </div>
        </div>`}

        <div class="thread-card-bottom">
          <span class="reply-icon-box" style="display: flex; align-items: center; justify-content: center;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
              <circle cx="8" cy="10" r="1" fill="currentColor" stroke="none"></circle>
              <circle cx="12" cy="10" r="1" fill="currentColor" stroke="none"></circle>
              <circle cx="16" cy="10" r="1" fill="currentColor" stroke="none"></circle>
            </svg>
          </span>
          <a class="post-count-text" href="discussions-thread.html?id=${thread.id}" style="margin-left: 6px;">${countText(thread.id)}</a>
        </div>
      </div>
    `).join("");
  }

  try {
    const [mockThreads, mockReplies, mockCourses] = await Promise.all([
      loadJSON("data/mock-discussions.json").catch(() => []),
      loadJSON("data/mock-discussion-replies.json").catch(() => []),
      loadJSON("data/mock-courses.json").catch(() => [])
    ]);
    threads = mockThreads.concat(readStored(NEW_THREADS_KEY));
    replies = mockReplies.concat(readStored(NEW_REPLIES_KEY));
    courses = mockCourses;
  } catch (err) {
    container.innerHTML = '<p class="discussions-subtitle">Could not load the discussions.</p>';
    return;
  }

  buildCourseControls();

  if (searchInput) {
    searchInput.value = new URLSearchParams(window.location.search).get("q") || "";
    searchInput.addEventListener("input", renderThreads);
  }

  renderThreads();

  tabsWrapper.addEventListener("click", (e) => {
    const tab = e.target.closest(".course-tab");
    if (!tab) return;

    tabsWrapper.querySelectorAll(".course-tab").forEach(t => t.classList.remove("active"));
    tab.classList.add("active");

    currentFilter = tab.getAttribute("data-course");
    renderThreads();
  });

  container.addEventListener("click", (e) => {
    const btn = e.target.closest(".btn-quick-reply");
    if (!btn) return;

    const card = btn.closest(".thread-preview-card");
    const textarea = card.querySelector(".thread-quick-reply");
    const content = textarea.value.trim();
    const threadId = parseInt(card.getAttribute("data-id"));

    if (!content) {
      textarea.focus();
      return;
    }

    const reply = {
      id: Date.now(),
      discussionId: threadId,
      author: user ? user.name : "Student",
      authorRole: myRoleLabel(),
      date: new Date().toISOString().slice(0, 10),
      content: content
    };
    replies.push(reply);
    localStorage.setItem(NEW_REPLIES_KEY, JSON.stringify(readStored(NEW_REPLIES_KEY).concat(reply)));

    btn.textContent = "Posted!";
    btn.disabled = true;

    const countSpan = card.querySelector(".post-count-text");
    if (countSpan) countSpan.textContent = countText(threadId);

    setTimeout(() => {
      textarea.value = "";
      btn.textContent = "Post";
      btn.disabled = false;
    }, 1200);
  });

  const modal = document.getElementById("post-modal");
  const btnToggleModal = document.getElementById("btn-toggle-modal");
  const btnCloseModal = document.getElementById("btn-close-modal");
  const btnCancelPost = document.getElementById("btn-cancel-post");
  const newPostForm = document.getElementById("new-post-form");

  if (btnToggleModal && modal) {
    btnToggleModal.addEventListener("click", () => {
      modal.classList.remove("hidden");
    });
  }

  const closeModal = () => {
    if (modal) modal.classList.add("hidden");
    if (newPostForm) newPostForm.reset();
  };

  if (btnCloseModal) btnCloseModal.addEventListener("click", closeModal);
  if (btnCancelPost) btnCancelPost.addEventListener("click", closeModal);

  if (newPostForm) {
    newPostForm.addEventListener("submit", (e) => {
      e.preventDefault();

      const code = courseSelect.value;
      const course = courses.find(c => c.code === code);
      const newThread = {
        id: Date.now(),
        subject: course ? course.name : code,
        course: code,
        title: document.getElementById("post-title").value.trim(),
        author: user ? user.name : "Student",
        authorRole: myRoleLabel(),
        date: new Date().toISOString().slice(0, 10),
        content: document.getElementById("post-content").value.trim(),
        replies: 0
      };

      threads.unshift(newThread);
      localStorage.setItem(NEW_THREADS_KEY, JSON.stringify(readStored(NEW_THREADS_KEY).concat(newThread)));

      if (currentFilter !== "ALL" && currentFilter !== newThread.course) {
        tabsWrapper.querySelectorAll(".course-tab").forEach(t => t.classList.remove("active"));
        const targetTab = tabsWrapper.querySelector('.course-tab[data-course="' + newThread.course + '"]');
        if (targetTab) targetTab.classList.add("active");
        currentFilter = newThread.course;
      }

      renderThreads();
      closeModal();
    });
  }
});