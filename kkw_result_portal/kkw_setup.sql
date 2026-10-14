-- ============================================================
--  kkw_setup.sql  (OPTIONAL - only if you want to create manually)
--  The project creates this database and tables AUTOMATICALLY
--  when you open it the first time. Importing is NOT required.
-- ============================================================
CREATE DATABASE IF NOT EXISTS kkw_srms_db2026 CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE kkw_srms_db2026;

CREATE TABLE IF NOT EXISTS kkw_login_users (
    uid        INT AUTO_INCREMENT PRIMARY KEY,
    full_name  VARCHAR(100) NOT NULL,
    login_name VARCHAR(50)  NOT NULL UNIQUE,
    email      VARCHAR(150) NOT NULL DEFAULT '',
    pass_hash  VARCHAR(255) NOT NULL,
    made_on    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS kkw_student_master (
    sid          INT AUTO_INCREMENT PRIMARY KEY,
    roll_no      VARCHAR(20)  NOT NULL UNIQUE,
    stu_name     VARCHAR(100) NOT NULL,
    father_name  VARCHAR(100) NOT NULL,
    dob          DATE         NOT NULL,
    gender       VARCHAR(10)  NOT NULL,
    email        VARCHAR(100) NOT NULL,
    mobile       VARCHAR(15)  NOT NULL,
    course       VARCHAR(50)  NOT NULL,
    semester     VARCHAR(10)  NOT NULL,
    address      TEXT         NOT NULL,
    sub1 INT NOT NULL DEFAULT 0,
    sub2 INT NOT NULL DEFAULT 0,
    sub3 INT NOT NULL DEFAULT 0,
    sub4 INT NOT NULL DEFAULT 0,
    sub5 INT NOT NULL DEFAULT 0,
    added_on     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS kkw_result_store (
    rid          INT AUTO_INCREMENT PRIMARY KEY,
    sid          INT NOT NULL UNIQUE,
    total_marks  INT NOT NULL,
    percent      DECIMAL(5,2) NOT NULL,
    grade        VARCHAR(5)  NOT NULL,
    final_status VARCHAR(10) NOT NULL,
    saved_by     VARCHAR(50) NOT NULL,
    saved_on     DATETIME    NOT NULL,
    CONSTRAINT fk_kkw_result_student FOREIGN KEY (sid)
        REFERENCES kkw_student_master(sid) ON DELETE CASCADE
) ENGINE=InnoDB;
-- Default login (admin / admin123) is inserted automatically by kkw_db_config.php
