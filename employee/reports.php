<?php
require '../config.php';
require '../includes/auth.php';
checkRole('employee');

$emp = $_SESSION['user'];
$userId = $_SESSION['user']['id']; // fixed here

// Attendance history
$attendance = $conn->query("
    SELECT date, status 
    FROM attendance 
    WHERE user_id = $userId 
    ORDER BY date DESC 
    LIMIT 30
");

// Leave requests
$leaves = $conn->query("
    SELECT reason, status, applied_at 
    FROM leaves 
    WHERE user_id = $userId 
    ORDER BY applied_at DESC
");

// Salary history
$salaries = $conn->query("
    SELECT month, basic, overtime_hours, overtime_rate, deductions, total, generated_at 
    FROM salaries 
    WHERE user_id = $userId 
    ORDER BY generated_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Reports</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<!-- Sidebar -->
<aside class="w-64 bg-blue-800 text-white flex flex-col fixed h-screen">
    <div class="p-6 border-b border-blue-700">
        <h1 class="text-xl font-bold flex items-center">
            <i class="fa-solid fa-chart-line mr-2"></i> Employee Panel
        </h1>
    </div>
    <nav class="flex-1 px-4 py-6 space-y-2">
        <a href="dashboard.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700">
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
        <a href="reports.php" class="block py-2 px-3 rounded-lg bg-blue-700">
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
            <h2 class="text-lg font-semibold text-gray-700">My Reports</h2>
            <div class="flex items-center space-x-4">
                <span class="text-gray-700"><i class="fas fa-user-circle text-blue-600 mr-1"></i><?php echo htmlspecialchars($emp['name']); ?></span>
                <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm"><i class="fas fa-sign-out-alt mr-1"></i>Logout</a>
            </div>
        </header>

    <!-- Attendance -->
    <div class="bg-white p-4 rounded shadow-lg mb-6 mt-4">
        <h2 class="text-lg font-semibold mb-3">Recent Attendance</h2>
        <table class="w-full border">
            <thead class="bg-gray-200">
                <tr>
                    <th class="p-2 border">Date</th>
                    <th class="p-2 border">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($a = $attendance->fetch_assoc()): ?>
                    <tr>
                        <td class="p-2 border"><?= htmlspecialchars($a['date']) ?></td>
                        <td class="p-2 border"><?= htmlspecialchars($a['status']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Leaves -->
    <div class="bg-white p-4 rounded shadow mb-6">
        <h2 class="text-lg font-semibold mb-3">Leave Requests</h2>
        <table class="w-full border">
            <thead class="bg-gray-200">
                <tr>
                    <th class="p-2 border">Reason</th>
                    <th class="p-2 border">Status</th>
                    <th class="p-2 border">Applied At</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($l = $leaves->fetch_assoc()): ?>
                    <tr>
                        <td class="p-2 border"><?= htmlspecialchars($l['reason']) ?></td>
                        <td class="p-2 border"><?= htmlspecialchars($l['status']) ?></td>
                        <td class="p-2 border"><?= htmlspecialchars($l['applied_at']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Salaries -->
    <div class="bg-white p-4 rounded shadow">
        <h2 class="text-lg font-semibold mb-3">Salary History</h2>
        <table class="w-full border">
            <thead class="bg-gray-200">
                <tr>
                    <th class="p-2 border">Month</th>
                    <th class="p-2 border">Basic</th>
                    <th class="p-2 border">Overtime</th>
                    <th class="p-2 border">Deductions</th>
                    <th class="p-2 border">Total</th>
                    <th class="p-2 border">Generated At</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($s = $salaries->fetch_assoc()): ?>
                    <tr>
                        <td class="p-2 border"><?= htmlspecialchars($s['month']) ?></td>
                        <td class="p-2 border">$<?= $s['basic'] ?></td>
                        <td class="p-2 border"><?= $s['overtime_hours'] ?> hrs x $<?= $s['overtime_rate'] ?></td>
                        <td class="p-2 border">$<?= $s['deductions'] ?></td>
                        <td class="p-2 border font-bold">₹<?= $s['total'] ?></td>
                        <td class="p-2 border"><?= $s['generated_at'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    </body>

</html>