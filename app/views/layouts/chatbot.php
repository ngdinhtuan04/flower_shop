<!-- Chatbot Widget -->
<div class="chatbot-widget">
    <!-- Chatbot Button -->
    <button class="chatbot-button" id="chatbotToggle">
        <i class="fas fa-comment-dots"></i>
        <i class="fas fa-times"></i>
        <span class="chatbot-badge">1</span>
    </button>

    <!-- Chatbot Window -->
    <div class="chatbot-window" id="chatbotWindow">
        <!-- Header -->
        <div class="chatbot-header">
            <div class="chatbot-avatar">
                🌸
            </div>
            <div class="chatbot-info">
                <h4>Flower Shop Bot</h4>
                <p>
                    <span class="chatbot-online-indicator"></span>
                    Trực tuyến
                </p>
            </div>
        </div>

        <!-- Chat Body -->
        <div class="chatbot-body" id="chatbotMessages">
            <!-- Welcome Message -->
            <div class="welcome-message">
                <h3>👋 Xin chào!</h3>
                <p>Tôi là trợ lý ảo của Flower Shop. Tôi có thể giúp gì cho bạn?</p>
                
                <!-- Quick Suggestions -->
                <div class="quick-suggestions" id="quickSuggestions">
                    <button class="suggestion-chip" data-message="Giờ mở cửa là mấy giờ?">
                        ⏰ Giờ mở cửa
                    </button>
                    <button class="suggestion-chip" data-message="Giao hàng mất bao lâu?">
                        🚚 Giao hàng
                    </button>
                    <button class="suggestion-chip" data-message="Có khuyến mãi gì không?">
                        🎁 Khuyến mãi
                    </button>
                    <button class="suggestion-chip" data-message="Cách thanh toán như thế nào?">
                        💳 Thanh toán
                    </button>
                    <button class="suggestion-chip" data-message="Liên hệ hotline">
                        📞 Liên hệ
                    </button>
                </div>
            </div>

            <!-- Typing Indicator -->
            <div class="typing-indicator" id="typingIndicator">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>

        <!-- Footer -->
        <div class="chatbot-footer">
            <input 
                type="text" 
                class="chatbot-input" 
                id="chatbotInput" 
                placeholder="Nhập tin nhắn..."
                autocomplete="off"
            >
            <button class="chatbot-send-btn" id="chatbotSendBtn">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
</div>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/chatbot.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script>
// Chatbot JavaScript
(function() {
    const chatbotToggle = document.getElementById('chatbotToggle');
    const chatbotWindow = document.getElementById('chatbotWindow');
    const chatbotMessages = document.getElementById('chatbotMessages');
    const chatbotInput = document.getElementById('chatbotInput');
    const chatbotSendBtn = document.getElementById('chatbotSendBtn');
    const typingIndicator = document.getElementById('typingIndicator');
    const badge = document.querySelector('.chatbot-badge');
    
    let isOpen = false;

    // Toggle chatbot
    chatbotToggle.addEventListener('click', function() {
        isOpen = !isOpen;
        chatbotWindow.classList.toggle('show');
        chatbotToggle.classList.toggle('active');
        
        if (isOpen) {
            if (badge) badge.style.display = 'none';
            chatbotInput.focus();
        } else {
            // Hide window when closing
            chatbotWindow.classList.remove('show');
        }
    });

    // Send message on button click
    chatbotSendBtn.addEventListener('click', sendMessage);

    // Send message on Enter key
    chatbotInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    // Quick suggestions
    document.getElementById('quickSuggestions').addEventListener('click', function(e) {
        if (e.target.classList.contains('suggestion-chip')) {
            const message = e.target.getAttribute('data-message');
            chatbotInput.value = message;
            sendMessage();
        }
    });

    function sendMessage() {
        const message = chatbotInput.value.trim();
        
        if (message === '') return;

        // Add user message
        addMessage(message, 'user');
        
        // Clear input
        chatbotInput.value = '';
        
        // Show typing indicator
        typingIndicator.classList.add('show');
        scrollToBottom();

        // Send to server
        fetch('<?= BASE_URL ?>/chatbot/send-message', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'message=' + encodeURIComponent(message)
        })
        .then(response => response.json())
        .then(data => {
            // Hide typing indicator
            setTimeout(() => {
                typingIndicator.classList.remove('show');
                
                // Debug log
                console.log('Chatbot Response:', data);
                
                if (data.success) {
                    addMessage(data.response, 'bot', data.timestamp);
                } else {
                    addMessage('Xin lỗi, đã có lỗi xảy ra. Vui lòng thử lại sau.', 'bot');
                }
            }, 1000);
        })
        .catch(error => {
            typingIndicator.classList.remove('show');
            addMessage('Xin lỗi, không thể kết nối đến máy chủ.', 'bot');
        });
    }

    function addMessage(text, sender, time) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'chat-message ' + sender;
        
        const currentTime = time || getCurrentTime();
        
        // Format text với xuống dòng
        const formattedText = text.replace(/\n/g, '<br>');
        
        messageDiv.innerHTML = `
            <div class="message-bubble">
                ${formattedText}
                <div class="message-time">${currentTime}</div>
            </div>
        `;
        
        chatbotMessages.appendChild(messageDiv);
        scrollToBottom();
    }

    function getCurrentTime() {
        const now = new Date();
        return now.getHours().toString().padStart(2, '0') + ':' + 
               now.getMinutes().toString().padStart(2, '0');
    }

    function scrollToBottom() {
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }

    // Auto show after 3 seconds
    setTimeout(() => {
        if (!isOpen) {
            badge.style.display = 'flex';
        }
    }, 3000);
})();
</script>
