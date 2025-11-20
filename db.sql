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
-- DONE 
-- ===============================







-- ==========++++++++++===============++++++






-- ==========================================
-- NEW PAYROLL SYSTEM DATABASE STRUCTURE
-- ==========================================

CREATE DATABASE IF NOT EXISTS payroll_system;
USE payroll_system;

-- ==========================================
-- TABLE: departments
-- ==========================================
CREATE TABLE departments (
  id INT(11) NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  description TEXT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABLE: designations
-- ==========================================
CREATE TABLE designations (
  id INT(11) NOT NULL AUTO_INCREMENT,
  title VARCHAR(100) NOT NULL,
  description TEXT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABLE: users
-- ==========================================
CREATE TABLE users (
  id INT(11) NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(20) NULL,
  address VARCHAR(255) NULL,
  dob DATE NULL,
  join_date DATE NULL,
  role ENUM('admin','employee') DEFAULT 'employee',
  department_id INT(11) NULL,
  designation_id INT(11) NULL,
  created_at TIMESTAMP DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY email (email),
  KEY department_id (department_id),
  KEY designation_id (designation_id),
  CONSTRAINT fk_users_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
  CONSTRAINT fk_users_designation FOREIGN KEY (designation_id) REFERENCES designations(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABLE: attendance
-- ==========================================
CREATE TABLE attendance (
  id INT(11) NOT NULL AUTO_INCREMENT,
  user_id INT(11) NOT NULL,
  date DATE NOT NULL,
  clock_in TIME NULL,
  clock_out TIME NULL,
  hours_worked DECIMAL(5,2) NULL,
  status ENUM('Present','Absent','Leave') DEFAULT 'Absent',
  created_at TIMESTAMP DEFAULT current_timestamp(),
  break_in TIME NULL,
  break_out TIME NULL,
  break_duration VARCHAR(10) NULL,
  PRIMARY KEY (id),
  KEY user_id (user_id),
  CONSTRAINT fk_attendance_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABLE: leaves
-- ==========================================
CREATE TABLE leaves (
  id INT(11) NOT NULL AUTO_INCREMENT,
  user_id INT(11) NOT NULL,
  leave_type VARCHAR(50) NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  duration INT(11) NOT NULL,
  reason VARCHAR(255) NULL,
  status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
  applied_at TIMESTAMP DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY user_id (user_id),
  CONSTRAINT fk_leaves_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABLE: performance
-- ==========================================
CREATE TABLE performance (
  id INT(11) NOT NULL AUTO_INCREMENT,
  user_id INT(11) NOT NULL,
  evaluator VARCHAR(100) NULL,
  review_date DATE NULL,
  punctuality INT(11) NULL,
  teamwork INT(11) NULL,
  productivity INT(11) NULL,
  quality_of_work INT(11) NULL,
  initiative INT(11) NULL,
  remarks TEXT NULL,
  total_score INT(11) NULL,
  rating FLOAT NULL,
  PRIMARY KEY (id),
  KEY user_id (user_id),
  CONSTRAINT fk_performance_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABLE: salaries
-- ==========================================
CREATE TABLE salaries (
  id INT(11) NOT NULL AUTO_INCREMENT,
  user_id INT(11) NOT NULL,
  month VARCHAR(20) NOT NULL,
  basic DECIMAL(10,2) NOT NULL,
  overtime_hours INT(11) DEFAULT 0,
  overtime_rate DECIMAL(10,2) DEFAULT 0.00,
  deductions DECIMAL(10,2) DEFAULT 0.00,
  total DECIMAL(10,2) NOT NULL,
  generated_at TIMESTAMP DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY user_id (user_id),
  CONSTRAINT fk_salaries_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABLE: activities
-- ==========================================
CREATE TABLE activities (
  id INT(11) NOT NULL AUTO_INCREMENT,
  user_id INT(11) NOT NULL,
  user_name VARCHAR(100) NOT NULL,
  description TEXT NULL,
  created_at TIMESTAMP DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY user_id (user_id),
  CONSTRAINT fk_activities_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- DATABASE SETUP COMPLETE
-- ==========================================
