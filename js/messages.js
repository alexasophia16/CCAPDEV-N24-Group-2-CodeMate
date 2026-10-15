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
  const isAdmin = currentUser?.role === "admin";

  if (isAdmin && btnCompose) {
    btnCompose.style.display = "none";
  }

  const readKey = "codemate_read_convs_" + currentUserName.toLowerCase();
  let readConvIds = [];
  try {
    readConvIds = JSON.parse(localStorage.getItem(readKey)) || [];
  } catch(e) {
    readConvIds = [];
  }

  function escapeHtml(s) {
    return String(s == null ? "" : s).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" }[c];
    });
  }

  function formatConvTime(timestamp) {
    if (!timestamp) return "";
    const date = new Date(timestamp);
    if (isNaN(date.getTime())) return "";

    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const targetDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
    const diffDays = Math.round((today - targetDate) / (1000 * 60 * 60 * 24));

    if (diffDays === 0) {
      return date.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
    } else if (diffDays === 1) {
      return "Yesterday";
    } else if (diffDays > 1 && diffDays < 7) {
      return date.toLocaleDateString([], { weekday: "short" });
    } else {
      return date.toLocaleDateString([], { month: "2-digit", day: "2-digit", year: "numeric" });
    }
  }

  function formatMessageTime(timestamp) {
    if (!timestamp) return "";
    const date = new Date(timestamp);
    if (isNaN(date.getTime())) return "";

    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const targetDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
    const diffDays = Math.round((today - targetDate) / (1000 * 60 * 60 * 24));
    const timeStr = date.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });

    if (diffDays === 0) {
      return timeStr;
    } else if (diffDays === 1) {
      return `Yesterday, ${timeStr}`;
    } else if (diffDays > 1 && diffDays < 7) {
      const weekday = date.toLocaleDateString([], { weekday: "short" });
      return `${weekday}, ${timeStr}`;
    } else {
      const dateStr = date.toLocaleDateString([], { month: "2-digit", day: "2-digit", year: "numeric" });
      return `${dateStr}, ${timeStr}`;
    }
  }

  function getConversationTimestamp(conv) {
    if (conv.rawTimestamp) {
      const t = new Date(conv.rawTimestamp).getTime();
      if (!isNaN(t)) return t;
    }
    if (Array.isArray(conv.messages) && conv.messages.length > 0) {
      const lastMsg = conv.messages[conv.messages.length - 1];
      if (lastMsg && lastMsg.rawTimestamp) {
        const t = new Date(lastMsg.rawTimestamp).getTime();
        if (!isNaN(t)) return t;
      }
    }
    return 0;
  }

  function sortConversations() {
    conversations.sort((a, b) => getConversationTimestamp(b) - getConversationTimestamp(a));
  }

  try {
    const [msgRes, userRes] = await Promise.all([
      fetch("data/mock-messages.json?t=" + Date.now()),
      fetch("data/mock-users.json?t=" + Date.now()).catch(() => null)
    ]);

    if (!msgRes.ok) throw new Error(`HTTP error! status: ${msgRes.status}`);
    const rawConvs = await msgRes.json();

    const filteredConvs = rawConvs.filter(conv => {
      if (isAdmin) return true;
      if (Array.isArray(conv.participants)) {
        return conv.participants.some(p => String(p).trim().toLowerCase() === currentUserName.toLowerCase());
      }
      return true;
    });

    conversations = filteredConvs.map((conv, index) => {
      const convId = String(conv.id || index + 1);
      const participants = Array.isArray(conv.participants) ? conv.participants : [conv.name || "User"];

      const otherPerson = participants.find(p => String(p).trim().toLowerCase() !== currentUserName.toLowerCase()) || participants[0];
      const displayName = (isAdmin && participants.length >= 2)
        ? `${participants[0]} & ${participants[1]}`
        : otherPerson;

      const lastMsg = Array.isArray(conv.messages) && conv.messages.length > 0
        ? conv.messages[conv.messages.length - 1]
        : null;

      const initials = (isAdmin && participants.length >= 2)
        ? (participants[0][0] + participants[1][0]).toUpperCase()
        : String(displayName).split(" ").filter(Boolean).map(w => w[0]).join("").slice(0, 2).toUpperCase();

      const isRead = readConvIds.includes(convId);
      const unreadStatus = isRead ? false : Boolean(conv.unread);

      const primarySender = conv.messages && conv.messages[0] ? conv.messages[0].sender : (participants[0] || "");

      const latestTime = conv.timestamp || (lastMsg && lastMsg.timestamp ? lastMsg.timestamp : null);

      return {
        id: convId,
        participants: participants,
        name: displayName,
        avatar: initials || "U",
        preview: conv.lastMessage || (lastMsg ? lastMsg.text : ""),
        time: latestTime ? formatConvTime(latestTime) : "",
        rawTimestamp: latestTime,
        unread: unreadStatus,
        messages: Array.isArray(conv.messages)
          ? conv.messages.map(m => {
              const isSent = isAdmin 
                ? (m.sender === primarySender)
                : (m.sender && m.sender.trim().toLowerCase() === currentUserName.toLowerCase());

              return {
                senderRole: isSent ? "sent" : "received",
                originalSender: m.sender || "User",
                text: m.text,
                time: m.timestamp ? formatMessageTime(m.timestamp) : "",
                rawTimestamp: m.timestamp || null
              };
            })
          : []
      };
    });

    sortConversations();

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
        <div class="conversation-avatar">${escapeHtml(c.avatar)}</div>
        <div class="conversation-info">
          <div class="conv-title-row">
            <span class="conv-name">${escapeHtml(c.name)}</span>
            <span class="conv-time">${escapeHtml(c.time)}</span>
          </div>
          <div class="conv-preview">${escapeHtml(c.preview)}</div>
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
      if (isAdmin && conv.participants && conv.participants.length >= 2) {
        recipientInfo.innerHTML = `
          <div class="conversation-avatar">${escapeHtml(conv.avatar)}</div>
          <div style="display: flex; flex-direction: column;">
            <span class="recipient-name">${escapeHtml(conv.participants[0])} &amp; ${escapeHtml(conv.participants[1])}</span>
            <span class="recipient-status" style="font-size: 12px; color: var(--ink-soft);">Admin Oversight &middot; Direct Conversation</span>
          </div>
        `;
      } else {
        recipientInfo.innerHTML = `
          <div class="conversation-avatar">${escapeHtml(conv.avatar)}</div>
          <div>
            <span class="recipient-name">${escapeHtml(conv.name)}</span>
            <span class="recipient-status">Active now</span>
          </div>
        `;
      }
    }

    if (chatHistory) {
      chatHistory.innerHTML = conv.messages.map(m => `
        <div class="message-bubble ${m.senderRole === "sent" ? "sent" : "received"}">
          ${isAdmin ? `<span style="font-size: 11px; font-weight: 600; opacity: 0.8; display: block; margin-bottom: 2px;">${escapeHtml(m.originalSender)}</span>` : ''}
          <p>${escapeHtml(m.text)}</p>
          <span class="msg-time">${escapeHtml(m.time)}</span>
        </div>
      `).join("");
      chatHistory.scrollTop = chatHistory.scrollHeight;
    }

    if (isAdmin) {
      if (messageInput) {
        messageInput.disabled = true;
        messageInput.value = "";
        messageInput.placeholder = "Read-only mode (Admin oversight)";
      }
      if (btnSend) {
        btnSend.disabled = true;
        btnSend.style.display = "none";
      }
    } else {
      if (messageInput) {
        messageInput.disabled = false;
        messageInput.placeholder = "Write a message...";
      }
      if (btnSend) {
        btnSend.disabled = false;
        btnSend.style.display = "";
      }
    }
  }

  if (chatForm) {
    chatForm.addEventListener("submit", (e) => {
      e.preventDefault();
      if (isAdmin) return;

      const text = messageInput.value.trim();
      if (!text || !activeConvId) return;

      const convIndex = conversations.findIndex(c => String(c.id) === String(activeConvId));
      if (convIndex !== -1) {
        const conv = conversations[convIndex];
        const now = new Date();
        const isoNow = now.toISOString();

        conv.messages.push({
          senderRole: "sent",
          originalSender: currentUserName,
          text: text,
          time: formatMessageTime(isoNow),
          rawTimestamp: isoNow
        });
        conv.preview = text;
        conv.time = formatConvTime(isoNow);
        conv.rawTimestamp = isoNow;
        messageInput.value = "";

        sortConversations();
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
        <button type="button" class="compose-user-item" data-name="${escapeHtml(uName)}">
          <div class="compose-user-avatar">${escapeHtml(initials)}</div>
          <div class="compose-user-meta">
            <span class="compose-user-name">${escapeHtml(uName)}</span>
            <span class="compose-user-role">${escapeHtml(u.role || "User")}</span>
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
          const now = new Date();
          const isoNow = now.toISOString();
          existing = {
            id: newId,
            participants: [currentUserName, targetName],
            name: targetName,
            avatar: initials,
            preview: "Started conversation",
            time: formatConvTime(isoNow),
            rawTimestamp: isoNow,
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
        sortConversations();
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