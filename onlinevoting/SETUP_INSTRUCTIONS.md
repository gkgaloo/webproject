# Online Voting System - Complete Setup Instructions

## Quick Start Guide

### Prerequisites
- Windows OS (or Linux/Mac with appropriate LAMP/MAMP stack)
- XAMPP installed
- Web browser (Chrome, Firefox, Edge)

### Installation Steps

#### 1. Install XAMPP
```
1. Download XAMPP from: https://www.apachefriends.org/
2. Install to C:\xampp
3. Launch XAMPP Control Panel
4. Start Apache and MySQL services
```

#### 2. Setup Project Files
```
1. Project is already in: C:\xampp\htdocs\onlinevoting
2. Verify folder structure:
   - onlinevoting/
     ├── index.html (frontend)
     ├── backend/ (PHP files)
     ├── css/
     └── js/
```

#### 3. Create Database
```
1. Open browser: http://localhost/phpmyadmin
2. Click "New" to create database
3. Database name: voting_system
4. Collation: utf8mb4_unicode_ci
5. Click "Create"
```

#### 4. Import Database Schema
```
1. In phpMyAdmin, select "voting_system" database
2. Click "Import" tab
3. Click "Choose File"
4. Navigate to: C:\xampp\htdocs\onlinevoting\backend\database.sql
5. Click "Go" button
6. Wait for "Import has been successfully finished"
```

#### 5. Verify Database Tables
```
1. Click on "voting_system" in left sidebar
2. You should see 4 tables:
   - users
   - elections
   - candidates
   - votes
3. Click on "users" table
4. You should see 1 admin user
```

#### 6. Test Backend Connection
```
1. Open browser: http://localhost/onlinevoting/backend/config/db.php
2. If no error appears, connection is successful
3. If error appears, check MySQL service is running
```

#### 7. Open Application
```
1. Open browser: http://localhost/onlinevoting
2. Landing page should load with modern design
3. Click "Register" to create account
4. Or login with admin credentials:
   Email: admin@voting.com
   Password: admin123
```

## Default Test Data

### Admin Account
- Email: admin@voting.com
- Password: admin123
- Role: Administrator

### Active Election
- Title: 2025 General Election
- Status: Active
- 4 sample candidates pre-loaded

### Sample Candidates
1. Sarah Johnson - Progressive Alliance
2. Michael Chen - Tech Innovation Party
3. Emily Rodriguez - Green Future Coalition
4. David Thompson - Economic Growth Party

## User Workflows

### For Voters

#### Registration
```
1. Go to: http://localhost/onlinevoting/register.html
2. Fill in:
   - Full Name
   - Email
   - Voter ID (any unique ID like VOTER001)
   - Password (min 6 characters)
   - Confirm Password
3. Click "Register"
4. Redirected to login page
```

#### Login and Vote
```
1. Go to: http://localhost/onlinevoting/login.html
2. Enter email and password
3. Click "Login"
4. Redirected to Voter Dashboard
5. View all candidates
6. Click "Select Candidate" on your choice
7. Click "Cast Your Vote"
8. Confirm your selection
9. Vote recorded - cannot vote again
```

#### View Results
```
1. Go to: http://localhost/onlinevoting/results.html
2. View real-time results
3. See vote counts and percentages
4. Winner highlighted with trophy badge
```

### For Admin

#### Login
```
1. Go to: http://localhost/onlinevoting/login.html
2. Email: admin@voting.com
3. Password: admin123
4. Redirected to Admin Dashboard
```

#### Manage Candidates
```
1. View all candidates with vote counts
2. Add new candidate:
   - Enter name, party, description
   - Choose emoji icon
   - Click "Add Candidate"
3. Remove candidate:
   - Click "Remove" button
   - Confirm deletion
```

#### Control Election
```
1. View voting statistics:
   - Total voters
   - Voted count
   - Pending voters
   - Turnout percentage
2. Toggle election status:
   - Click "Close Election" to stop voting
   - Click "Reopen Election" to allow voting
```

#### View Detailed Results
```
1. Click "Results" in navigation
2. View complete election results
3. See detailed statistics
```

## API Testing (Optional)

### Using Browser Console

Test registration:
```javascript
fetch('http://localhost/onlinevoting/backend/auth/register.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    name: 'Test User',
    voterId: 'TEST001',
    email: 'test@example.com',
    password: 'test123'
  })
}).then(r => r.json()).then(console.log);
```

Test login:
```javascript
fetch('http://localhost/onlinevoting/backend/auth/login.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'test@example.com',
    password: 'test123'
  }),
  credentials: 'include'
}).then(r => r.json()).then(console.log);
```

## Troubleshooting

### Problem: "Database connection failed"
**Solution:**
- Open XAMPP Control Panel
- Ensure MySQL is running (green highlight)
- Click "Admin" next to MySQL to open phpMyAdmin
- Verify `voting_system` database exists

### Problem: "404 Not Found" errors
**Solution:**
- Verify Apache is running in XAMPP
- Check project is in `C:\xampp\htdocs\onlinevoting`
- Clear browser cache
- Try: http://localhost/onlinevoting/index.html

### Problem: "Import has been successfully finished" but tables not visible
**Solution:**
- Refresh phpMyAdmin page
- Click on "voting_system" in left sidebar
- Check if tables appear
- If not, re-import database.sql

### Problem: Login not working
**Solution:**
- Open browser developer console (F12)
- Check for JavaScript errors
- Verify session cookies are enabled
- Try clearing all cookies for localhost

### Problem: Votes not recording
**Solution:**
- Check if election status is "active"
- Verify you haven't already voted
- Check browser console for errors
- Ensure MySQL is running

### Problem: Admin credentials not working
**Solution:**
- Verify database import was successful
- Check users table in phpMyAdmin
- Ensure admin user exists
- Password hash should be present

## File Permissions (Linux/Mac)
```bash
chmod -R 755 /path/to/onlinevoting
chmod -R 777 /path/to/onlinevoting/backend/config
```

## Security Recommendations

### Development
- Current setup is for localhost development
- Default passwords are acceptable for testing

### Production (If deploying)
1. Change admin password
2. Update database credentials
3. Enable HTTPS
4. Set secure session cookies
5. Implement rate limiting
6. Add CSRF protection
7. Enable error logging (not display)
8. Use environment variables for config

## Common MySQL Commands

### View all users:
```sql
SELECT id, name, email, role FROM users;
```

### View active elections:
```sql
SELECT * FROM elections WHERE status = 'active';
```

### View vote counts:
```sql
SELECT c.name, c.party, COUNT(v.id) as votes 
FROM candidates c 
LEFT JOIN votes v ON c.id = v.candidate_id 
GROUP BY c.id;
```

### Reset all votes:
```sql
DELETE FROM votes;
```

### Create new admin:
```sql
INSERT INTO users (name, voter_id, email, password, role) 
VALUES ('New Admin', 'ADM002', 'newadmin@voting.com', 
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
        'admin');
```

## Support
For technical issues:
1. Check XAMPP error logs
2. Check browser console for JavaScript errors
3. Verify database connections
4. Review backend/README.md for API documentation

## Next Steps
1. Create voter accounts and test registration
2. Login as different users and cast votes
3. Login as admin and manage candidates
4. Toggle election status and observe changes
5. Explore the results page
6. Experiment with the API endpoints

Enjoy your secure Online Voting System!
