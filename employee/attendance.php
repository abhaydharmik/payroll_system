<?php
session_start();
require '../config.php';
date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'employee') {
  header('Location: ../index.php');
  exit;
}

$emp = $_SESSION['user'];
$user_id = $emp['id'];
$today = date('Y-m-d');
$message = "";

// Check if today's record exists
$stmt = $conn->prepare("SELECT * FROM attendance WHERE user_id=? AND date=?");
$stmt->bind_param("is", $user_id, $today);
$stmt->execute();
$todayRecord = $stmt->get_result()->fetch_assoc();

// 🕐 Handle Clock In
if (isset($_POST['clock_in'])) {
  if ($todayRecord) {
    $message = "<span class='text-yellow-600'>You already clocked in today.</span>";
  } else {
    $clockIn = date('Y-m-d H:i:s'); // ✅ Full date-time
    $status = "Present";
    $insert = $conn->prepare("INSERT INTO attendance (user_id, date, clock_in, status) VALUES (?, ?, ?, ?)");
    $insert->bind_param("isss", $user_id, $today, $clockIn, $status);
    $insert->execute();
    $message = "<span class='text-green-600'>Clocked in successfully at " . date('h:i A', strtotime($clockIn)) . "</span>";
  }
}

// 🕒 Handle Clock Out
if (isset($_POST['clock_out'])) {
  // refresh record
  $stmt = $conn->prepare("SELECT * FROM attendance WHERE user_id=? AND date=?");
  $stmt->bind_param("is", $user_id, $today);
  $stmt->execute();
  $todayRecord = $stmt->get_result()->fetch_assoc();

  if (!$todayRecord || !$todayRecord['clock_in']) {
    $message = "<span class='text-red-600'>You must clock in first.</span>";
  } elseif ($todayRecord['clock_out']) {
    $message = "<span class='text-yellow-600'>You already clocked out today.</span>";
  } else {
    $clockOut = date('Y-m-d H:i:s'); // ✅ Full date-time
    $clockInTime = strtotime($todayRecord['clock_in']);
    $clockOutTime = strtotime($clockOut);
    $workedSeconds = $clockOutTime - $clockInTime;
    $hours = floor($workedSeconds / 3600);
    $minutes = floor(($workedSeconds % 3600) / 60);
    $hoursWorked = sprintf("%02dh %02dm", $hours, $minutes);

    $update = $conn->prepare("UPDATE attendance SET clock_out=?, hours_worked=? WHERE user_id=? AND date=?");
    $update->bind_param("ssis", $clockOut, $hoursWorked, $user_id, $today);
    $update->execute();

    $message = "<span class='text-green-600'>Clocked out successfully at " . date('h:i A', strtotime($clockOut)) . " (Worked $hoursWorked)</span>";
  }
}

// Fetch attendance history
$stmt = $conn->prepare("SELECT * FROM attendance WHERE user_id=? ORDER BY date DESC LIMIT 10");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$attendance = $stmt->get_result();

$pageTitle = "My Attendance";


