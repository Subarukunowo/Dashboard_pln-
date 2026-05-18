/* ============================================================
   PLN Chatbot UI — Chatbot.js
   ============================================================ */
(function () {

    /* ── Inject animasi loading langsung ke <head> ── */
    (function injectStyles() {
        const style = document.createElement('style');
        style.textContent = `
            @keyframes plnBounce {
                0%, 60%, 100% { transform: translateY(0);    opacity: 0.3; }
                30%            { transform: translateY(-8px); opacity: 1;   }
            }
            .loading-dot {
                width: 7px !important;
                height: 7px !important;
                border-radius: 50% !important;
                background: #e8a020 !important;
                display: inline-block !important;
                animation: plnBounce 1.2s infinite ease-in-out both !important;
            }
            .loading-dot:nth-child(1) { animation-delay: 0s !important; }
            .loading-dot:nth-child(2) { animation-delay: 0.2s !important; }
            .loading-dot:nth-child(3) { animation-delay: 0.4s !important; }
        `;
        document.head.appendChild(style);
    })();

    const chatWindow   = document.getElementById('chat-window');
    const chatBubble   = document.getElementById('chat-bubble');
    const chatMessages = document.getElementById('chat-messages');
    const chatInput    = document.getElementById('chat-input');
    const sendBtn      = document.getElementById('send-btn');
    const closeBtn     = document.querySelector('.close-btn');
    const chips        = document.querySelectorAll('.quick-chip');

    let isOpen = false;

    /* ── Buka / Tutup ── */
    function toggleChat() {
        isOpen = !isOpen;
        chatWindow.classList.toggle('chat-closed', !isOpen);
        chatBubble.setAttribute('aria-expanded', isOpen);
        if (isOpen) scrollBottom();
    }

    chatBubble.addEventListener('click', toggleChat);
    closeBtn.addEventListener('click', () => {
        isOpen = false;
        chatWindow.classList.add('chat-closed');
        chatBubble.setAttribute('aria-expanded', 'false');
    });

    /* ── Scroll ke bawah ── */
    function scrollBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    /* ── Timestamp ── */
    function now() {
        const d = new Date();
        return d.getHours().toString().padStart(2, '0') + ':' +
               d.getMinutes().toString().padStart(2, '0');
    }

    /* ── Render pesan ── */
    function appendMessage(text, type = 'bot') {
        removeLoader();
        const row = document.createElement('div');
        row.className = 'msg-row' + (type === 'user' ? ' user' : '');

        if (type !== 'user') {
            const av = document.createElement('div');
            av.className = 'bot-avatar-sm';
            av.textContent = 'AI';
            row.appendChild(av);
        }

        const col = document.createElement('div');
        col.className = 'bubble-col';

        const bub = document.createElement('div');
        bub.className = type === 'user' ? 'user-msg' : 'ai-msg';
        bub.textContent = text;

        const time = document.createElement('span');
        time.className = 'bubble-time';
        time.textContent = now();

        col.appendChild(bub);
        col.appendChild(time);
        row.appendChild(col);
        chatMessages.appendChild(row);
        scrollBottom();
    }

    /* ── Loading Dots ── */
    function showLoader() {
        const row = document.createElement('div');
        row.className = 'loading-indicator';
        row.id = 'chat-loader';

        const av = document.createElement('div');
        av.className = 'bot-avatar-sm';
        av.textContent = 'AI';

        const bub = document.createElement('div');
        bub.className = 'loading-bubble';

        [1, 2, 3].forEach(() => {
            const d = document.createElement('div');
            d.className = 'loading-dot';
            bub.appendChild(d);
        });

        row.appendChild(av);
        row.appendChild(bub);
        chatMessages.appendChild(row);
        scrollBottom();
    }

    function removeLoader() {
        const l = document.getElementById('chat-loader');
        if (l) l.remove();
    }

    /* ── Kirim pesan ── */
    function sendMessage() {
        const text = chatInput.value.trim();
        if (!text) return;
        appendMessage(text, 'user');
        chatInput.value = '';
        autoResize();
        showLoader();
        fetchBotReply(text);
    }

    function fetchBotReply(userText) {
        // TODO: ganti dengan fetch() ke endpoint AI Laravel Anda
        setTimeout(() => {
            removeLoader();
            appendMessage('Sedang memproses permintaan: "' + userText + '". Mohon tunggu sebentar.', 'bot');
        }, 1800);
    }

    /* ── Event ── */
    sendBtn.addEventListener('click', sendMessage);
    chatInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    /* ── Auto-resize textarea ── */
    function autoResize() {
        chatInput.style.height = 'auto';
        chatInput.style.height = Math.min(chatInput.scrollHeight, 80) + 'px';
    }
    chatInput.addEventListener('input', autoResize);

    /* ── Quick Chips ── */
    chips.forEach(chip => {
        chip.addEventListener('click', () => {
            chatInput.value = chip.dataset.chip;
            chatInput.focus();
            autoResize();
        });
    });

    /* ── Pesan selamat datang ── */
    appendMessage('Halo User! Ada data spesifik yang ingin Anda tanyakan hari ini?', 'bot');

})();