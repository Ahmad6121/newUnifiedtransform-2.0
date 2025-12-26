@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row" style="height: calc(100vh - 120px); min-height: 520px;">
            <!-- Left: conversations -->
            <div class="col-md-4 col-lg-3 border-end d-flex flex-column p-0">
                <div class="p-3 border-bottom d-flex align-items-center gap-2">
                    <div class="fw-bold">Messages</div>
                    <div class="ms-auto">
                        <button class="btn btn-sm btn-primary" id="btnNewChat">New</button>
                    </div>
                </div>

                <div class="p-2 border-bottom" id="newChatBox" style="display:none;">
                    <input class="form-control form-control-sm" id="userSearchInput" placeholder="Search users... (name/email)">
                    <div class="list-group mt-2" id="userSearchResults" style="max-height: 220px; overflow:auto;"></div>
                </div>

                <div class="list-group list-group-flush flex-grow-1" id="convList" style="overflow:auto;"></div>
            </div>

            <!-- Right: chat -->
            <div class="col-md-8 col-lg-9 d-flex flex-column p-0">
                <div class="p-3 border-bottom d-flex align-items-center">
                    <div>
                        <div class="fw-bold" id="chatTitle">Select a conversation</div>
                        <div class="text-muted small" id="chatSubtitle"></div>
                    </div>
                    <div class="ms-auto">
                        <span class="badge bg-secondary" id="chatTypeBadge" style="display:none;"></span>
                    </div>
                </div>

                <div class="flex-grow-1 p-3" id="messagesBox" style="overflow:auto; background: #f7f7f7;">
                    <div class="text-muted">No conversation selected.</div>
                </div>

                <div class="p-3 border-top">
                    <form id="sendForm" class="d-flex gap-2" autocomplete="off">
                        @csrf
                        <input type="text" class="form-control" id="msgInput" placeholder="Type a message..." disabled>
                        <button class="btn btn-success" id="btnSend" disabled>Send</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const csrf = document.querySelector('input[name="_token"]').value;

            let currentConvId = null;
            let currentTitle = '';
            let polling = null;

            const convList = document.getElementById('convList');
            const messagesBox = document.getElementById('messagesBox');
            const chatTitle = document.getElementById('chatTitle');
            const chatSubtitle = document.getElementById('chatSubtitle');
            const chatTypeBadge = document.getElementById('chatTypeBadge');
            const msgInput = document.getElementById('msgInput');
            const btnSend = document.getElementById('btnSend');

            const btnNewChat = document.getElementById('btnNewChat');
            const newChatBox = document.getElementById('newChatBox');
            const userSearchInput = document.getElementById('userSearchInput');
            const userSearchResults = document.getElementById('userSearchResults');

            function escapeHtml(s) {
                return (s || '').replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\"/g,"&quot;").replace(/\'/g,"&#039;");
            }

            async function apiGet(url) {
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
                return await res.json();
            }

            async function apiPost(url, data) {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data || {})
                });
                return await res.json();
            }

            function renderConversations(items) {
                convList.innerHTML = '';
                if (!items || !items.length) {
                    convList.innerHTML = '<div class="p-3 text-muted">No conversations yet.</div>';
                    return;
                }

                items.forEach(c => {
                    const active = (currentConvId && String(currentConvId) === String(c.id)) ? 'active' : '';
                    const unread = c.unread > 0 ? `<span class="badge bg-danger rounded-pill ms-auto">${c.unread}</span>` : '';
                    const last = c.last_message ? escapeHtml(c.last_message).slice(0, 60) : '';
                    const time = c.last_time ? `<div class="small text-muted">${escapeHtml(c.last_time)}</div>` : '';

                    const el = document.createElement('button');
                    el.className = `list-group-item list-group-item-action d-flex align-items-start gap-2 ${active}`;
                    el.onclick = () => openConversation(c.id, c.title, c.type);

                    el.innerHTML = `
        <div class="flex-grow-1">
          <div class="d-flex align-items-center gap-2">
            <div class="fw-semibold">${escapeHtml(c.title)}</div>
            <div class="ms-auto">${time}</div>
          </div>
          <div class="small ${active ? 'text-white-50' : 'text-muted'}">${last}</div>
        </div>
        ${unread}
      `;
                    convList.appendChild(el);
                });
            }

            function renderMessages(msgs) {
                messagesBox.innerHTML = '';
                if (!msgs || !msgs.length) {
                    messagesBox.innerHTML = '<div class="text-muted">No messages yet. Say hi 👋</div>';
                    return;
                }

                const meId = {{ auth()->id() }};
                msgs.forEach(m => {
                    const isMe = String(m.sender_id) === String(meId);
                    const wrap = document.createElement('div');
                    wrap.className = 'd-flex mb-2 ' + (isMe ? 'justify-content-end' : 'justify-content-start');

                    const bubble = document.createElement('div');
                    bubble.className = 'p-2 rounded-3 shadow-sm';
                    bubble.style.maxWidth = '70%';
                    bubble.style.background = isMe ? '#d1f7c4' : '#ffffff';

                    bubble.innerHTML = `
        <div class="small text-muted mb-1">${isMe ? 'You' : escapeHtml(m.sender_name)}</div>
        <div>${escapeHtml(m.body)}</div>
        <div class="small text-muted mt-1 text-end">${escapeHtml(m.created_at || '')}</div>
      `;
                    wrap.appendChild(bubble);
                    messagesBox.appendChild(wrap);
                });

                messagesBox.scrollTop = messagesBox.scrollHeight;
            }

            async function loadConversations() {
                const items = await apiGet('/messages/conversations');
                renderConversations(items);
            }

            async function openConversation(id, title, type) {
                currentConvId = id;
                currentTitle = title;

                chatTitle.textContent = title || 'Conversation';
                chatSubtitle.textContent = 'Conversation ID: ' + id;

                if (type) {
                    chatTypeBadge.style.display = '';
                    chatTypeBadge.textContent = type;
                } else {
                    chatTypeBadge.style.display = 'none';
                }

                msgInput.disabled = false;
                btnSend.disabled = false;

                const msgs = await apiGet('/messages/' + id + '/list');
                renderMessages(msgs);

                // refresh list so unread updates
                loadConversations();

                if (polling) clearInterval(polling);
                polling = setInterval(async () => {
                    if (!currentConvId) return;
                    const msgs2 = await apiGet('/messages/' + currentConvId + '/list');
                    renderMessages(msgs2);
                    // keep list fresh (last message/unread)
                    loadConversations();
                }, 4000);
            }

            document.getElementById('sendForm').addEventListener('submit', async function (e) {
                e.preventDefault();
                if (!currentConvId) return;

                const body = (msgInput.value || '').trim();
                if (!body) return;

                msgInput.value = '';
                await apiPost('/messages/' + currentConvId + '/send', { body });

                const msgs = await apiGet('/messages/' + currentConvId + '/list');
                renderMessages(msgs);
                loadConversations();
            });

            // New chat UI
            btnNewChat.addEventListener('click', function () {
                newChatBox.style.display = (newChatBox.style.display === 'none') ? '' : 'none';
                userSearchInput.value = '';
                userSearchResults.innerHTML = '';
                if (newChatBox.style.display !== 'none') userSearchInput.focus();
            });

            let searchTimer = null;
            userSearchInput.addEventListener('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(async () => {
                    const q = userSearchInput.value.trim();
                    const users = await apiGet('/messages/users/search?q=' + encodeURIComponent(q));
                    userSearchResults.innerHTML = '';
                    if (!users.length) {
                        userSearchResults.innerHTML = '<div class="text-muted small p-2">No users found.</div>';
                        return;
                    }
                    users.forEach(u => {
                        const el = document.createElement('button');
                        el.type = 'button';
                        el.className = 'list-group-item list-group-item-action d-flex align-items-center';
                        el.innerHTML = `<div class="flex-grow-1">${escapeHtml(u.name)}</div><span class="badge bg-light text-dark">${escapeHtml(u.role_slug || '')}</span>`;
                        el.onclick = async () => {
                            const resp = await apiPost('/messages/start', { recipient_id: u.id });
                            if (resp && resp.conversation_id) {
                                newChatBox.style.display = 'none';
                                await loadConversations();
                                // open newly created
                                openConversation(resp.conversation_id, u.name, 'general');
                            }
                        };
                        userSearchResults.appendChild(el);
                    });
                }, 300);
            });

            // init
            loadConversations();
        })();
    </script>
@endsection
