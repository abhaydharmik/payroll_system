<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

// Add new designation
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    $designation = trim($_POST['designation']);
    if (!empty($designation)) {
        $stmt = $conn->prepare("INSERT INTO designations (name) VALUES (?)");
        $stmt->bind_param("s", $designation);
        if ($stmt->execute()) {
            $message = "✅ Designation added successfully!";
        } else {
            $message = "❌ Error: " . $stmt->error;
        }
    }
}

// Delete designation
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM designations WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "🗑️ Designation deleted!";
    } else {
        $message = "❌ Error: " . $stmt->error;
    }
}

// Fetch all designations
$result = $conn->query("SELECT * FROM designations ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Designations</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto p-6">
        <h2 class="text-2xl font-bold mb-4">Manage Designations</h2>

        <?php if ($message): ?>
            <div class="p-3 mb-4 text-white bg-green-500 rounded">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <!-- Add New Designation -->
        <form method="post" class="flex gap-2 mb-6">
            <input type="text" name="designation" placeholder="Enter Designation"
                   class="border p-2 rounded flex-grow" required>
            <button type="submit" name="add"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                ➕ Add
            </button>
        </form>

        <!-- List of Designations -->
        <table class="w-full bg-white shadow rounded">
            <thead class="bg-gray-200">
                <tr>
                    <th class="p-2 border">ID</th>
                    <th class="p-2 border">Designation</th>
                    <th class="p-2 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr class="text-center">
                    <td class="p-2 border"><?= $row['id'] ?></td>
                    <td class="p-2 border"><?= htmlspecialchars($row['name']) ?></td>
                    <td class="p-2 border">
                        <a href="?delete=<?= $row['id'] ?>"
                           class="bg-red-500 text-white px-3 py-1 rounded"
                           onclick="return confirm('Delete this designation?')">🗑 Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <a href="dashboard.php" class="block mt-6 text-blue-600">⬅ Back to Dashboard</a>
    </div>
</body>
</html>
