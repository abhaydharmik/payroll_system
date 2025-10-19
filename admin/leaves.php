<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

$emp = $_SESSION['user'] ?? ['name' => 'Admin'];

// Handle Approve/Reject actions securely via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['action'])) {
  $id = intval($_POST['id']);
  $action = $_POST['action'];
  $status = ($action === 'approve') ? 'Approved' : (($action === 'reject') ? 'Rejected' : null);

  if ($status) {
    $stmt = $conn->prepare("UPDATE leaves SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
  }
  header("Location: leaves.php");
  exit;
}

// Fetch leave requests
$sql = "SELECT l.id, u.name, l.leave_type, l.reason, l.start_date, l.end_date, l.status, l.applied_at 
        FROM leaves l 
        JOIN users u ON l.user_id=u.id 
        ORDER BY l.applied_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Leave Requests | Admin</title>
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
    <!-- Top Navbar -->
    <header class="fixed top-0 left-0 right-0 md:left-64 bg-white shadow flex justify-between items-center px-4 py-3 z-40">
      <div class="flex items-center space-x-3">
        <button id="sidebarToggle" class="md:hidden text-gray-700 focus:outline-none">
          <i class="fa-solid fa-bars text-xl"></i>
        </button>
        <h1 class="text-lg font-semibold text-gray-700">Leave Requests</h1>
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
        <div class="overflow-x-auto shadow-sm rounded-lg border border-gray-200">
          <table class="min-w-full border-collapse table-auto text-sm">
            <thead class="bg-blue-600 text-white text-sm uppercase">
              <tr>
                <th class="px-3 py-2 text-left">No</th>
                <th class="px-3 py-2 text-left">Employee</th>
                <th class="px-3 py-2 text-left hidden sm:table-cell">Type</th>
                <th class="px-3 py-2 text-left hidden sm:table-cell">Reason</th>
                <th class="px-3 py-2 text-left">Duration</th>
                <th class="px-3 py-2 text-left">Applied</th>
                <th class="px-3 py-2 text-left">Status</th>
                <th class="px-3 py-2 text-left">Action</th>
              </tr>
            </thead>
            <tbody class="text-gray-700">
              <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                  <tr class="border-b hover:bg-gray-50">
                    <td class="px-3 py-2 font-medium"><?= $row['id'] ?></td>
                    <td class="px-3 py-2"><?= htmlspecialchars($row['name']) ?></td>
                    <td class="px-3 py-2 hidden sm:table-cell"><?= htmlspecialchars($row['leave_type']) ?></td>
                    <td class="px-3 py-2 hidden sm:table-cell max-w-xs"><?= htmlspecialchars($row['reason']) ?></td>
                    <td class="px-3 py-2 text-xs md:text-sm whitespace-nowrap">
                      <?= htmlspecialchars($row['start_date']) ?> to <?= htmlspecialchars($row['end_date']) ?>
                    </td>
                    <td class="px-3 py-2 text-xs md:text-sm whitespace-nowrap"><?= $row['applied_at'] ?></td>
                    <td class="px-3 py-2 whitespace-nowrap">
                      <?php if ($row['status'] === 'Pending'): ?>
                        <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs font-medium">Pending</span>
                      <?php elseif ($row['status'] === 'Approved'): ?>
                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-medium">Approved</span>
                      <?php else: ?>
                        <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-medium">Rejected</span>
                      <?php endif; ?>
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap flex flex-col sm:flex-row gap-1 sm:gap-2">
                      <?php if ($row['status'] === 'Pending'): ?>
                        <form method="POST" class="flex gap-1 sm:gap-2">
                          <input type="hidden" name="id" value="<?= $row['id'] ?>">
                          <button name="action" value="approve"
                            class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-xs md:text-sm flex items-center justify-center sm:justify-start">
                            ✅ <span class="hidden md:inline ml-1">Approve</span>
                          </button>
                          <button name="action" value="reject"
                            class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs md:text-sm flex items-center justify-center sm:justify-start">
                            ❌ <span class="hidden md:inline ml-1">Reject</span>
                          </button>
                        </form>
                      <?php else: ?>
                        <span class="text-gray-500 italic text-xs md:text-sm text-center">Completed</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" class="text-center text-gray-500 py-4">No leave requests found.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <div class="mt-4 md:mt-6">
          <a href="dashboard.php" class="text-blue-600 hover:text-blue-800 flex items-center text-sm md:text-base">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back to Dashboard
          </a>
        </div>
      </div>
    </main>
  </div>

  <script src="../assets/js/script.js"></script>
</body>
</html>
