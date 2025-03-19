<?php
require_once '../vendor/autoload.php';

use Smalot\PdfParser\Parser;

class ResumeAnalyzer {
    private $skills_keywords = [
        'programming_languages' => [
            'python', 'java', 'javascript', 'php', 'c++', 'c#', 'ruby', 'swift', 'kotlin', 'go',
            'rust', 'typescript', 'scala', 'perl', 'r', 'matlab', 'dart', 'lua', 'haskell', 'objective-c',
            'assembly', 'vba', 'fortran', 'cobol', 'pascal', 'sql', 'plsql', 'bash'
        ],
        'web_technologies' => [
            'html', 'css', 'sass', 'less', 'bootstrap', 'tailwind', 'jquery', 'react', 'angular',
            'vue.js', 'node.js', 'express.js', 'django', 'flask', 'laravel', 'spring', 'asp.net',
            'wordpress', 'drupal', 'magento', 'shopify', 'webflow', 'gatsby', 'next.js', 'nuxt.js',
            'svelte', 'webpack', 'babel', 'graphql', 'rest api', 'soap', 'xml', 'json'
        ],
        'databases' => [
            'mysql', 'postgresql', 'mongodb', 'oracle', 'sql server', 'sqlite', 'redis', 'cassandra',
            'elasticsearch', 'dynamodb', 'mariadb', 'neo4j', 'couchdb', 'firebase', 'supabase'
        ],
        'cloud_platforms' => [
            'aws', 'azure', 'google cloud', 'heroku', 'digitalocean', 'cloudflare', 'vercel',
            'netlify', 'aws ec2', 'aws s3', 'aws lambda', 'azure functions', 'gcp compute engine',
            'kubernetes', 'docker', 'terraform', 'ansible'
        ],
        'ai_ml' => [
            'machine learning', 'deep learning', 'tensorflow', 'pytorch', 'keras', 'scikit-learn',
            'opencv', 'nltk', 'pandas', 'numpy', 'matplotlib', 'seaborn', 'jupyter', 'computer vision',
            'nlp', 'neural networks', 'data mining', 'predictive modeling'
        ],
        'soft_skills' => [
            'leadership', 'communication', 'teamwork', 'problem solving', 'analytical', 'project management',
            'time management', 'critical thinking', 'decision making', 'conflict resolution', 'negotiation',
            'presentation', 'mentoring', 'agile', 'scrum', 'customer service', 'collaboration',
            'adaptability', 'creativity', 'emotional intelligence'
        ],
        'development_tools' => [
            'git', 'github', 'gitlab', 'bitbucket', 'jira', 'confluence', 'trello', 'asana', 'slack',
            'visual studio', 'vscode', 'intellij', 'eclipse', 'xcode', 'android studio', 'postman',
            'swagger', 'jenkins', 'travis ci', 'circle ci', 'docker compose', 'kubernetes helm'
        ],
        'certifications' => [
            'aws certified', 'azure certified', 'google certified', 'cisco certified', 'comptia',
            'pmp', 'scrum master', 'itil', 'cissp', 'ceh', 'security+', 'network+', 'a+',
            'oracle certified', 'microsoft certified'
        ]
    ];

    private $experience_indicators = [
        'executive' => [
            'chief', 'cto', 'cio', 'vp', 'vice president', 'director', '10+ years', 
            'head of', 'principal', '15+ years', 'executive'
        ],
        'senior' => [
            'senior', 'lead', 'architect', 'manager', '5+ years', '6+ years', '7+ years', '8+ years',
            'staff engineer', 'technical lead', 'team lead'
        ],
        'mid' => [
            'mid-level', '3+ years', '4+ years', '3 years', '4 years', 'intermediate',
            'software engineer ii', 'developer ii', 'experienced'
        ],
        'junior' => [
            'junior', 'entry level', '1+ year', '2+ years', 'intern', 'fresher', 'associate',
            'software engineer i', 'developer i', 'graduate'
        ]
    ];

    private $education_keywords = [
        'degrees' => [
            'phd' => ['phd', 'ph.d', 'doctor of philosophy'],
            'masters' => ['master', 'ms', 'm.s.', 'msc', 'm.sc', 'ma', 'm.a.', 'mba', 'm.b.a'],
            'bachelors' => ['bachelor', 'bs', 'b.s.', 'ba', 'b.a.', 'btech', 'b.tech', 'be', 'b.e']
        ],
        'institutions' => 'university|college|institute|school',
        'graduation_patterns' => [
            '/(?:19|20)\d{2}(?:\s*-\s*(?:19|20)\d{2})?/i',
            '/graduated|graduation|class of/i'
        ]
    ];

