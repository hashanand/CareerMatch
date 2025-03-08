<?php
require_once 'openai_config.php';
require_once 'db_config.php';
require_once 'session_manager.php';

class ChatbotHandler {
    private $messages = [];
    private $user_id;

    public function __construct($user_id) {
        $this->user_id = $user_id;
        $this->messages[] = [
            "role" => "system",
            "content" => CAREER_GUIDANCE_PROMPT
        ];
    }

    public function getConversationHistory($limit = 5) {
        $sql = "SELECT message, response FROM chat_history 
                WHERE user_id = ? ORDER BY created_at DESC LIMIT ?";
        $history = getRows($sql, [$this->user_id, $limit]);
        
        // Add conversation history to messages array
        foreach (array_reverse($history) as $entry) {
            $this->messages[] = ["role" => "user", "content" => $entry['message']];
            $this->messages[] = ["role" => "assistant", "content" => $entry['response']];
        }
    }

    public function processMessage($userMessage) {
        if (!validateOpenAIKey()) {
            return [
                'success' => false,
                'error' => 'OpenAI API key not configured'
            ];
        }

        // Add user message to conversation
        $this->messages[] = [
            "role" => "user",
            "content" => $userMessage
        ];

        // Prepare the API request
        $data = [
            'model' => OPENAI_MODEL,
            'messages' => $this->messages,
            'max_tokens' => OPENAI_MAX_TOKENS,
            'temperature' => OPENAI_TEMPERATURE,
        ];

        // Make API request
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, getOpenAIHeaders());

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return [
                'success' => false,
                'error' => 'API request failed with status ' . $httpCode
            ];
        }

        $result = json_decode($response, true);
        if (!isset($result['choices'][0]['message']['content'])) {
            return [
                'success' => false,
                'error' => 'Invalid API response'
            ];
        }

        $aiResponse = $result['choices'][0]['message']['content'];

        // Save to database
        $sql = "INSERT INTO chat_history (user_id, message, response) VALUES (?, ?, ?)";
        insertData($sql, [$this->user_id, $userMessage, $aiResponse]);

        return [
            'success' => true,
            'response' => $aiResponse
        ];
    }
}

// API endpoint handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'User not authenticated']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['message'])) {
        echo json_encode(['success' => false, 'error' => 'No message provided']);
        exit;
    }

    $chatbot = new ChatbotHandler($_SESSION['user_id']);
    $chatbot->getConversationHistory();
    echo json_encode($chatbot->processMessage($data['message']));
    exit;
}
?> 