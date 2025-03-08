CREATE DATABASE IF NOT EXISTS career_match;  
USE career_match;  
CREATE TABLE IF NOT EXISTS saved_jobs (  
  id INT AUTO_INCREMENT PRIMARY KEY,  
  user_id INT NOT NULL,  
  job_title VARCHAR(255) NOT NULL,  
  company VARCHAR(255) NOT NULL,  
  location VARCHAR(255),  
  salary VARCHAR(255),  
  job_type VARCHAR(100),  
  description TEXT,  
  company_logo VARCHAR(255),  
  job_url VARCHAR(1024) NOT NULL,  
  posted_at VARCHAR(100),  
  skills_match DECIMAL(5,2),  
  saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  
  INDEX (user_id)  
); 
