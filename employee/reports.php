<?php
require '../config.php';
require '../includes/auth.php';
checkRole('employee');

$emp = $_SESSION['user'];
$userId = $_SESSION['user']['id'];

// Attendance history
$attendance = $conn->query("
    SELECT date, status 
    FROM attendance 
    WHERE user_id = $userId 
    ORDER BY date DESC 
    LIMIT 30
");

// Calculate attendance stats
$totalWorkingDays = $conn->query("SELECT COUNT(*) as total FROM attendance WHERE user_id = $userId")->fetch_assoc()['total'] ?? 0;
$daysPresent = $conn->query("SELECT COUNT(*) as total FROM attendance WHERE user_id = $userId AND status = 'Present'")->fetch_assoc()['total'] ?? 0;
$totalAbsent = $conn->query("SELECT COUNT(*) as total FROM attendance WHERE user_id = $userId AND status = 'Absent'")->fetch_assoc()['total'] ?? 0;

// Leave requests
$leaves = $conn->query("
    SELECT reason, status, applied_at 
    FROM leaves 
    WHERE user_id = $userId 
    ORDER BY applied_at DESC
");

// Leave stats
$annualLeave = $conn->query("SELECT COUNT(*) as total FROM leaves WHERE user_id = $userId AND leave_type = 'Vacation' AND status = 'Approved'")->fetch_assoc()['total'] ?? 0;
$sickLeave = $conn->query("SELECT COUNT(*) as total FROM leaves WHERE user_id = $userId AND leave_type = 'Sick Leave' AND status = 'Approved'")->fetch_assoc()['total'] ?? 0;
$personalLeave = $conn->query("SELECT COUNT(*) as total FROM leaves WHERE user_id = $userId AND leave_type = 'Casual Leave' AND status = 'Approved'")->fetch_assoc()['total'] ?? 0;

// Salary history
$salaries = $conn->query("
    SELECT month, basic, overtime_hours, overtime_rate, deductions, total, generated_at 
    FROM salaries 
    WHERE user_id = $userId 
    ORDER BY generated_at DESC
");

// Latest salary
$latestSalary = $conn->query("SELECT * FROM salaries WHERE user_id = $userId ORDER BY generated_at DESC LIMIT 1")->fetch_assoc();
$grossSalary = $latestSalary['basic'] ?? 0;
$deductions = $latestSalary['deductions'] ?? 0;
$netSalary = $latestSalary['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Reports</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        #sidebar {
            transition: transform 0.3s ease-in-out;
        }

        @media (max-width: 767px) {
            #sidebar.mobile-hidden {
                transform: translateX(-100%);
            }
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Sidebar -->
    <?php include '../includes/sidebaremp.php'; ?>

    <!-- Overlay for Mobile -->
    <div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden z-30 md:hidden"></div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-h-screen md:ml-64">
        <!-- Navbar -->
        <header class="fixed top-0 left-0 right-0 md:left-64 bg-white shadow flex justify-between items-center px-4 py-3 z-40">
            <div class="flex items-center space-x-3">
                <button id="sidebarToggle" class="md:hidden text-gray-700 focus:outline-none">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <h1 class="text-lg font-semibold text-gray-700">Reports</h1>
            </div>
            <div class="flex items-center space-x-3">
                <span class="text-gray-700 flex items-center">
                    <i class="fas fa-user-circle text-blue-600 mr-1"></i>
                    <span class="hidden sm:inline"><?= htmlspecialchars($emp['name']) ?></span>
                </span>
                <a href="../logout.php" class="text-red-600 hover:text-red-800">
                    <i class="fas fa-sign-out-alt text-lg"></i>
                </a>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 pt-20 px-4 md:px-8 pb-8">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">My Reports</h2>
                    <p class="text-sm text-gray-500">View and download your performance reports</p>
                </div>
                <select class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option>Last 30 Days</option>
                    <option>Last 60 Days</option>
                    <option>Last 90 Days</option>
                    <option>This Year</option>
                </select>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="bg-white rounded-lg shadow p-5 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Total Working Days</p>
                            <p class="text-3xl font-bold text-gray-800 mt-1"><?= $totalWorkingDays ?></p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-lg">
                            <i class="fa-solid fa-calendar-days text-blue-600 text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-5 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Days Present</p>
                            <p class="text-3xl font-bold text-gray-800 mt-1"><?= $daysPresent ?></p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-lg">
                            <i class="fa-solid fa-check-circle text-green-600 text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-5 border-l-4 border-yellow-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Total Leaves</p>
                            <p class="text-3xl font-bold text-gray-800 mt-1"><?= $annualLeave + $sickLeave + $personalLeave ?></p>
                        </div>
                        <div class="bg-yellow-100 p-3 rounded-lg">
                            <i class="fa-solid fa-umbrella-beach text-yellow-600 text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-5 border-l-4 border-red-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Days Absent</p>
                            <p class="text-3xl font-bold text-gray-800 mt-1"><?= $totalAbsent ?></p>
                        </div>
                        <div class="bg-red-100 p-3 rounded-lg">
                            <i class="fa-solid fa-xmark-circle text-red-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Cards Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Attendance Report -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-start mb-4">
                        <div class="bg-blue-100 p-3 rounded-lg mr-4">
                            <i class="fa-solid fa-calendar-check text-blue-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-800">Attendance Report</h3>
                            <p class="text-sm text-gray-500">Monthly attendance summary</p>
                        </div>
                    </div>
                    <div class="space-y-3 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Present Days:</span>
                            <span class="font-semibold text-green-600"><?= $daysPresent ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Late Arrivals:</span>
                            <span class="font-semibold text-orange-600">3</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Early Departures:</span>
                            <span class="font-semibold text-yellow-600">1</span>
                        </div>
                    </div>
                    <button class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg font-medium transition-colors duration-200">
                        <i class="fa-solid fa-download mr-2"></i>Download PDF
                    </button>
                </div>

                <!-- Leave Report -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-start mb-4">
                        <div class="bg-green-100 p-3 rounded-lg mr-4">
                            <i class="fa-solid fa-file-lines text-green-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-800">Leave Report</h3>
                            <p class="text-sm text-gray-500">Leave balance and history</p>
                        </div>
                    </div>
                    <div class="space-y-3 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Annual Leave:</span>
                            <span class="font-semibold text-blue-600"><?= $annualLeave ?>/20 used</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Sick Leave:</span>
                            <span class="font-semibold text-blue-600"><?= $sickLeave ?>/10 used</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Personal Leave:</span>
                            <span class="font-semibold text-blue-600"><?= $personalLeave ?>/5 used</span>
                        </div>
                    </div>
                    <button class="w-full bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-lg font-medium transition-colors duration-200">
                        <i class="fa-solid fa-download mr-2"></i>Download PDF
                    </button>
                </div>

                <!-- Salary Report -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-start mb-4">
                        <div class="bg-purple-100 p-3 rounded-lg mr-4">
                            <i class="fa-solid fa-money-bill-wave text-purple-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-800">Salary Report</h3>
                            <p class="text-sm text-gray-500">Monthly salary breakdown</p>
                        </div>
                    </div>
                    <div class="space-y-3 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Gross Salary:</span>
                            <span class="font-semibold text-gray-800">₹<?= number_format($grossSalary, 2) ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Deductions:</span>
                            <span class="font-semibold text-red-600">₹<?= number_format($deductions, 2) ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Net Salary:</span>
                            <span class="font-semibold text-green-600">₹<?= number_format($netSalary, 2) ?></span>
                        </div>
                    </div>
                    <button class="w-full bg-purple-600 hover:bg-purple-700 text-white py-2.5 rounded-lg font-medium transition-colors duration-200">
                        <i class="fa-solid fa-download mr-2"></i>Download PDF
                    </button>
                </div>

                <!-- Performance Report -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-start mb-4">
                        <div class="bg-orange-100 p-3 rounded-lg mr-4">
                            <i class="fa-solid fa-chart-line text-orange-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-800">Performance Report</h3>
                            <p class="text-sm text-gray-500">Quarterly performance summary</p>
                        </div>
                    </div>
                    <div class="space-y-3 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Overall Rating:</span>
                            <span class="font-semibold text-orange-600">4.2/5.0</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Goals Achieved:</span>
                            <span class="font-semibold text-gray-800">6/10</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Projects Completed:</span>
                            <span class="font-semibold text-gray-800">12</span>
                        </div>
                    </div>
                    <button class="w-full bg-orange-600 hover:bg-orange-700 text-white py-2.5 rounded-lg font-medium transition-colors duration-200">
                        <i class="fa-solid fa-download mr-2"></i>Download PDF
                    </button>
                </div>
            </div>

            <!-- Recent Downloads -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-2">Recent Downloads</h3>
                <p class="text-sm text-gray-500 mb-4">Your recently downloaded reports</p>

                <div class="space-y-3">
                    <!-- Download Item -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex items-center space-x-3">
                            <div class="bg-blue-100 p-2 rounded">
                                <i class="fa-solid fa-file-pdf text-blue-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800 text-sm">Attendance Report - December 2024</p>
                                <p class="text-xs text-gray-500">Attendance • 243 KB • 2024-12-15</p>
                            </div>
                        </div>
                        <button class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                            Download Again
                        </button>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex items-center space-x-3">
                            <div class="bg-purple-100 p-2 rounded">
                                <i class="fa-solid fa-file-pdf text-purple-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800 text-sm">Salary Slip - November 2024</p>
                                <p class="text-xs text-gray-500">Salary • 188 KB • 2024-11-30</p>
                            </div>
                        </div>
                        <button class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                            Download Again
                        </button>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex items-center space-x-3">
                            <div class="bg-green-100 p-2 rounded">
                                <i class="fa-solid fa-file-pdf text-green-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800 text-sm">Leave Summary - Q4 2024</p>
                                <p class="text-xs text-gray-500">Leave • 156 KB • 2024-11-15</p>
                            </div>
                        </div>
                        <button class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                            Download Again
                        </button>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex items-center space-x-3">
                            <div class="bg-orange-100 p-2 rounded">
                                <i class="fa-solid fa-file-pdf text-orange-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800 text-sm">Performance Review - Q3 2024</p>
                                <p class="text-xs text-gray-500">Performance • 298 KB • 2024-10-01</p>
                            </div>
                        </div>
                        <button class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                            Download Again
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/script.js"></script>
</body>

</html>