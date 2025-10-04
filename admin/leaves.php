<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

$emp = $_SESSION['user'];

// Handle Approve/Reject
if (isset($_GET['id']) && isset($_GET['action'])) {
    $status = ($_GET['action'] == 'approve') ? 'Approved' : 'Rejected';
    $stmt = $conn->prepare("UPDATE leaves SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $_GET['id']);
    $stmt->execute();
    header("Location: leaves.php");
    exit;
}

$sql = "SELECT l.id, u.name, l.reason, l.status, l.applied_at 
        FROM leaves l 
        JOIN users u ON l.user_id=u.id 
        ORDER BY l.applied_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Leave Requests | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 flex">

    <!-- Sidebar -->
    <aside class="w-64 bg-blue-800 text-white flex flex-col fixed h-screen">
        <div class="p-6 border-b border-blue-700">
            <h1 class="text-2xl font-bold flex items-center">
                <i class="fa-solid fa-chart-line mr-2"></i> Admin Panel
            </h1>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="dashboard.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700">
                <i class="fa-solid fa-gauge mr-2"></i> Dashboard
            </a>
            <a href="employees.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700">
                <i class="fa-solid fa-users mr-2"></i> Employees
            </a>
            <a href="attendance.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700">
                <i class="fa-solid fa-calendar-check mr-2"></i> Attendance
            </a>
            <a href="leaves.php" class="block py-2 px-3 rounded-lg bg-blue-700">
                <i class="fa-solid fa-file-signature mr-2"></i> Leaves
            </a>
            <a href="generate_salary.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700">
                <i class="fa-solid fa-sack-dollar mr-2"></i> Generate Salary
            </a>
            <a href="salary_history.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700">
                <i class="fa-solid fa-file-invoice-dollar mr-2"></i> Salary History
            </a>
            <a href="departments.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700">
                <i class="fa-solid fa-building mr-2"></i> Departments
            </a>
            <a href="designations.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700">
                <i class="fa-solid fa-briefcase mr-2"></i> Designations
            </a>
        </nav>
        <div class="p-4 border-t border-blue-700">
            <p class="text-sm">&copy; <?php echo date("Y"); ?> <span class="font-semibold">Payroll System</span>. All rights reserved.</p>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 ml-64 p-8">

        <header class="bg-white shadow px-6 py-4 flex justify-between items-center rounded">
            <h2 class="text-lg font-semibold text-gray-700"> Leave Requests</h2>
            <div class="flex items-center space-x-4">
                <span class="text-gray-700"><i class="fas fa-user-circle text-blue-600 mr-1"></i><?php echo htmlspecialchars($emp['name']); ?></span>
                <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm"><i class="fas fa-sign-out-alt mr-1"></i>Logout</a>
            </div>
        </header>

        <div class="bg-white shadow-md rounded-lg p-6 mt-4">
            <!-- <h2 class="text-2xl font-bold mb-6 text-gray-700 flex items-center">
                <i class="fa-solid fa-file-signature text-blue-600 mr-2"></i> Leave Requests
            </h2> -->

            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-200 rounded-lg">
                    <thead class="bg-blue-600 text-white">
                        <tr>
                            <th class="px-4 py-2 text-left">ID</th>
                            <th class="px-4 py-2 text-left">Employee</th>
                            <th class="px-4 py-2 text-left">Reason</th>
                            <th class="px-4 py-2 text-left">Applied At</th>
                            <th class="px-4 py-2 text-left">Status</th>
                            <th class="px-4 py-2 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-2"><?= $row['id'] ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($row['name']) ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($row['reason']) ?></td>
                                    <td class="px-4 py-2"><?= $row['applied_at'] ?></td>
                                    <td class="px-4 py-2">
                                        <?php if ($row['status'] == 'Pending'): ?>
                                            <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-sm font-medium">Pending</span>
                                        <?php elseif ($row['status'] == 'Approved'): ?>
                                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-sm font-medium">Approved</span>
                                        <?php elseif ($row['status'] == 'Rejected'): ?>
                                            <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-sm font-medium">Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-2">
                                        <?php if ($row['status'] == 'Pending'): ?>
                                            <a href="?id=<?= $row['id'] ?>&action=approve"
                                                class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">✅ Approve</a>
                                            <a href="?id=<?= $row['id'] ?>&action=reject"
                                                class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm ml-2">❌ Reject</a>
                                        <?php else: ?>
                                            <span class="text-gray-500 italic">No action</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-gray-500 py-4">No leave requests found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                <a href="dashboard.php" class="text-blue-600 hover:text-blue-800 flex items-center">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </main>
</body>

</html>