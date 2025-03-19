import pandas as pd
import numpy as np
from sklearn.preprocessing import StandardScaler
from sklearn.ensemble import RandomForestClassifier
from datetime import datetime
import json
import sqlite3
import joblib

class ApplicationTracker:
    def __init__(self):
        self.model = RandomForestClassifier(n_estimators=100, random_state=42)
        self.scaler = StandardScaler()
        self.db_path = 'applications.db'
        self.setup_database()

    def setup_database(self):
        conn = sqlite3.connect(self.db_path)
        cursor = conn.cursor()
        
        # Create applications table
        cursor.execute('''
            CREATE TABLE IF NOT EXISTS applications (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                job_id INTEGER,
                company TEXT,
                position TEXT,
                application_date DATETIME,
                status TEXT,
                response_time INTEGER,
                salary_range TEXT,
                location TEXT,
                job_type TEXT,
                experience_required INTEGER,
                skills_match_score FLOAT,
                interview_scheduled BOOLEAN,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ''')
        
        # Create tracking_metrics table
        cursor.execute('''
            CREATE TABLE IF NOT EXISTS tracking_metrics (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                application_id INTEGER,
                response_rate FLOAT,
                interview_rate FLOAT,
                average_response_time INTEGER,
                success_probability FLOAT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (application_id) REFERENCES applications(id)
            )
        ''')
        
        conn.commit()
        conn.close()

    def preprocess_data(self, data):
        # Convert categorical variables to numerical
        data['job_type_encoded'] = pd.Categorical(data['job_type']).codes
        data['location_encoded'] = pd.Categorical(data['location']).codes
        data['status_encoded'] = pd.Categorical(data['status']).codes
        
        # Extract salary range features
        data[['min_salary', 'max_salary']] = data['salary_range'].str.extract(r'(\d+)k?\s*-\s*(\d+)k?')
        data[['min_salary', 'max_salary']] = data[['min_salary', 'max_salary']].astype(float)
        
        # Select features for model
        features = ['response_time', 'experience_required', 'skills_match_score',
                   'job_type_encoded', 'location_encoded', 'min_salary', 'max_salary']
        
        return data[features]

    def train_model(self, user_id):
        conn = sqlite3.connect(self.db_path)
        data = pd.read_sql(f"SELECT * FROM applications WHERE user_id = {user_id}", conn)
        conn.close()
        
        if len(data) < 5:  # Need minimum data points to train
            return False
        
        X = self.preprocess_data(data)
        y = (data['status'] == 'Accepted').astype(int)  # Binary classification
        
        # Scale features
        X_scaled = self.scaler.fit_transform(X)
        
        # Train model
        self.model.fit(X_scaled, y)
        
        # Save model for this user
        joblib.dump(self.model, f'models/user_{user_id}_model.pkl')
        joblib.dump(self.scaler, f'models/user_{user_id}_scaler.pkl')
        
        return True

    def predict_success_probability(self, application_data, user_id):
        try:
            # Load user's model if exists
            self.model = joblib.load(f'models/user_{user_id}_model.pkl')
            self.scaler = joblib.load(f'models/user_{user_id}_scaler.pkl')
        except:
            # Use default model if no user-specific model exists
            pass
        
        # Preprocess single application
        df = pd.DataFrame([application_data])
        X = self.preprocess_data(df)
        X_scaled = self.scaler.transform(X)
        
        # Predict probability
        prob = self.model.predict_proba(X_scaled)[0][1]
        return float(prob)

    def update_metrics(self, user_id, application_id):
        conn = sqlite3.connect(self.db_path)
        cursor = conn.cursor()
        
        # Calculate metrics
        metrics = {}
        
        # Response rate
        cursor.execute('''
            SELECT COUNT(*) as total,
                   SUM(CASE WHEN status != 'Pending' THEN 1 ELSE 0 END) as responses
            FROM applications
            WHERE user_id = ?
        ''', (user_id,))
        result = cursor.fetchone()
        metrics['response_rate'] = result[1] / result[0] if result[0] > 0 else 0
        
        # Interview rate
        cursor.execute('''
            SELECT COUNT(*) as total,
                   SUM(CASE WHEN interview_scheduled = 1 THEN 1 ELSE 0 END) as interviews
            FROM applications
            WHERE user_id = ?
        ''', (user_id,))
        result = cursor.fetchone()
        metrics['interview_rate'] = result[1] / result[0] if result[0] > 0 else 0
        
        # Average response time
        cursor.execute('''
            SELECT AVG(response_time)
            FROM applications
            WHERE user_id = ? AND response_time IS NOT NULL
        ''', (user_id,))
        metrics['average_response_time'] = cursor.fetchone()[0] or 0
        
        # Get latest application data
        cursor.execute('''
            SELECT * FROM applications WHERE id = ?
        ''', (application_id,))
        app_data = cursor.fetchone()
        
        # Calculate success probability
        if app_data:
            app_dict = {
                'job_type': app_data[10],
                'location': app_data[9],
                'salary_range': app_data[8],
                'experience_required': app_data[11],
                'skills_match_score': app_data[12],
                'response_time': app_data[7]
            }
            metrics['success_probability'] = self.predict_success_probability(app_dict, user_id)
        else:
            metrics['success_probability'] = 0.0
        
        # Store metrics
        cursor.execute('''
            INSERT INTO tracking_metrics
            (user_id, application_id, response_rate, interview_rate, 
             average_response_time, success_probability)
            VALUES (?, ?, ?, ?, ?, ?)
        ''', (user_id, application_id, metrics['response_rate'],
              metrics['interview_rate'], metrics['average_response_time'],
              metrics['success_probability']))
        
        conn.commit()
        conn.close()
        
        return metrics

    def get_insights(self, user_id):
        conn = sqlite3.connect(self.db_path)
        cursor = conn.cursor()
        
        insights = {
            'total_applications': 0,
            'pending_applications': 0,
            'interview_scheduled': 0,
            'success_rate': 0,
            'best_performing_categories': [],
            'recommended_improvements': [],
            'application_trends': []
        }
        
        # Get basic stats
        cursor.execute('''
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN interview_scheduled = 1 THEN 1 ELSE 0 END) as interviews,
                SUM(CASE WHEN status = 'Accepted' THEN 1 ELSE 0 END) as accepted
            FROM applications
            WHERE user_id = ?
        ''', (user_id,))
        
        result = cursor.fetchone()
        if result:
            insights['total_applications'] = result[0]
            insights['pending_applications'] = result[1]
            insights['interview_scheduled'] = result[2]
            insights['success_rate'] = (result[3] / result[0]) * 100 if result[0] > 0 else 0
        
        # Get best performing job types
        cursor.execute('''
            SELECT job_type, COUNT(*) as count,
                   SUM(CASE WHEN status = 'Accepted' THEN 1 ELSE 0 END) as accepted
            FROM applications
            WHERE user_id = ?
            GROUP BY job_type
            HAVING count > 1
            ORDER BY (accepted * 1.0 / count) DESC
            LIMIT 3
        ''', (user_id,))
        
        insights['best_performing_categories'] = [
            {'category': row[0], 'success_rate': (row[2] / row[1]) * 100}
            for row in cursor.fetchall()
        ]
        
        # Generate recommendations
        cursor.execute('''
            SELECT AVG(skills_match_score) as avg_score,
                   AVG(response_time) as avg_response
            FROM applications
            WHERE user_id = ? AND status = 'Accepted'
        ''', (user_id,))
        
        successful_metrics = cursor.fetchone()
        if successful_metrics:
            if successful_metrics[0]:  # Skills match score
                insights['recommended_improvements'].append({
                    'area': 'Skills Match',
                    'recommendation': f'Aim for applications with skills match score above {successful_metrics[0]:.1f}%'
                })
            if successful_metrics[1]:  # Response time
                insights['recommended_improvements'].append({
                    'area': 'Application Speed',
                    'recommendation': f'Try to apply within {successful_metrics[1]:.1f} days of job posting'
                })
        
        # Get application trends
        cursor.execute('''
            SELECT DATE(application_date) as date,
                   COUNT(*) as applications,
                   SUM(CASE WHEN status = 'Accepted' THEN 1 ELSE 0 END) as accepted
            FROM applications
            WHERE user_id = ?
            GROUP BY DATE(application_date)
            ORDER BY date DESC
            LIMIT 30
        ''', (user_id,))
        
        insights['application_trends'] = [
            {
                'date': row[0],
                'applications': row[1],
                'accepted': row[2]
            }
            for row in cursor.fetchall()
        ]
        
        conn.close()
        return insights

if __name__ == "__main__":
    tracker = ApplicationTracker() 