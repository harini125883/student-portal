-- ===========================================================
-- Student Portal - Database Schema (MySQL 8+)
-- Run:  mysql -u root -p < database/schema.sql
-- ===========================================================
CREATE DATABASE IF NOT EXISTS student_portal CHARACTER SET utf8mb4;
USE student_portal;

-- Student records (used by the PHP admin panel and the Servlet portal)
CREATE TABLE IF NOT EXISTS students (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(60)  NOT NULL,
  roll_no       VARCHAR(20)  NOT NULL UNIQUE,
  email         VARCHAR(100) NOT NULL,
  course        VARCHAR(60)  NOT NULL,
  password_hash VARCHAR(255) NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Metadata of every uploaded file, linked to a student (1 student : many files)
CREATE TABLE IF NOT EXISTS student_files (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  student_id    INT NOT NULL,
  file_kind     ENUM('profile','id_proof','certificate') NOT NULL,
  original_name VARCHAR(255) NOT NULL,
  file_path     VARCHAR(255) NOT NULL,      -- relative path, e.g. uploads/ab12cd.jpg
  file_type     VARCHAR(100) NOT NULL,      -- MIME type, e.g. image/png
  file_size     INT NOT NULL,               -- bytes
  uploaded_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_student FOREIGN KEY (student_id)
      REFERENCES students(id) ON DELETE CASCADE
);

-- Login accounts for the Servlet/JSP portal (password stored as SHA-256)
CREATE TABLE IF NOT EXISTS users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(40) NOT NULL UNIQUE,
  password_hash CHAR(64)    NOT NULL
);
INSERT IGNORE INTO users (username, password_hash) VALUES ('admin', SHA2('Admin@123', 256));

-- Sample data
INSERT IGNORE INTO students (name, roll_no, email, course) VALUES
 ('Priya Raman',  '21CS001', 'priya@example.com',  'B.E. CSE'),
 ('Arjun Kumar',  '21EC014', 'arjun@example.com',  'B.E. ECE');
