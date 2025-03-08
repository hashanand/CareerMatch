<?php
function formatSalary($amount, $period = null) {
    if (is_numeric($amount)) {
        return '$' . number_format($amount, 2) . ($period ? '/' . $period : '');
    }
    return $amount;
}

function extractSalaryFromText($text) {
    // Remove any extra whitespace
    $text = trim($text);
    
    // Check if it's an hourly rate
    if (preg_match('/\$?([\d,.]+)\s*(?:an?\s*hour|\/\s*hr|\/\s*hour|per\s*hour)/i', $text, $matches)) {
        return '$' . number_format((float)str_replace(',', '', $matches[1]), 2) . '/hour';
    }
    
    // Check if it's a range with hourly rates
    if (preg_match('/\$?([\d,.]+)\s*-\s*\$?([\d,.]+)\s*(?:an?\s*hour|\/\s*hr|\/\s*hour|per\s*hour)/i', $text, $matches)) {
        $min = number_format((float)str_replace(',', '', $matches[1]), 2);
        $max = number_format((float)str_replace(',', '', $matches[2]), 2);
        return '$' . $min . ' - $' . $max . '/hour';
    }
    
    // Check if it's a yearly salary range
    if (preg_match('/\$?([\d,.]+)K?\s*-\s*\$?([\d,.]+)K?\s*(?:a\s*year|\/\s*year|per\s*year|yearly|annually)/i', $text, $matches)) {
        $min = (float)str_replace(',', '', $matches[1]);
        $max = (float)str_replace(',', '', $matches[2]);
        // Convert K notation to full numbers
        if (stripos($matches[1], 'K') !== false) $min *= 1000;
        if (stripos($matches[2], 'K') !== false) $max *= 1000;
        return '$' . number_format($min) . ' - $' . number_format($max) . '/year';
    }
    
    return $text;
}

function fetchJobsFromSerpApi($skills) {
    $api_key = 'c3df845defd3cf9ef506074c06c3edb2c3998454150c70995d1d3131b26e3d56';
    
    // Convert skills array to search query
    $skillsQuery = implode(' OR ', array_map(function($skill) {
        return '"' . trim($skill) . '"';
    }, $skills));
    
    // Build the API URL
    $url = "https://serpapi.com/search.json?" . http_build_query([
        'engine' => 'google_jobs',
        'q' => $skillsQuery,
        'google_domain' => 'google.com',
        'api_key' => $api_key
    ]);
    
    // Make the API request
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    
    // Extract and format job listings
    $jobs = [];
    if (isset($data['jobs_results'])) {
        foreach ($data['jobs_results'] as $job) {
            // Format salary information
            $salary = '';
            
            // Try to get salary from detected extensions first
            if (isset($job['detected_extensions']['salary_range'])) {
                $salary = extractSalaryFromText($job['detected_extensions']['salary_range']);
            } elseif (isset($job['detected_extensions']['salary'])) {
                $salary = extractSalaryFromText($job['detected_extensions']['salary']);
            } elseif (isset($job['salary_range'])) {
                $salary = extractSalaryFromText($job['salary_range']);
            } elseif (isset($job['salary'])) {
                if (is_array($job['salary'])) {
                    if (isset($job['salary']['min']) || isset($job['salary']['max'])) {
                        $min = $job['salary']['min'] ?? null;
                        $max = $job['salary']['max'] ?? null;
                        $period = strtolower($job['salary']['period'] ?? '');
                        
                        // Convert period to standardized format
                        if (strpos($period, 'hour') !== false) {
                            $period = 'hour';
                        } elseif (strpos($period, 'year') !== false) {
                            $period = 'year';
                        } elseif (strpos($period, 'month') !== false) {
                            $period = 'month';
                        }

                        if ($min && $max) {
                            $salary = formatSalary($min) . ' - ' . formatSalary($max, $period);
                        } elseif ($min) {
                            $salary = 'From ' . formatSalary($min, $period);
                        } elseif ($max) {
                            $salary = 'Up to ' . formatSalary($max, $period);
                        }
                    } elseif (isset($job['salary']['snippet'])) {
                        $salary = extractSalaryFromText($job['salary']['snippet']);
                    }
                } else {
                    $salary = extractSalaryFromText($job['salary']);
                }
            }

            // Clean up salary string
            if (!empty($salary)) {
                // Ensure dollar sign is present
                if (strpos($salary, '$') === false && preg_match('/\d/', $salary)) {
                    $salary = '$' . $salary;
                }
                // Remove any duplicate dollar signs
                $salary = preg_replace('/\${2,}/', '$', $salary);
                // Ensure proper spacing around ranges
                $salary = preg_replace('/\s*-\s*/', ' - ', $salary);
                // Add /hour if amount looks like hourly rate without period
                if (strpos($salary, '/') === false && preg_match('/^\$?\d{1,3}(\.\d{2})?$/', $salary)) {
                    $salary .= '/hour';
                }
            } else {
                $salary = 'Salary not specified';
            }

            // Format posting time
            $posted_at = '';
            if (isset($job['detected_extensions']['posted_at'])) {
                $posted_at = formatTimeAgo($job['detected_extensions']['posted_at']);
            } elseif (isset($job['posted_at'])) {
                $posted_at = formatTimeAgo($job['posted_at']);
            }

            // Get job type with fallback options
            $job_type = '';
            if (isset($job['detected_extensions']['schedule_type'])) {
                $job_type = $job['detected_extensions']['schedule_type'];
            } elseif (isset($job['job_type'])) {
                $job_type = $job['job_type'];
            } elseif (isset($job['detected_extensions']['work_type'])) {
                $job_type = $job['detected_extensions']['work_type'];
            }

            $jobs[] = [
                'title' => $job['title'] ?? '',
                'company' => $job['company_name'] ?? '',
                'company_logo' => $job['thumbnail'] ?? null,
                'location' => $job['location'] ?? '',
                'description' => $job['description'] ?? '',
                'salary' => $salary,
                'posted_at' => $posted_at ?: 'Recently posted',
                'url' => $job['link'] ?? '',
                'job_type' => $job_type ?: 'Not specified',
                'skills_match' => calculateSkillsMatch($job['description'] ?? '', $skills),
                'has_salary' => $salary !== 'Salary not specified',
                'is_hourly' => strpos(strtolower($salary), '/hour') !== false
            ];
        }
    }
    
    return $jobs;
}

