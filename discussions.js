document.addEventListener("DOMContentLoaded", () => {
  const container = document.getElementById("threads-container");
  
  if (!container) return;

  const blankThreads = [
    { course: "CCINFOM", replies: 0 },
    { course: "CCAPDEV", replies: 0 },
    { course: "CSARCH1", replies: 0 },
    { course: "CCPROG3", replies: 0 }
  ];

  container.innerHTML = blankThreads.map(thread => `
    <div class="thread-preview-card">
      <div class="thread-card-top">
        <span class="thread-badge-blue">${thread.course}</span>
      </div>

      <div class="thread-interactive-area">
        <textarea class="thread-quick-reply" placeholder="Type your discussion or reply here..."></textarea>
        <div class="thread-reply-actions">
          <button type="button" class="btn-quick-reply" onclick="alert('Mock reply button clicked! Backend not connected yet.')">Post</button>
        </div>
      </div>
      
      
      <div class="thread-card-bottom">
        <span class="reply-icon-box" style="display: flex; align-items: center; justify-content: center;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            <circle cx="8" cy="10" r="1" fill="currentColor" stroke="none"></circle>
            <circle cx="12" cy="10" r="1" fill="currentColor" stroke="none"></circle>
            <circle cx="16" cy="10" r="1" fill="currentColor" stroke="none"></circle>
          </svg>
        </span>
        <span style="margin-left: 6px;">${thread.replies} replies</span>
      </div>
    </div>
  `).join("");
});
