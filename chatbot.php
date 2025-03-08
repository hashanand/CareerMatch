<?php
require_once 'includes/session_manager.php';
require_once 'includes/db_config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get chat history
$sql = "SELECT message, response, created_at FROM chat_history 
        WHERE user_id = ? ORDER BY created_at DESC LIMIT 10";
$chat_history = getRows($sql, [$_SESSION['user_id']]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Career Advisor Chat - CareerMatch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .chat-container {
            height: calc(100vh - 180px);
            display: flex;
            flex-direction: column;
        }
        .chat-messages {
            flex-grow: 1;
            overflow-y: auto;
            padding: 1rem;
            display: flex;
            flex-direction: column-reverse;
        }
        .message {
            max-width: 80%;
            margin-bottom: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 1rem;
        }
        .user-message {
            background-color: #007bff;
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 0.25rem;
        }
        .bot-message {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            align-self: flex-start;
            border-bottom-left-radius: 0.25rem;
        }
        .chat-input {
            padding: 1rem;
            background-color: #fff;
            border-top: 1px solid #dee2e6;
        }
        .typing-indicator {
            display: none;
            align-items: center;
            margin-bottom: 1rem;
        }
        .typing-dot {
            width: 8px;
            height: 8px;
            margin: 0 2px;
            background-color: #6c757d;
            border-radius: 50%;
            animation: typing 1s infinite ease-in-out;
        }
        @keyframes typing {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }
        .welcome-message {
            text-align: center;
            padding: 2rem;
            background-color: #f8f9fa;
            border-radius: 1rem;
            margin: 1rem;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container py-4">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-robot"></i> Career Advisor Chat
                        </h5>
                    </div>
                    <div class="chat-container">
                        <div class="chat-messages" id="chatMessages">
                            <?php if (empty($chat_history)): ?>
                                <div class="welcome-message">
                                    <h4>Welcome to Career Advisor!</h4>
                                    <p>I'm here to help you with career guidance, resume tips, interview preparation, and more.</p>
                                    <p>Feel free to ask me anything!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($chat_history as $chat): ?>
                                    <div class="message user-message">
                                        <?php echo htmlspecialchars($chat['message']); ?>
                                    </div>
                                    <div class="message bot-message">
                                        <?php echo nl2br(htmlspecialchars($chat['response'])); ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <div class="typing-indicator" id="typingIndicator">
                                <div class="message bot-message">
                                    <div class="typing-dot"></div>
                                    <div class="typing-dot"></div>
                                    <div class="typing-dot"></div>
                                </div>
                            </div>
                        </div>
                        <div class="chat-input">
                            <form id="chatForm" class="d-flex gap-2">
                                <input type="text" id="messageInput" class="form-control" 
                                       placeholder="Type your message here..." required>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-send"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('chatForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value.trim();
            if (!message) return;
            
            // Clear input
            messageInput.value = '';
            
            // Add user message to chat
            addMessage(message, true);
            
            // Show typing indicator
            document.getElementById('typingIndicator').style.display = 'flex';
            
            try {
                const response = await fetch('includes/chatbot_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ message: message })
                });
                
                const data = await response.json();
                
                // Hide typing indicator
                document.getElementById('typingIndicator').style.display = 'none';
                
                if (data.success) {
                    addMessage(data.response, false);
                } else {
                    addMessage('Sorry, I encountered an error: ' + data.error, false);
                }
            } catch (error) {
                // Hide typing indicator
                document.getElementById('typingIndicator').style.display = 'none';
                addMessage('Sorry, I encountered an error while processing your message.', false);
            }
        });

        function addMessage(text, isUser) {
            const messagesDiv = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isUser ? 'user-message' : 'bot-message'}`;
            messageDiv.textContent = text;
            
            // Insert after typing indicator
            const typingIndicator = document.getElementById('typingIndicator');
            messagesDiv.insertBefore(messageDiv, typingIndicator);
        }
    </script>
</body>
</html> 