function formatTimeAgo($timestamp) {
    if (is_numeric($timestamp)) {
        $timestamp = '@' . $timestamp;
    }
    
    $time = strtotime($timestamp);
    $current = time();
    $diff = $current - $time;
    
    $intervals = array(
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    );
    
    foreach ($intervals as $seconds => $label) {
        $interval = floor($diff / $seconds);
        if ($interval >= 1) {
            $plural = $interval > 1 ? 's' : '';
            return $interval . ' ' . $label . $plural . ' ago';
        }
    }
    
    return 'Just now';
}

function calculateSkillsMatch($description, $skills) {
    $description = strtolower($description);
    $totalScore = 0;
    $matchedSkills = [];
    
    // Define skill importance weights
    $skillWeights = [
        'programming' => 1.5,    // Programming languages
        'framework' => 1.3,      // Frameworks
        'database' => 1.2,       // Database technologies
        'tool' => 1.1,          // Development tools
        'soft' => 1.0           // Soft skills
    ];
    
    // Common variations of skill names
    $skillVariations = [
        'js' => ['javascript', 'js', 'ecmascript'],
        'python' => ['python', 'py'],
        'react' => ['react', 'reactjs', 'react.js'],
        'node' => ['node', 'nodejs', 'node.js'],
        'postgres' => ['postgresql', 'postgres'],
        'mysql' => ['mysql', 'mariadb'],
        'aws' => ['aws', 'amazon web services'],
        'ui' => ['ui', 'user interface'],
        'ux' => ['ux', 'user experience'],
        'c#' => ['c#', 'csharp', 'c sharp'],
        'cpp' => ['c++', 'cpp'],
    ];
    
    foreach ($skills as $skill) {
        $skill = strtolower(trim($skill));
        $found = false;
        $weight = 1.0; // Default weight
        
        // Determine skill weight based on category
        if (preg_match('/(java|python|php|javascript|ruby|c\+\+|c#|swift|kotlin|go)/i', $skill)) {
            $weight = $skillWeights['programming'];
        } elseif (preg_match('/(react|angular|vue|django|laravel|spring|flask)/i', $skill)) {
            $weight = $skillWeights['framework'];
        } elseif (preg_match('/(sql|mysql|postgresql|mongodb|oracle|redis)/i', $skill)) {
            $weight = $skillWeights['database'];
        } elseif (preg_match('/(git|docker|kubernetes|jenkins|aws|azure)/i', $skill)) {
            $weight = $skillWeights['tool'];
        }
        
        // Check for direct match
        if (preg_match('/\b' . preg_quote($skill, '/') . '\b/i', $description)) {
            $found = true;
        } else {
            // Check for variations of the skill
            foreach ($skillVariations as $baseSkill => $variations) {
                if (in_array($skill, $variations)) {
                    foreach ($variations as $variant) {
                        if (preg_match('/\b' . preg_quote($variant, '/') . '\b/i', $description)) {
                            $found = true;
                            break 2;
                        }
                    }
                }
            }
        }
        
        // Check for related terms
        if (!$found) {
            $relatedTerms = [
                'frontend' => ['front-end', 'front end', 'ui', 'user interface'],
                'backend' => ['back-end', 'back end', 'server-side', 'api'],
                'fullstack' => ['full-stack', 'full stack', 'end-to-end'],
                'testing' => ['test', 'qa', 'quality assurance', 'unit testing'],
                'mobile' => ['android', 'ios', 'mobile development', 'app development']
            ];
            
            foreach ($relatedTerms as $category => $terms) {
                if (in_array($skill, $terms)) {
                    foreach ($terms as $term) {
                        if (preg_match('/\b' . preg_quote($term, '/') . '\b/i', $description)) {
                            $found = true;
                            $weight *= 0.8; // Slightly lower weight for related terms
                            break 2;
                        }
                    }
                }
            }
        }
        
        if ($found) {
            $matchedSkills[] = [
                'skill' => $skill,
                'weight' => $weight
            ];
            $totalScore += $weight;
        }
    }
    
    // Calculate weighted percentage
    $maxPossibleScore = count($skills) * max($skillWeights);
    $percentage = ($totalScore / $maxPossibleScore) * 100;
    
    // Bonus for matching multiple skills
    $matchCount = count($matchedSkills);
    if ($matchCount >= 3) {
        $percentage += 10; // Bonus for matching 3 or more skills
    }
    
    // Cap at 100%
    return min(100, round($percentage));
}

function saveJobsToSession($jobs) {
    $_SESSION['job_matches'] = [
        'jobs' => $jobs,
        'timestamp' => time(),
        'total' => count($jobs)
    ];
} 