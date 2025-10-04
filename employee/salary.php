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
    <title>My Salary Slips</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
            <a href="dashboard.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700 ">
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
            <a href="salary.php" class="block py-2 px-3 rounded-lg bg-blue-700">
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
        <!-- Content -->
        <header class="bg-white shadow px-6 py-4 flex justify-between items-center rounded">
            <h2 class="text-lg font-semibold text-gray-700">Salary Slips</h2>
            <div class="flex items-center space-x-4">
                <span class="text-gray-700"><i class="fas fa-user-circle text-blue-600 mr-1"></i><?php echo htmlspecialchars($emp['name']); ?></span>
                <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm"><i class="fas fa-sign-out-alt mr-1"></i>Logout</a>
            </div>
        </header>
        <div class="flex-1 p-6 flex flex-col items-center">
            <div class="w-full max-w-5xl bg-white rounded-xl shadow-md p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Salary Slips</h2>

                <div class="overflow-x-auto">
                    <table class="w-full border border-gray-200 rounded-lg">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left">Month</th>
                                <th class="px-4 py-2 text-left">Basic</th>
                                <th class="px-4 py-2 text-left">Overtime Hours</th>
                                <th class="px-4 py-2 text-left">Overtime Rate</th>
                                <th class="px-4 py-2 text-left">Deductions</th>
                                <th class="px-4 py-2 text-left">Total</th>
                                <th class="px-4 py-2 text-left">Generated At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($salaries->num_rows > 0) { ?>
                                <?php while ($row = $salaries->fetch_assoc()) { ?>
                                    <tr class="border-t hover:bg-gray-50 transition">
                                        <td class="px-4 py-2"><?php echo htmlspecialchars($row['month']); ?></td>
                                        <td class="px-4 py-2">₹<?php echo number_format($row['basic'], 2); ?></td>
                                        <td class="px-4 py-2"><?php echo $row['overtime_hours']; ?></td>
                                        <td class="px-4 py-2">₹<?php echo number_format($row['overtime_rate'], 2); ?></td>
                                        <td class="px-4 py-2 text-red-600">₹<?php echo number_format($row['deductions'], 2); ?></td>
                                        <td class="px-4 py-2 text-green-600 font-semibold">₹<?php echo number_format($row['total'], 2); ?></td>
                                        <td class="px-4 py-2"><?php echo $row['generated_at']; ?></td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-gray-500">No salary records found.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
</body>

</html>