-- Add preferred_time column to booking table
-- This script will add the missing preferred_time column to match the intended schema

-- Check if the column doesn't already exist, then add it
ALTER TABLE `booking` 
ADD COLUMN `preferred_time` TIME NULL 
AFTER `preferred_date`;

-- Optional: Set a default time for existing bookings (e.g., 10:00 AM)
UPDATE `booking` 
SET `preferred_time` = '10:00:00' 
WHERE `preferred_time` IS NULL;

-- Verify the column was added successfully
DESCRIBE `booking`;