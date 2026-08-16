-- ============================================================
-- TaskFlow: Day-Routing migration
-- Run this once in phpMyAdmin → SQL tab on database `Task_traker`
-- Safe to re-run (uses INFORMATION_SCHEMA guards).
-- ============================================================

-- --------------------------------------------------------
-- 1) todos: add day_closed, priority, completed_at
-- --------------------------------------------------------

SET @c1 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME   = 'todos'
             AND COLUMN_NAME   = 'day_closed');
SET @sql = IF(@c1 = 0,
  'ALTER TABLE todos ADD COLUMN day_closed TINYINT(1) NOT NULL DEFAULT 0',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c2 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME   = 'todos'
             AND COLUMN_NAME   = 'priority');
SET @sql = IF(@c2 = 0,
  "ALTER TABLE todos ADD COLUMN priority VARCHAR(20) NOT NULL DEFAULT 'medium'",
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c3 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME   = 'todos'
             AND COLUMN_NAME   = 'completed_at');
SET @sql = IF(@c3 = 0,
  'ALTER TABLE todos ADD COLUMN completed_at DATETIME NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- --------------------------------------------------------
-- 2) daily_progress: add day_closed, carry_forward_done
-- --------------------------------------------------------

SET @c4 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME   = 'daily_progress'
             AND COLUMN_NAME   = 'day_closed');
SET @sql = IF(@c4 = 0,
  'ALTER TABLE daily_progress ADD COLUMN day_closed TINYINT(1) NOT NULL DEFAULT 0',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c5 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME   = 'daily_progress'
             AND COLUMN_NAME   = 'carry_forward_done');
SET @sql = IF(@c5 = 0,
  'ALTER TABLE daily_progress ADD COLUMN carry_forward_done TINYINT(1) NOT NULL DEFAULT 0',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- --------------------------------------------------------
-- 3) Helpful index for the "yesterday's uncompleted" query
-- --------------------------------------------------------

SET @i1 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME   = 'todos'
             AND INDEX_NAME   = 'idx_user_date_status');
SET @sql = IF(@i1 = 0,
  'CREATE INDEX idx_user_date_status ON todos (user_id, created_at, status, day_closed)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- --------------------------------------------------------
-- 4) daily_progress index — needed by the profile Mon-Sun
--    chart which now reads from this table.
-- --------------------------------------------------------

SET @i2 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
           WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME   = 'daily_progress'
             AND INDEX_NAME   = 'idx_dp_user_date');
SET @sql = IF(@i2 = 0,
  'CREATE INDEX idx_dp_user_date ON daily_progress (user_id, progress_date)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================
-- Sanity check (run these separately to confirm):
--
-- DESCRIBE todos;
-- DESCRIBE daily_progress;
-- SHOW INDEX FROM daily_progress;
-- ============================================================



--  but when i logen in to the users account it shows me the previous tasks to me and now want to have empty page only for every fresh day except i intenshnally press the button of continue with uncompleted tasks other wise give me only empty start page which dosent ahve any thing in task in completion ,pending, total boxes also persentage show also be 0 ,so automatically delet all the tasks but make shure that preogress in profile should not effected it should be clear and safe in which day how much i did progress like that also make shure that this all website is sutabl for phone screen also cause the mostof the time this webapplication is going ot be used throw phone screen so make shure it fits in to it also make the changepassword button works well and it should provide email and phone number verfication to change password can u make this in profeshal way thankyou claoude ji