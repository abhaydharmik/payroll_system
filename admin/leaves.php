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
    // ✅ Update leave status
    $stmt = $conn->prepare("UPDATE leaves SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();

    // ✅ Fetch user name for activity log
    $userQuery = $conn->prepare("
      SELECT u.id AS user_id, u.name AS user_name 
      FROM leaves l 
      JOIN users u ON l.user_id = u.id 
      WHERE l.id = ?
    ");
    $userQuery->bind_param("i", $id);
    $userQuery->execute();
    $userData = $userQuery->get_result()->fetch_assoc();

    if ($userData) {
      // ✅ Insert activity record
      $desc = "Leave Request {$status}: " . $userData['user_name'];
      $activity = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
      $activity->bind_param("is", $userData['user_id'], $desc);
      $activity->execute();
    }
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
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0 mb-4">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Leave Requests</h2>
          <p class="text-gray-600">Manage employee leave requests</p>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">All Leave Requests</h3>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applied</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 text-sm">
              <?php if ($result && $result->num_rows > 0): ?>
                <?php
                $i = 1;
                while ($row = $result->fetch_assoc()):
                ?>
                  <tr class="<?= $i % 2 == 0 ? 'bg-gray-50' : 'bg-white' ?>">
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900"><?= $i++ ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-900"><?= htmlspecialchars($row['name']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-900"><?= htmlspecialchars($row['leave_type']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                      <?= date("d M Y", strtotime($row['start_date'])) ?> to <?= date("d M Y", strtotime($row['end_date'])) ?>
                    </td>
                    <td class="px-6 py-4 text-gray-900 max-w-xs truncate"><?= htmlspecialchars($row['reason']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                      <?= date("d M Y, h:i A", strtotime($row['applied_at'])) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <?php if ($row['status'] === 'Pending'): ?>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                      <?php elseif ($row['status'] === 'Approved'): ?>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                      <?php else: ?>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                      <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div class="flex space-x-2">
                        <?php if ($row['status'] === 'Pending'): ?>
                          <form method="POST" class="flex space-x-2">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">

                            <!-- Approve -->
                            <button name="action" value="approve"
                              class="text-green-600 hover:text-green-900 p-1 hover:bg-green-50 rounded"
                              title="Approve">
                              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                <path d="m9 11 3 3L22 4" />
                              </svg>
                            </button>

                            <!-- Reject -->
                            <button name="action" value="reject"
                              class="text-red-600 hover:text-red-900 p-1 hover:bg-red-50 rounded"
                              title="Reject">
                              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <circle cx="12" cy="12" r="10" />
                                <path d="m15 9-6 6" />
                                <path d="m9 9 6 6" />
                              </svg>
                            </button>
                          </form>
                        <?php endif; ?>

                        <!-- View button (always visible) -->
                        <a href="view_leave.php?id=<?= $row['id'] ?>"
                          class="text-blue-600 hover:text-blue-900 p-1 hover:bg-blue-50 rounded"
                          title="View Details">
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                            <circle cx="12" cy="12" r="3" />
                          </svg>
                        </a>
                      </div>
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

        <div class="p-6 border-t border-gray-200">
          <a href="dashboard.php" class="text-blue-600 hover:text-blue-800 flex items-center text-sm md:text-base">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Dashboard
          </a>
        </div>
      </div>

    </main>
  </div>

  <script src="../assets/js/script.js"></script>
</body>

</html>