<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

// Get attendance records with employee info
$sql = "SELECT a.id, u.name, a.date, a.status 
        FROM attendance a 
        JOIN users u ON a.user_id=u.id 
        ORDER BY a.date DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Records</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-5xl mx-auto mt-10 bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">📅 Attendance Records</h2>

        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 rounded-lg">
                <thead class="bg-blue-600 text-white">
                    <tr>
                        <th class="px-4 py-2 text-left">ID</th>
                        <th class="px-4 py-2 text-left">Employee</th>
                        <th class="px-4 py-2 text-left">Date</th>
                        <th class="px-4 py-2 text-left">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2"><?= $row['id'] ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($row['name']) ?></td>
                        <td class="px-4 py-2"><?= $row['date'] ?></td>
                        <td class="px-4 py-2">
                            <?php if($row['status'] == 'Present'): ?>
                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-sm font-medium">Present</span>
                            <?php elseif($row['status'] == 'Absent'): ?>
                                <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-sm font-medium">Absent</span>
                            <?php else: ?>
                                <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-sm font-medium"><?= htmlspecialchars($row['status']) ?></span>
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
