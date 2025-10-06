<?php
require '../config.php';
require '../includes/auth.php';
include '../includes/sidebar.php';
checkRole('admin');

$emp = $_SESSION['user'] ?? ['name' => 'Admin'];

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
        ORDER BY l.applied_at ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Requests | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 flex">

    <!-- Main Content -->
    <main class="flex-1 ml-0 md:ml-64 p-4 md:p-8 pt-24 md:pt-8 transition-all">

        <!-- Page Header -->
        <header class="bg-white shadow px-4 py-3 md:px-6 md:py-4 flex flex-col md:flex-row justify-between items-start md:items-center rounded mb-4">
            <h2 class="text-xl font-semibold text-gray-700 mb-2 md:mb-0">Leave Requests</h2>
            <div class="flex items-center space-x-3 text-sm flex-wrap">
                <span class="text-gray-700 whitespace-nowrap flex items-center">
                    <i class="fas fa-user-circle text-blue-600 mr-1"></i>
                    <?= htmlspecialchars($emp['name'] ?? 'Admin'); ?>
                </span>
                <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs md:text-sm flex items-center">
                    <i class="fas fa-sign-out-alt mr-1"></i>Logout
                </a>
            </div>
        </header>

        <!-- Leaves Table -->
        <div class="bg-white shadow-md rounded-lg p-4 md:p-6 overflow-x-auto">
            <table class="min-w-[400px] w-full border-collapse table-auto">
                <thead class="bg-blue-600 text-white text-sm">
                    <tr>
                        <th class="px-3 py-2 text-left">No</th>
                        <th class="px-3 py-2 text-left">Employee</th>
                        <th class="px-3 py-2 text-left hidden sm:table-cell">Reason</th>
                        <th class="px-3 py-2 text-left">Applied At</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-left">Action</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-3 py-2 font-medium"><?= $row['id'] ?></td>
                                <td class="px-3 py-2"><?= htmlspecialchars($row['name']) ?></td>
                                <td class="px-3 py-2 hidden sm:table-cell truncate max-w-xs"><?= htmlspecialchars($row['reason']) ?></td>
                                <td class="px-3 py-2 text-xs md:text-sm whitespace-nowrap"><?= $row['applied_at'] ?></td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <?php if ($row['status'] == 'Pending'): ?>
                                        <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs font-medium">Pending</span>
                                    <?php elseif ($row['status'] == 'Approved'): ?>
                                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-medium">Approved</span>
                                    <?php elseif ($row['status'] == 'Rejected'): ?>
                                        <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-medium">Rejected</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap flex flex-col sm:flex-row gap-1 sm:gap-2">
                                    <?php if ($row['status'] == 'Pending'): ?>
                                        <a href="?id=<?= $row['id'] ?>&action=approve"
                                            class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-xs md:text-sm flex items-center justify-center sm:justify-start">
                                            ✅ <span class="hidden md:inline ml-1">Approve</span>
                                        </a>
                                        <a href="?id=<?= $row['id'] ?>&action=reject"
                                            class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs md:text-sm flex items-center justify-center sm:justify-start">
                                            ❌ <span class="hidden md:inline ml-1">Reject</span>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-500 italic text-xs md:text-sm text-center">Completed</span>
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

        <div class="mt-4 md:mt-6">
            <a href="dashboard.php" class="text-blue-600 hover:text-blue-800 flex items-center text-sm md:text-base">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>
    </main>


    <!-- Sidebar Toggle Script -->
    <script src="../assets/js/script.js"></script>
</body>

</html>