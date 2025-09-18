# Mini Payroll & Salary Management System (PHP + MySQL)
## Overview
Simple starter project built with PHP (mysqli) and MySQL for XAMPP. Use it to learn and expand.

## Setup (XAMPP)
1. Copy the project folder `mini-payroll` into `C:/xampp/htdocs/` (Windows) or your webroot.
2. Start Apache and MySQL from XAMPP control panel.
3. Create a database named `mini_payroll` and import `db.sql` using phpMyAdmin or the mysql CLI.
   - Alternatively run: `mysql -u root -p < db.sql` if you use a root password.
4. Update `config.php` DB credentials if needed.
5. Visit `http://localhost/mini-payroll/` in your browser.

## Default admin login
- Email: admin@example.com
- Password: admin123

## Notes
- This is a minimal demo. Do not use as-is in production.
- Extend features: validations, prepared statements, role-based access, secure password hashing, file uploads, reports, PDF export.
