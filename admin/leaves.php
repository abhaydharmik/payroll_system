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
        // Update leave status
        $stmt = $conn->prepare("UPDATE leaves SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();

        // Fetch user name for activity log
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
            // Insert activity record
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

$pageTitle = "Leaves";
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Leave Requests | Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="bg-gray-100">

  <?php include_once '../includes/sidebar.php'; ?>

  <div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden z-30 md:hidden"></div>

  <div class="flex-1 flex flex-col min-h-screen md:ml-64">

    <?php include_once '../includes/header.php'; ?>

    <main class="flex-1 pt-20 px-4 md:px-8 pb-8">

      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 space-y-3 sm:space-y-0">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Leave Requests</h2>
          <p class="text-gray-600">Manage employee leave requests</p>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200">

        <div class="p-6 border-b flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-3 sm:space-y-0">
          <h3 class="text-lg font-semibold text-gray-900">All Leave Requests</h3>

          <a href="leave_report_admin.php" target="_blank"
            class="bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition-all flex items-center space-x-2">
            <i class="fa-solid fa-file-pdf"></i>
            <span>Download PDF</span>
          </a>
        </div>

        <!-- DESKTOP TABLE VIEW -->
        <div class="hidden md:block overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Applied</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status / Action</th>
              </tr>
            </thead>

            <tbody class="bg-white divide-y text-sm">
              <?php if ($result->num_rows > 0): $i = 1; ?>
                <?php while ($row = $result->fetch_assoc()): ?>

                  <?php
                  $badge =
                    ($row['status'] == "Pending") ? "bg-yellow-100 text-yellow-800" :
                    (($row['status'] == "Approved") ? "bg-green-100 text-green-800" : "bg-red-100 text-red-800");
                  ?>

                  <tr class="<?= $i % 2 == 0 ? 'bg-gray-50' : 'bg-white' ?>">
                    <td class="px-6 py-4"><?= $i++ ?></td>
                    <td class="px-6 py-4"><?= htmlspecialchars($row['name']) ?></td>
                    <td class="px-6 py-4"><?= htmlspecialchars($row['leave_type']) ?></td>
                    <td class="px-6 py-4">
                      <?= date("d M Y", strtotime($row['start_date'])) ?> →
                      <?= date("d M Y", strtotime($row['end_date'])) ?>
                    </td>
                    <td class="px-6 py-4 max-w-xs truncate"><?= htmlspecialchars($row['reason']) ?></td>
                    <td class="px-6 py-4"><?= date("d M Y, h:i A", strtotime($row['applied_at'])) ?></td>
                    <td class="px-6 py-4">
                      <div class="flex items-center space-x-2">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $badge ?>">
                          <?= $row['status'] ?>
                        </span>

                        <?php if ($row['status'] === 'Pending'): ?>
                          <form method="post" class="inline-block">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="action" value="approve">
                            <button class="bg-green-600 text-white text-xs px-3 py-1 rounded-md hover:bg-green-700">
                              Approve
                            </button>
                          </form>

                          <form method="post" class="inline-block">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="action" value="reject">
                            <button class="bg-red-600 text-white text-xs px-3 py-1 rounded-md hover:bg-red-700">
                              Reject
                            </button>
                          </form>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>

                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="7" class="text-center py-4 text-gray-500">No leave requests found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- MOBILE VIEW -->
        <div class="md:hidden p-4 space-y-4">

          <?php if ($result->num_rows > 0): ?>
            <?php $result->data_seek(0);
            while ($row = $result->fetch_assoc()): ?>

              <?php
                $badge =
                  ($row['status'] == "Pending") ? "bg-yellow-100 text-yellow-800" :
                  (($row['status'] == "Approved") ? "bg-green-100 text-green-800" : "bg-red-100 text-red-800");
              ?>

              <div class="bg-white p-4 rounded-xl shadow border border-gray-200 space-y-3">

                <div class="flex justify-between items-center">
                  <div>
                    <p class="font-semibold text-gray-900 text-base">
                      <?= htmlspecialchars($row['name']) ?>
                    </p>
                    <p class="text-sm text-gray-500"><?= $row['leave_type'] ?></p>
                  </div>
                  <span class="px-3 py-1 text-xs font-semibold rounded-full <?= $badge ?>">
                    <?= $row['status'] ?>
                  </span>
                </div>

                <div class="text-sm border-l-4 border-blue-500 pl-3">
                  <span class="font-semibold">Duration:</span><br>
                  <?= date("d M Y", strtotime($row['start_date'])) ?> →
                  <?= date("d M Y", strtotime($row['end_date'])) ?>
                </div>

                <div class="text-sm">
                  <span class="font-semibold">Reason:</span><br>
                  <?= nl2br(htmlspecialchars($row['reason'])) ?>
                </div>

                <div class="text-sm">
                  <span class="font-semibold">Applied:</span><br>
                  <?= date("d M Y, h:i A", strtotime($row['applied_at'])) ?>
                </div>

                <?php if ($row['status'] === 'Pending'): ?>
                <div class="flex gap-2">
                  <form method="post" class="flex-1">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <input type="hidden" name="action" value="approve">
                    <button class="w-full bg-green-600 text-white py-2 rounded-lg text-sm hover:bg-green-700">
                      Approve
                    </button>
                  </form>

                  <form method="post" class="flex-1">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <input type="hidden" name="action" value="reject">
                    <button class="w-full bg-red-600 text-white py-2 rounded-lg text-sm hover:bg-red-700">
                      Reject
                    </button>
                  </form>
                </div>
                <?php endif; ?>

              </div>

            <?php endwhile; ?>
          <?php else: ?>
            <p class="text-center text-gray-500 py-5">No leave requests found.</p>
          <?php endif; ?>

        </div>

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
