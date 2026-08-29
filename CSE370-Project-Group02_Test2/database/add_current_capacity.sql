-- ============================================================
--  add_current_capacity.sql
--
--  RUN THIS ONCE in phpMyAdmin (SQL tab) on the smart_recycling
--  database, BEFORE using the updated centers.php / deposit_data.php.
--
--  Why: the collection center now STORES how full it is, in a new
--  column called `current_capacity`. Every accepted deposit adds to
--  it, so the collection_center table itself changes as waste comes
--  in, and the "free space" check has a real stored number to guard.
-- ============================================================

-- 1. Add the column that holds the used (deposited) weight in kg.
ALTER TABLE `collection_center`
  ADD COLUMN `current_capacity` DECIMAL(10,2) NOT NULL DEFAULT 0
  AFTER `max_capacity`;

-- 2. Backfill it from the deposits that already exist, so the number
--    is correct from the very first page load.
UPDATE `collection_center` c
SET c.`current_capacity` = (
    SELECT COALESCE(SUM(d.`weight`), 0)
    FROM `deposit` d
    WHERE d.`center_id` = c.`Center_ID`
);

-- 3. Make the Status text agree with the freshly-filled number.
UPDATE `collection_center`
SET `Status` = CASE
        WHEN `current_capacity` >= `max_capacity` THEN 'Filled'
        ELSE 'Open'
    END;
