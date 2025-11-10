<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'employee') {
  header('Location: ../index.php');
  exit;
}

$emp = $_SESSION['user'];
$emp_id = $emp['id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $leave_type = $_POST['leave_type'];
  $duration = $_POST['duration'];
  $start_date = $_POST['start_date'];
  $end_date = $_POST['end_date'];
  $reason = trim($_POST['reason']);

  $stmt = $conn->prepare("INSERT INTO leaves (user_id, leave_type, duration, start_date, end_date, reason) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("isssss", $emp_id, $leave_type, $duration, $start_date, $end_date, $reason);
  if ($stmt->execute()) {
    $message = "<span class='text-green-600 font-medium'>✅ Leave request submitted successfully!</span>";
  } else {
    $message = "<span class='text-red-600 font-medium'>❌ Error: " . $stmt->error . "</span>";
  }
}

$stmt = $conn->prepare("SELECT * FROM leaves WHERE user_id=? ORDER BY applied_at DESC");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$leaves = $stmt->get_result();

$pageTitle = "Leaves";


?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Leave Requests</title>
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

  <!-- Sidebar -->
  <?php include '../includes/sidebaremp.php'; ?>

  <!-- Overlay for Mobile -->
  <div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden z-30 md:hidden"></div>

  <!-- Main Content -->
  <div class="flex-1 flex flex-col min-h-screen md:ml-64">
    <!-- Navbar -->
    <?php include_once '../includes/header.php'; ?>


    <!-- Page Content -->
    <main class="flex-1 pt-20 px-4 md:px-8 pb-8">
      <!-- Page Header -->
      <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Leave Management</h2>
        <p class="text-gray-500 text-sm">Submit and track your leave applications</p>
      </div>

      <!-- Leave Application Form -->
      <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h3 class="text-lg font-bold mb-4 flex items-center">
          <!-- <i class="fa-solid fa-file-circle-plus text-blue-600 mr-2"></i> -->
          Apply for Leave
        </h3>

        <form method="POST" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Leave Type -->
            <div class="w-full">
              <label class="block text-sm font-medium text-gray-700 mb-2">Leave Type</label>
              <select name="leave_type" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="Vacation">Vacation</option>
                <option value="Sick Leave">Sick Leave</option>
                <option value="Casual Leave">Casual Leave</option>
                <option value="Emergency Leave">Emergency Leave</option>
              </select>
            </div>

            <!-- Duration -->
            <div class="w-full">
              <label class="block text-sm font-medium text-gray-700 mb-2">Duration</label>
              <select name="duration" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="Full Day">Full Day</option>
                <option value="Half Day">Half Day</option>
              </select>
            </div>

            <!-- Start Date -->
            <div class="w-full">
              <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
              <input type="date" name="start_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- End Date -->
            <div class="w-full">
              <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
              <input type="date" name="end_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
          </div>

          <!-- Reason -->
          <div class="w-full">
            <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
            <textarea name="reason" required placeholder="Please provide a reason for your leave request..." class="w-full border border-gray-300 rounded-lg px-3 py-2 h-28 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"></textarea>
          </div>

          <!-- Submit Button -->
          <div class="w-full">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition-colors duration-200">
              <i class="fa-solid fa-paper-plane mr-2"></i>Submit Request
            </button>
          </div>
        </form>

        <!-- Message -->
        <?php if (!empty($message)): ?>
          <div class="mt-4 p-3 rounded-lg bg-green-50 border border-green-200">
            <?php echo $message; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200 mt-6">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">My Leave Requests</h3>
        </div>

        <!-- Desktop View -->
        <div class="hidden md:block overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Range</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <?php while ($row = $leaves->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50">
                  <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($row['leave_type']) ?></td>

                  <td class="px-6 py-4 text-sm text-gray-900">
                    <?= htmlspecialchars($row['duration']) ?>
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                    <?= date("M d, Y", strtotime($row['start_date'])) ?> - <?= date("M d, Y", strtotime($row['end_date'])) ?>
                  </td>

                  <td class="px-6 py-4 text-sm text-gray-700 max-w-xs truncate">
                    <?= htmlspecialchars($row['reason']) ?>
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap">
                    <?php if ($row['status'] == 'Approved'): ?>
                      <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                    <?php elseif ($row['status'] == 'Rejected'): ?>
                      <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                    <?php else: ?>
                      <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                    <?php endif; ?>
                  </td>

                  <td class="px-6 py-4 text-sm">
                    <button class="text-blue-600 hover:text-blue-900 p-1 hover:bg-blue-50 rounded" title="View">
                      <i class="fa-solid fa-eye"></i>
                    </button>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden p-4 space-y-4">
          <?php $leaves->data_seek(0);
          while ($row = $leaves->fetch_assoc()): ?>
            <div class="border border-gray-200 rounded-lg p-4 shadow-sm">
              <div class="flex justify-between mb-2">
                <div>
                  <p class="font-semibold text-gray-800"><?= htmlspecialchars($row['leave_type']) ?></p>
                  <p class="text-sm text-gray-500"><?= htmlspecialchars($row['duration']) ?></p>
                </div>

                <?php if ($row['status'] == 'Approved'): ?>
                  <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded">Approved</span>
                <?php elseif ($row['status'] == 'Rejected'): ?>
                  <span class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded">Rejected</span>
                <?php else: ?>
                  <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded">Pending</span>
                <?php endif; ?>
              </div>

              <p class="text-sm text-gray-700">
                <i class="fa-regular fa-calendar text-gray-400 mr-1"></i>
                <?= date("M d, Y", strtotime($row['start_date'])) ?> - <?= date("M d, Y", strtotime($row['end_date'])) ?>
              </p>

              <p class="text-sm text-gray-600 mt-2">
                <i class="fa-regular fa-comment text-gray-400 mr-1"></i>
                <?= htmlspecialchars($row['reason']) ?>
              </p>

              <div class="mt-3 pt-3 border-t text-right">
                <button class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                  <i class="fa-solid fa-eye mr-1"></i> View
                </button>
              </div>
            </div>
          <?php endwhile; ?>
        </div>

        <?php if ($leaves->num_rows == 0): ?>
          <p class="text-gray-500 text-center py-6">No leave requests found.</p>
        <?php endif; ?>
      </div>

    </main>
  </div>

  <script src="../assets/js/script.js"></script>
</body>

</html>