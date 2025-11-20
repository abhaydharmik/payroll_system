<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

$emp = $_SESSION['user'];
$pageTitle = "Edit Employee";

$id = $_GET['id'] ?? 0;
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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
      <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="bg-gray-50">

    <!-- SIDEBAR -->
    <?php include_once '../includes/sidebar.php'; ?>

    <!-- Overlay for mobile -->
    <div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden z-30 md:hidden"></div>
    <!-- MAIN CONTENT -->
    <div class="flex-1 flex flex-col md:ml-64">
        <!-- HEADER -->
        <?php include_once '../includes/header.php'; ?>
        <!-- Page Content -->
        <main class="pt-20 px-4 sm:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Edit Employee</h2>
                    <p class="text-gray-600">Modify employee details and department information</p>
                </div>

                <!-- <a href="employees.php"
                    class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 flex items-center w-full sm:w-auto justify-center transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg> Back to Employees
                </a> -->
            </div>

            <?php include '../includes/breadcrumb.php'; ?>


            <!-- Card -->
            <div class="bg-white border border-gray-200 shadow rounded-xl p-6 sm:p-8 mx-auto">
                <?php if (!empty($error)): ?>
                    <div class="mb-4 bg-red-50 text-red-700 px-4 py-2 rounded-lg border border-red-200">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="post" class="space-y-5">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($employee['name']) ?>" required
                            class="w-full border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 px-3 py-2 rounded-lg text-gray-900">
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($employee['email']) ?>" required
                            class="w-full border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 px-3 py-2 rounded-lg text-gray-900">
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" name="password" placeholder="Leave blank to keep current"
                            class="w-full border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 px-3 py-2 rounded-lg text-gray-900">
                    </div>

                    <!-- Department -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                        <select name="department_id"
                            class="w-full border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 px-3 py-2 rounded-lg text-gray-900">
                            <option value="">Select Department</option>
                            <?php while ($d = $departments->fetch_assoc()): ?>
                                <option value="<?= $d['id'] ?>" <?= ($employee['department_id'] == $d['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($d['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-3 pt-4">
                        <a href="employees.php"
                            class="bg-gray-100 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-200 transition">
                            Cancel
                        </a>
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2 rounded-lg transition">
                            Update Employee
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <!-- JS for Sidebar Toggle -->
    <script src="../assets/js/script.js"></script>

</body>

</html>