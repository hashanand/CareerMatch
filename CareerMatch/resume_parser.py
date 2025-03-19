import json
import sys
import re
import os
import nltk
import pandas as pd
import numpy as np
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.multiclass import OneVsRestClassifier
from sklearn.svm import LinearSVC
from nltk.tokenize import word_tokenize, sent_tokenize
from nltk.corpus import stopwords
import pdfplumber
from docx import Document
import datetime

# Download required NLTK data
try:
    nltk.data.find('tokenizers/punkt')
    nltk.data.find('corpora/stopwords')
except LookupError:
    nltk.download('punkt')
    nltk.download('stopwords')
    nltk.download('averaged_perceptron_tagger')
    nltk.download('maxent_ne_chunker')
    nltk.download('words')

class ResumeParser:
    def __init__(self):
        self.stop_words = set(stopwords.words('english'))
        
        # Categories and their keywords
        self.categories = {
            'education': [
                'education', 'degree', 'university', 'college', 'school', 'bachelor',
                'master', 'phd', 'mba', 'btech', 'mtech', 'diploma', 'certification'
            ],
            'experience': [
                'experience', 'work', 'employment', 'job', 'career', 'position',
                'role', 'company', 'organization', 'employer'
            ],
            'skills': [
                'skills', 'technologies', 'programming', 'languages', 'tools',
                'frameworks', 'methodologies', 'expertise', 'proficient'
            ],
            'projects': [
                'projects', 'assignments', 'implementations', 'developments',
                'applications', 'solutions', 'deliverables'
            ],
            'achievements': [
                'achievements', 'awards', 'honors', 'accomplishments', 'recognition',
                'certifications', 'accolades'
            ]
        }
        
        # Initialize ML components
        self.vectorizer = TfidfVectorizer(
            stop_words='english',
            max_features=1000,
            ngram_range=(1, 2)
        )
        
        self.classifier = OneVsRestClassifier(LinearSVC(random_state=42))
        
        # Education-related patterns
        self.education_patterns = [
            r"(?i)(bachelor|master|phd|doctorate|bs|ba|ms|ma|mba|b\.tech|m\.tech|b\.e|m\.e).*?(?=\n|$)",
            r"(?i)(university|college|institute|school).*?(?=\n|$)",
            r"(?i)gpa:?\s*\d+\.?\d*\s*\/?\s*\d*\.?\d*"
        ]
        
        # Year patterns
        self.year_pattern = r"(19|20)\d{2}\s*-\s*(19|20)\d{2}"
        
        # Common skills
        self.skills_list = [
            "Project Management", "Public Relations", "Teamwork", "Time Management",
            "Leadership", "Communication", "Critical Thinking", "Problem Solving",
            "Strategic Planning", "Research", "Data Analysis", "Marketing",
            "Social Media", "Customer Service", "Sales", "Negotiation",
            "Business Development", "Financial Analysis", "Risk Management",
            "Quality Assurance", "Agile", "Scrum", "Product Management",
            "Team Leadership", "Process Improvement", "Budget Management",
            "Strategic Planning", "Presentation Skills", "Client Relations",
            "Business Strategy", "Operations Management", "Change Management"
        ]

    def extract_text_from_pdf(self, pdf_path):
        try:
            with pdfplumber.open(pdf_path) as pdf:
                text = ""
                for page in pdf.pages:
                    text += page.extract_text() + "\n"
                return text
        except Exception as e:
            print(f"Error extracting text from PDF: {str(e)}")
            return None

    def extract_text_from_docx(self, docx_path):
        text = ""
        try:
            doc = Document(docx_path)
            for paragraph in doc.paragraphs:
                text += paragraph.text + "\n"
        except Exception as e:
            print(f"Error extracting text from DOCX: {str(e)}")
        return text

    def preprocess_text(self, text):
        # Convert to lowercase and tokenize
        tokens = word_tokenize(text.lower())
        # Remove stopwords and non-alphabetic tokens
        tokens = [t for t in tokens if t not in self.stop_words and t.isalnum()]
        return " ".join(tokens)

    def clean_text(self, text):
        # Remove extra whitespace and newlines
        text = re.sub(r'\s+', ' ', text)
        # Remove special characters except basic punctuation
        text = re.sub(r'[^\w\s.,;:-]', '', text)
        return text.strip()

    def extract_education(self, text):
        education = []
        lines = text.split('\n')
        current_entry = {}
        in_education_section = False
        
        for line in lines:
            line = line.strip()
            if not line:
                continue
            
            # Check if we're in the education section
            if re.search(r'\bEDUCATION\b', line.upper()):
                in_education_section = True
                continue
            
            # If we find a section that's not education, stop processing
            if in_education_section and re.search(r'\b(EXPERIENCE|SKILLS|PROJECTS)\b', line.upper()):
                in_education_section = False
                break
                
            if not in_education_section:
                continue
                
            # Look for year ranges
            year_match = re.search(self.year_pattern, line)
            if year_match:
                if current_entry and any(current_entry.values()):
                    education.append(current_entry)
                current_entry = {'years': year_match.group()}
                continue
            
            # Look for university/institution names
            if re.search(r'(?i)university|college|institute|school', line) and not re.search(r'(?i)(develop|execute|strateg)', line):
                if 'institution' not in current_entry:
                    current_entry['institution'] = line.strip()
                continue
            
            # Look for degree information
            degree_match = re.search(r'(?i)(bachelor|master|phd|doctorate|bs|ba|ms|ma|mba|b\.tech|m\.tech|b\.e|m\.e)\s+(?:of|in)?\s*([\w\s]+?)(?:\s+(?:that|and|with|to)\s+|$)', line)
            if degree_match and not re.search(r'(?i)(develop|execute|strateg)', line):
                if 'degree' not in current_entry:
                    degree = degree_match.group(1).strip()
                    field = degree_match.group(2).strip() if degree_match.group(2) else ''
                    current_entry['degree'] = f"{degree} of {field}" if field else degree
                continue
            
            # Also look for standalone "Bachelor" or "Master" followed by "of Business"
            simple_degree_match = re.search(r'(?i)(bachelor|master)\s+of\s+business(?:\s+|$)', line)
            if simple_degree_match and not re.search(r'(?i)(develop|execute|strateg)', line):
                if 'degree' not in current_entry:
                    current_entry['degree'] = simple_degree_match.group(0).strip()
                continue
                
            # Look for GPA
            gpa_match = re.search(r'(?i)gpa:?\s*(\d+\.?\d*\s*\/?\s*\d*\.?\d*)', line)
            if gpa_match:
                current_entry['gpa'] = gpa_match.group(1)
        
        if current_entry and any(current_entry.values()):
            education.append(current_entry)
            
        # Clean up and combine education entries
        cleaned_education = []
        entries_by_year = {}
        
        for edu in education:
            if ('degree' in edu or 'institution' in edu) and 'years' in edu:
                year = edu['years']
                if year not in entries_by_year:
                    entries_by_year[year] = {
                        'years': year,
                        'institution': '',
                        'degree': '',
                        'gpa': ''
                    }
                
                # Update the entry with non-empty values
                if edu.get('institution'):
                    entries_by_year[year]['institution'] = edu['institution']
                if edu.get('degree'):
                    entries_by_year[year]['degree'] = edu['degree']
                if edu.get('gpa'):
                    entries_by_year[year]['gpa'] = edu['gpa']
        
        # Convert combined entries to list
        cleaned_education = list(entries_by_year.values())
        
        # Sort by years (most recent first)
        cleaned_education.sort(key=lambda x: x['years'], reverse=True)
        
        return cleaned_education

    def extract_skills(self, text):
        skills = []
        
        # Extract skills from predefined list
        for skill in self.skills_list:
            if re.search(rf'\b{re.escape(skill)}\b', text, re.IGNORECASE):
                skills.append(skill)
        
        return sorted(list(set(skills)))

    def extract_experience(self, text):
        experience = []
        sections = re.split(r'\n{2,}', text)
        
        experience_section = None
        for section in sections:
            if re.search(r'\b(?:EXPERIENCE|EMPLOYMENT|WORK|INTERNSHIP)\b', section.upper()):
                experience_section = section
                break
        
        if not experience_section:
            return []
        
        # Split into entries by date patterns
        entries = re.split(r'(?:\b(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\.?\s*\d{4})', experience_section)
        current_exp = None
        
        for entry in entries:
            entry = self.clean_text(entry)
            if not entry:
                continue
            
            # Look for company and position
            company_match = re.search(r'(?i)(?:at|with|for)?\s*([\w\s&]+(?:Technologies|Company|Corp|Inc|Ltd|Limited|LLC))', entry)
            position_match = re.search(r'(?i)([\w\s]+(?:Engineer|Developer|Manager|Director|Analyst|Consultant|Intern|Trainee))', entry)
            
            if company_match or position_match:
                if current_exp:
                    experience.append(current_exp)
                
                current_exp = {
                    'company': company_match.group(1).strip() if company_match else '',
                    'position': position_match.group(1).strip() if position_match else '',
                    'duration': '',
                    'responsibilities': []
                }
                
                # Look for duration
                duration_match = re.search(r'(?i)([A-Za-z]+\s*\d{4}\s*(?:-|to)\s*(?:present|[A-Za-z]+\s*\d{4}))', entry)
                if duration_match:
                    current_exp['duration'] = duration_match.group(1)
                
                # Extract responsibilities
                lines = [line.strip() for line in entry.split('\n') if line.strip()]
                for line in lines:
                    if len(line) > 10 and not any(keyword in line.lower() for keyword in ['company', 'technologies', 'corp', 'inc', 'ltd', 'limited']):
                        if not any(resp in line for resp in current_exp['responsibilities']):  # Avoid duplicates
                            current_exp['responsibilities'].append(line)
        
        # Add the last experience entry
        if current_exp:
            experience.append(current_exp)
        
        return experience

    def extract_projects(self, text):
        projects = []
        sections = re.split(r'\n{2,}', text)
        
        projects_section = None
        for section in sections:
            if re.search(r'\b(?:PROJECTS?|ASSIGNMENTS?)\b', section.upper()):
                projects_section = section
                break
        
        if not projects_section:
            return []
        
        # Split into entries by project indicators
        entries = re.split(r'(?:\d+\.|•|\*|\-|\n{2,})', projects_section)
        current_project = None
        
        for entry in entries:
            entry = self.clean_text(entry)
            if not entry or len(entry) < 10:
                continue
            
            # Look for project name at the start of entry
            name_match = re.search(r'^([^:.]{3,50})', entry)
            if name_match:
                if current_project:
                    projects.append(current_project)
                
                name = name_match.group(1).strip()
                if len(name) <= 50 and not any(keyword in name.lower() for keyword in ['contact', 'email', 'phone', 'address']):
                    current_project = {
                        'name': name,
                        'technologies': [],
                        'description': []
                    }
                    
                    # Extract technologies
                    tech_matches = re.finditer(r'(?i)(?:using|with|in|technologies?:?)\s*([\w\s,]+)', entry)
                    for match in tech_matches:
                        techs = [t.strip() for t in match.group(1).split(',')]
                        current_project['technologies'].extend(t for t in techs if len(t) > 2)
                    
                    # Add description
                    desc = entry.replace(name, '').strip()
                    if len(desc) > 10:
                        current_project['description'].append(desc)
        
        # Add the last project entry
        if current_project:
            projects.append(current_project)
        
        # Clean up projects
        cleaned_projects = []
        for project in projects:
            # Remove duplicates and invalid entries
            if project['name'] and not any(p['name'] == project['name'] for p in cleaned_projects):
                if not any(keyword in project['name'].lower() for keyword in ['contact', 'email', 'phone', 'address', 'education', 'experience']):
                    cleaned_projects.append(project)
        
        return cleaned_projects

    def extract_achievements(self, text):
        achievements = []
        sentences = sent_tokenize(text)
        
        achievement_keywords = [
            'achieved', 'awarded', 'earned', 'received', 'won',
            'recognized', 'selected', 'honored', 'certified',
            'accomplished', 'completed', 'succeeded'
        ]
        
        for sentence in sentences:
            sentence = self.clean_text(sentence)
            words = word_tokenize(sentence.lower())
            
            # Check if sentence contains achievement keywords
            if any(keyword in words for keyword in achievement_keywords):
                # Validate achievement context
                if re.search(r'(?i)(award|certification|recognition|achievement|honor|prize)', sentence):
                    # Clean up the achievement text
                    achievement = sentence
                    # Remove date patterns
                    achievement = re.sub(r'\b(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\.?\s*\d{4}\b', '', achievement)
                    # Remove common prefixes
                    achievement = re.sub(r'(?i)^(?:- |\* |• )', '', achievement)
                    
                    achievement = self.clean_text(achievement)
                    if achievement and len(achievement) > 10:
                        achievements.append(achievement)
        
        return achievements

    def parse_resume(self, pdf_path):
        text = self.extract_text_from_pdf(pdf_path)
        if not text:
            return {
                "error": "Failed to extract text from PDF",
                "data": {
                    "education": [],
                    "skills": []
                }
            }

        # Extract information
        education = self.extract_education(text)
        skills = self.extract_skills(text)
        
        # Return structured data
        return {
            "data": {
                "education": education,
                "skills": skills
            }
        }

    def save_to_json(self, data, output_file):
        try:
            with open(output_file, 'w', encoding='utf-8') as f:
                json.dump(data, f, indent=2, ensure_ascii=False)
            return True
        except Exception as e:
            print(f"Error saving to JSON: {str(e)}")
            return False

def main():
    if len(sys.argv) != 2:
        print("Usage: python resume_parser.py <pdf_path>")
        sys.exit(1)

    pdf_path = sys.argv[1]
    parser = ResumeParser()
    
    try:
        result = parser.parse_resume(pdf_path)
        print(json.dumps(result))
    except Exception as e:
        print(f"Error: {str(e)}")
        sys.exit(1)

if __name__ == "__main__":
    main() 