<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$user = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payroll System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Navbar -->
    <nav class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center shadow-md">
        <!-- Logo -->
        <span class="text-xl font-bold tracking-wide">Payroll System</span>
        
        <!-- Links -->
        <div class="space-x-4">
            <?php if ($user): ?>
                <?php if ($user['role'] == 'admin'): ?>
                    <a href="../admin/dashboard.php" class="hover:underline">Dashboard</a>
                    <a href="../admin/employees.php" class="hover:underline">Employees</a>
                    <a href="../admin/attendance.php" class="hover:underline">Attendance</a>
                    <a href="../admin/leaves.php" class="hover:underline">Leaves</a>
                    <a href="../admin/generate_salary.php" class="hover:underline">Generate Salary</a>
                    <a href="../admin/salary_history.php" class="hover:underline">Salary History</a>
                <?php elseif ($user['role'] == 'employee'): ?>
                    <a href="../employee/dashboard.php" class="hover:underline">Dashboard</a>
                    <a href="../employee/profile.php" class="hover:underline">Profile</a>
                    <a href="../employee/attendance.php" class="hover:underline">Attendance</a>
                    <a href="../employee/leaves.php" class="hover:underline">Leaves</a>
                    <a href="../employee/salary.php" class="hover:underline">Salary</a>
                <?php endif; ?>
                <a href="../logout.php" class="bg-white text-blue-600 px-3 py-1 rounded-md font-semibold hover:bg-gray-200 transition">Logout</a>
            <?php else: ?>
                <a href="../index.php" class="hover:underline">Login</a>
                <a href="../register.php" class="bg-white text-blue-600 px-3 py-1 rounded-md font-semibold hover:bg-gray-200 transition">Register</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Page Content -->
    <div class="p-6">
