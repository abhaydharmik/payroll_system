-- ===============================
-- Payroll Management System (MPR)
-- Complete SQL Database Script
-- Compatible with XAMPP / MySQL
-- ===============================

CREATE DATABASE IF NOT EXISTS payroll_system;
USE payroll_system;

-- ===============================
-- USERS TABLE (Login Accounts)
-- ===============================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    profile_image VARCHAR(255) NULL,
    phone VARCHAR(20) NULL,
    address VARCHAR(255) NULL,
    dob DATE NULL,
    join_date DATE NULL,
    role ENUM('admin','employee') DEFAULT 'employee',
    department_id INT NULL,
    designation_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ===============================
-- DEPARTMENTS TABLE
-- ===============================
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

-- ===============================
-- DESIGNATIONS TABLE
-- ===============================
CREATE TABLE designations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL
);

-- Add relationships for users
ALTER TABLE users
ADD FOREIGN KEY (department_id) REFERENCES departments(id),
ADD FOREIGN KEY (designation_id) REFERENCES designations(id);

-- ===============================
-- ATTENDANCE TABLE
-- ===============================
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    clock_in TIME NULL,
    clock_out TIME NULL,
    hours_worked DECIMAL(5,2) NULL,
    status ENUM('Present','Absent','Leave') DEFAULT 'Absent',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ===============================
-- LEAVES TABLE
-- ===============================
CREATE TABLE leaves (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    leave_type VARCHAR(50) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    duration INT NOT NULL,
    reason VARCHAR(255),
    status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ===============================
-- SALARIES TABLE
-- ===============================
CREATE TABLE salaries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    month VARCHAR(20) NOT NULL,
    basic DECIMAL(10,2) NOT NULL,
    overtime_hours INT DEFAULT 0,
    overtime_rate DECIMAL(10,2) DEFAULT 0,
    deductions DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ===============================
-- PERFORMANCE TABLE
-- ===============================
CREATE TABLE performance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  evaluator VARCHAR(100),
  review_date DATE,
  punctuality INT,
  teamwork INT,
  productivity INT,
  quality_of_work INT,
  initiative INT,
  remarks TEXT,
  total_score INT,
  rating FLOAT,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ===============================
-- ACTIVITIES TABLE (Audit Logs)
-- ===============================
CREATE TABLE activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_name VARCHAR(100) NOT NULL,
    action VARCHAR(255) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ===============================
-- DEFAULT ADMIN ACCOUNT
-- Email: admin@example.com
-- Password: admin123 (bcrypt)
-- ===============================
INSERT INTO users (name, email, password, role)
VALUES (
  'Admin User',
  'admin@example.com',
  '$2y$10$9pwuGJ5boe5FVtSlS7TeF.qb/ZvyAw7rN7HJQZ8G3HRiMPcz31gMe',
  'admin'
);

-- ===============================
-- SAMPLE DEPARTMENTS & DESIGNATIONS
-- ===============================
INSERT INTO departments (name) VALUES ('HR'), ('Finance'), ('IT'), ('Operations');
INSERT INTO designations (title) VALUES ('Manager'), ('Accountant'), ('Developer'), ('Intern');

-- ===============================
-- DONE ✅
-- ===============================
