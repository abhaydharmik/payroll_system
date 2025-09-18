<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

// Fetch all employees
$result = $conn->query("SELECT * FROM users WHERE role='employee'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Employees</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-6xl mx-auto p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">👥 Manage Employees</h2>

        <!-- Add Employee Button -->
        <a href="add_employee.php" 
           class="inline-block mb-4 bg-blue-600 text-white px-4 py-2 rounded-md shadow hover:bg-blue-700 transition">
            ➕ Add Employee
        </a>

        <!-- Employee Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full border-collapse">
                <thead class="bg-blue-600 text-white">
                    <tr>
                        <th class="px-4 py-2 text-left">ID</th>
                        <th class="px-4 py-2 text-left">Name</th>
                        <th class="px-4 py-2 text-left">Email</th>
                        <th class="px-4 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2"><?= $row['id'] ?></td>
                        <td class="px-4 py-2 font-medium"><?= htmlspecialchars($row['name']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($row['email']) ?></td>
                        <td class="px-4 py-2">
                            <a href="edit_employee.php?id=<?= $row['id'] ?>" 
                               class="text-blue-600 hover:underline">✏ Edit</a> | 
                            <a href="delete_employee.php?id=<?= $row['id'] ?>" 
                               onclick="return confirm('Delete this employee?')" 
                               class="text-red-600 hover:underline">🗑 Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Back to Dashboard -->
        <div class="mt-6">
            <a href="dashboard.php" 
               class="text-gray-600 hover:text-blue-600 hover:underline">⬅ Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
