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
