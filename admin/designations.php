<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

$emp = $_SESSION['user'];
$message = '';
$messageType = 'success';

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $title = trim($_POST['designation']);
  if (!empty($title)) {
    $stmt = $conn->prepare("SELECT id FROM designations WHERE title=?");
    $stmt->bind_param("s", $title);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows == 0 || isset($_POST['edit'])) {
      if (isset($_POST['edit'])) {
        $id = (int) $_POST['edit'];
        $stmt = $conn->prepare("UPDATE designations SET title=? WHERE id=?");
        $stmt->bind_param("si", $title, $id);
        $stmt->execute();
        $message = "✏️ Designation updated successfully!";
      } else {
        $stmt = $conn->prepare("INSERT INTO designations (title) VALUES (?)");
        $stmt->bind_param("s", $title);
        $stmt->execute();
        $message = "✅ Designation added successfully!";
      }
    } else {
      $message = "⚠️ Designation already exists!";
      $messageType = 'error';
    }
  }
  header("Location: designations.php");
  exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
  $id = (int) $_GET['delete'];
  $stmt = $conn->prepare("DELETE FROM designations WHERE id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $message = "🗑️ Designation deleted!";
}

// Fetch All
$result = $conn->query("SELECT * FROM designations ORDER BY id DESC");

// Fetch for Edit
$edit = null;
if (isset($_GET['edit'])) {
  $id = (int) $_GET['edit'];
  $stmt = $conn->prepare("SELECT * FROM designations WHERE id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $edit = $stmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Designations | Admin</title>
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

  <!-- SIDEBAR -->
  <?php include_once '../includes/sidebar.php'; ?>

  <!-- Overlay for Mobile -->
  <div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden z-30 md:hidden"></div>

  <!-- MAIN CONTENT -->
  <div class="flex-1 flex flex-col min-h-screen md:ml-64">

    <!-- NAVBAR -->
    <header class="fixed top-0 left-0 right-0 md:left-64 bg-white shadow flex justify-between items-center px-4 py-3 z-40">
      <div class="flex items-center space-x-3">
        <button id="sidebarToggle" class="md:hidden text-gray-700 focus:outline-none">
          <i class="fa-solid fa-bars text-xl"></i>
        </button>
        <h1 class="text-lg font-semibold text-gray-700">Manage Designations</h1>
      </div>
      <div class="flex items-center space-x-3">
        <span class="text-gray-700 flex items-center">
          <i class="fas fa-user-circle text-blue-600 mr-1"></i>
          <?= htmlspecialchars($emp['name']) ?>
        </span>
        <a href="../logout.php" class="text-red-600 hover:text-red-800">
          <i class="fas fa-sign-out-alt text-lg"></i>
        </a>
      </div>
    </header>

    <!-- PAGE CONTENT -->
    <main class="flex-1 pt-20 px-4 md:px-8 pb-8">

      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0 mb-4">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Designations</h2>
          <p class="text-gray-600"> Manage job titles and roles across all departments.</p>
        </div>
      </div>

      <!-- Flash Message -->
      <?php if ($message): ?>
        <div class="p-3 mb-4 rounded <?= $messageType == 'error' ? 'bg-red-500' : 'bg-green-500' ?> text-white text-sm">
          <?= $message ?>
        </div>
      <?php endif; ?>

      <!-- Add / Edit Form Card -->
      <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="post" class="flex flex-col md:flex-row gap-2">
          <input type="text" name="designation" placeholder="Enter Designation"
            class="border p-2 rounded flex-grow focus:outline-none focus:ring"
            value="<?= $edit ? htmlspecialchars($edit['title']) : '' ?>" required>

          <?php if ($edit): ?>
            <button type="submit" name="edit" value="<?= $edit['id'] ?>"
              class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 mt-2 md:mt-0 md:ml-2">
              Update
            </button>
            <a href="designations.php"
              class="px-4 py-2 border rounded text-blue-600 hover:underline mt-2 md:mt-0 md:ml-2">
              Cancel
            </a>
          <?php else: ?>
            <button type="submit" name="add"
              class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 mt-2 md:mt-0 md:ml-2">
              ➕ Add
            </button>
          <?php endif; ?>
        </form>
      </div>

      <!-- Table Card -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="overflow-x-auto">
          <table class="w-full border-collapse table-auto min-w-max">
            <thead class="bg-gray-200 text-left">
              <tr>
                <th class="p-2 border">ID</th>
                <th class="p-2 border">Designation</th>
                <th class="p-2 border text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50 text-center">
                  <td class="p-2 border"><?= $row['id'] ?></td>
                  <td class="p-2 border"><?= htmlspecialchars($row['title']) ?></td>
                  <td class="p-2 border flex justify-center gap-2 flex-wrap">
                    <a href="?edit=<?= $row['id'] ?>" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">✏ Edit</a>
                    <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this designation?')"
                      class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">🗑 Delete</a>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>

        <!-- Back Button -->
        <div class="mt-4 text-center md:text-left">
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