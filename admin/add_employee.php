<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

$emp = $_SESSION['user'];

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
    //  Get the ID of the newly added employee
    $new_user_id = $stmt->insert_id;

    //  Log activity (by the admin who added)
    $addedBy = $_SESSION['user']['name'];
    $addedById = $_SESSION['user']['id'];
    $activity = "Added new employee: $name";

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

$pageTitle = "Add Employee";
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Employee | Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    @media (max-width: 767px) {
      #sidebar.mobile-hidden {
        transform: translateX(-100%);
      }
    }
  </style>
</head>

<body class="bg-gray-100 min-h-screen flex">

  <!-- SIDEBAR -->
  <?php include_once '../includes/sidebar.php'; ?>

  <!-- Overlay for mobile -->
  <div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden z-30 md:hidden"></div>

  <!-- MAIN CONTENT -->
  <div class="flex-1 flex flex-col md:ml-64">
    <!-- HEADER -->
    <?php include_once '../includes/header.php'; ?>



    <!-- PAGE CONTENT -->
    <main class="flex-1 pt-20 pb-10 px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-0 mb-4 px-2 sm:px-0">
        <!-- Left Section: Title -->
        <div class="text-center sm:text-left w-full sm:w-auto">
          <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Employees</h2>
          <p class="text-gray-600 text-sm sm:text-base">Manage your workforce</p>
        </div>
      </div>
      <?php include '../includes/breadcrumb.php'; ?>
      <div class="mx-auto bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8">

        <div class="mb-6 text-center sm:text-left">
          <h2 class="text-2xl font-bold text-gray-900">Add Employee</h2>
          <p class="text-gray-600 text-sm">Fill in the details to add a new employee</p>
        </div>

        <form method="POST" class="space-y-5">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
            <input type="text" name="name" placeholder="Enter full name" required
              class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" placeholder="Enter email" required
              class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input type="password" name="password" placeholder="Enter password" required
              class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
            <select name="department_id"
              class="w-full border border-gray-300 rounded-lg px-3 py-2.5 bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              <option value="">Select Department</option>
              <?php while ($d = $departments->fetch_assoc()): ?>
                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="pt-3">
            <button type="submit"
              class="w-full bg-blue-600 text-white font-semibold py-2.5 rounded-lg hover:bg-blue-700 transition">
              <i class="fa-solid fa-user-plus mr-2"></i> Add Employee
            </button>
          </div>
        </form>

        <?php if (!empty($error)): ?>
          <p class="text-red-600 mt-4 text-center"><?= $error ?></p>
        <?php endif; ?>

        <div class="text-center mt-6">
          <a href="employees.php" class="text-blue-600 hover:text-blue-800 flex items-center justify-center sm:justify-start text-sm sm:text-base">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg> Back to Employee List
          </a>
        </div>
      </div>
    </main>
  </div>

  <script src="../assets/js/script.js"></script>
</body>

</html>