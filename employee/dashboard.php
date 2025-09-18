<?php
session_start();
require '../config.php';

// Only employees can access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'employee') {
    header('Location: ../index.php');
    exit;
}

$emp = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center shadow-md">
        <h1 class="text-xl font-bold">Employee Dashboard</h1>
        <div>
            <span class="mr-4">👤 <?php echo htmlspecialchars($emp['name']); ?></span>
            <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-sm">Logout</a>
        </div>
    </nav>

    <!-- Content -->
    <div class="flex flex-1">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-md p-6 hidden md:block">
            <ul class="space-y-4 text-gray-700 font-medium">
                <li><a href="profile.php" class="block hover:text-blue-600">My Profile</a></li>
                <li><a href="attendance.php" class="block hover:text-blue-600">Mark Attendance</a></li>
                <li><a href="leaves.php" class="block hover:text-blue-600">Apply for Leave</a></li>
                <li><a href="salary.php" class="block hover:text-blue-600">View Salary Slips</a></li>
            </ul>
        </aside>

        <!-- Main Section -->
        <main class="flex-1 p-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Welcome, <?php echo htmlspecialchars($emp['name']); ?> 🎉</h2>
                <p class="text-gray-600 mb-2"><strong>Email:</strong> <?php echo htmlspecialchars($emp['email']); ?></p>
                <p class="text-gray-600"><strong>Role:</strong> Employee</p>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-6">
                <a href="profile.php" class="bg-blue-500 hover:bg-blue-600 text-white rounded-lg p-6 shadow-md text-center">
                    <h3 class="text-lg font-semibold">👤 Profile</h3>
                </a>
                <a href="attendance.php" class="bg-green-500 hover:bg-green-600 text-white rounded-lg p-6 shadow-md text-center">
                    <h3 class="text-lg font-semibold">🕒 Attendance</h3>
                </a>
                <a href="leaves.php" class="bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg p-6 shadow-md text-center">
                    <h3 class="text-lg font-semibold">📄 Leaves</h3>
                </a>
                <a href="salary.php" class="bg-purple-500 hover:bg-purple-600 text-white rounded-lg p-6 shadow-md text-center">
                    <h3 class="text-lg font-semibold">💰 Salary</h3>
                </a>
            </div>
        </main>
    </div>
</body>
</html>
