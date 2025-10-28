CREATE DATABASE IF NOT EXISTS payroll_system;
USE payroll_system;

-- Users table (for login, both Admin & Employee)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','employee') DEFAULT 'employee',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Attendance table
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('Present','Absent','Leave') DEFAULT 'Absent',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Leave requests
CREATE TABLE leaves (
    id INT AUTO_INCREMENT PRIMARY KEY,      
    user_id INT NOT NULL,
    reason VARCHAR(255),
    status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Salary table
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

-- First Admin user (email: admin@example.com, password: admin123)
INSERT INTO users (name, email, password, role) 
VALUES ('Admin User', 'admin@example.com', 
        '$2y$10$9pwuGJ5boe5FVtSlS7TeF.qb/ZvyAw7rN7HJQZ8G3HRiMPcz31gMe', 
        'admin');

-- Departments Table
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

-- Designations Table
CREATE TABLE designations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL
);

-- Update Users Table
ALTER TABLE users 
ADD COLUMN department_id INT NULL,
ADD COLUMN designation_id INT NULL,
ADD FOREIGN KEY (department_id) REFERENCES departments(id),
ADD FOREIGN KEY (designation_id) REFERENCES designations(id);

-- Activities Table (for logs)
CREATE TABLE activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_name VARCHAR(100) NOT NULL,
    action VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE performance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  evaluator VARCHAR(100),
  review_date DATE,
  punctuality INT,         -- 1–5 rating
  teamwork INT,            -- 1–5 rating
  productivity INT,         -- 1–5 rating
  quality_of_work INT,      -- 1–5 rating
  initiative INT,           -- 1–5 rating
  remarks TEXT,
  total_score INT,
  rating FLOAT,
  FOREIGN KEY (user_id) REFERENCES users(id)
);


ALTER TABLE users 
ADD COLUMN profile_image VARCHAR(255) NULL AFTER password;
ADD COLUMN phone VARCHAR(20) NULL,
ADD COLUMN address VARCHAR(255) NULL,
ADD COLUMN dob DATE NULL,
ADD COLUMN join_date DATE NULL;