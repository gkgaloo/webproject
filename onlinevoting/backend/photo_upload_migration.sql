-- ============================================
-- Candidate Photo Upload Feature
-- Database Schema Update
-- ============================================

USE voting_system;

-- Update candidates table to support photo uploads
-- Check if column already exists (for idempotency)
SET @col_exists = (SELECT COUNT(*) 
                   FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = 'voting_system' 
                   AND TABLE_NAME = 'candidates' 
                   AND COLUMN_NAME = 'photo');

-- Add photo column if it doesn't exist
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE candidates MODIFY COLUMN image VARCHAR(10) DEFAULT ''👤'', ADD COLUMN photo VARCHAR(255) NULL AFTER image',
    'SELECT "Column photo already exists" AS message');

PREPARE stmt FROM @sql;EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index for better performance
CREATE INDEX IF NOT EXISTS idx_photo ON candidates(photo);

-- ============================================
-- Notes:
-- - photo column stores relative path to uploaded image (e.g., 'uploads/candidates/123_1234567890.jpg')
-- - image column keeps emoji for backward compatibility
-- - NULL photo means use default placeholder
-- ============================================
