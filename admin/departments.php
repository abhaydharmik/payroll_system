<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

// Handle Add Department
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO departments (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
    }
    header("Location: departments.php");
    exit;
}

// Handle Update Department
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $stmt = $conn->prepare("UPDATE departments SET name=? WHERE id=?");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
    }
    header("Location: departments.php");
    exit;
}

// Handle Delete Department
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM departments WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: departments.php");
    exit;
}

// Fetch Departments
$result = $conn->query("SELECT * FROM departments ORDER BY id DESC");

// If editing, fetch department data
$editDept = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM departments WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editDept = $stmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Departments | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans min-h-screen flex flex-col items-center py-10">

    <div class="w-full max-w-4xl bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-6 text-gray-700">Manage Departments</h2>

        <!-- Add/Edit Department Form -->
        <form method="POST" class="flex mb-6">
            <?php if ($editDept): ?>
                <input type="hidden" name="id" value="<?= $editDept['id'] ?>">
                <input type="text" name="name" value="<?= htmlspecialchars($editDept['name']) ?>" 
                       class="flex-grow border rounded-l px-3 py-2 focus:outline-none focus:ring" required>
                <button type="submit" name="update" 
                        class="bg-yellow-500 text-white px-4 rounded-r hover:bg-yellow-600">Update</button>
                <a href="departments.php" class="ml-2 text-blue-600 hover:underline px-4 py-2 rounded border">Cancel</a>
            <?php else: ?>
                <input type="text" name="name" placeholder="Department Name" 
                       class="flex-grow border rounded-l px-3 py-2 focus:outline-none focus:ring" required>
                <button type="submit" name="add" 
                        class="bg-blue-600 text-white px-4 rounded-r hover:bg-blue-700">Add</button>
            <?php endif; ?>
        </form>

        <!-- Departments Table -->
        <div class="overflow-x-auto">
        <table class="w-full border-collapse table-auto">
            <thead>
                <tr class="bg-gray-200 text-left">
                    <th class="p-2 border">ID</th>
                    <th class="p-2 border">Department</th>
                    <!-- 🔴 REMOVED: Created column -->
                    <th class="p-2 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50">
                    <td class="p-2 border"><?= $row['id'] ?></td>
                    <td class="p-2 border"><?= htmlspecialchars($row['name']) ?></td>
                    <!-- 🔴 REMOVED: created_at usage -->
                    <td class="p-2 border space-x-2">
                        <a href="departments.php?edit=<?= $row['id'] ?>" 
                           class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Edit</a>
                        <a href="departments.php?delete=<?= $row['id'] ?>" 
                           onclick="return confirm('Delete this department?')" 
                           class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        </div>

        <div class="mt-4">
            <a href="dashboard.php" class="text-blue-600 hover:underline">⬅ Back to Dashboard</a>
        </div>
    </div>

</body>
</html>
