<?php
// Replace 'your_api_key_here' with your actual OpenAI API key
define('OPENAI_API_KEY', 'your_api_key_here');

// Upload directory configurations
define('UPLOAD_DIR', 'uploads/resumes/');
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}
?> 