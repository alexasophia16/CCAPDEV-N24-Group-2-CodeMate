document.addEventListener("DOMContentLoaded", async () => {
  const convList = document.getElementById("conversations-list");
  const chatHistory = document.getElementById("chat-history");
  const recipientInfo = document.getElementById("chat-recipient-info");
  const chatForm = document.getElementById("chat-form");
  const messageInput = document.getElementById("message-input");
  const btnSend = document.getElementById("btn-send");
  const convSearch = document.getElementById("conv-search");
  const btnCompose = document.getElementById("btn-compose");
  const composeModal = document.getElementById("compose-modal");
  const btnCloseCompose = document.getElementById("btn-close-compose");
  const composeSearch = document.getElementById("compose-user-search");
  const composeList = document.getElementById("compose-user-list");

  let conversations = [];
  let availableUsers = [];
  let activeConvId = null;

  let currentUser = null;
  if (typeof CodeMateSession !== "undefined") {
    currentUser = await CodeMateSession.getCurrentUser().catch(() => null);
  }
  if (!currentUser) {
    try {
      currentUser = JSON.parse(localStorage.getItem("codemate_user")) || 
                    JSON.parse(sessionStorage.getItem("codemate_user"));
    } catch(e) {}
  }

  const rawName = currentUser?.name || currentUser?.fullName || currentUser?.username;
  const currentUserName = (rawName && rawName.trim().length > 0 ? rawName : "Alexa Canaria").trim();

  const readKey = "codemate_read_convs_" + currentUserName.toLowerCase();
  let readConvIds = [];
  try {
    readConvIds = JSON.parse(localStorage.getItem(readKey)) || [];
  } catch(e) {
    readConvIds = [];
  }

  try {
    const [msgRes, userRes] = await Promise.all([
      fetch("data/mock-messages.json?t=" + Date.now()),
      fetch("data/mock-users.json?t=" + Date.now()).catch(() => null)
    ]);

    if (!msgRes.ok) throw new Error(`HTTP error! status: ${msgRes.status}`);
    const rawConvs = await msgRes.json();

    const filteredConvs = rawConvs.filter(conv => {
      if (Array.isArray(conv.participants)) {
        return conv.participants.some(p => String(p).trim().toLowerCase() === currentUserName.toLowerCase());
      }
      return true;
    });

    conversations = filteredConvs.map((conv, index) => {
      const convId = String(conv.id || index + 1);
      const otherPerson = Array.isArray(conv.participants)
        ? conv.participants.find(p => String(p).trim().toLowerCase() !== currentUserName.toLowerCase()) || conv.participants[0]
        : (conv.name || "User");

      const lastMsg = Array.isArray(conv.messages) && conv.messages.length > 0
        ? conv.messages[conv.messages.length - 1]
        : null;

      const initials = String(otherPerson)
        .split(" ")
        .filter(Boolean)
        .map(w => w[0])
        .join("")
        .slice(0, 2)
        .toUpperCase();

      const isRead = readConvIds.includes(convId);
      const unreadStatus = isRead ? false : Boolean(conv.unread);

      return {
        id: convId,
        name: otherPerson,
        avatar: initials || "U",
        preview: conv.lastMessage || (lastMsg ? lastMsg.text : ""),
        time: conv.timestamp ? new Date(conv.timestamp).toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" }) : "",
        unread: unreadStatus,
        messages: Array.isArray(conv.messages)
          ? conv.messages.map(m => ({
              sender: (m.sender && m.sender.trim().toLowerCase() === currentUserName.toLowerCase()) ? "sent" : "received",
              text: m.text,
              time: m.timestamp ? new Date(m.timestamp).toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" }) : ""
            }))
          : []
      };
    });

    if (userRes && userRes.ok) {
      availableUsers = await userRes.json();
    }
  } catch (err) {
    conversations = [];
  }

  function updateUnreadBadges() {
    const unreadCount = conversations.filter(c => c.unread).length;
    if (typeof renderBadge === "function") {
      renderBadge(unreadCount);
    } else {
      const sidebarBadge = document.getElementById("sidebar-messages-badge");
      if (sidebarBadge) {
        if (unreadCount > 0) {
          sidebarBadge.textContent = unreadCount;
          sidebarBadge.hidden = false;
        } else {
          sidebarBadge.hidden = true;
        }
      }
    }
  }

  function markAsRead(id) {
    const conv = conversations.find(c => String(c.id) === String(id));
    if (conv && conv.unread) {
      conv.unread = false;
      if (!readConvIds.includes(String(id))) {
        readConvIds.push(String(id));
        localStorage.setItem(readKey, JSON.stringify(readConvIds));
      }
      updateUnreadBadges();
    }
  }

  function renderConversations(filterText = "") {
    if (!convList) return;
    const q = filterText.toLowerCase();
    const filtered = conversations.filter(c => c.name.toLowerCase().includes(q) || c.preview.toLowerCase().includes(q));

    if (filtered.length === 0) {
      convList.innerHTML = '<div style="padding: 20px; text-align: center; color: var(--ink-faint); font-size: 13px;">No conversations found</div>';
      return;
    }

    convList.innerHTML = filtered.map(c => `
      <button type="button" class="conversation-item ${String(c.id) === String(activeConvId) ? "active" : ""} ${c.unread ? "unread" : ""}" data-id="${c.id}">
        <div class="conversation-avatar">${c.avatar}</div>
        <div class="conversation-info">
          <div class="conv-title-row">
            <span class="conv-name">${c.name}</span>
            <span class="conv-time">${c.time}</span>
          </div>
          <div class="conv-preview">${c.preview}</div>
        </div>
        ${c.unread ? '<span class="conv-unread-badge">1</span>' : ''}
      </button>
    `).join("");

    convList.querySelectorAll(".conversation-item").forEach(item => {
      item.addEventListener("click", () => {
        activeConvId = String(item.dataset.id);
        markAsRead(activeConvId);
        renderConversations(convSearch ? convSearch.value.trim() : "");
        renderActiveChat();
      });
    });
  }

  function renderActiveChat() {
    const conv = conversations.find(c => String(c.id) === String(activeConvId));

    if (!conv) {
      if (recipientInfo) {
        recipientInfo.innerHTML = '<span style="font-size: 14px; font-weight: 600; color: var(--ink-soft);">Select a conversation</span>';
      }
      if (chatHistory) {
        chatHistory.innerHTML = `
          <div style="margin: auto; text-align: center; color: var(--ink-faint); font-size: 13.5px;">
            Select a chat from the left to start messaging.
          </div>
        `;
      }
      if (messageInput) {
        messageInput.disabled = true;
        messageInput.value = "";
      }
      if (btnSend) {
        btnSend.disabled = true;
      }
      return;
    }

    if (recipientInfo) {
      recipientInfo.innerHTML = `
        <div class="conversation-avatar">${conv.avatar}</div>
        <div>
          <span class="recipient-name">${conv.name}</span>
          <span class="recipient-status">Active now</span>
        </div>
      `;
    }

    if (chatHistory) {
      chatHistory.innerHTML = conv.messages.map(m => `
        <div class="message-bubble ${m.sender === "sent" ? "sent" : "received"}">
          <p>${m.text}</p>
          <span class="msg-time">${m.time}</span>
        </div>
      `).join("");
      chatHistory.scrollTop = chatHistory.scrollHeight;
    }

    if (messageInput) messageInput.disabled = false;
    if (btnSend) btnSend.disabled = false;
  }

  if (chatForm) {
    chatForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const text = messageInput.value.trim();
      if (!text || !activeConvId) return;

      const conv = conversations.find(c => String(c.id) === String(activeConvId));
      if (conv) {
        const timeNow = new Date().toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
        conv.messages.push({ sender: "sent", text: text, time: timeNow });
        conv.preview = text;
        conv.time = timeNow;
        messageInput.value = "";
        renderConversations(convSearch ? convSearch.value.trim() : "");
        renderActiveChat();
      }
    });
  }

  if (convSearch) {
    convSearch.addEventListener("input", (e) => {
      renderConversations(e.target.value.trim());
    });
  }

  if (btnCompose) {
    btnCompose.addEventListener("click", () => {
      if (composeModal) composeModal.hidden = false;
      renderComposeList();
    });
  }

  if (btnCloseCompose) {
    btnCloseCompose.addEventListener("click", () => {
      if (composeModal) composeModal.hidden = true;
    });
  }

  if (composeModal) {
    composeModal.addEventListener("click", (e) => {
      if (e.target === composeModal) composeModal.hidden = true;
    });
  }

  function renderComposeList(filterText = "") {
    if (!composeList) return;
    const q = filterText.toLowerCase();
    const users = availableUsers.filter(u => {
      const name = (u.name || u.username || "").toLowerCase();
      return name.includes(q) && name !== currentUserName.toLowerCase();
    });

    if (users.length === 0) {
      composeList.innerHTML = '<div style="padding: 16px; text-align: center; color: var(--ink-faint); font-size: 13px;">No users found</div>';
      return;
    }

    composeList.innerHTML = users.map(u => {
      const uName = u.name || u.username;
      const initials = uName.split(" ").filter(Boolean).map(w => w[0]).join("").slice(0, 2).toUpperCase();
      return `
        <button type="button" class="compose-user-item" data-name="${uName}">
          <div class="compose-user-avatar">${initials}</div>
          <div class="compose-user-meta">
            <span class="compose-user-name">${uName}</span>
            <span class="compose-user-role">${u.role || "User"}</span>
          </div>
        </button>
      `;
    }).join("");

    composeList.querySelectorAll(".compose-user-item").forEach(item => {
      item.addEventListener("click", () => {
        const targetName = item.dataset.name;
        let existing = conversations.find(c => c.name.toLowerCase() === targetName.toLowerCase());

        if (!existing) {
          const initials = targetName.split(" ").filter(Boolean).map(w => w[0]).join("").slice(0, 2).toUpperCase();
          const newId = "conv-" + Date.now();
          existing = {
            id: newId,
            name: targetName,
            avatar: initials,
            preview: "Started conversation",
            time: new Date().toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" }),
            unread: false,
            messages: []
          };
          conversations.unshift(existing);
          markAsRead(newId);
        } else {
          markAsRead(existing.id);
        }

        activeConvId = existing.id;
        if (composeModal) composeModal.hidden = true;
        renderConversations(convSearch ? convSearch.value.trim() : "");
        renderActiveChat();
      });
    });
  }

  if (composeSearch) {
    composeSearch.addEventListener("input", (e) => {
      renderComposeList(e.target.value.trim());
    });
  }

  updateUnreadBadges();
  renderConversations();
  renderActiveChat();
});