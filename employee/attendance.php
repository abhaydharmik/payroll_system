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
    <header class="fixed top-0 left-0 right-0 md:left-64 bg-white shadow flex justify-between items-center px-4 py-3 z-40">
      <div class="flex items-center space-x-3">
        <button id="sidebarToggle" class="md:hidden text-gray-700 focus:outline-none">
          <i class="fa-solid fa-bars text-xl"></i>
        </button>
        <h1 class="text-lg font-semibold text-gray-700">My Attendance</h1>
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

    <main class="flex-1 pt-20 px-4 md:px-8 pb-8 transition-all duration-300">

      <?php if ($message): ?>
        <div class="mb-4 bg-white p-4 rounded-lg shadow text-center text-sm font-medium">
          <?= $message ?>
        </div>
      <?php endif; ?>

      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center  mb-6 space-y-4 sm:space-y-0">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">My Attendance</h2>
          <p class="text-gray-600">Track your attendance and working hours</p>
        </div>
        <div class="flex space-x-3">
          <form method="POST" class="flex flex-col sm:flex-row gap-3">
            <button type="submit" name="clock_in" class="bg-green-600 text-white px-4 py-2 rounded-xl hover:bg-green-700 transition-all flex items-center space-x-2">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock w-4 h-4">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
              </svg>
              <span>Clock In</span>
            </button>
            <button type="submit" name="clock_out" class="bg-red-600 text-white px-4 py-2 rounded-xl hover:bg-red-700 transition-all flex items-center space-x-2">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock w-4 h-4">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
              </svg>
              <span>Clock Out</span>
            </button>
          </form>
        </div>
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

      <!-- Today's Status -->
      <div class="bg-white rounded-lg shadow p-6 mb-6 grid grid-cols-1 sm:grid-cols-3 gap-6 text-center">
        <div class="p-4 border rounded-lg">
          <p class="text-gray-500 mb-1">Clock In</p>
          <p class="text-xl font-semibold text-green-600"><?= $todayRecord['clock_in'] ?? '--:--' ?></p>
        </div>
        <div class="p-4 border rounded-lg">
          <p class="text-gray-500 mb-1">Hours Worked</p>
          <p class="text-xl font-semibold text-blue-600"><?= $todayRecord['hours_worked'] ?? '--:--' ?></p>
        </div>
        <div class="p-4 border rounded-lg">
          <p class="text-gray-500 mb-1">Status</p>
          <p class="text-xl font-semibold <?= ($todayRecord['status'] == 'Present') ? 'text-green-600' : 'text-gray-500' ?>">
            <?= $todayRecord['status'] ?? 'Not Marked' ?>
          </p>
        </div>
      </div>

      <!-- Attendance History -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-4">
          <h3 class="text-base sm:text-lg font-semibold text-gray-700">Attendance History</h3>
          <a href="./attendance_report.php" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm w-full sm:w-auto text-center">
            <i class="fa fa-file-pdf mr-1"></i> Export PDF
          </a>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full border border-gray-200 rounded-lg text-sm">
            <thead class="bg-gray-100 text-gray-700">
              <tr>
                <th class="px-4 py-2 text-left">Date</th>
                <th class="px-4 py-2 text-left">Clock In</th>
                <th class="px-4 py-2 text-left">Clock Out</th>
                <th class="px-4 py-2 text-left">Hours Worked</th>
                <th class="px-4 py-2 text-left">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $attendance->fetch_assoc()): ?>
                <tr class="border-t hover:bg-gray-50">
                  <td class="px-4 py-2"><?= htmlspecialchars($row['date']) ?></td>
                  <td class="px-4 py-2"><?= $row['clock_in'] ?: '--:--' ?></td>
                  <td class="px-4 py-2"><?= $row['clock_out'] ?: '--:--' ?></td>
                  <td class="px-4 py-2"><?= $row['hours_worked'] ?: '--' ?></td>
                  <td class="px-4 py-2">
                    <?php if ($row['status'] == "Present"): ?>
                      <span class="text-green-600 font-semibold">Present</span>
                    <?php elseif ($row['status'] == "Leave"): ?>
                      <span class="text-yellow-600 font-semibold">Leave</span>
                    <?php else: ?>
                      <span class="text-red-600 font-semibold">Absent</span>
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