    public function extractTextFromPDF($file_path) {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($file_path);
            return $pdf->getText();
        } catch (Exception $e) {
            return false;
        }
    }

    public function findSkills($text) {
        $text = strtolower($text);
        $found_skills = [];
        $skill_levels = [];
        $variations = $this->getSkillVariations();
        
        foreach ($this->skills_keywords as $category => $skills) {
            $found_skills[$category] = [];
            foreach ($skills as $skill) {
                $skill_variations = array_merge([$skill], $variations[$skill] ?? []);
                foreach ($skill_variations as $variation) {
                    if (preg_match('/\b' . preg_quote($variation, '/') . '\b/i', $text)) {
                        if (!in_array($skill, $found_skills[$category])) {
                            $found_skills[$category][] = $skill;
                            
                            // Try to determine skill level
                            $skill_level = $this->determineSkillLevel($text, $variation);
                            if ($skill_level) {
                                $skill_levels[$skill] = $skill_level;
                            }
                        }
                        break;
                    }
                }
            }
        }
        
        return ['skills' => $found_skills, 'levels' => $skill_levels];
    }

    private function determineSkillLevel($text, $skill) {
        $level_indicators = [
            'expert' => ['expert in', 'advanced', 'proficient', 'mastery'],
            'intermediate' => ['intermediate', 'working knowledge', 'good understanding'],
            'beginner' => ['basic', 'fundamental', 'beginner', 'learning']
        ];

        $skill_context = $this->extractSkillContext($text, $skill);
        foreach ($level_indicators as $level => $indicators) {
            foreach ($indicators as $indicator) {
                if (strpos($skill_context, $indicator) !== false) {
                    return $level;
                }
            }
        }
        return null;
    }

    private function extractSkillContext($text, $skill) {
        // Extract 100 characters before and after the skill mention
        $pattern = '/(.{0,100})' . preg_quote($skill, '/') . '(.{0,100})/i';
        if (preg_match($pattern, $text, $matches)) {
            return strtolower($matches[0]);
        }
        return '';
    }

    public function determineExperienceLevel($text) {
        $text = strtolower($text);
        
        foreach ($this->experience_indicators as $level => $indicators) {
            foreach ($indicators as $indicator) {
                if (strpos($text, strtolower($indicator)) !== false) {
                    return $level;
                }
            }
        }
        
        return 'entry level';
    }

    private function getSkillVariations() {
        $variations = [];
        foreach ($this->skills_keywords as $category => $skills) {
            foreach ($skills as $skill) {
                // Add common variations
                $variations[$skill] = [
                    str_replace('.', '', $skill), // Remove dots
                    str_replace('.js', '', $skill), // Remove .js
                    str_replace('-', ' ', $skill), // Replace hyphens with spaces
                    str_replace(' ', '', $skill), // Remove spaces
                ];
            }
        }
        return $variations;
    }

    private function calculateTotalExperience($text) {
        $total_months = 0;
        $date_patterns = [
            // Match date ranges like "2018 - 2021" or "Jan 2018 - Dec 2021"
            '/(?:(?:jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)[a-z]* )?(\d{4})\s*-\s*(?:(?:jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)[a-z]* )?(\d{4}|\bpresent\b)/i'
        ];

        foreach ($date_patterns as $pattern) {
            preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $start_year = intval($match[1]);
                $end_year = strtolower($match[2]) === 'present' ? date('Y') : intval($match[2]);
                $total_months += ($end_year - $start_year) * 12;
            }
        }

        return $total_months;
    }

    public function analyzeResume($text) {
        $analysis = [
            'skills' => $this->findSkills($text),
            'experience_level' => $this->determineExperienceLevel($text),
            'education' => $this->findEducation($text),
            'total_experience' => $this->calculateTotalExperience($text),
            'key_achievements' => $this->findKeyAchievements($text)
        ];
        
        return $analysis;
    }

    private function findEducation($text) {
        $education = [];
        $text = strtolower($text);

        // Find degrees
        foreach ($this->education_keywords['degrees'] as $level => $keywords) {
            foreach ($keywords as $keyword) {
                if (preg_match('/\b' . preg_quote($keyword, '/') . '\b/i', $text)) {
                    $education['degree_level'] = $level;
                    break 2;
                }
            }
        }

        // Find institutions
        if (preg_match('/(' . $this->education_keywords['institutions'] . ')\s+of\s+[\w\s]+/i', $text, $matches)) {
            $education['institution'] = trim($matches[0]);
        }

        // Find graduation year
        foreach ($this->education_keywords['graduation_patterns'] as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $education['graduation_year'] = trim($matches[0]);
                break;
            }
        }

        return $education;
    }

    private function findKeyAchievements($text) {
        $achievements = [];
        $achievement_indicators = [
            '/achieved|accomplished|developed|implemented|led|managed|created|designed|improved|increased|reduced|saved|awarded|recognized/i'
        ];

        // Split text into sentences
        $sentences = preg_split('/[.!?]+/', $text);
        
        foreach ($sentences as $sentence) {
            foreach ($achievement_indicators as $pattern) {
                if (preg_match($pattern, $sentence)) {
                    $achievements[] = trim($sentence);
                    break;
                }
            }
        }

        return array_slice($achievements, 0, 5); // Return top 5 achievements
    }
}

