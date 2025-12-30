-- ============================================
-- Online Voting System Database Schema
-- ============================================

-- Create database
CREATE DATABASE IF NOT EXISTS voting_system;
USE voting_system;

-- ============================================
-- Users Table
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'voter') DEFAULT 'voter',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Elections Table
-- ============================================
CREATE TABLE IF NOT EXISTS elections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    status ENUM('pending', 'active', 'closed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Candidates Table
-- ============================================
CREATE TABLE IF NOT EXISTS candidates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    party VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(10) DEFAULT '👤',
    election_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE,
    INDEX idx_election (election_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Votes Table
-- ============================================
CREATE TABLE IF NOT EXISTS votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    candidate_id INT NOT NULL,
    election_id INT NOT NULL,
    voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE,
    -- Ensure one vote per user per election
    UNIQUE KEY unique_vote (user_id, election_id),
    INDEX idx_election_votes (election_id),
    INDEX idx_candidate_votes (candidate_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Insert Sample Data
-- ============================================

-- Insert admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES
('Administrator', 'admin@voting.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample election
INSERT INTO elections (title, description, start_date, end_date, status) VALUES
('2025 General Election', 'National general election for leadership positions', 
 NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 'active');

SET @election_id = LAST_INSERT_ID();

-- Insert sample candidates
INSERT INTO candidates (name, party, description, image, election_id) VALUES
('Sarah Johnson', 'Progressive Alliance', 'Advocating for education reform and sustainable development.', '👩‍💼', @election_id),
('Michael Chen', 'Tech Innovation Party', 'Focusing on digital infrastructure and innovation.', '👨‍💻', @election_id),
('Emily Rodriguez', 'Green Future Coalition', 'Champion of environmental protection and renewable energy.', '👩‍🌾', @election_id),
('David Thompson', 'Economic Growth Party', 'Promoting business development and job creation.', '👨‍💼', @election_id);

-- ============================================
-- Useful Queries for Testing
-- ============================================

-- View all users
-- SELECT id, name, email, role FROM users;

-- View election results
-- SELECT c.name, c.party, COUNT(v.id) as vote_count 
-- FROM candidates c 
-- LEFT JOIN votes v ON c.id = v.candidate_id 
-- WHERE c.election_id = 1 
-- GROUP BY c.id 
-- ORDER BY vote_count DESC;

-- Check if user has voted in an election
-- SELECT EXISTS(SELECT 1 FROM votes WHERE user_id = ? AND election_id = ?) as has_voted;

-- ============================================
-- End of Schema
-- ============================================
