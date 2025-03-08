<?php
require_once 'vendor/autoload.php';

use Smalot\PdfParser\Parser;

try {
    echo "PDF Parser Test\n";
    echo "---------------\n";
    
    // Create an instance of the parser
    $parser = new Parser();
    echo "Parser initialized successfully!\n";
    
    // Test if we can access the uploads directory
    $uploadDir = 'uploads/resumes/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
        echo "Created uploads directory: {$uploadDir}\n";
    } else {
        echo "Uploads directory exists: {$uploadDir}\n";
    }
    
    echo "\nSetup completed successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 