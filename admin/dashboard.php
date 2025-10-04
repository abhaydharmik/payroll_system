<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');


$emp = $_SESSION['user'];
// Stats queries
$totalEmployees = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='employee'")->fetch_assoc()['total'];
$presentToday   = $conn->query("SELECT COUNT(*) as present FROM attendance WHERE date=CURDATE() AND status='Present'")->fetch_assoc()['present'];
$pendingLeaves  = $conn->query("SELECT COUNT(*) as leaves FROM leaves WHERE status='Pending'")->fetch_assoc()['leaves'];
$currentMonth = date('F Y'); // e.g. "September 2025"
$monthlyPayroll = $conn->query("SELECT COALESCE(SUM(total),0) as total 
                                FROM salaries 
                                WHERE month='$currentMonth'")
    ->fetch_assoc()['total'];

// Recent activities
$activities = $conn->query("SELECT action, user_name, created_at FROM activities ORDER BY created_at DESC LIMIT 5");

// Departments
$departments = $conn->query("
    SELECT d.name, COUNT(u.id) as employees, COALESCE(SUM(s.total),0) as payroll
    FROM departments d
    LEFT JOIN users u ON u.department_id=d.id
    LEFT JOIN salaries s ON s.user_id=u.id AND s.month='$currentMonth'
    GROUP BY d.id
");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Payroll System</title>
    <script src="../assets/js/script.js" defer></script>  
    <!-- <script type="module" src="./js/script.js"></script> -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Smooth sidebar toggle */
        #sidebar {
            transition: transform 0.3s ease-in-out;
        }

        #sidebar.mobile-hidden {
            transform: translateX(-100%);
        }
    </style>
</head>