$message = '';
$skills = [];
$experience_level = '';
$resume_text = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["resume"])) {
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $target_file = $target_dir . basename($_FILES["resume"]["name"]);
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    if ($file_type != "pdf") {
        $message = "Sorry, only PDF files are allowed.";
    } else {
        if (move_uploaded_file($_FILES["resume"]["tmp_name"], $target_file)) {
            $analyzer = new ResumeAnalyzer();
            $resume_text = $analyzer->extractTextFromPDF($target_file);
            
            if ($resume_text) {
                $analysis = $analyzer->analyzeResume($resume_text);
                $skills_analysis = $analysis['skills'];
                $experience_level = $analysis['experience_level'];
                $education = $analysis['education'];
                $total_experience = $analysis['total_experience'];
                $achievements = $analysis['key_achievements'];
                $message = "Resume analyzed successfully!";
            } else {
                $message = "Error extracting text from PDF.";
            }
        } else {
            $message = "Sorry, there was an error uploading your file.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resume Analyzer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #333;
        }
        .upload-form {
            margin: 20px 0;
            padding: 20px;
            border: 2px dashed #ccc;
            border-radius: 4px;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .skills-section {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .skill-category {
            margin: 10px 0;
        }
        .skill-tag {
            display: inline-block;
            padding: 5px 10px;
            margin: 2px;
            background-color: #007bff;
            color: white;
            border-radius: 15px;
            font-size: 14px;
        }
        .experience-level {
            padding: 10px;
            margin: 10px 0;
            background-color: #e9ecef;
            border-radius: 4px;
            font-weight: bold;
        }
        .skill-tag small {
            opacity: 0.8;
            font-size: 0.8em;
            margin-left: 3px;
        }
        
        .skill-category {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 6px;
        }
        
        .skill-category h3 {
            color: #495057;
            margin-bottom: 10px;
            font-size: 1.1em;
        }

        .education-section,
        .experience-summary,
        .achievements-section {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 6px;
            border-left: 4px solid #007bff;
        }

        .achievements-section ul {
            list-style-type: none;
            padding-left: 0;
        }

        .achievements-section li {
            margin-bottom: 10px;
            padding-left: 20px;
            position: relative;
        }

        .achievements-section li:before {
            content: "•";
            position: absolute;
            left: 0;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Resume Analyzer</h1>
        
        <div class="upload-form">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                <div>
                    <label for="resume">Upload Resume (PDF only):</label>
                    <input type="file" name="resume" id="resume" accept=".pdf" required>
                </div>
                <div style="margin-top: 10px;">
                    <button type="submit">Analyze Resume</button>
                </div>
            </form>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($skills_analysis['skills'])): ?>
            <div class="skills-section">
                <h2>Detected Skills</h2>
                <?php foreach ($skills_analysis['skills'] as $category => $category_skills): ?>
                    <?php if (!empty($category_skills)): ?>
                        <div class="skill-category">
                            <h3><?php echo ucfirst(str_replace('_', ' ', $category)); ?>:</h3>
                            <?php foreach ($category_skills as $skill): ?>
                                <span class="skill-tag">
                                    <?php echo htmlspecialchars($skill); ?>
                                    <?php if (isset($skills_analysis['levels'][$skill])): ?>
                                        <small>(<?php echo htmlspecialchars($skills_analysis['levels'][$skill]); ?>)</small>
                                    <?php endif; ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div class="experience-level">
                Experience Level: <?php echo ucfirst($experience_level); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($education)): ?>
            <div class="education-section">
                <h2>Education</h2>
                <div class="education-details">
                    <?php if (isset($education['degree_level'])): ?>
                        <p><strong>Degree Level:</strong> <?php echo ucfirst($education['degree_level']); ?></p>
                    <?php endif; ?>
                    <?php if (isset($education['institution'])): ?>
                        <p><strong>Institution:</strong> <?php echo htmlspecialchars($education['institution']); ?></p>
                    <?php endif; ?>
                    <?php if (isset($education['graduation_year'])): ?>
                        <p><strong>Graduation:</strong> <?php echo htmlspecialchars($education['graduation_year']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($total_experience)): ?>
            <div class="experience-summary">
                <h2>Experience Summary</h2>
                <p>Total Experience: <?php echo floor($total_experience/12) . ' years ' . ($total_experience%12) . ' months'; ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($achievements)): ?>
            <div class="achievements-section">
                <h2>Key Achievements</h2>
                <ul>
                    <?php foreach ($achievements as $achievement): ?>
                        <li><?php echo htmlspecialchars($achievement); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
