# Online Voting System - Backend Setup Guide

## Overview
This is a secure PHP/MySQL backend for an Online Voting System built with core PHP (no frameworks) and PDO for database access.

## Technology Stack
- **Language**: PHP 7.4+
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **Server**: Apache (XAMPP/WAMP/LAMP)
- **Security**: Password hashing, prepared statements, session-based auth

## Project Structure
```
backend/
├── config/
│   └── db.php                 # Database connection
├── includes/
│   ├── session.php            # Session management
│   └── functions.php          # Utility functions
├── auth/
│   ├── register.php           # User registration
│   ├── login.php              # User login
│   ├── logout.php             # User logout
│   └── check.php              # Auth status check
├── admin/
│   ├── add_candidate.php      # Add candidate
│   ├── edit_candidate.php     # Edit candidate
│   ├── delete_candidate.php   # Delete candidate
│   ├── results.php            # View results
│   └── manage_election.php    # Manage elections
├── voter/
│   ├── cast_vote.php          # Cast vote
│   ├── check_vote_status.php  # Check if voted
│   └── get_candidates.php     # Get candidates list
└── database.sql               # Database schema
```

## Installation Instructions

### Step 1: Install XAMPP
1. Download XAMPP from https://www.apachefriends.org/
2. Install XAMPP on your system
3. Start Apache and MySQL services from XAMPP Control Panel

### Step 2: Create Database
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Create a new database named `voting_system`
3. Import the database schema:
   - Click on `voting_system` database
   - Go to "Import" tab
   - Choose file: `backend/database.sql`
   - Click "Go" to execute

### Step 3: Configure Database Connection
1. Open `backend/config/db.php`
2. Update database credentials if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'voting_system');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // Default XAMPP password is empty
   ```

### Step 4: Verify Installation
1. Place project in `C:\xampp\htdocs\onlinevoting`
2. Open browser: http://localhost/onlinevoting
3. The frontend should load successfully

## Default Admin Credentials
- **Email**: admin@voting.com
- **Password**: admin123

## API Endpoints

### Authentication
- `POST /backend/auth/register.php` - Register new voter
- `POST /backend/auth/login.php` - Login user
- `GET /backend/auth/logout.php` - Logout user
- `GET /backend/auth/check.php` - Check auth status

### Voter Operations
- `GET /backend/voter/get_candidates.php` - Get candidates list
- `POST /backend/voter/cast_vote.php` - Cast vote
- `GET /backend/voter/check_vote_status.php` - Check if voted

### Admin Operations
- `POST /backend/admin/add_candidate.php` - Add candidate
- `POST /backend/admin/edit_candidate.php` - Edit candidate
- `POST /backend/admin/delete_candidate.php` - Delete candidate
- `GET /backend/admin/results.php` - Get voting results
- `GET|POST|DELETE /backend/admin/manage_election.php` - Manage elections

## Security Features

### 1. Password Security
- Passwords hashed using `password_hash()` with bcrypt
- Verified using `password_verify()`

### 2. SQL Injection Prevention
- All queries use PDO prepared statements
- Parameters properly bound and escaped

### 3. Session Security
- HTTP-only session cookies
- Session-based authentication
- Role-based access control (RBAC)

### 4. Input Validation
- Server-side validation for all inputs
- Email format validation
- Password strength requirements (min 6 chars)
- HTML sanitization with `htmlspecialchars()`

### 5. Access Control
- Admin-only routes protected with `require_admin()`
- Voter-only routes protected with `require_voter()`
- Authentication required for sensitive operations

### 6. Vote Integrity
- Unique constraint on (user_id, election_id) prevents duplicate votes
- Foreign key constraints ensure data integrity
- Election status validation before accepting votes

## Database Schema

### users
- `id` (PK)
- `name`, `voter_id` (UNIQUE), `email` (UNIQUE)
- `password` (hashed), `role` (admin/voter)
- Created timestamp

### elections
- `id` (PK)
- `title`, `description`
- `start_date`, `end_date`, `status` (pending/active/closed)
- Timestamps

### candidates
- `id` (PK)
- `name`, `party`, `description`, `image`
- `election_id` (FK -> elections.id)

### votes
- `id` (PK)
- `user_id` (FK -> users.id)
- `candidate_id` (FK -> candidates.id)
- `election_id` (FK -> elections.id)
- UNIQUE (user_id, election_id) - prevents duplicate votes

## Testing the System

### 1. Test Registration
```bash
# Using curl or Postman
POST http://localhost/onlinevoting/backend/auth/register.php
Content-Type: application/json

{
  "name": "John Doe",
  "voterId": "VOTER123",
  "email": "john@example.com",
  "password": "password123"
}
```

### 2. Test Login
```bash
POST http://localhost/onlinevoting/backend/auth/login.php
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

### 3. Test Vote Casting
```bash
POST http://localhost/onlinevoting/backend/voter/cast_vote.php
Content-Type: application/json

{
  "candidate_id": 1,
  "election_id": 1
}
```

## Troubleshooting

### Database Connection Error
- Verify MySQL service is running in XAMPP
- Check database credentials in `config/db.php`
- Ensure database `voting_system` exists

### Session Not Working
- Check `session_start()` is called in `includes/session.php`
- Verify session.save_path is writable

### 404 Errors
- Check file paths are correct
- Verify Apache is running
- Check .htaccess configuration (if applicable)

### CORS Errors (if using frontend separately)
Add to each PHP file:
```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');
```

## Best Practices Implemented

1. **Separation of Concerns**: Config, includes, and endpoints are separated
2. **DRY Principle**: Reusable functions in `includes/functions.php`
3. **Error Handling**: Try-catch blocks with proper error logging
4. **Consistent API**: All endpoints return JSON responses
5. **Code Comments**: Clear documentation for each file
6. **Security First**: Multiple layers of security (hashing, prepared statements, validation)

## Production Deployment

Before deploying to production:

1. **Enable HTTPS**: Set `session.cookie_secure = 1`
2. **Update Database Password**: Use strong password for MySQL
3. **Error Logging**: Set `error_reporting(0)` and log to files
4. **CORS Configuration**: Restrict to specific origins
5. **Rate Limiting**: Implement rate limiting for API endpoints
6. **Backup Strategy**: Regular database backups
7. **Environment Variables**: Move sensitive config to .env file

## Support & Documentation

For issues or questions:
- Review code comments in each PHP file
- Check error logs in XAMPP/Apache logs
- Verify database constraints are working

## License
Academic project for educational purposes.
