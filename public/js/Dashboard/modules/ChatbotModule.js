export default class ChatbotModule {
    constructor() {
        this.wrapper = document.getElementById('chat-wrapper');
        this.window = document.getElementById('chat-window');
        this.input = document.getElementById('chat-input');
        this.messages = document.getElementById('chat-messages');
    }

    toggle() {
        if (this.window) {
            this.window.classList.toggle('chat-closed');
            if (!this.window.classList.contains('chat-closed')) {
                this.input.focus();
            }
        }
    }

    appendMessage(sender, text) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `message ${sender}-msg`;
        msgDiv.innerText = text;
        this.messages.appendChild(msgDiv);
        this.messages.scrollTop = this.messages.scrollHeight;
    }

  async send() {
    const text = this.input.value.trim();
    if (!text) return;

    this.appendMessage('user', text);
    this.input.value = '';

    // Tampilkan indikator loading
    const loadingId = 'loading-' + Date.now();
    const loadingDiv = document.createElement('div');
    loadingDiv.id = loadingId;
    loadingDiv.className = 'message ai-msg';
    loadingDiv.innerText = "...";
    this.messages.appendChild(loadingDiv);

    try {
        const response = await fetch('/api/chatbot', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ question: text })
        });

        const res = await response.json();
        
        // Hapus loading
        document.getElementById(loadingId).remove();

        if (response.ok) {
            this.appendMessage('ai', res.data); // Menangkap res.data dari Controller
        } else {
            this.appendMessage('ai', "Maaf, server sedang sibuk.");
        }
    } catch (e) {
        document.getElementById(loadingId).innerText = "Gagal terhubung ke server.";
        console.error("Fetch Error:", e);
    }
}
}