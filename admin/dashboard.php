<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Payroll System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex flex-col">

    <!-- Navbar -->
    <header class="bg-blue-700 text-white shadow-md">
        <div class="max-w-6xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-xl font-bold">Admin Dashboard</h1>
            <div class="flex items-center space-x-4">
                <span class="font-medium">👋 Welcome, <?php echo $_SESSION['user']['name']; ?></span>
                <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition">Logout</a>
            </div>
        </div>
    </header>

    <!-- Dashboard Content -->
    <main class="flex-grow max-w-6xl mx-auto px-6 py-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <!-- Manage Employees -->
            <a href="employees.php" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition border-l-4 border-blue-600">
                <h2 class="text-lg font-semibold text-gray-800">👨‍💼 Manage Employees</h2>
                <p class="text-gray-600 text-sm mt-2">Add, edit, and manage employee records.</p>
            </a>

            <!-- Manage Attendance -->
            <a href="attendance.php" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition border-l-4 border-green-600">
                <h2 class="text-lg font-semibold text-gray-800">📅 Manage Attendance</h2>
                <p class="text-gray-600 text-sm mt-2">Track and update employee attendance.</p>
            </a>

            <!-- Manage Leave Requests -->
            <a href="leaves.php" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition border-l-4 border-yellow-600">
                <h2 class="text-lg font-semibold text-gray-800">📝 Leave Requests</h2>
                <p class="text-gray-600 text-sm mt-2">Review and approve/reject leave requests.</p>
            </a>

            <!-- Generate Salary -->
            <a href="generate_salary.php" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition border-l-4 border-purple-600">
                <h2 class="text-lg font-semibold text-gray-800">💰 Generate Salary</h2>
                <p class="text-gray-600 text-sm mt-2">Calculate and generate employee salaries.</p>
            </a>

            <!-- Salary History -->
            <a href="salary_history.php" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition border-l-4 border-pink-600">
                <h2 class="text-lg font-semibold text-gray-800">📊 Salary History</h2>
                <p class="text-gray-600 text-sm mt-2">View and download past salary reports.</p>
            </a>

            <!-- Manage Departments -->
            <a href="departments.php" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition border-l-4 border-indigo-600">
                <h2 class="text-lg font-semibold text-gray-800">🏢 Manage Departments</h2>
                <p class="text-gray-600 text-sm mt-2">Add, edit, and manage company departments.</p>
            </a>

            <a href="designations.php"
                class="block bg-purple-600 hover:bg-purple-700 text-white text-center px-4 py-3 rounded-lg shadow">
                📌 Manage Designations
            </a>


        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-gray-300 py-4 text-center text-sm">
        Payroll Management System © <?php echo date("Y"); ?>
    </footer>

</body>

</html>