<body class="bg-gray-100 flex">

    <!-- Mobile Header -->
    <header class="fixed top-0 left-0 right-0 bg-white shadow-md flex items-center justify-between px-4 py-3 md:hidden z-50">
        <h1 class="text-lg font-bold text-gray-800 flex items-center">
            <i class="fa-solid fa-chart-line mr-2"></i> Admin Panel
        </h1>
        <button id="sidebarToggle" class="text-gray-800 text-2xl focus:outline-none">
            <i class="fa-solid fa-bars"></i>
        </button>
    </header>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-white flex flex-col transform -translate-x-full md:translate-x-0 transition-transform duration-300 z-40">
        <!-- <div id="hidden" class="p-6 border-b border-blue-700">
            <h1 class="text-2xl font-bold flex items-center">
                <i class="fa-solid fa-chart-line mr-2"></i> Admin Panel
            </h1>
        </div> -->
        <nav class="flex-1 px-4 py-7 mt-10 space-y-2 overflow-y-auto">
            <a href="dashboard.php" class="block py-2 px-3 flex items-center rounded-lg bg-blue-50 text-blue-600 border border-blue-200"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bar-chart3 w-5 h-5 m-r-2">
                    <path d="M3 3v18h18"></path>
                    <path d="M18 17V9"></path>
                    <path d="M13 17V5"></path>
                    <path d="M8 17v-3"></path>
                </svg> Dashboard</a>
            <a href="employees.php" class="block py-2 px-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900"><i class="fa-solid fa-users mr-2"></i> Employees</a>
            <a href="attendance.php" class="block py-2 px-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900"><i class="fa-solid fa-calendar-check mr-2"></i> Attendance</a>
            <a href="leaves.php" class="block py-2 px-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900"><i class="fa-solid fa-file-signature mr-2"></i> Leaves</a>
            <a href="generate_salary.php" class="block py-2 px-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900"><i class="fa-solid fa-sack-dollar mr-2"></i> Generate Salary</a>
            <a href="salary_history.php" class="block py-2 px-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900"><i class="fa-solid fa-file-invoice-dollar mr-2"></i> Salary History</a>
            <a href="departments.php" class="block py-2 px-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900"><i class="fa-solid fa-building mr-2"></i> Departments</a>
            <a href="designations.php" class="block py-2 px-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900"><i class="fa-solid fa-briefcase mr-2"></i> Designations</a>
        </nav>
        <div class="p-4 border-t mt-4 border-blue-700">
            <p class="text-sm">&copy; <?php echo date("Y"); ?> <span class="font-semibold">Payroll System</span>. All rights reserved.</p>
        </div>
    </aside>

    <!-- Overlay for mobile -->
    <div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden z-30 md:hidden"></div>
    <!-- Main Content -->
    <main class="flex-1 ml-0 md:ml-64 p-8 pt-24 md:pt-8 transition-all">
        <header class="bg-white shadow px-6 py-4 flex justify-between items-center rounded">
            <h2 class="text-lg font-semibold text-gray-700">Dashboard</h2>
            <div class="flex items-center space-x-4">
                <span class="text-gray-700"><i class="fas fa-user-circle text-blue-600 mr-1"></i><?php echo htmlspecialchars($emp['name']); ?></span>
                <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm"><i class="fas fa-sign-out-alt mr-1"></i>Logout</a>
            </div>
        </header>


        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-4 mb-6">
            <div class="bg-white rounded-lg shadow p-5 flex items-center justify-between border-l-4 border-blue-600">
                <div>
                    <h3 class="text-gray-500 text-sm">Total Employees</h3>
                    <p class="text-2xl font-bold"><?= $totalEmployees ?></p>
                </div>
                <i class="fa-solid fa-users text-blue-600 text-3xl"></i>
            </div>

            <div class="bg-white rounded-lg shadow p-5 flex items-center justify-between border-l-4 border-green-600">
                <div>
                    <h3 class="text-gray-500 text-sm">Present Today</h3>
                    <p class="text-2xl font-bold"><?= $presentToday ?></p>
                </div>
                <i class="fa-solid fa-calendar-check text-green-600 text-3xl"></i>
            </div>

            <div class="bg-white rounded-lg shadow p-5 flex items-center justify-between border-l-4 border-yellow-600">
                <div>
                    <h3 class="text-gray-500 text-sm">Pending Leaves</h3>
                    <p class="text-2xl font-bold"><?= $pendingLeaves ?></p>
                </div>
                <i class="fa-solid fa-file-signature text-yellow-600 text-3xl"></i>
            </div>

            <div class="bg-white rounded-lg shadow p-5 flex items-center justify-between border-l-4 border-purple-600">
                <div>
                    <h3 class="text-gray-500 text-sm">Monthly Payroll</h3>
                    <p class="text-2xl font-bold">₹<?= number_format($monthlyPayroll / 1000, 1) ?>K</p>
                </div>
                <i class="fa-solid fa-sack-dollar text-purple-600 text-3xl"></i>
            </div>
        </div>

        <!-- Bottom Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Activities -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold mb-4">Recent Activities</h3>
                <ul class="space-y-4">
                    <?php if ($activities && $activities->num_rows > 0): ?>
                        <?php while ($a = $activities->fetch_assoc()): ?>
                            <li class="flex items-start space-x-3">
                                <div class="bg-blue-100 text-blue-600 p-2 rounded-full">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                                <div>
                                    <p class="font-medium"><?= htmlspecialchars($a['action']) ?></p>
                                    <p class="text-sm text-gray-500"><?= $a['user_name'] ?> • <?= date("M d, H:i", strtotime($a['created_at'])) ?></p>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-gray-500">No recent activities.</p>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Department Overview -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold mb-4">Department Overview</h3>
                <ul class="divide-y">
                    <?php while ($d = $departments->fetch_assoc()): ?>
                        <li class="flex justify-between py-2">
                            <div>
                                <p class="font-medium"><?= htmlspecialchars($d['name']) ?></p>
                                <p class="text-sm text-gray-500"><?= $d['employees'] ?> employees</p>
                            </div>
                            <p class="font-semibold text-gray-700">₹<?= number_format($d['payroll'] / 1000) ?>K</p>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>
    </main>

    <!-- <script>
        const sidebar = document.getElementById("sidebar");
        const toggleBtn = document.getElementById("sidebarToggle");
        const overlay = document.getElementById("overlay");

        toggleBtn.addEventListener("click", () => {
            sidebar.classList.toggle("-translate-x-full");
            overlay.classList.toggle("hidden");
        });

        overlay.addEventListener("click", () => {
            sidebar.classList.add("-translate-x-full");
            overlay.classList.add("hidden");
        });
    </script> -->


</body>

</html>