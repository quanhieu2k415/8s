-- Migration: Add session_token column to admin_users table
-- For single session login feature

ALTER TABLE admin_users ADD COLUMN session_token VARCHAR(64) NULL AFTER remember_token;

-- Create index for faster lookup
ALTER TABLE admin_users ADD INDEX idx_session_token (session_token);
