<div id="chat-wrapper" role="region" aria-label="Asisten Chat AI">
    <button id="chat-bubble" aria-label="Buka obrolan asisten" aria-controls="chat-window" aria-expanded="false">
        <i class="fas fa-comment-dots" aria-hidden="true"></i>
        <span class="notification-badge">AI</span>
    </button>

    <div id="chat-window" class="chat-closed" aria-labelledby="chat-header-title">
        <div class="chat-header">
            <div class="header-info">
                <div class="status-dot" aria-hidden="true"></div>
                <span id="chat-header-title">PLN Batam Assistant</span>
            </div>
            <button class="close-btn" aria-label="Tutup obrolan">&times;</button>
        </div>
        
        <div id="chat-messages" role="log" aria-live="polite" aria-relevant="additions">
            <div class="message ai-msg">
                Halo User! Ada data spesifik yang ingin Anda tanyakan hari ini?
            </div>
        </div>

        <div class="chat-input-area">
            <label for="chat-input" class="visually-hidden">Ketik pesan Anda</label>
            <input type="text" id="chat-input" placeholder="Tanya sesuatu..." autocomplete="off">
            
            <button id="send-btn" aria-label="Kirim pesan">
                <i class="fas fa-paper-plane" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</div>

<style>
/* Tambahkan ini untuk mendukung label yang tidak terlihat secara visual tapi terbaca alat bantu */
.visually-hidden {
    position: absolute !important;
    width: 1px !important;
    height: 1px !important;
    padding: 0 !important;
    margin: -1px !important;
    overflow: hidden !important;
    clip: rect(0,0,0,0) !important;
    white-space: nowrap !important;
    border: 0 !important;
}

/* Sedikit penyesuaian kontras untuk skor Accessibility */
#chat-header-title {
    color: #ffffff;
    font-weight: 600;
}

#chat-bubble {
    border: none;
    cursor: pointer;
}
</style>