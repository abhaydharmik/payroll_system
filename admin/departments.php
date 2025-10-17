<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

$emp = $_SESSION['user'];

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

// Handle Delete Department (with employee check)
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);
  $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM users WHERE department_id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $count = $stmt->get_result()->fetch_assoc()['cnt'];
  if ($count > 0) {
    echo "<script>alert('❌ Cannot delete department: employees are still assigned.'); 
              window.location='departments.php';</script>";
    exit;
  } else {
    $stmt = $conn->prepare("DELETE FROM departments WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: departments.php");
    exit;
  }
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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage Departments | Payroll System</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    #sidebar {
      transition: transform 0.3s ease-in-out;
    }

    @media (max-width: 767px) {
      #sidebar.mobile-hidden {
        transform: translateX(-100%);
      }
    }
  </style>
</head>

<body class="bg-gray-100">

  <?php include_once '../includes/sidebar.php'; ?>

  <!-- Overlay for Mobile -->
  <div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden z-30 md:hidden"></div>

  <!-- MAIN CONTENT -->
  <div class="flex-1 flex flex-col min-h-screen md:ml-64">
    <!-- NAVBAR -->
    <header class="fixed top-0 left-0 right-0 md:left-64 bg-white shadow flex justify-between items-center px-4 py-3 z-40">
      <div class="flex items-center space-x-3">
        <!-- Mobile menu button -->
        <button id="sidebarToggle" class="md:hidden text-gray-700 focus:outline-none">
          <i class="fa-solid fa-bars text-xl"></i>
        </button>
        <h1 class="text-lg font-semibold text-gray-700">Manage Departments</h1>
      </div>
      <div class="flex items-center space-x-3">
        <span class="text-gray-700 flex items-center">
          <i class="fas fa-user-circle text-blue-600 mr-1"></i>
          <?= htmlspecialchars($emp['name']); ?>
        </span>
        <a href="../logout.php" class="text-red-600 hover:text-red-800">
          <i class="fas fa-sign-out-alt text-lg"></i>
        </a>
      </div>
    </header>

    <!-- Page Content -->
    <main class="flex-1 pt-20 px-4 md:px-8 pb-8">
      <div class="bg-white shadow-md rounded-lg p-4 md:p-6">

        <!-- Add/Edit Department Form -->
        <form method="POST" class="flex flex-col md:flex-row mb-6 space-y-2 md:space-y-0">
          <?php if ($editDept): ?>
            <input type="hidden" name="id" value="<?= $editDept['id'] ?>">
            <input type="text" name="name" value="<?= htmlspecialchars($editDept['name']) ?>" class="flex-grow border rounded-l px-3 py-2 focus:outline-none focus:ring" required>
            <button type="submit" name="update" class="bg-yellow-500 text-white px-4 rounded-r hover:bg-yellow-600 mt-2 md:mt-0 md:ml-2">Update</button>
            <a href="departments.php" class="text-blue-600 hover:underline px-4 py-2 rounded border mt-2 md:mt-0 md:ml-2">Cancel</a>
          <?php else: ?>
            <input type="text" name="name" placeholder="Department Name" class="flex-grow border rounded-l px-3 py-2 focus:outline-none focus:ring" required>
            <button type="submit" name="add" class="bg-blue-600 text-white px-4 rounded-r hover:bg-blue-700 mt-2 md:mt-0 md:ml-2">Add</button>
          <?php endif; ?>
        </form>

        <!-- Departments Table -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="overflow-x-auto">
            <table class="w-full border-collapse table-auto min-w-max">
              <thead>
                <tr class="bg-indigo-600 text-white text-left">
                  <th class="p-2 border">ID</th>
                  <th class="p-2 border">Department</th>
                  <th class="p-2 border">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                  <tr class="hover:bg-gray-50">
                    <td class="p-2 border"><?= $row['id'] ?></td>
                    <td class="p-2 border"><?= htmlspecialchars($row['name']) ?></td>
                    <td class="p-2 border space-x-2 flex flex-wrap">
                      <a href="departments.php?edit=<?= $row['id'] ?>" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Edit</a>
                      <a href="departments.php?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this department?')" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Delete</a>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>

          <div class="mt-6 text-center md:text-left">
            <a href="dashboard.php" class="text-blue-600 hover:underline flex items-center justify-center md:justify-start">
              <i class="fa-solid fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
          </div>
        </div>
    </main>
  </div>

  <script src="../assets/js/script.js"></script>
</body>

</html>