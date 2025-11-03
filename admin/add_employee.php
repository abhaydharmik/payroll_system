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

    // Insert into users table
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, department_id) VALUES (?, ?, ?, 'employee', ?)");
    $stmt->bind_param("sssi", $name, $email, $password, $department_id);

    if ($stmt->execute()) {

        // ✅ Get the ID of the newly added employee
        $new_user_id = $stmt->insert_id;

        // ✅ Log activity (by the admin who added)
        $addedBy = $_SESSION['user']['name'];
        $addedById = $_SESSION['user']['id'];
        $activity = "Added new employee: $name";

        // ✅ Include user_id in insert to satisfy foreign key
        $activity_sql = "INSERT INTO activities (user_id, description, user_name, created_at) VALUES (?, ?, ?, NOW())";
        $activity_stmt = $conn->prepare($activity_sql);
        $activity_stmt->bind_param("iss", $addedById, $activity, $addedBy);
        $activity_stmt->execute();

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Employee</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center px-4 sm:px-6">
    <div class="w-full max-w-md sm:max-w-lg bg-white shadow-lg rounded-2xl p-6 sm:p-8">
        <h2 class="text-2xl sm:text-3xl font-bold mb-6 text-gray-800 text-center">Add Employee</h2>

        <form method="post" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Full Name</label>
                <input type="text" name="name" placeholder="Enter full name" required
                    class="w-full border border-gray-300 px-3 py-2 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Email</label>
                <input type="email" name="email" placeholder="Enter email" required
                    class="w-full border border-gray-300 px-3 py-2 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Password</label>
                <input type="password" name="password" placeholder="Enter password" required
                    class="w-full border border-gray-300 px-3 py-2 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Department</label>
                <select name="department_id"
                    class="w-full border border-gray-300 px-3 py-2 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">Select Department</option>
                    <?php while ($d = $departments->fetch_assoc()): ?>
                        <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="pt-2">
                <button type="submit"
                    class="w-full bg-blue-600 text-white font-semibold py-2.5 rounded-lg hover:bg-blue-700 transition">
                    Add Employee
                </button>
            </div>
        </form>

        <?php if (!empty($error)): ?>
            <p class="text-red-600 mt-4 text-center"><?= $error ?></p>
        <?php endif; ?>

        <div class="text-center mt-6">
            <a href="employees.php" class="text-blue-600 hover:underline text-sm sm:text-base">
                ⬅ Back to Employee List
            </a>
        </div>
    </div>
</body>
</html>
