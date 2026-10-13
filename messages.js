document.addEventListener("DOMContentLoaded", async () => {
  const convList = document.getElementById("conversations-list");
  const recipientInfo = document.getElementById("chat-recipient-info");
  const chatHistory = document.getElementById("chat-history");
  const chatForm = document.getElementById("chat-form");
  const messageInput = document.getElementById("message-input");
  const convSearch = document.getElementById("conv-search");
  const sendBtn = document.getElementById("btn-send");

  const btnCompose = document.getElementById("btn-compose");
  const composeModal = document.getElementById("compose-modal");
  const btnCloseCompose = document.getElementById("btn-close-compose");
  const composeUserSearch = document.getElementById("compose-user-search");
  const composeUserList = document.getElementById("compose-user-list");

  let conversations = [];
  let availableUsers = [];
  let activeConvId = null;

  function syncSidebarBadge() {
    const badge = document.getElementById("sidebar-messages-badge");
    const totalUnread = conversations.reduce((sum, c) => sum + (Number(c.unread) || 0), 0);

    localStorage.setItem("codemate_unread_count", totalUnread.toString());

    if (!badge) return;

    if (totalUnread > 0) {
      badge.textContent = totalUnread;
      badge.hidden = false;
      badge.removeAttribute("hidden");
    } else {
      badge.textContent = "";
      badge.hidden = true;
      badge.setAttribute("hidden", "");
    }
  }

  function renderConversations(filterText = "") {
    if (!convList) return;

    const filtered = conversations.filter(c => 
      c.user.toLowerCase().includes(filterText.toLowerCase()) ||
      c.preview.toLowerCase().includes(filterText.toLowerCase())
    );

    if (filtered.length === 0) {
      convList.innerHTML = `<p style="padding: 16px; font-size: 13px; color: var(--ink-faint);">No chats found.</p>`;
      return;
    }

    convList.innerHTML = filtered.map(c => {
      const isUnread = (Number(c.unread) || 0) > 0;
      const isActive = Number(c.id) === Number(activeConvId);

      return `
        <button type="button" class="conversation-item ${isActive ? "active" : ""} ${isUnread ? "unread" : ""}" data-id="${c.id}">
          <div class="conversation-avatar">${c.avatar}</div>
          <div class="conversation-info">
            <div class="conv-title-row">
              <span class="conv-name">${c.user}</span>
              <span class="conv-time">${c.time}</span>
            </div>
            <div class="conv-preview-row">
              <p class="conv-preview">${c.preview}</p>
              ${isUnread ? `<span class="conv-unread-dot"></span>` : ""}
            </div>
          </div>
        </button>
      `;
    }).join("");

    convList.querySelectorAll(".conversation-item").forEach(btn => {
      btn.addEventListener("click", () => {
        const clickedId = Number(btn.dataset.id);
        activeConvId = clickedId;

        const selectedConv = conversations.find(c => Number(c.id) === clickedId);
        if (selectedConv) {
          selectedConv.unread = 0;

          const rawIds = JSON.parse(localStorage.getItem("codemate_read_conv_ids") || "[]");
          const readIds = rawIds.map(Number);
          if (!readIds.includes(clickedId)) {
            readIds.push(clickedId);
            localStorage.setItem("codemate_read_conv_ids", JSON.stringify(readIds));
          }
        }

        syncSidebarBadge();
        renderConversations(convSearch ? convSearch.value.trim() : "");
        renderActiveChat();
      });
    });
  }

  function renderActiveChat() {
    const conv = conversations.find(c => Number(c.id) === Number(activeConvId));

    if (!conv) {
      if (recipientInfo) {
        recipientInfo.innerHTML = `
          <div>
            <span class="recipient-name" style="color: var(--ink-faint);">Select a conversation</span>
          </div>
        `;
      }
      if (chatHistory) {
        chatHistory.innerHTML = `
          <div style="margin: auto; text-align: center; color: var(--ink-faint); font-size: 14px;">
            Select a chat from the left to start messaging.
          </div>
        `;
      }
      if (messageInput) messageInput.disabled = true;
      if (sendBtn) sendBtn.disabled = true;
      return;
    }

    if (messageInput) messageInput.disabled = false;
    if (sendBtn) sendBtn.disabled = false;

    if (recipientInfo) {
      recipientInfo.innerHTML = `
        <div class="conversation-avatar">${conv.avatar}</div>
        <div>
          <span class="recipient-name">${conv.user}</span>
          <span class="recipient-status">${conv.status}</span>
        </div>
      `;
    }

    if (chatHistory) {
      chatHistory.innerHTML = conv.messages.map(m => `
        <div class="message-bubble ${m.sender}">
          <p>${m.text}</p>
          <span class="msg-time">${m.time}</span>
        </div>
      `).join("");
      chatHistory.scrollTop = chatHistory.scrollHeight;
    }
  }

  function renderComposeUserList(filterText = "") {
    if (!composeUserList) return;

    const filtered = availableUsers.filter(u => 
      u.name.toLowerCase().includes(filterText.toLowerCase())
    );

    if (filtered.length === 0) {
      composeUserList.innerHTML = `<p style="padding: 10px; font-size: 12px; color: var(--ink-faint);">No users found.</p>`;
      return;
    }

    composeUserList.innerHTML = filtered.map(u => {
      const initials = u.name.split(" ").map(n => n[0]).join("").slice(0, 2).toUpperCase();
      return `
        <button type="button" class="compose-user-item" data-name="${u.name}" data-initials="${initials}">
          <div class="compose-user-avatar">${initials}</div>
          <div class="compose-user-meta">
            <span class="compose-user-name">${u.name}</span>
            <span class="compose-user-role">${u.role || "Peer"}</span>
          </div>
        </button>
      `;
    }).join("");

    composeUserList.querySelectorAll(".compose-user-item").forEach(btn => {
      btn.addEventListener("click", () => {
        const userName = btn.dataset.name;
        const initials = btn.dataset.initials;
        startConversationWithUser(userName, initials);
        closeComposeModal();
      });
    });
  }

  function startConversationWithUser(userName, initials) {
    let conv = conversations.find(c => c.user.toLowerCase() === userName.toLowerCase());

    if (!conv) {
      const newId = conversations.length ? Math.max(...conversations.map(c => Number(c.id))) + 1 : 1;
      conv = {
        id: newId,
        user: userName,
        avatar: initials,
        status: "Online",
        time: "Just now",
        unread: 0,
        preview: "Conversation started.",
        messages: []
      };
      conversations.unshift(conv);
    }

    activeConvId = conv.id;
    renderConversations();
    renderActiveChat();
    if (messageInput) messageInput.focus();
  }

  function openComposeModal() {
    if (!composeModal) return;
    composeModal.hidden = false;
    composeModal.removeAttribute("hidden");
    if (composeUserSearch) {
      composeUserSearch.value = "";
      composeUserSearch.focus();
    }
    renderComposeUserList();
  }

  function closeComposeModal() {
    if (!composeModal) return;
    composeModal.hidden = true;
    composeModal.setAttribute("hidden", "");
  }

  if (btnCompose) btnCompose.addEventListener("click", openComposeModal);
  if (btnCloseCompose) btnCloseCompose.addEventListener("click", closeComposeModal);

  if (composeModal) {
    composeModal.addEventListener("click", (e) => {
      if (e.target === composeModal) closeComposeModal();
    });
  }

  if (composeUserSearch) {
    composeUserSearch.addEventListener("input", (e) => {
      renderComposeUserList(e.target.value.trim());
    });
  }

  if (convSearch) {
    convSearch.addEventListener("input", (e) => {
      renderConversations(e.target.value.trim());
    });
  }

  if (chatForm) {
    chatForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const text = messageInput.value.trim();
      if (!text || activeConvId === null) return;

      const conv = conversations.find(c => Number(c.id) === Number(activeConvId));
      if (conv) {
        const timeNow = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        conv.messages.push({ sender: "sent", text: text, time: timeNow });
        conv.preview = text;
        conv.time = timeNow;
        messageInput.value = "";
        renderConversations(convSearch ? convSearch.value.trim() : "");
        renderActiveChat();
      }
    });
  }

  try {
    const [msgRes, userRes] = await Promise.all([
      fetch("mock-messages.json"),
      fetch("mock-users.json").catch(() => null)
    ]);

    if (!msgRes.ok) throw new Error(`HTTP error! status: ${msgRes.status}`);
    conversations = await msgRes.json();

    if (userRes && userRes.ok) {
      availableUsers = await userRes.json();
    }

    const rawIds = JSON.parse(localStorage.getItem("codemate_read_conv_ids") || "[]");
    const readIds = rawIds.map(Number);
    conversations.forEach(c => {
      if (readIds.includes(Number(c.id))) {
        c.unread = 0;
      }
    });

    activeConvId = null;
    syncSidebarBadge();
    renderConversations();
    renderActiveChat();
  } catch (err) {
    console.error("Failed to load data:", err);
  }
});
// Admin message oversight: keep the original Messages interface, but make it read-only.
CodeMateSession.getCurrentUser().then(function (u) {
  if (u.role !== "admin") return;

  const composer = document.getElementById("chat-form");
  const composeButton = document.getElementById("btn-compose");

  if (composeButton) composeButton.style.display = "none";

  if (composer) {
    composer.style.display = "none";
    const note = document.createElement("p");
    note.textContent = "Read-only: admins can read conversations but cannot send messages.";
    note.style.cssText = "padding:14px 18px;font-size:13px;color:var(--ink-faint);border-top:1px solid var(--line);margin:0;";
    composer.insertAdjacentElement("afterend", note);
  }
});