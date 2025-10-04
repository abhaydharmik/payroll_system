<div class="sidebar">
    <h2>MPR System</h2>
    <ul>
        <!-- Admin Menu -->
        <?php if($_SESSION['role'] == 'admin'): ?>
            <li><a href="/admin/dashboard.php">📊 Dashboard</a></li>
            <li><a href="/admin/employees.php">👨‍💼 Employees</a></li>
            <li><a href="/admin/attendance.php">🕒 Attendance</a></li>
            <li><a href="/admin/leaves.php">📅 Leaves</a></li>
            <li><a href="/admin/salary_history.php">💰 Salary</a></li>
        <?php endif; ?>

        <!-- Employee Menu -->
        <?php if($_SESSION['role'] == 'employee'): ?>
            <li><a href="/employee/dashboard.php">🏠 Dashboard</a></li>
            <li><a href="/employee/attendance.php">🕒 Attendance</a></li>
            <li><a href="/employee/leaves.php">📅 Leaves</a></li>
            <li><a href="/employee/profile.php">🙍 Profile</a></li>
            <li><a href="/salary.php">💵 Salary</a></li>
        <?php endif; ?>

        <li><a href="/logout.php">🚪 Logout</a></li>
    </ul>
</div>
