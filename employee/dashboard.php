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
    <!-- Font Awesome CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 flex">

    <!-- Sidebar -->
    <aside class="w-64 bg-blue-800 text-white flex flex-col fixed h-screen">
        <div class="p-6 border-b border-blue-700">
            <h1 class="text-xl font-bold flex items-center">
                <i class="fa-solid fa-chart-line mr-2"></i> Employee Panel
            </h1>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="dashboard.php" class="block py-2 px-3 rounded-lg bg-blue-700">
                <i class="fa-solid fa-gauge mr-2"></i> Dashboard
            </a>
            <a href="profile.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700">
                <i class="fa-solid fa-users mr-2"></i> Profile
            </a>
            <a href="attendance.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700">
                <i class="fa-solid fa-calendar-check mr-2"></i> My Attendance
            </a>
            <a href="leaves.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700">
                <i class="fa-solid fa-file-signature mr-2"></i> My Leaves
            </a>
            <a href="salary.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700">
                <i class="fa-solid fa-sack-dollar mr-2"></i> Salary Slips
            </a>
            <a href="reports.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700">
                <i class="fa-solid fa-file-invoice-dollar mr-2"></i> My Reports
            </a>
        </nav>
        <div class="p-4 border-t border-blue-700">
            <p class="text-sm">&copy; <?php echo date("Y"); ?> <span class="font-semibold">Payroll System</span>. All rights reserved.</p>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 ml-64 p-8">
        <header class="bg-white shadow px-6 py-4 flex justify-between items-center rounded">
            <h2 class="text-lg font-semibold text-gray-700">Dashboard</h2>
            <div class="flex items-center space-x-4">
                <span class="text-gray-700"><i class="fas fa-user-circle text-blue-600 mr-1"></i><?php echo htmlspecialchars($emp['name']); ?></span>
                <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm"><i class="fas fa-sign-out-alt mr-1"></i>Logout</a>
            </div>
        </header>

        <!-- Main Section -->
        <!-- <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">
                    Welcome, <?php echo htmlspecialchars($emp['name']); ?> <i class="fas fa-smile-beam text-yellow-500"></i>
                </h2>
                <p class="text-gray-600 mb-2"><strong>Email:</strong> <?php echo htmlspecialchars($emp['email']); ?></p>
                <p class="text-gray-600"><strong>Role:</strong> Employee</p>
            </div> -->

        <!-- Quick Actions -->
        <!-- <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-6">
                <a href="profile.php" class="bg-blue-500 hover:bg-blue-600 text-white rounded-lg p-6 shadow-md text-center">
                    <h3 class="text-lg font-semibold"><i class="fas fa-user mb-2"></i><br>Profile</h3>
                </a>
                <a href="attendance.php" class="bg-green-500 hover:bg-green-600 text-white rounded-lg p-6 shadow-md text-center">
                    <h3 class="text-lg font-semibold"><i class="fas fa-clock mb-2"></i><br>Attendance</h3>
                </a>
                <a href="leaves.php" class="bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg p-6 shadow-md text-center">
                    <h3 class="text-lg font-semibold"><i class="fas fa-file-alt mb-2"></i><br>Leaves</h3>
                </a>
                <a href="salary.php" class="bg-purple-500 hover:bg-purple-600 text-white rounded-lg p-6 shadow-md text-center">
                    <h3 class="text-lg font-semibold"><i class="fas fa-money-bill-wave mb-2"></i><br>Salary</h3>
                </a>
            </div> -->
</body>

</html>