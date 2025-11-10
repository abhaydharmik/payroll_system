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

$pageTitle = "Dashboard";

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
    <?php include_once '../includes/header.php'; ?>


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
            <p class="text-2xl flex items-center gap-1 font-bold">
              <?php
              // Fetch average or latest performance rating
              $perfQuery = $conn->prepare("SELECT rating, review_date FROM performance WHERE user_id=? ORDER BY review_date DESC LIMIT 1");
              $perfQuery->bind_param("i", $user_id);
              $perfQuery->execute();
              $perfResult = $perfQuery->get_result()->fetch_assoc();

              if ($perfResult) {
                $rating = round($perfResult['rating'], 1);
                $reviewDate = date("d M Y", strtotime($perfResult['review_date']));
                echo $rating .
                  '
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="yellow" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star-icon text-yellow-300 lucide-star">
                <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"/>
                </svg>';
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
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>

          <div class="grid grid-cols-2 gap-4">

            <!-- Mark Attendance -->
            <a href="attendance.php" class="p-4 border border-gray-200 rounded-xl hover:bg-gray-50 transition-all text-left block">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path d="M8 2v4" />
                <path d="M16 2v4" />
                <rect width="18" height="18" x="3" y="4" rx="2" />
                <path d="M3 10h18" />
              </svg>
              <p class="font-medium text-gray-900">Mark Attendance</p>
              <p class="text-sm text-gray-600">Clock In / Out</p>
            </a>

            <!-- Apply Leave -->
            <a href="leaves.php" class="p-4 border border-gray-200 rounded-xl hover:bg-gray-50 transition-all text-left block">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-green-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z" />
                <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                <path d="M10 9H8" />
                <path d="M16 13H8" />
                <path d="M16 17H8" />
              </svg>
              <p class="font-medium text-gray-900">Apply Leave</p>
              <p class="text-sm text-gray-600">Request Time Off</p>
            </a>

            <!-- Salary Slip -->
            <a href="salary.php" class="p-4 border border-gray-200 rounded-xl hover:bg-gray-50 transition-all text-left block">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-purple-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <line x1="12" x2="12" y1="2" y2="22" />
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
              </svg>
              <p class="font-medium text-gray-900">Salary Slip</p>
              <p class="text-sm text-gray-600">Download Payslip</p>
            </a>

            <!-- Update Profile -->
            <a href="profile.php" class="p-4 border border-gray-200 rounded-xl hover:bg-gray-50 transition-all text-left block">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-yellow-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                <circle cx="12" cy="7" r="4" />
              </svg>
              <p class="font-medium text-gray-900">Update Profile</p>
              <p class="text-sm text-gray-600">Edit Personal Details</p>
            </a>

          </div>
        </div>

        <!-- Recent Attendance -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Attendance</h3>

          <div class="space-y-3">
            <?php while ($row = $recentData->fetch_assoc()): ?>
              <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div>
                  <p class="font-medium text-gray-900"><?= date("M d, Y", strtotime($row['date'])) ?></p>
                  <p class="text-sm text-gray-600">
                    <?= (!empty($row['clock_in']) && !empty($row['clock_out']))
                      ? date("h:i A", strtotime($row['clock_in'])) . " - " . date("h:i A", strtotime($row['clock_out']))
                      : "No time recorded" ?>
                  </p>
                </div>

                <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium
            <?= $row['status'] == 'Present' ? 'bg-green-100 text-green-800' : ($row['status'] == 'Leave' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                  <?= $row['status'] ?>
                </span>
              </div>
            <?php endwhile; ?>

            <?php if ($recentData->num_rows == 0): ?>
              <p class="text-gray-500 text-sm text-center py-4">No attendance records found.</p>
            <?php endif; ?>
          </div>
        </div>

      </div>

    </main>
  </div>

  <script src="../assets/js/script.js"></script>
</body>

</html>