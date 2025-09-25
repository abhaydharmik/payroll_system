<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

// Get all departments for filter dropdown
$departments = $conn->query("SELECT * FROM departments ORDER BY name ASC");

// Handle filter
$selectedDept = $_GET['department_id'] ?? '';

$sql = "SELECT u.id, u.name, u.email, d.name AS department
        FROM users u
        LEFT JOIN departments d ON u.department_id = d.id
        WHERE u.role='employee'";

if (!empty($selectedDept)) {
    $sql .= " AND u.department_id = " . intval($selectedDept);
}

$sql .= " ORDER BY u.id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Employees</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-6xl mx-auto mt-10 bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-6 text-gray-700">Employee List</h2>

        <div class="flex justify-between items-center mb-4">
            <a href="add_employee.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">+ Add Employee</a>

            <!-- Department Filter -->
            <form method="get" class="flex items-center">
                <label for="department_id" class="mr-2 text-gray-700 font-medium">Filter:</label>
                <select name="department_id" id="department_id" class="border border-gray-300 rounded px-3 py-2 mr-2">
                    <option value="">All Departments</option>
                    <?php while ($d = $departments->fetch_assoc()): ?>
                        <option value="<?= $d['id'] ?>" <?= ($selectedDept == $d['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($d['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" class="bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-900">Apply</button>
            </form>
        </div>

        <table class="w-full border-collapse border border-gray-300">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border border-gray-300 px-4 py-2">ID</th>
                    <th class="border border-gray-300 px-4 py-2">Name</th>
                    <th class="border border-gray-300 px-4 py-2">Email</th>
                    <th class="border border-gray-300 px-4 py-2">Department</th>
                    <th class="border border-gray-300 px-4 py-2">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-100">
                            <td class="border border-gray-300 px-4 py-2"><?= $row['id'] ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['name']) ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['email']) ?></td>
                            <td class="border border-gray-300 px-4 py-2">
                                <?= $row['department'] ? htmlspecialchars($row['department']) : '<span class="text-gray-400 italic">Not Assigned</span>' ?>
                            </td>
                            <td class="border border-gray-300 px-4 py-2">
                                <a href="edit_employee.php?id=<?= $row['id'] ?>" class="text-blue-600 hover:underline">✏ Edit</a> | 
                                <a href="delete_employee.php?id=<?= $row['id'] ?>" class="text-red-600 hover:underline" onclick="return confirm('Are you sure?')">🗑 Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-gray-500 py-4">No employees found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <br><a href="dashboard.php" class="text-blue-600 hover:underline">⬅ Back</a>
    </div>
</body>
</html>
