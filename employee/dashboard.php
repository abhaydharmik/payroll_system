<?php
session_start();
require '../config.php';

// Only employees can access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'employee') {
    header('Location: ../index.php');
    exit;
}

$emp = $_SESSION['user'];
$user_id = $emp['id'];

// ---------- Stats ----------
$presentTodayQuery = $conn->prepare("SELECT COUNT(*) as total FROM attendance WHERE user_id=? AND date=CURDATE() AND status='Present'");
$presentTodayQuery->bind_param("i", $user_id);
$presentTodayQuery->execute();
$presentToday = $presentTodayQuery->get_result()->fetch_assoc()['total'] ?? 0;

$totalAllowedLeave = 20; // yearly allowance
$usedLeaveQuery = $conn->prepare("SELECT COUNT(*) as total FROM leaves WHERE user_id=? AND status='Approved'");
$usedLeaveQuery->bind_param("i", $user_id);
$usedLeaveQuery->execute();
$usedLeave = $usedLeaveQuery->get_result()->fetch_assoc()['total'] ?? 0;
$leaveBalance = max($totalAllowedLeave - $usedLeave, 0);

$currentMonth = date('F Y');
$salaryQuery = $conn->prepare("SELECT total, generated_at FROM salaries WHERE user_id=? AND month=?");
$salaryQuery->bind_param("is", $user_id, $currentMonth);
$salaryQuery->execute();
$salaryData = $salaryQuery->get_result()->fetch_assoc();
$thisMonthSalary = $salaryData['total'] ?? 0;
$salaryDate = isset($salaryData['generated_at']) ? date("d M, Y", strtotime($salaryData['generated_at'])) : "Not Generated";

// Recent Attendance
$recentAttendance = $conn->prepare("SELECT date, status FROM attendance WHERE user_id=? ORDER BY date DESC LIMIT 5");
$recentAttendance->bind_param("i", $user_id);
$recentAttendance->execute();
$recentData = $recentAttendance->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Employee Dashboard</title>
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
    <header class="fixed top-0 left-0 right-0 md:left-64 bg-white shadow flex justify-between items-center px-4 py-3 z-40">
      <div class="flex items-center space-x-3">
        <!-- Mobile menu button -->
        <button id="sidebarToggle" class="md:hidden text-gray-700 focus:outline-none">
          <i class="fa-solid fa-bars text-xl"></i>
        </button>
        <h1 class="text-lg md:text-xs font-semibold text-gray-700">Employee Dashboard</h1>
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

    <!-- Page Content -->
    <main class="flex-1 pt-20 px-4 md:px-8 pb-8">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-4 mb-8">
        <div class="bg-white rounded-lg shadow p-5 flex items-center justify-between border-l-4 border-blue-600">
          <div>
            <h3 class="text-gray-500 text-sm">Present Today</h3>
            <p class="text-2xl font-bold"><?= $presentToday ?></p>
          </div>
          <i class="fa-solid fa-calendar-check text-blue-600 text-3xl"></i>
        </div>

        <div class="bg-white rounded-lg shadow p-5 flex items-center justify-between border-l-4 border-yellow-600">
          <div>
            <h3 class="text-gray-500 text-sm">Leave Balance</h3>
            <p class="text-2xl font-bold"><?= $leaveBalance ?></p>
          </div>
          <i class="fa-solid fa-file-signature text-yellow-600 text-3xl"></i>
        </div>

        <div class="bg-white rounded-lg shadow p-5 flex items-center justify-between border-l-4 border-purple-600">
          <div>
            <h3 class="text-gray-500 text-sm">This Month Salary</h3>
            <p class="text-2xl font-bold">₹<?= number_format($thisMonthSalary, 2) ?></p>
            <p class="text-xs text-gray-500 mt-1">Paid on <?= $salaryDate ?></p>
          </div>
          <i class="fa-solid fa-sack-dollar text-purple-600 text-3xl"></i>
        </div>

        <div class="bg-white rounded-lg shadow p-5 flex items-center justify-between border-l-4 border-green-600">
          <div>
            <h3 class="text-gray-500 text-sm">Performance</h3>
            <p class="text-2xl font-bold">
              <?php
              $totalDaysQuery = $conn->prepare("SELECT COUNT(*) as total FROM attendance WHERE user_id=? AND MONTH(date)=MONTH(CURDATE())");
              $totalDaysQuery->bind_param("i", $user_id);
              $totalDaysQuery->execute();
              $totalDays = $totalDaysQuery->get_result()->fetch_assoc()['total'] ?? 1;
              $presentDaysQuery = $conn->prepare("SELECT COUNT(*) as total FROM attendance WHERE user_id=? AND status='Present' AND MONTH(date)=MONTH(CURDATE())");
              $presentDaysQuery->bind_param("i", $user_id);
              $presentDaysQuery->execute();
              $presentDays = $presentDaysQuery->get_result()->fetch_assoc()['total'] ?? 0;
              $performanceScore = ($totalDays > 0) ? ($presentDays / $totalDays) * 5 : 0;
              $performanceScore = round($performanceScore, 1);
              echo $performanceScore;
              ?>
            </p>
          </div>
          <i class="fa-solid fa-chart-line text-green-600 text-3xl"></i>
        </div>
      </div>

      <!-- Recent Attendance -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-bold mb-4">Recent Attendance</h3>
        <ul class="divide-y">
          <?php while ($row = $recentData->fetch_assoc()): ?>
            <li class="flex justify-between py-2">
              <span><?= date("M d, Y", strtotime($row['date'])) ?></span>
              <span class="px-2 py-1 rounded text-xs <?= $row['status']=='Present' ? 'bg-green-100 text-green-700' : ($row['status']=='Leave' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') ?>">
                <?= $row['status'] ?>
              </span>
            </li>
          <?php endwhile; ?>
          <?php if ($recentData->num_rows == 0): ?>
            <p class="text-gray-500 text-sm">No attendance records found.</p>
          <?php endif; ?>
        </ul>
      </div>
    </main>
  </div>

  <script src="../assets/js/script.js"></script>
</body>

</html>
