<?php
// OpenAI API Configuration
define('OPENAI_API_KEY', ''); // Add your API key here
define('OPENAI_MODEL', 'gpt-3.5-turbo'); // Default model
define('OPENAI_MAX_TOKENS', 150); // Default max tokens for response
define('OPENAI_TEMPERATURE', 0.7); // Default temperature

// Career guidance system prompt
define('CAREER_GUIDANCE_PROMPT', 'You are a professional career advisor helping users with their career-related questions. ' .
    'Provide concise, practical advice based on current industry trends and best practices. ' .
    'Focus on actionable steps and maintain a supportive, encouraging tone.');

// Function to get OpenAI client headers
function getOpenAIHeaders() {
    return [
        'Content-Type: application/json',
        'Authorization: Bearer ' . OPENAI_API_KEY
    ];
}

// Function to validate API key
function validateOpenAIKey() {
    return !empty(OPENAI_API_KEY);
}
?> 