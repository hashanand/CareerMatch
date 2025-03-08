# CareerMatch - Resume Parser and Analysis System

CareerMatch is a Smart Job Recommendation Platform designed to streamline the job application process for fresh graduates. This system includes a **Resume Parser and Analysis System** that extracts, structures, and analyzes resume data to match candidates with relevant job opportunities. 

## Features

### 1. Automated Resume Processing
- Extracts and structures information from **PDF** and **DOCX** resume files.
- Eliminates manual data entry and reduces human error.
- Saves significant time for HR personnel and recruiters.

### 2. Standardized Information Extraction
- Parses and organizes key resume details:
  - **Education history** (degrees, institutions, GPAs, years)
  - **Professional skills**
  - **Work experience**
  - **Projects**
  - **Achievements**

### 3. Smart Skills Matching
- Uses **machine learning** (`TfidfVectorizer` and `LinearSVC`) to categorize and match skills.
- Ensures standardized skill descriptions across different resume formats.

### 4. Integration with Job Matching
- Extracted data is used to **automatically match candidates** with relevant job postings.
- Direct integration with the job search feature (`job-matches.php`).

### 5. User-Friendly Interface
- Modern **web interface** for uploading resumes.
- Supports **local file uploads** and **Google Drive integration** (planned for future updates).
- Displays parsed resume data in an organized and intuitive format.

### 6. Data Organization
- Stores parsed resume data in a **structured database**.
- Enables easy search and filtering of candidates.
- Maintains a history of uploaded resumes.

### 7. Error Handling and Validation
- **Validates** file formats and sizes before processing.
- Provides **clear error messages** for invalid files.
- Ensures **data quality** through robust parsing algorithms.

## Technologies Used
- **Backend:** PHP, MySQL
- **Natural Language Processing:** NLTK
- **Machine Learning:** `TfidfVectorizer`, `LinearSVC`
- **Frontend:** HTML, CSS, JavaScript

## Installation & Setup
1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/careermatch.git
   cd careermatch
   ```
2. Install dependencies (if applicable):
   ```bash
   composer install
   npm install
   ```
3. Configure the database:
   - Import the provided `database.sql` file into MySQL.
   - Update database credentials in `config.php`.
4. Start the local development server:
   ```bash
   php -S localhost:8000
   ```
5. Access the platform at `http://localhost:8000`

## Future Enhancements
- **Google Drive Integration** for direct resume uploads.
- **Advanced AI-based job matching** using deep learning.
- **Improved resume analysis** with additional NLP techniques.

## Contributing
Contributions are welcome! If you'd like to improve this project:
- Fork the repository
- Create a new branch
- Commit your changes
- Open a Pull Request


---
Developed for the hackathon **HackJKLU v4.0** - **CareerMatch** 🚀
