-- ============================================================
--  deposit_feature.sql
--
--  Run this ONCE in phpMyAdmin (SQL tab) on the smart_recycling
--  database. It only ADDS two new tables and some demo manager
--  accounts. It does not touch any existing table.
--
--  Tables added:
--    1. center_manager   - manager accounts (mirrors `recycler`)
--    2. deposit_request  - the "waiting room" for deposits a user
--                          has posted but a manager has not yet
--                          accepted (mirrors `pickup_request`)
-- ============================================================

-- ---------- 1. Collection Center Manager accounts ----------
CREATE TABLE `center_manager` (
  `Manager_ID` varchar(20) NOT NULL,
  `name`       varchar(100) DEFAULT NULL,
  `email`      varchar(100) DEFAULT NULL,
  `password`   varchar(255) DEFAULT NULL,
  `Center_ID`  varchar(20)  DEFAULT NULL,
  `phone`      varchar(20)  DEFAULT NULL,
  PRIMARY KEY (`Manager_ID`),
  UNIQUE KEY `manager_email` (`email`),
  KEY `Center_ID` (`Center_ID`),
  CONSTRAINT `center_manager_ibfk_1`
    FOREIGN KEY (`Center_ID`) REFERENCES `collection_center` (`Center_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- A few demo managers, each tied to one real center.
-- (Same plain-text password style as the recycler demo rows.)
INSERT INTO `center_manager`
  (`Manager_ID`, `name`, `email`, `password`, `Center_ID`, `phone`) VALUES
('MGR001', 'Karim Uddin',   'mgr001@recycle.com', 'Mgr@123', 'C001', '01911000001'),
('MGR002', 'Salma Khatun',  'mgr002@recycle.com', 'Mgr@123', 'C002', '01911000002'),
('MGR003', 'Rafiq Hasan',   'mgr003@recycle.com', 'Mgr@123', 'C003', '01911000003');


-- ---------- 2. Pending deposit requests (the waiting room) ----------
CREATE TABLE `deposit_request` (
  `request_id`   varchar(20)  NOT NULL,
  `User_id`      int(11)      DEFAULT NULL,
  `center_id`    varchar(20)  DEFAULT NULL,
  `waste_type`   varchar(50)  DEFAULT NULL,
  `weight`       decimal(8,2) DEFAULT NULL,
  `status`       varchar(30)  NOT NULL DEFAULT 'Pending',
  `request_date` datetime     DEFAULT NULL,
  `handled_by`   varchar(20)  DEFAULT NULL,
  `handled_at`   datetime     DEFAULT NULL,
  `req_seq`      int(11)      NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`request_id`),
  UNIQUE KEY `req_seq` (`req_seq`),
  KEY `User_id` (`User_id`),
  KEY `center_id` (`center_id`),
  KEY `handled_by` (`handled_by`),
  CONSTRAINT `deposit_request_ibfk_1`
    FOREIGN KEY (`User_id`)    REFERENCES `user` (`User_id`),
  CONSTRAINT `deposit_request_ibfk_2`
    FOREIGN KEY (`center_id`)  REFERENCES `collection_center` (`Center_ID`),
  CONSTRAINT `deposit_request_ibfk_3`
    FOREIGN KEY (`handled_by`) REFERENCES `center_manager` (`Manager_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
