document.addEventListener("DOMContentLoaded", async () => {
  const container = document.getElementById("threads-container");
  
  if (!container) return;

  // admins can read discussions but not post
  const user = await CodeMateSession.getCurrentUser().catch(() => null);
  const readOnly = !!user && user.role === "admin";
  if (readOnly) {
    const askBtn = document.getElementById("btn-toggle-modal");
    if (askBtn) askBtn.hidden = true;
    const subtitle = document.querySelector(".discussions-subtitle");
    if (subtitle) subtitle.textContent = "View-only: admins can read discussions but can't post.";
  }

  let currentFilter = "ALL";
  let threadsData = [
    { id: Date.now() + 1, course: "CCINFOM", posts: 0 },
    { id: Date.now() + 2, course: "CCAPDEV", posts: 0 },
    { id: Date.now() + 3, course: "CSARCH1", posts: 0 },
    { id: Date.now() + 4, course: "CCPROG3", posts: 0 }
  ];

  function renderThreads() {
    const filtered = currentFilter === "ALL" 
      ? threadsData 
      : threadsData.filter(t => t.course === currentFilter);

  container.innerHTML = filtered.map(thread => `
      <div class="thread-preview-card${readOnly ? " read-only" : ""}" data-id="${thread.id}">
        <div class="thread-card-top">
          <span class="thread-badge-blue">${thread.course}</span>
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
          <span class="post-count-text" style="margin-left: 6px;">${thread.posts} post/s</span>
        </div>
      </div>
    `).join("");
  }

  renderThreads();

  const courseTabs = document.querySelectorAll(".course-tab");
  courseTabs.forEach(tab => {
    tab.addEventListener("click", () => {
      
      courseTabs.forEach(t => t.classList.remove("active"));
      tab.classList.add("active");
      
      
      currentFilter = tab.getAttribute("data-course");
      renderThreads();
    });
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

    const threadIndex = threadsData.findIndex(t => t.id === threadId);
    if (threadIndex !== -1) {
      threadsData[threadIndex].posts += 1;
    }

    btn.textContent = "Posted!";
    btn.disabled = true;

    const countSpan = card.querySelector(".post-count-text");
    if (countSpan && threadIndex !== -1) {
      countSpan.textContent = `${threadsData[threadIndex].posts} post/s`;
    }

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
      
      const courseSelect = document.getElementById("post-course");
      const newThread = {
        id: Date.now(),
        course: courseSelect.value,
        posts: 0
      };
      
   
      threadsData.unshift(newThread);
     
      if (currentFilter !== "ALL" && currentFilter !== newThread.course) {
        courseTabs.forEach(t => t.classList.remove("active"));
        const targetTab = document.querySelector(`.course-tab[data-course="${newThread.course}"]`);
        if (targetTab) targetTab.classList.add("active");
        currentFilter = newThread.course;
      }

      renderThreads();
      closeModal();
    });
  }
});
