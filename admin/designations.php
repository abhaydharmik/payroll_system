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

$pageTitle = "Designations";

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
    <?php include_once '../includes/header.php'; ?>

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


      <!-- Table Card -->
      <div class="bg-white shadow-sm rounded-lg p-6">
        <!-- Add / Edit Form Card -->
        <div class="mb-6">
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
                Add
              </button>
            <?php endif; ?>
          </form>
        </div>

        <div class="overflow-x-auto bg-white  rounded-lg border border-gray-200">
          <table class="w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Designation</th>
                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50 transition">
                  <td class="px-6 py-4 text-gray-700"><?= $row['id'] ?></td>
                  <td class="px-6 py-4 font-medium text-gray-900"><?= htmlspecialchars($row['title']) ?></td>
                  <td class="px-6 py-4">
                    <div class="flex justify-center flex-wrap gap-2">
                      <a href="?edit=<?= $row['id'] ?>"
                        class="inline-flex items-center px-3 py-1.5 bg-yellow-100 text-yellow-700 text-xs font-medium rounded-full hover:bg-yellow-200 transition">
                        ✏️ Edit
                      </a>
                      <a href="?delete=<?= $row['id'] ?>"
                        onclick="return confirm('Are you sure you want to delete this designation?')"
                        class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 text-xs font-medium rounded-full hover:bg-red-200 transition">
                        🗑️ Delete
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>

        <!-- Back Button -->
      <div class="mt-6 text-center md:text-left">
        <a href="dashboard.php"
          class="text-blue-600 hover:underline flex items-center justify-center md:justify-start">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg> Back to Dashboard
        </a>
      </div>
      </div>

      
  </div>


  </main>
  </div>

  <script src="../assets/js/script.js"></script>
</body>

</html>