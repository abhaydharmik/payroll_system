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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name  = $_POST['name'];
    $email = $_POST['email'];

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, password=? WHERE id=?");
        $stmt->bind_param("sssi", $name, $email, $password, $id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name=?, email=? WHERE id=?");
        $stmt->bind_param("ssi", $name, $email, $id);
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Employee</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-lg mx-auto bg-white shadow-md rounded-lg p-6 mt-10">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">✏ Edit Employee</h2>

        <form method="post" class="space-y-4">
            <div>
                <label class="block text-gray-700 font-medium">Name</label>
                <input type="text" name="name" 
                       value="<?= htmlspecialchars($employee['name']) ?>" 
                       required 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-blue-300">
            </div>

            <div>
                <label class="block text-gray-700 font-medium">Email</label>
                <input type="email" name="email" 
                       value="<?= htmlspecialchars($employee['email']) ?>" 
                       required 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-blue-300">
            </div>

            <div>
                <label class="block text-gray-700 font-medium">New Password</label>
                <input type="password" name="password" 
                       placeholder="Leave blank to keep current password" 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-blue-300">
            </div>

            <button type="submit" 
                    class="w-full bg-blue-600 text-white py-2 rounded-md font-semibold hover:bg-blue-700 transition">
                Update Employee
            </button>
        </form>

        <?php if(!empty($error)): ?>
            <p class="text-red-600 mt-4"><?= $error ?></p>
        <?php endif; ?>

        <div class="mt-6 text-center">
            <a href="employees.php" class="text-gray-600 hover:text-blue-600 hover:underline">⬅ Back to Employees</a>
        </div>
    </div>
</body>
</html>
