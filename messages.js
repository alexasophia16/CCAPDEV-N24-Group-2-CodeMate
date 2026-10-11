document.addEventListener("DOMContentLoaded", async () => {
  const convList = document.getElementById("conversations-list");
  const recipientInfo = document.getElementById("chat-recipient-info");
  const chatHistory = document.getElementById("chat-history");
  const chatForm = document.getElementById("chat-form");
  const messageInput = document.getElementById("message-input");
  const convSearch = document.getElementById("conv-search");
  const sendBtn = document.getElementById("btn-send");

  let conversations = [];
  let activeConvId = null;
//total unread messages
  function syncSidebarBadge() {
    const badge = document.getElementById("sidebar-messages-badge");
    if (!badge) return;

    const totalUnread = conversations.reduce((sum, c) => sum + (Number(c.unread) || 0), 0);

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

  try {
    const res = await fetch("mock-messages.json");
    if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
    conversations = await res.json();
    
    activeConvId = null;
    syncSidebarBadge();
  } catch (err) {
    console.error("Failed to load messages:", err);
  }
//render all conversations
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
      const isUnread = (c.unread || 0) > 0;
      const isActive = c.id === activeConvId;

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
        activeConvId = Number(btn.dataset.id);

        const selectedConv = conversations.find(c => c.id === activeConvId);
        if (selectedConv) {
          selectedConv.unread = 0;
        }

        syncSidebarBadge();
        renderConversations(convSearch ? convSearch.value.trim() : "");
        renderActiveChat();
      });
    });
  }
//load active conversation
  function renderActiveChat() {
    const conv = conversations.find(c => c.id === activeConvId);

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
            Select a conversation to start messaging.
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

  if (convSearch) {
    convSearch.addEventListener("input", (e) => {
      renderConversations(e.target.value.trim());
    });
  }

  if (chatForm) {
    chatForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const text = messageInput.value.trim();
      if (!text || !activeConvId) return;

      const conv = conversations.find(c => c.id === activeConvId);
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

  renderConversations();
  renderActiveChat();
});