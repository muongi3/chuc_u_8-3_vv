<!-- ══════════════════════════════════════
     CHATBOT UI — Messenger Style
══════════════════════════════════════ -->
<div id="chatbot-wrapper">

    <!-- Nút mở Chat — Messenger icon -->
    <button id="chatbot-toggle" title="Chat với CLK Assistant">
        <!-- Messenger lightning bolt SVG -->
        <svg width="26" height="26" viewBox="0 0 28 28" fill="white" xmlns="http://www.w3.org/2000/svg">
            <path d="M14 2C7.373 2 2 7.03 2 13.2c0 3.414 1.607 6.47 4.134 8.548V26l3.88-2.128c1.037.287 2.138.44 3.286.44 6.627 0 12-5.03 12-11.2S20.627 2 14 2zm1.194 15.08l-3.05-3.262L6.04 17.08l6.69-7.12 3.126 3.262 6.03-3.262-6.692 7.12z"/>
        </svg>
        <span class="notification-dot"></span>
    </button>

    <!-- Khung Chat -->
    <div id="chatbot-container">

        <!-- Header -->
        <div class="chatbot-header">
            <div class="bot-info">
                <div class="bot-avatar">
                    <img src="assets/phone.png" alt="CLK Bot"
                         onerror="this.style.display='none';this.parentElement.innerHTML='<span style=\'font-size:20px\'>🤖</span>'">
                    <span class="online-status"></span>
                </div>
                <div class="bot-name">
                    <h6>CLK Assistant</h6>
                    <small>Đang hoạt động</small>
                </div>
            </div>
            <button id="chatbot-close" title="Đóng">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Messages -->
        <div id="chatbot-messages">
            <div class="chat-date-sep">Hôm nay</div>
            <!-- Tin nhắn chào mừng -->
            <div class="chat-msg-row bot">
                <div class="chat-avatar">
                    <img src="assets/phone.png" alt="Bot"
                         onerror="this.style.display='none';this.parentElement.innerHTML='🤖'">
                </div>
                <div class="chat-bubble">
                    Xin chào! Mình là <strong>CLK Assistant</strong> 👋<br>
                    Mình có thể tư vấn Điện thoại, Tai nghe, Ốp lưng và các Phụ kiện khác cho bạn!
                </div>
            </div>
            <div class="chat-msg-row bot" style="animation-delay:0.4s">
                <div class="chat-avatar">
                    <img src="assets/phone.png" alt="Bot"
                         onerror="this.style.display='none';this.parentElement.innerHTML='🤖'">
                </div>
                <div class="chat-bubble">
                    💬 Bạn muốn hỏi gì hôm nay?
                </div>
            </div>
        </div>

        <!-- Input -->
        <form id="chatbot-input-area" autocomplete="off">
            <input type="text" id="chatbot-input"
                   placeholder="Nhập tin nhắn..."
                   autocomplete="off"
                   spellcheck="false">
            <button type="submit" id="chatbot-send" title="Gửi">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>

    </div>
</div>
