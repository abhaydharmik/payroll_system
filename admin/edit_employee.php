<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id=? AND role='employee'");
$stmt->bind_param("i", $id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();

if (!$employee) {
    die("Employee not found!");
}

// Fetch departments
$departments = $conn->query("SELECT * FROM departments ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name  = $_POST['name'];
    $email = $_POST['email'];
    $department_id = !empty($_POST['department_id']) ? $_POST['department_id'] : null;

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, password=?, department_id=? WHERE id=?");
        $stmt->bind_param("sssii", $name, $email, $password, $department_id, $id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, department_id=? WHERE id=?");
        $stmt->bind_param("ssii", $name, $email, $department_id, $id);
    }

    if ($stmt->execute()) {
        header("Location: employees.php");
        exit;
    } else {
        $error = "Error: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Edit Employee</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-gray-100">
    <div class="max-w-lg mx-auto mt-10 bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-6 text-gray-700">Edit Employee</h2>

        <form method="post" class="space-y-4">
            <input type="text" name="name" value="<?= htmlspecialchars($employee['name']) ?>" required class="w-full border px-3 py-2 rounded">
            <input type="email" name="email" value="<?= htmlspecialchars($employee['email']) ?>" required class="w-full border px-3 py-2 rounded">
            <input type="password" name="password" placeholder="New Password (leave blank to keep)" class="w-full border px-3 py-2 rounded">

            <!-- Department Dropdown -->
            <select name="department_id" class="w-full border px-3 py-2 rounded">
                <option value="">Select Department</option>
                <?php while($d = $departments->fetch_assoc()): ?>
                    <option value="<?= $d['id'] ?>" <?= ($employee['department_id'] == $d['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($d['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Update Employee</button>
        </form>

        <?php if(!empty($error)): ?>
            <p class="text-red-600 mt-3"><?= $error ?></p>
        <?php endif; ?>

        <br><a href="employees.php" class="text-blue-600 hover:underline">⬅ Back</a>
    </div>
</body>
</html>
