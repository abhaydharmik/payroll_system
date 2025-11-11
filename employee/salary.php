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

$pageTitle = "My Salary";


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
        <?php include_once '../includes/header.php'; ?>

        <!-- Page Content -->
        <main class="flex-1 pt-20 px-4 md:px-8 pb-8">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800">My Salary Slips</h2>
                    <p class="text-sm text-gray-500">Download and view your salary slips</p>
                </div>
                <a href="./salary_report.php" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm w-full sm:w-auto text-center">
                    <i class="fa fa-file-pdf mr-1"></i> Export PDF
                </a>
            </div>
            <!-- Salary Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 mt-6">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                        <h3 class="text-lg font-semibold text-gray-900">Salary History</h3>

                        <a href="./salary_report.php" target="_blank"
                            class="bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition-all flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download-icon lucide-download">
                                <path d="M12 15V3" />
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                <path d="m7 10 5 5 5-5" />
                            </svg>
                            <span>Export PDF</span>
                        </a>
                    </div>
                </div>

                <!-- Desktop View -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Basic</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Overtime</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deductions</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Net Pay</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $salaries->data_seek(0);
                            while ($row = $salaries->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($row['month']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">₹<?= number_format($row['basic'], 2) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= $row['overtime_hours'] ?>h × ₹<?= number_format($row['overtime_rate'], 2) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">-₹<?= number_format($row['deductions'], 2) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">₹<?= number_format($row['total'], 2) ?></td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Paid</span>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <!-- Download Slip -->
                                            <a href="./salary_slip.php?id=<?= $row['id'] ?>" class="text-blue-600 hover:text-blue-900 p-1 hover:bg-blue-50 rounded" title="Download Slip">
                                                <i class="fa-solid fa-download"></i>
                                            </a>
                                            <!-- View Details -->
                                            <button class="viewSalaryBtn text-green-600 hover:text-green-900 p-1 hover:bg-green-50 rounded"
                                                data-month="<?= htmlspecialchars($row['month']) ?>"
                                                data-basic="₹<?= number_format($row['basic'], 2) ?>"
                                                data-overtime="<?= $row['overtime_hours'] ?>h @ ₹<?= number_format($row['overtime_rate'], 2) ?>"
                                                data-deductions="₹<?= number_format($row['deductions'], 2) ?>"
                                                data-total="₹<?= number_format($row['total'], 2) ?>"
                                                data-date="<?= date('d M Y', strtotime($row['generated_at'])) ?>">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="lg:hidden p-4 space-y-4">
                    <?php $salaries->data_seek(0);
                    while ($row = $salaries->fetch_assoc()): ?>
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex justify-between items-start">
                                <h3 class="font-bold text-gray-800"><?= htmlspecialchars($row['month']) ?></h3>
                                <span class="text-xs px-2 py-1 rounded bg-green-100 text-green-700">Paid</span>
                            </div>

                            <p class="text-sm text-gray-500 mt-1"><?= date('d M Y', strtotime($row['generated_at'])) ?></p>

                            <p class="text-lg font-semibold text-green-600 mt-3">₹<?= number_format($row['total'], 2) ?></p>

                            <button class="viewSalaryBtn w-full mt-4 bg-blue-50 hover:bg-blue-100 text-blue-600 py-2 rounded-lg text-sm font-medium"
                                data-month="<?= htmlspecialchars($row['month']) ?>"
                                data-basic="₹<?= number_format($row['basic'], 2) ?>"
                                data-overtime="<?= $row['overtime_hours'] ?>h @ ₹<?= number_format($row['overtime_rate'], 2) ?>"
                                data-deductions="₹<?= number_format($row['deductions'], 2) ?>"
                                data-total="₹<?= number_format($row['total'], 2) ?>"
                                data-date="<?= date('d M Y', strtotime($row['generated_at'])) ?>">
                                <i class="fa-solid fa-eye mr-1"></i> View Details
                            </button>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

        </main>
    </div>

    <script src="../assets/js/script.js"></script>

</body>

</html>