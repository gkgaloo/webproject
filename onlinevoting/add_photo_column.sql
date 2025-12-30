-- Quick Database Migration for Photo Upload
-- Run this in phpMyAdmin SQL tab

USE voting_system;

-- Add photo column if it doesn't exist
ALTER TABLE candidates 
ADD COLUMN IF NOT EXISTS photo VARCHAR(255) NULL 
AFTER image;

-- Verify the column was added
SHOW COLUMNS FROM candidates LIKE 'photo';

-- Check current candidates
SELECT id, name, image, photo FROM candidates;
