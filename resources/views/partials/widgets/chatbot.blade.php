<section id="chat-wrapper" aria-label="Asisten Chat AI">
    <button id="chat-bubble" aria-label="Buka obrolan asisten"
            aria-controls="chat-window" aria-expanded="false">
        <i class="fas fa-comment-dots" aria-hidden="true"></i>
        <span class="notification-badge">AI</span>
    </button>

    <div id="chat-window" class="chat-closed" aria-labelledby="chat-header-title">
        <div class="chat-header">
            <div class="header-avatar"></div>
            <div class="header-info">
                <span id="chat-header-title">PLN Batam Assistant</span>
                <span class="status-dot-label">● Online — siap membantu</span>
            </div>
            <button class="close-btn" aria-label="Tutup obrolan">&times;</button>
        </div>

        <div id="chat-messages" role="log" aria-live="polite" aria-relevant="additions">
            <!-- pesan dirender JS -->
        </div>

        <div class="quick-chips">
            <div class="quick-chip" data-chip="BPP Februari">BPP Februari</div>
            <div class="quick-chip" data-chip="Total Customer">Total Customer</div>
            <div class="quick-chip" data-chip="Growth YOY">Growth YOY</div>
        </div>

        <div class="chat-input-area">
            <label for="chat-input" class="visually-hidden">Ketik pesan Anda</label>
            <textarea id="chat-input" placeholder="Tanya sesuatu..."
                      autocomplete="off" rows="1"></textarea>
            <button id="send-btn" aria-label="Kirim pesan">
                <i class="fas fa-paper-plane" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</section>
@push('global-scripts')
    @vite(['resources/js/Dashboard/components/Chatbot.js'])
@endpush