?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Attendance</title>
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

    <main class="flex-1 pt-20 px-4 md:px-8 pb-8 transition-all duration-300">

      <?php if ($message): ?>
        <div class="mb-4 bg-white p-4 rounded-lg shadow text-center text-sm font-medium">
          <?= $message ?>
        </div>
      <?php endif; ?>

      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4 px-2 sm:px-0">
        <!-- Left: Title -->
        <div class="text-center sm:text-left w-full sm:w-auto">
          <h2 class="text-xl sm:text-2xl font-bold text-gray-900">My Attendance</h2>
          <p class="text-gray-600 text-sm sm:text-base">Track your attendance and working hours</p>
        </div>

        <!-- Right: Clock In/Out Buttons -->
        <form method="POST" class="flex flex-col sm:flex-row w-full sm:w-auto gap-3 sm:justify-end">
          <button type="submit" name="clock_in"
            class="bg-green-600 text-white px-4 py-2 rounded-xl hover:bg-green-700 transition-all flex items-center justify-center gap-2 w-full sm:w-auto">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              class="lucide lucide-clock">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            <span>Clock In</span>
          </button>

          <button type="submit" name="clock_out"
            class="bg-red-600 text-white px-4 py-2 rounded-xl hover:bg-red-700 transition-all flex items-center justify-center gap-2 w-full sm:w-auto">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              class="lucide lucide-clock">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            <span>Clock Out</span>
          </button>
        </form>
      </div>


      <!-- Buttons -->
      <!-- <div class="p-5 flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <h2 class="text-lg sm:text-xl font-semibold text-gray-700">Mark Attendance</h2>
        <form method="POST" class="flex flex-col sm:flex-row gap-3">
          <button type="submit" name="clock_in" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg w-full sm:w-auto">
            <i class="fa fa-sign-in-alt mr-1"></i> Clock In
          </button>
          <button type="submit" name="clock_out" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg w-full sm:w-auto">
            <i class="fa fa-sign-out-alt mr-1"></i> Clock Out
          </button>
        </form>
      </div> -->

      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Today's Status</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

          <!-- Clock In -->
          <div class="text-center p-4 bg-green-50 rounded-xl">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-green-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <circle cx="12" cy="12" r="10" />
              <polyline points="12 6 12 12 16 14" />
            </svg>
            <p class="text-sm text-gray-600">Clock In</p>
            <p class="text-xl font-bold text-gray-900">
              <?= $todayRecord['clock_in'] ?? '--:--' ?>
            </p>
          </div>

          <!-- Hours Worked -->
          <div class="text-center p-4 bg-blue-50 rounded-xl">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-blue-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path d="M3 3v18h18" />
              <path d="M18 17V9" />
              <path d="M13 17V5" />
              <path d="M8 17v-3" />
            </svg>
            <p class="text-sm text-gray-600">Hours Worked</p>
            <p class="text-xl font-bold text-gray-900">
              <?= $todayRecord['hours_worked'] ?? '--:--' ?>
            </p>
          </div>

          <!-- Status -->
          <div class="text-center p-4 bg-purple-50 rounded-xl">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-purple-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path d="M8 2v4" />
              <path d="M16 2v4" />
              <rect width="18" height="18" x="3" y="4" rx="2" />
              <path d="M3 10h18" />
            </svg>
            <p class="text-sm text-gray-600">Status</p>
            <p class="text-xl font-bold 
        <?= ($todayRecord['status'] ?? '') == 'Present' ? 'text-green-600' : 'text-gray-500' ?>">
              <?= $todayRecord['status'] ?? 'Not Marked' ?>
            </p>
          </div>

        </div>
      </div>

      <!-- Attendance History -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
            <h3 class="text-lg font-semibold text-gray-900">Attendance History</h3>

            <div class="flex space-x-2">
              <form method="get">
                <select name="filter" class="px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                  <option value="">This Month</option>
                  <option value="week">This Week</option>
                  <option value="last_month">Last Month</option>
                </select>
              </form>

              <a href="./attendance_report.php" target="_blank"
                class="bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition-all flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor"
                  stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                  class="lucide lucide-download w-4 h-4">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                  <polyline points="7 10 12 15 17 10"></polyline>
                  <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span>Export</span>
              </a>
            </div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clock In</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clock Out</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
              <?php while ($row = $attendance->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <?= date("M d, Y", strtotime($row['date'])) ?>
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <?= $row['clock_in'] ?: '--:--' ?>
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <?= $row['clock_out'] ?: '--:--' ?>
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    <?= $row['hours_worked'] ?: '--' ?>
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap">
                    <?php if ($row['status'] == "Present"): ?>
                      <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Present</span>
                    <?php elseif ($row['status'] == "Leave"): ?>
                      <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Leave</span>
                    <?php else: ?>
                      <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Absent</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>


    </main>
  </div>

  <!-- JS for Sidebar Toggle -->
  <script src="../assets/js/script.js"></script>

</body>

</html>