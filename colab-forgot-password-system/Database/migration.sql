-- ============================================================
-- Migration: Add password reset columns to users table
-- Project:   Expense Tracker - Forgot Password System
-- Date:      2026-05-19
-- ============================================================

-- Add reset_token column to store the hashed password reset token
-- Add reset_token_expiry column to store the token expiration time

ALTER TABLE users
    ADD COLUMN reset_token VARCHAR(255) DEFAULT NULL AFTER password,
    ADD COLUMN reset_token_expiry DATETIME DEFAULT NULL AFTER reset_token;

-- Add index on reset_token for faster lookups during password reset
ALTER TABLE users
    ADD INDEX idx_reset_token (reset_token);

-- ============================================================
-- ROLLBACK (if needed):
-- ALTER TABLE users DROP INDEX idx_reset_token;
-- ALTER TABLE users DROP COLUMN reset_token, DROP COLUMN reset_token_expiry;
-- ============================================================
