<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

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
    <title>Leave Requests</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-6xl mx-auto mt-10 bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">📌 Leave Requests</h2>

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
                    <?php while($row = $result->fetch_assoc()): ?>
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
                </tbody>
            </table>
        </div>

        <div class="mt-6 text-center">
            <a href="dashboard.php" class="text-gray-600 hover:text-blue-600 hover:underline">⬅ Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
