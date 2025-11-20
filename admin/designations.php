<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

$emp = $_SESSION['user'];
$message = '';
$messageType = 'success';

// Handle Add / Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $title = trim($_POST['designation']);

  if (!empty($title)) {
    // Check existing
    $stmt = $conn->prepare("SELECT id FROM designations WHERE title=?");
    $stmt->bind_param("s", $title);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows == 0 || isset($_POST['edit'])) {

      // Update
      if (isset($_POST['edit'])) {
        $id = (int) $_POST['edit'];
        $stmt = $conn->prepare("UPDATE designations SET title=? WHERE id=?");
        $stmt->bind_param("si", $title, $id);
        $stmt->execute();

        //  Log activity
        $log = $conn->prepare("INSERT INTO activities (user_id, description, user_name, created_at) VALUES (?, ?, ?, NOW())");
        $desc = "Updated designation to: $title";
        $log->bind_param("iss", $_SESSION['user']['id'], $desc, $_SESSION['user']['name']);
        $log->execute();

        $message = "✏️ Designation updated successfully!";
      } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO designations (title) VALUES (?)");
        $stmt->bind_param("s", $title);
        $stmt->execute();

        //  Log activity
        $log = $conn->prepare("INSERT INTO activities (user_id, description, user_name, created_at) VALUES (?, ?, ?, NOW())");
        $desc = "Added new designation: $title";
        $log->bind_param("iss", $_SESSION['user']['id'], $desc, $_SESSION['user']['name']);
        $log->execute();

        $message = " Designation added successfully!";
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

  // Get designation name before delete
  $get = $conn->prepare("SELECT title FROM designations WHERE id=?");
  $get->bind_param("i", $id);
  $get->execute();
  $get->bind_result($delTitle);
  $get->fetch();
  $get->close();

  // Delete
  $stmt = $conn->prepare("DELETE FROM designations WHERE id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute();

  //  Log activity
  $log = $conn->prepare("INSERT INTO activities (user_id, description, user_name, created_at) VALUES (?, ?, ?, NOW())");
  $desc = "Deleted designation: $delTitle";
  $log->bind_param("iss", $_SESSION['user']['id'], $desc, $_SESSION['user']['name']);
  $log->execute();

  $message = "🗑️ Designation deleted!";
  header("Location: designations.php");
  exit;
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
  <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="bg-gray-100">

  <?php include '../includes/sidebar.php'; ?>
  <div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden z-30 md:hidden"></div>

  <div class="flex-1 flex flex-col min-h-screen md:ml-64">
    <?php include '../includes/header.php'; ?>

    <main class="flex-1 pt-20 px-4 md:px-8 pb-8">
      <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Designations</h2>
        <p class="text-gray-600">Manage job roles and position titles in your organization.</p>
      </div>

      <?php if ($message): ?>
        <div class="p-3 mb-4 rounded <?= $messageType == 'error' ? 'bg-red-500' : 'bg-green-500' ?> text-white text-sm">
          <?= $message ?>
        </div>
      <?php endif; ?>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200">

        <!-- FORM -->
        <div class="p-6 border-b border-gray-200">
          <form method="post" class="flex flex-col sm:flex-row gap-3">

            <input
              type="text"
              name="designation"
              placeholder="Enter Designation"
              value="<?= $edit ? htmlspecialchars($edit['title']) : '' ?>"
              class="flex-1 border rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500"
              required>

            <?php if ($edit): ?>

              <button type="submit" name="edit" value="<?= $edit['id'] ?>"
                class="px-4 py-2 bg-yellow-600 text-white rounded-xl hover:bg-yellow-700">
                Update
              </button>

              <a href="designations.php"
                class="px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-50">
                Cancel
              </a>

            <?php else: ?>

              <button type="submit" name="add"
                class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                Add Designation
              </button>

            <?php endif; ?>

          </form>
        </div>


        <!-- DESKTOP TABLE -->
        <div class="hidden md:block overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Designation</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>

            <tbody class="divide-y">
              <?php $result->data_seek(0); ?>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50 transition">
                  <td class="px-6 py-4 text-gray-500"><?= $row['id'] ?></td>
                  <td class="px-6 py-4 font-medium text-gray-900"><?= htmlspecialchars($row['title']) ?></td>
                  <td class="px-6 py-4">
                    <div class="flex space-x-3 text-lg">

                      <a href="?edit=<?= $row['id'] ?>"
                        class="text-yellow-600 hover:text-yellow-800">
                        <i class="fa-solid fa-pen-to-square"></i>
                      </a>

                      <a href="?delete=<?= $row['id'] ?>"
                        onclick="return confirm('Delete this designation?')"
                        class="text-red-600 hover:text-red-800">
                        <i class="fa-solid fa-trash"></i>
                      </a>

                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>


        <!-- MOBILE VIEW -->
        <div class="md:hidden p-4 space-y-4">

          <?php $result->data_seek(0); ?>
          <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>

              <div class="p-4 bg-white rounded-xl shadow border border-gray-200">

                <!-- Top Row -->
                <div class="flex items-center justify-between">
                  <p class="text-lg font-semibold text-gray-900">
                    <?= htmlspecialchars($row['title']) ?>
                  </p>

                  <span class="text-gray-400 text-sm">#<?= $row['id'] ?></span>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-6 mt-4 text-xl">

                  <a href="?edit=<?= $row['id'] ?>"
                    class="text-yellow-600 hover:text-yellow-800">
                    <i class="fa-solid fa-pen-to-square"></i>
                  </a>

                  <a href="?delete=<?= $row['id'] ?>"
                    onclick="return confirm('Delete this designation?')"
                    class="text-red-600 hover:text-red-800">
                    <i class="fa-solid fa-trash"></i>
                  </a>

                </div>

              </div>

            <?php endwhile; ?>
          <?php else: ?>
            <p class="text-center text-gray-500 py-4">No designations found.</p>
          <?php endif; ?>

        </div>


        <!-- Footer -->
        <div class="p-6 border-t border-gray-200">
          <a href="dashboard.php" class="text-blue-600 hover:text-blue-800 flex items-center text-sm">
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