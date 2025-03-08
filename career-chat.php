<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Career Guidance Chatbot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .chat-container {
            height: 70vh;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            background: #f8f9fa;
        }
        .message {
            margin-bottom: 15px;
            max-width: 80%;
        }
        .user-message {
            margin-left: auto;
            background: #007bff;
            color: white;
            padding: 10px 15px;
            border-radius: 15px 15px 0 15px;
        }
        .bot-message {
            background: white;
            padding: 10px 15px;
            border-radius: 15px 15px 15px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .input-group {
            margin-top: 20px;
        }
        .typing-indicator {
            display: none;
            padding: 10px 15px;
            background: #e9ecef;
            border-radius: 15px;
            margin-bottom: 15px;
            width: fit-content;
        }
        .typing-indicator span {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #6c757d;
            border-radius: 50%;
            margin-right: 5px;
            animation: typing 1s infinite;
        }
        @keyframes typing {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
        .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
        .file-upload-container {
            display: none;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2 class="text-center mb-4">Career Guidance Assistant</h2>
                <div class="chat-container" id="chatContainer">
                    <div class="message bot-message">
                        Hello! I'm your AI-powered career guidance assistant. I can help you with:
                        <ul>
                            <li>Career path suggestions</li>
                            <li>Skills assessment</li>
                            <li>Industry insights</li>
                            <li>Resume analysis and tips</li>
                            <li>Interview preparation</li>
                        </ul>
                        What would you like to know about? You can also upload your resume for analysis by typing "analyze my resume".
                    </div>
                    <div class="typing-indicator" id="typingIndicator">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
                <div class="input-group">
                    <input type="text" class="form-control" id="userInput" placeholder="Type your question here...">
                    <button class="btn btn-primary" onclick="sendMessage()">Send</button>
                </div>
                <div class="file-upload-container" id="fileUploadContainer">
                    <div class="input-group mt-2">
                        <input type="file" class="form-control" id="resumeFile" accept=".pdf,.doc,.docx">
                        <button class="btn btn-success" onclick="uploadResume()">Upload Resume</button>
                    </div>
                    <small class="text-muted">Supported formats: PDF, DOC, DOCX (Max size: 5MB)</small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const chatContainer = document.getElementById('chatContainer');
        const userInput = document.getElementById('userInput');
        const typingIndicator = document.getElementById('typingIndicator');
        const fileUploadContainer = document.getElementById('fileUploadContainer');

        function showTypingIndicator() {
            typingIndicator.style.display = 'block';
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        function hideTypingIndicator() {
            typingIndicator.style.display = 'none';
        }

        function addMessage(message, isUser = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isUser ? 'user-message' : 'bot-message'}`;
            messageDiv.textContent = message;
            chatContainer.insertBefore(messageDiv, typingIndicator);
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        function checkForResumeRequest(message) {
            if (message.toLowerCase().includes('analyze my resume')) {
                fileUploadContainer.style.display = 'block';
                return true;
            }
            fileUploadContainer.style.display = 'none';
            return false;
        }

        async function sendMessage() {
            const message = userInput.value.trim();
            if (message === '') return;

            addMessage(message, true);
            userInput.value = '';

            if (!checkForResumeRequest(message)) {
                showTypingIndicator();

                try {
                    const response = await fetch('chat-process.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'message=' + encodeURIComponent(message)
                    });

                    const data = await response.json();
                    hideTypingIndicator();
                    addMessage(data.response);
                } catch (error) {
                    hideTypingIndicator();
                    addMessage('Sorry, there was an error processing your request. Please try again.');
                    console.error('Error:', error);
                }
            }
        }

        async function uploadResume() {
            const fileInput = document.getElementById('resumeFile');
            const file = fileInput.files[0];

            if (!file) {
                addMessage('Please select a file first.');
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                addMessage('File size should be less than 5MB.');
                return;
            }

            const formData = new FormData();
            formData.append('resume', file);
            formData.append('message', 'analyze my resume');

            showTypingIndicator();

            try {
                const response = await fetch('chat-process.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                hideTypingIndicator();
                addMessage(data.response);
                fileUploadContainer.style.display = 'none';
                fileInput.value = '';
            } catch (error) {
                hideTypingIndicator();
                addMessage('Sorry, there was an error uploading your resume. Please try again.');
                console.error('Error:', error);
            }
        }

        userInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        userInput.addEventListener('input', function(e) {
            checkForResumeRequest(e.target.value);
        });
    </script>

    <?php include 'footer.php'; ?>
</body>
</html> 