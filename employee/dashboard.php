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

// Present today
$presentTodayQuery = $conn->prepare("SELECT COUNT(*) as total FROM attendance WHERE user_id=? AND date=CURDATE() AND status='Present'");
$presentTodayQuery->bind_param("i", $user_id);
$presentTodayQuery->execute();
$presentToday = $presentTodayQuery->get_result()->fetch_assoc()['total'] ?? 0;

// Leave balance
$totalAllowedLeave = 20; // yearly allowance
$usedLeaveQuery = $conn->prepare("SELECT COUNT(*) as total FROM leaves WHERE user_id=? AND status='Approved'");
$usedLeaveQuery->bind_param("i", $user_id);
$usedLeaveQuery->execute();
$usedLeave = $usedLeaveQuery->get_result()->fetch_assoc()['total'] ?? 0;
$leaveBalance = max($totalAllowedLeave - $usedLeave, 0);

// This month salary (fixed + show month)
// Latest paid salary (fetch most recent regardless of month)
$salaryQuery = $conn->prepare("
  SELECT month, total, generated_at 
  FROM salaries 
  WHERE user_id=? 
  ORDER BY generated_at DESC 
  LIMIT 1
");
$salaryQuery->bind_param("i", $user_id);
$salaryQuery->execute();
$salaryData = $salaryQuery->get_result()->fetch_assoc();

$thisMonthSalary = $salaryData['total'] ?? 0;
$salaryDate = isset($salaryData['generated_at']) ? date("d M, Y", strtotime($salaryData['generated_at'])) : "Not Generated";
$salaryMonthLabel = isset($salaryData['month']) ? date("F Y", strtotime($salaryData['month'])) : "N/A";

// Label logic — determine if it's current, advance, or past
if (isset($salaryData['month'])) {
  $salaryMonthTime = strtotime($salaryData['month']);
  $currentMonthTime = strtotime(date('Y-m-01'));

  if (date('Ym', $salaryMonthTime) == date('Ym', $currentMonthTime)) {
    $salaryStatusLabel = "This Month’s Salary";
  } elseif ($salaryMonthTime > $currentMonthTime) {
    $salaryStatusLabel = "Advance Salary";
  } else {
    $salaryStatusLabel = "Previous Month Salary";
  }
} else {
  $salaryStatusLabel = "Not Generated";
}

// Recent attendance
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
        <h1 class="text-lg font-semibold text-gray-700">Employee Dashboard</h1>
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
        <!-- Present Today -->
        <div class="bg-white rounded-xl shadow-sm p-5 flex items-center justify-between border border-gray-200">
          <div>
            <h3 class="text-gray-500 text-sm">Present Today</h3>
            <p class="text-2xl font-bold"><?= $presentToday ?></p>
          </div>
          <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar w-6 h-6 text-green-600">
              <path d="M8 2v4"></path>
              <path d="M16 2v4"></path>
              <rect width="18" height="18" x="3" y="4" rx="2"></rect>
              <path d="M3 10h18"></path>
            </svg>
          </div>
        </div>

        <!-- Leave Balance -->
        <div class="bg-white rounded-xl shadow-sm p-5 flex items-center justify-between border border-gray-200">
          <div>
            <h3 class="text-gray-500 text-sm">Leave Balance</h3>
            <p class="text-2xl font-bold"><?= $leaveBalance ?></p>
          </div>
          <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor"
              stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              class="lucide lucide-file-text w-6 h-6 text-blue-600">
              <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
              <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
              <path d="M10 9H8"></path>
              <path d="M16 13H8"></path>
              <path d="M16 17H8"></path>
            </svg>
          </div>
        </div>

        <!-- This Month Salary -->
        <div class="bg-white rounded-xl shadow-sm p-5 flex items-center justify-between border border-gray-200">
          <div>
            <h3 class="text-gray-500 text-sm"><?= $salaryStatusLabel ?></h3>
            <p class="text-2xl font-bold">₹<?= number_format($thisMonthSalary, 2) ?></p>
            <p class="text-xs text-gray-500 mt-1">Paid for <?= $salaryMonthLabel ?></p>
            <p class="text-xs text-gray-500">Paid on <?= $salaryDate ?></p>
          </div>
          <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor"
              stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              class="lucide lucide-dollar-sign w-6 h-6 text-purple-600">
              <line x1="12" x2="12" y1="2" y2="22"></line>
              <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>
          </div>
        </div>

        <!-- Performance -->
        <div class="bg-white rounded-xl shadow-sm p-5 flex items-center justify-between border border-gray-200">
          <div>
            <h3 class="text-gray-500 text-sm">Performance Rating</h3>
            <p class="text-2xl font-bold">
              <?php
              // Fetch average or latest performance rating
              $perfQuery = $conn->prepare("SELECT rating, review_date FROM performance WHERE user_id=? ORDER BY review_date DESC LIMIT 1");
              $perfQuery->bind_param("i", $user_id);
              $perfQuery->execute();
              $perfResult = $perfQuery->get_result()->fetch_assoc();

              if ($perfResult) {
                $rating = round($perfResult['rating'], 1);
                $reviewDate = date("d M Y", strtotime($perfResult['review_date']));
                echo $rating . " ⭐";
              } else {
                echo "No Rating Yet";
              }
              ?>
            </p>
            <p class="text-xs text-gray-500 mt-1">
              <?php if (!empty($reviewDate)) echo "Last Review: $reviewDate"; ?>
            </p>
          </div>
          <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
            <i class="fa-solid fa-chart-line text-green-600 text-2xl"></i>
          </div>
        </div>

      </div>

      <!-- Recent Attendance -->
      <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-bold mb-4">Recent Attendance</h3>
        <ul class="divide-y">
          <?php while ($row = $recentData->fetch_assoc()): ?>
            <li class="flex justify-between py-2">
              <span><?= date("M d, Y", strtotime($row['date'])) ?></span>
              <span
                class="px-2 py-1 rounded text-xs <?= $row['status'] == 'Present' ? 'bg-green-100 text-green-700' : ($row['status'] == 'Leave' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') ?>">
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