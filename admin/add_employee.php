<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

// Fetch departments
$departments = $conn->query("SELECT * FROM departments ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name  = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $department_id = !empty($_POST['department_id']) ? $_POST['department_id'] : null;

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, department_id) VALUES (?, ?, ?, 'employee', ?)");
    $stmt->bind_param("sssi", $name, $email, $password, $department_id);

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
<head>
    <title>Add Employee</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-lg mx-auto mt-10 bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-6 text-gray-700">Add Employee</h2>

        <form method="post" class="space-y-4">
            <input type="text" name="name" placeholder="Full Name" required class="w-full border px-3 py-2 rounded">
            <input type="email" name="email" placeholder="Email" required class="w-full border px-3 py-2 rounded">
            <input type="password" name="password" placeholder="Password" required class="w-full border px-3 py-2 rounded">

            <!-- Department Dropdown -->
            <select name="department_id" class="w-full border px-3 py-2 rounded">
                <option value="">Select Department</option>
                <?php while($d = $departments->fetch_assoc()): ?>
                    <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                <?php endwhile; ?>
            </select>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add Employee</button>
        </form>

        <?php if(!empty($error)): ?>
            <p class="text-red-600 mt-3"><?= $error ?></p>
        <?php endif; ?>

        <br><a href="employees.php" class="text-blue-600 hover:underline">⬅ Back</a>
    </div>
</body>
</html>
