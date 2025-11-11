<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

$emp = $_SESSION['user'];

/* ===========================
   ADD DEPARTMENT
   =========================== */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
  $name = trim($_POST['name']);

  if (!empty($name)) {
    $stmt = $conn->prepare("INSERT INTO departments (name) VALUES (?)");
    $stmt->bind_param("s", $name);
    $stmt->execute();

    // ✅ Log Activity
    $adminId = $_SESSION['user']['id'];
    $adminName = $_SESSION['user']['name'];
    $activity_desc = "Created new department: $name";

    $log = $conn->prepare("INSERT INTO activities (user_id, description, user_name, created_at) VALUES (?, ?, ?, NOW())");
    $log->bind_param("iss", $adminId, $activity_desc, $adminName);
    $log->execute();
  }

  header("Location: departments.php");
  exit;
}

/* ===========================
   UPDATE DEPARTMENT
   =========================== */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
  $id = intval($_POST['id']);
  $name = trim($_POST['name']);

  if (!empty($name)) {
    $stmt = $conn->prepare("UPDATE departments SET name=? WHERE id=?");
    $stmt->bind_param("si", $name, $id);
    $stmt->execute();

    // ✅ Log Activity
    $adminId = $_SESSION['user']['id'];
    $adminName = $_SESSION['user']['name'];
    $activity_desc = "Updated department to: $name";

    $log = $conn->prepare("INSERT INTO activities (user_id, description, user_name, created_at) VALUES (?, ?, ?, NOW())");
    $log->bind_param("iss", $adminId, $activity_desc, $adminName);
    $log->execute();
  }

  header("Location: departments.php");
  exit;
}

/* ===========================
   DELETE DEPARTMENT
   =========================== */
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);

  // Prevent delete if employees exist under this department
  $check = $conn->prepare("SELECT COUNT(*) as cnt FROM users WHERE department_id=?");
  $check->bind_param("i", $id);
  $check->execute();
  $count = $check->get_result()->fetch_assoc()['cnt'];

  if ($count > 0) {
    echo "<script>alert('❌ Cannot delete department: employees are still assigned.'); window.location='departments.php';</script>";
    exit;
  }

  // Get name for log
  $get = $conn->prepare("SELECT name FROM departments WHERE id=?");
  $get->bind_param("i", $id);
  $get->execute();
  $get->bind_result($deptName);
  $get->fetch();
  $get->close();

  // Delete department
  $del = $conn->prepare("DELETE FROM departments WHERE id=?");
  $del->bind_param("i", $id);
  $del->execute();

  // ✅ Log Activity
  $adminId = $_SESSION['user']['id'];
  $adminName = $_SESSION['user']['name'];
  $activity_desc = "Deleted department: $deptName";

  $log = $conn->prepare("INSERT INTO activities (user_id, description, user_name, created_at) VALUES (?, ?, ?, NOW())");
  $log->bind_param("iss", $adminId, $activity_desc, $adminName);
  $log->execute();

  header("Location: departments.php");
  exit;
}

/* ===========================
   FETCH & EDIT DATA
   =========================== */
$result = $conn->query("SELECT * FROM departments ORDER BY id DESC");
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

  <?php include '../includes/sidebar.php'; ?>
  <div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden z-30 md:hidden"></div>

  <div class="flex-1 flex flex-col min-h-screen md:ml-64">
    <?php include '../includes/header.php'; ?>

    <main class="flex-1 pt-20 px-4 md:px-8 pb-8">

      <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Departments</h2>
        <p class="text-gray-600">Manage your organization’s departments.</p>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200">

        <!-- Form -->
        <div class="p-6 border-b border-gray-200">
          <form method="POST" class="flex flex-col sm:flex-row gap-3">
            <?php if ($editDept): ?>
              <input type="hidden" name="id" value="<?= $editDept['id'] ?>">
              <input type="text" name="name" value="<?= htmlspecialchars($editDept['name']) ?>" class="flex-1 border rounded-xl px-4 py-2" required>
              <button type="submit" name="update" class="px-4 py-2 bg-yellow-500 text-white rounded-xl hover:bg-yellow-600">Update</button>
              <a href="departments.php" class="px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-50">Cancel</a>
            <?php else: ?>
              <input type="text" name="name" placeholder="Department Name" class="flex-1 border rounded-xl px-4 py-2" required>
              <button type="submit" name="add" class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700">Add Department</button>
            <?php endif; ?>
          </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50 transition">
                  <td class="px-6 py-4 text-gray-500"><?= $row['id'] ?></td>
                  <td class="px-6 py-4 font-medium text-gray-900"><?= htmlspecialchars($row['name']) ?></td>
                  <td class="px-6 py-4">
                    <div class="flex space-x-2">
                      <a href="departments.php?edit=<?= $row['id'] ?>" class="text-yellow-600 hover:text-yellow-800"><i class="fa-solid fa-pen-to-square"></i></a>
                      <a href="departments.php?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this department?')" class="text-red-600 hover:text-red-800"><i class="fa-solid fa-trash"></i></a>
                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>

        <div class="p-6 border-t border-gray-200">
          <a href="dashboard.php" class="text-blue-600 hover:text-blue-800 flex items-center text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg> Back to Dashboard
          </a>
        </div>

      </div>

    </main>
  </div>

  <script src="../assets/js/script.js"></script>
</body>

</html>