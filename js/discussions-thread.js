document.addEventListener("DOMContentLoaded", async () => {
  const NEW_THREADS_KEY = "codemate_discussions";
  const NEW_REPLIES_KEY = "codemate_discussion_replies";

  const threadId = Number(new URLSearchParams(window.location.search).get("id"));
  const mainCard = document.getElementById("thread-main-card");
  const repliesList = document.getElementById("replies-list");
  const repliesLabel = document.getElementById("replies-count-label");
  const repliesSection = document.querySelector(".replies-section");
  const composer = document.querySelector(".reply-composer-card");

  // admins can read discussions but not post, so swap the reply form for a note
  const user = await CodeMateSession.getCurrentUser().catch(() => null);
  if (user && user.role === "admin" && composer) {
    composer.innerHTML = "<h4>View-only</h4><p>Admins can read discussions but can't post replies.</p>";
  }

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

  function formatText(text) {
    return escapeHtml(text).replace(/\n/g, "<br>");
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

  function authorBlock(name, role) {
    return '<div class="thread-author">' +
      '<div class="thread-avatar">' + escapeHtml(initialsOf(name)) + "</div>" +
      "<div>" +
        '<span class="author-name">' + escapeHtml(name) + "</span>" +
        '<span class="author-role">' + escapeHtml(role) + "</span>" +
      "</div>" +
    "</div>";
  }

  let thread = null;
  let replies = [];

  try {
    const [mockThreads, mockReplies] = await Promise.all([
      loadJSON("data/mock-discussions.json"),
      loadJSON("data/mock-discussion-replies.json")
    ]);
    thread = mockThreads.concat(readStored(NEW_THREADS_KEY)).find(t => t.id === threadId);
    replies = mockReplies.concat(readStored(NEW_REPLIES_KEY)).filter(r => r.discussionId === threadId);
  } catch (err) {
    mainCard.innerHTML = "<p>Could not load this discussion.</p>";
    return;
  }

  if (!thread) {
    mainCard.innerHTML = "<p>Discussion not found.</p>";
    if (repliesSection) repliesSection.hidden = true;
    if (composer) composer.hidden = true;
    return;
  }

  document.title = "CodeMate - " + thread.title;

  mainCard.innerHTML =
    '<span class="thread-badge">' + escapeHtml(thread.course) + "</span>" +
    '<h2 class="thread-title">' + escapeHtml(thread.title) + "</h2>" +
    '<div class="thread-meta">' +
      authorBlock(thread.author, thread.authorRole) +
      '<span class="thread-date">' + escapeHtml(formatDate(thread.date)) + "</span>" +
    "</div>" +
    '<p class="thread-body">' + formatText(thread.content) + "</p>";

  function renderReplies() {
    const n = replies.length;
    repliesLabel.textContent = n === 0 ? "No replies yet" : n + (n === 1 ? " Reply" : " Replies");

    repliesList.innerHTML = replies
      .slice()
      .sort((a, b) => a.date.localeCompare(b.date) || a.id - b.id)
      .map(r =>
        '<article class="reply-card">' +
          '<div class="reply-top">' +
            authorBlock(r.author, r.authorRole) +
            '<span class="thread-date">' + escapeHtml(formatDate(r.date)) + "</span>" +
          "</div>" +
          '<p class="reply-body">' + formatText(r.content) + "</p>" +
        "</article>"
      ).join("");
  }

  renderReplies();

  const form = document.getElementById("reply-form");
  const textarea = document.getElementById("reply-content");

  if (form && textarea) {
    form.addEventListener("submit", (e) => {
      e.preventDefault();

      const content = textarea.value.trim();
      if (!content) {
        textarea.focus();
        return;
      }

      const reply = {
        id: Date.now(),
        discussionId: threadId,
        author: user ? user.name : "Student",
        authorRole: user && user.role === "tutor" ? "Tutor" : "Student",
        date: new Date().toISOString().slice(0, 10),
        content: content
      };

      replies.push(reply);
      localStorage.setItem(NEW_REPLIES_KEY, JSON.stringify(readStored(NEW_REPLIES_KEY).concat(reply)));

      textarea.value = "";
      renderReplies();
    });
  }
});