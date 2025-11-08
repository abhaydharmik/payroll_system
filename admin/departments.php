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

$pageTitle = "Departments";
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
    <?php include_once '../includes/header.php'; ?>

    <!-- Page Content -->
    <main class="flex-1 pt-20 px-4 md:px-8 pb-8">

      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0 mb-4">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Departments</h2>
          <p class="text-gray-600">Manage your organization’s departments and assign employees to each.</p>
        </div>
      </div>

      <div class="">

        <!-- Departments Table -->
        <div class="bg-white shadow-sm rounded-lg p-6">
          <!-- Add/Edit Department Form -->
          <form method="POST" class="flex flex-col md:flex-row mb-6 space-y-2 md:space-y-0">
            <?php if ($editDept): ?>
              <input type="hidden" name="id" value="<?= $editDept['id'] ?>">
              <input type="text" name="name" value="<?= htmlspecialchars($editDept['name']) ?>" class="flex-grow border rounded-xl px-3 py-2 focus:outline-none focus:ring" required>
              <button type="submit" name="update" class="bg-yellow-500 text-white px-4 rounded-xl  hover:bg-yellow-600 mt-2 md:mt-0 md:ml-2">Update</button>
              <a href="departments.php" class="text-blue-600 hover:underline px-4 py-2 rounded border mt-2 md:mt-0 md:ml-2">Cancel</a>
            <?php else: ?>
              <input type="text" name="name" placeholder="Department Name" class="flex-grow border rounded-l px-3 py-2 focus:outline-none focus:ring" required>
              <button type="submit" name="add" class="bg-blue-600 text-white px-4 rounded-r hover:bg-blue-700 mt-2 md:mt-0 md:ml-2">Add</button>
            <?php endif; ?>
          </form>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
              </thead>

              <tbody class="bg-white divide-y divide-gray-200">
                <?php while ($row = $result->fetch_assoc()): ?>
                  <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-4 text-gray-500"><?= $row['id'] ?></td>
                    <td class="px-4 py-4 font-medium text-gray-900"><?= htmlspecialchars($row['name']) ?></td>
                    <td class="px-4 py-4">
                      <div class="flex flex-wrap gap-1 text-center">
                        <a href="departments.php?edit=<?= $row['id'] ?>"
                          class="inline-flex items-center px-3 py-1 text-xs font-medium text-yellow-700 transition">
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M12 20h9" />
                            <path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4Z" />
                          </svg>
                          <!-- Edit -->
                        </a>
                        <a href="departments.php?delete=<?= $row['id'] ?>"
                          onclick="return confirm('Are you sure you want to delete this department?')"
                          class="inline-flex items-center px-3 py-1 text-xs font-medium text-red-700 transition">
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6" />
                            <path d="M19 6L17.5 20.5a2 2 0 0 1-2 1.5h-7a2 2 0 0 1-2-1.5L5 6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                            <line x1="10" y1="11" x2="10" y2="17" />
                            <line x1="14" y1="11" x2="14" y2="17" />
                          </svg>
                          <!-- Delete -->
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>

          </div>

          <div class="mt-6 text-center md:text-left">
            <a href="dashboard.php"
              class="text-blue-600 hover:underline flex items-center justify-center md:justify-start">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg> Back to Dashboard
            </a>
          </div>
        </div>

    </main>
  </div>

  <script src="../assets/js/script.js"></script>
</body>

</html>