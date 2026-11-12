document.addEventListener("DOMContentLoaded", () => {
  const container = document.getElementById("threads-container");
  if (!container) return;

  // Client-Side Search 
  const searchInput = document.getElementById("global-search");
  if (searchInput) {
    searchInput.addEventListener("input", (e) => {
      const query = e.target.value.toLowerCase().trim();
      const cards = container.querySelectorAll(".thread-preview-card");
      
      cards.forEach(card => {
        const text = card.textContent.toLowerCase();
        card.style.display = text.includes(query) ? "" : "none";
      });
    });
  }

  // Course Tabs 
  const tabsWrapper = document.querySelector(".course-tabs-wrapper");
  if (tabsWrapper) {
    tabsWrapper.addEventListener("click", (e) => {
      const tab = e.target.closest(".course-tab");
      if (!tab) return;
      
      const course = tab.getAttribute("data-course");
      const url = new URL(window.location.href);
      
      if (course === "ALL") {
        url.searchParams.delete("course");
      } else {
        url.searchParams.set("course", course);
      }
      
      window.location.href = url.toString();
    });
  }

  // Quick Reply Submission 
  container.addEventListener("click", async (e) => {
    const btn = e.target.closest(".btn-quick-reply");
    if (!btn) return;

    const card = btn.closest(".thread-preview-card");
    const textarea = card.querySelector(".thread-quick-reply");
    const content = textarea.value.trim();
    const threadId = card.getAttribute("data-id");

    if (!content) {
      textarea.focus();
      return;
    }

    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";

    try {
      btn.textContent = "Posting...";
      btn.disabled = true;

      const formData = new FormData();
      formData.append("thread_id", threadId);
      formData.append("content", content);
      if (csrfToken) formData.append("_csrf", csrfToken);

      // Matches the endpoint used in the thread view
      const response = await fetch("store-reply", {
        method: "POST",
        body: formData
      });

      if (response.ok) {
        btn.textContent = "Posted!";
        textarea.value = "";
        
        // Update the reply count text visually
        const countSpan = card.querySelector(".post-count-text");
        if (countSpan) {
          const currentText = countSpan.textContent;
          const match = currentText.match(/\d+/);
          if (match) {
            const newCount = parseInt(match[0]) + 1;
            countSpan.textContent = `${newCount} ${newCount === 1 ? 'reply' : 'replies'}`;
          }
        }
      } else {
        btn.textContent = "Error";
      }
    } catch (err) {
      btn.textContent = "Error";
    } finally {
      setTimeout(() => {
        if (btn.textContent === "Posted!" || btn.textContent === "Error") {
          btn.textContent = "Post";
          btn.disabled = false;
        }
      }, 1500);
    }
  });

  // Modal Interactions
  const modal = document.getElementById("post-modal");
  const btnToggleModal = document.getElementById("btn-toggle-modal");
  const btnCloseModal = document.getElementById("btn-close-modal");
  const btnCancelPost = document.getElementById("btn-cancel-post");

  if (btnToggleModal && modal) {
    btnToggleModal.addEventListener("click", () => modal.classList.remove("hidden"));
  }

  const closeModal = () => {
    if (modal) modal.classList.add("hidden");
  };

  if (btnCloseModal) btnCloseModal.addEventListener("click", closeModal);
  if (btnCancelPost) btnCancelPost.addEventListener("click", closeModal);

 
});
