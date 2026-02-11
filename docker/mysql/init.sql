-- =============================================================================
-- MySQL Initialization Script
-- Creates central database and grants necessary permissions
-- =============================================================================

-- Create central database for multi-tenancy
CREATE DATABASE IF NOT EXISTS `attendance_central` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Grant full privileges to the application user
GRANT ALL PRIVILEGES ON `attendance`.* TO 'laravel'@'%';
GRANT ALL PRIVILEGES ON `attendance_central`.* TO 'laravel'@'%';

-- Allow creating tenant databases dynamically
GRANT CREATE, DROP ON *.* TO 'laravel'@'%';

FLUSH PRIVILEGES;
