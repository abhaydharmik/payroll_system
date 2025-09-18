<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'employee') {
    header('Location: ../index.php');
    exit;
}

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
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center shadow-md">
        <h1 class="text-lg font-bold">My Salary History</h1>
        <a href="dashboard.php" class="bg-white text-blue-600 px-4 py-2 rounded-md text-sm hover:bg-gray-100 transition">⬅ Back</a>
    </nav>

    <!-- Content -->
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
