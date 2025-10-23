<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'employee') {
    header('Location: ../index.php');
    exit;
}

$emp = $_SESSION['user'];
$emp_id = $_SESSION['user']['id'];
$stmt = $conn->prepare("SELECT * FROM salaries WHERE user_id=? ORDER BY generated_at DESC");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$salaries = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Salary Slips</title>
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

<body class="bg-gray-100">

    <!-- Sidebar -->
    <?php include '../includes/sidebaremp.php'; ?>

    <!-- Overlay for Mobile -->
    <div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden z-30 md:hidden"></div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-h-screen md:ml-64">
        <!-- Navbar -->
        <header class="fixed top-0 left-0 right-0 md:left-64 bg-white shadow flex justify-between items-center px-4 py-3 z-40">
            <div class="flex items-center space-x-3">
                <!-- Mobile menu button -->
                <button id="sidebarToggle" class="md:hidden text-gray-700 focus:outline-none">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <h1 class="text-lg font-semibold text-gray-700">Salary</h1>
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
            <!-- Salary Section -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
                    <div>
                        <h2 class="text-xl md:text-2xl font-bold text-gray-800">My Salary Slips</h2>
                        <p class="text-sm text-gray-500">Download and view your salary slips</p>
                    </div>
                    <a href="./salary_report.php" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm w-full sm:w-auto text-center">
                        <i class="fa fa-file-pdf mr-1"></i> Export PDF
                    </a>
                </div>

                <!-- Desktop Table View -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Month</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Basic</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Overtime Hours</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Overtime Rate</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Deductions</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Total</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Generated At</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php if ($salaries->num_rows > 0) { ?>
                                <?php while ($row = $salaries->fetch_assoc()) { ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium text-gray-700"><?= htmlspecialchars($row['month']); ?></td>
                                        <td class="px-4 py-3 text-gray-700">₹<?= number_format($row['basic'], 2); ?></td>
                                        <td class="px-4 py-3 text-gray-700"><?= $row['overtime_hours']; ?></td>
                                        <td class="px-4 py-3 text-gray-700">₹<?= number_format($row['overtime_rate'], 2); ?></td>
                                        <td class="px-4 py-3 text-red-600">₹<?= number_format($row['deductions'], 2); ?></td>
                                        <td class="px-4 py-3 text-green-600 font-semibold">₹<?= number_format($row['total'], 2); ?></td>
                                        <td class="px-4 py-3 text-gray-500 text-sm"><?= date('d M Y', strtotime($row['generated_at'])); ?></td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-gray-500">No salary records found.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile/Tablet Card View -->
                <div class="lg:hidden space-y-4">
                    <?php
                    $salaries->data_seek(0);
                    if ($salaries->num_rows > 0) {
                        while ($row = $salaries->fetch_assoc()) {
                    ?>
                            <div class="border border-gray-200 rounded-lg p-4 bg-white hover:shadow-md transition-shadow">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h3 class="font-bold text-gray-800 text-base"><?= htmlspecialchars($row['month']); ?></h3>
                                        <p class="text-xs text-gray-500 mt-1">
                                            <i class="fa-regular fa-clock mr-1"></i>
                                            <?= date('d M Y', strtotime($row['generated_at'])); ?>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-gray-500 mb-1">Total Salary</p>
                                        <p class="text-lg font-bold text-green-600">₹<?= number_format($row['total'], 2); ?></p>
                                    </div>
                                </div>

                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between py-2 border-t border-gray-100">
                                        <span class="text-gray-600">Basic Salary:</span>
                                        <span class="font-medium text-gray-800">₹<?= number_format($row['basic'], 2); ?></span>
                                    </div>
                                    <div class="flex justify-between py-2 border-t border-gray-100">
                                        <span class="text-gray-600">Overtime Hours:</span>
                                        <span class="font-medium text-gray-800"><?= $row['overtime_hours']; ?> hrs</span>
                                    </div>
                                    <div class="flex justify-between py-2 border-t border-gray-100">
                                        <span class="text-gray-600">Overtime Rate:</span>
                                        <span class="font-medium text-gray-800">₹<?= number_format($row['overtime_rate'], 2); ?></span>
                                    </div>
                                    <div class="flex justify-between py-2 border-t border-gray-100">
                                        <span class="text-gray-600">Deductions:</span>
                                        <span class="font-medium text-red-600">-₹<?= number_format($row['deductions'], 2); ?></span>
                                    </div>
                                </div>

                                <div class="mt-4 pt-3 border-t border-gray-200">
                                    <button class="w-full bg-blue-50 hover:bg-blue-100 text-blue-600 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                                        <i class="fa-solid fa-download mr-2"></i>Download Slip
                                    </button>
                                </div>
                            </div>
                        <?php
                        }
                    } else {
                        ?>
                        <div class="text-center py-12">
                            <i class="fa-solid fa-receipt text-5xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500">No salary records found.</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/script.js"></script>

</body>

</html>