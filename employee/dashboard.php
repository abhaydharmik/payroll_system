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
// Get today's attendance record
$stmt = $conn->prepare("
    SELECT * 
    FROM attendance 
    WHERE user_id = ? 
      AND date = CURDATE()
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$todayRecord = $stmt->get_result()->fetch_assoc();


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

// ------------------ RECENT ACTIVITIES ------------------

// Recent Leave Requests
$recentLeavesStmt = $conn->prepare("
  SELECT id, leave_type, start_date, end_date, status, applied_at 
  FROM leaves 
  WHERE user_id=? 
  ORDER BY applied_at DESC 
  LIMIT 5
");
$recentLeavesStmt->bind_param("i", $user_id);
$recentLeavesStmt->execute();
$recentLeaves = $recentLeavesStmt->get_result()->fetch_all(MYSQLI_ASSOC);


// Recent Salaries
$recentSalaryStmt = $conn->prepare("
  SELECT month, total, generated_at 
  FROM salaries 
  WHERE user_id=? 
  ORDER BY generated_at DESC 
  LIMIT 5
");
$recentSalaryStmt->bind_param("i", $user_id);
$recentSalaryStmt->execute();
$recentSalaries = $recentSalaryStmt->get_result()->fetch_all(MYSQLI_ASSOC);


// Recent Profile Updates
$recentProfileStmt = $conn->prepare("
  SELECT created_at 
  FROM users 
  WHERE id=? 
");
$recentProfileStmt->bind_param("i", $user_id);
$recentProfileStmt->execute();
$profileUpdate = $recentProfileStmt->get_result()->fetch_assoc();
$lastProfileUpdate = $profileUpdate['created_at'] ?? null;


// Recent Attendance (optional)
$recentAttendanceStmt = $conn->prepare("
  SELECT date, clock_in, clock_out, status 
  FROM attendance 
  WHERE user_id=? 
  ORDER BY date DESC 
  LIMIT 5
");
$recentAttendanceStmt->bind_param("i", $user_id);
$recentAttendanceStmt->execute();
$recentAttendanceList = $recentAttendanceStmt->get_result()->fetch_all(MYSQLI_ASSOC);


// ------------------ MERGE ALL ACTIVITIES ------------------
$activities = [];

// Leaves
foreach ($recentLeaves as $l) {
  $activities[] = [
    "type" => "leave",
    "date" => $l['applied_at'],
    "data" => $l
  ];
}

// Salaries
foreach ($recentSalaries as $s) {
  $activities[] = [
    "type" => "salary",
    "date" => $s['generated_at'],
    "data" => $s
  ];
}

// Profile updates
if ($lastProfileUpdate) {
  $activities[] = [
    "type" => "profile",
    "date" => $lastProfileUpdate,
    "data" => []
  ];
}

// Attendance
foreach ($recentAttendanceList as $a) {
  $activities[] = [
    "type" => "attendance",
    "date" => $a['date'],
    "data" => $a
  ];
}

// Sort by newest first
usort($activities, function ($a, $b) {
  return strtotime($b['date']) - strtotime($a['date']);
});


// ------------------ TODAY'S WORK SUMMARY WITH FIXED SHIFT & LATE/EARLY ------------------

// helper already used earlier: dt($time) converts H:i:s to timestamp for today
// make sure dt() exists; if not, define it (safe re-declaration)
if (!function_exists('dt')) {
  function dt($time)
  {
    if (!$time) return null;
    return strtotime(date('Y-m-d') . " " . $time);
  }
}

// Today's raw values (TIME columns expected)
$clockIn   = $todayRecord['clock_in'] ?? null;
$clockOut  = $todayRecord['clock_out'] ?? null;
$breakIn   = $todayRecord['break_in'] ?? null;
$breakOut  = $todayRecord['break_out'] ?? null;
$breakLabel = $todayRecord['break_duration'] ?? "0m";

// SHIFT definition (fixed 9:00 - 17:00)
$shiftStart = strtotime(date('Y-m-d') . ' 09:00:00');
$shiftEnd   = strtotime(date('Y-m-d') . ' 17:00:00');
$requiredSeconds = 8 * 3600; // 28800

// timestamps for clock in/out (null-safe)
$clockInTs  = $clockIn ? dt($clockIn) : null;
$clockOutTs = $clockOut ? dt($clockOut) : null;

// Calculate break seconds (if break columns store TIME)
$breakSeconds = 0;
if ($breakIn && $breakOut) {
  $breakSeconds = max(0, dt($breakOut) - dt($breakIn));
} elseif ($breakIn && !$breakOut) {
  // running break
  $breakSeconds = max(0, time() - dt($breakIn));
}

// Calculate effective work window inside fixed shift
$totalWorkSeconds = 0; // default to avoid undefined variable

if ($clockInTs) {
  // effective start shouldn't be before shift start
  $effectiveStart = max($clockInTs, $shiftStart);

  // effective end is min(clockOutTs or now, shiftEnd)
  if ($clockOutTs) {
    $effectiveEnd = min($clockOutTs, $shiftEnd);
  } else {
    // running: take min(current time, shift end)
    $effectiveEnd = min(time(), $shiftEnd);
  }

  // compute raw seconds inside shift (can't be negative)
  $rawWorkSeconds = max(0, $effectiveEnd - $effectiveStart);

  // subtract break seconds that occurred inside shift window
  // Note: if break overlaps outside shift, this simple subtract is acceptable for single-break usage.
  $totalWorkSeconds = max(0, $rawWorkSeconds - $breakSeconds);

  // cap to max shift seconds
  if ($totalWorkSeconds > $requiredSeconds) {
    $totalWorkSeconds = $requiredSeconds;
  }

  // format workHours text
  $workHours = floor($totalWorkSeconds / 3600) . "h " . floor(($totalWorkSeconds % 3600) / 60) . "m";
  if (!$clockOutTs && time() < $shiftEnd) {
    $workHours .= " (running)";
  }
} else {
  $workHours = "0h 0m";
}

// Break duration display
if ($breakIn && $breakOut) {
  // prefer stored label if available
  $breakDuration = $breakLabel ?: sprintf(
    "%02dh %02dm",
    floor($breakSeconds / 3600),
    floor(($breakSeconds % 3600) / 60)
  );
} elseif ($breakIn && !$breakOut) {
  $mins = floor($breakSeconds / 60);
  $hrs  = floor($mins / 60);
  $mins = $mins % 60;
  $breakDuration = sprintf("%02dh %02dm (running)", $hrs, $mins);
} else {
  $breakDuration = "0m";
}

// Overtime (time worked after shift end) — optional, compute from actual clockOutTs
$overtime = "0h 0m";
if ($clockOutTs && $clockOutTs > $shiftEnd) {
  $overtimeSeconds = $clockOutTs - $shiftEnd;
  $overtime = floor($overtimeSeconds / 3600) . "h " . floor(($overtimeSeconds % 3600) / 60) . "m";
}

// ------------------ LATE / EARLY / DEFICIT CALCULATION ------------------

$lateBy = "0m";
$earlyLeave = "0m";
$workDeficit = "0m";

// LATE ARRIVAL: clockInTs later than shiftStart
if ($clockInTs && $clockInTs > $shiftStart) {
  $lateSeconds = $clockInTs - $shiftStart;
  if ($lateSeconds >= 3600) {
    $lateBy = floor($lateSeconds / 3600) . "h " . floor(($lateSeconds % 3600) / 60) . "m";
  } else {
    $lateBy = floor($lateSeconds / 60) . "m";
  }
}

// EARLY LEAVE: clockOutTs earlier than shiftEnd (only if clocked out)
if ($clockOutTs && $clockOutTs < $shiftEnd) {
  $earlySeconds = $shiftEnd - $clockOutTs;
  if ($earlySeconds >= 3600) {
    $earlyLeave = floor($earlySeconds / 3600) . "h " . floor(($earlySeconds % 3600) / 60) . "m";
  } else {
    $earlyLeave = floor($earlySeconds / 60) . "m";
  }
}

// WORK DEFICIT: if totalWorkSeconds less than required
if ($totalWorkSeconds < $requiredSeconds) {
  $deficitSeconds = $requiredSeconds - $totalWorkSeconds;
  $workDeficit = floor($deficitSeconds / 3600) . "h " . floor(($deficitSeconds % 3600) / 60) . "m";
}


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
  <link rel="stylesheet" href="../assets/css/style.css">
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

        <!-- Today Attendance Status -->
        <div class="bg-white rounded-xl shadow-sm p-5 flex items-center justify-between border border-gray-200">

          <!-- Left Section -->
          <div>
            <!-- Title matching Leave Balance -->
            <h3 class="text-gray-500 text-sm">Attendance Status</h3>

            <!-- Main Status Line (big text, like the number in Leave Balance) -->
            <p class="text-2xl font-bold text-gray-900 mt-1">
              <?php if ($todayRecord && $todayRecord['clock_in']): ?>
                <?= date('h:i A', strtotime($todayRecord['clock_in'])) ?>
              <?php else: ?>
                <span class="text-red-600">Not Clocked In</span>
              <?php endif; ?>
            </p>

            <!-- Small Subtext -->
            <p class="text-xs text-gray-500 mt-1">
              Status:
              <span class="<?= ($todayRecord['status'] ?? '') == 'Present' ? 'text-green-600' : 'text-gray-500' ?> font-semibold">
                <?= $todayRecord['status'] ?? 'Not Marked' ?>
              </span>
            </p>
          </div>

          <!-- Right Icon (Identical Size & Style) -->
          <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
              fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round"
              class="lucide lucide-calendar w-6 h-6 text-green-600">
              <path d="M8 2v4" />
              <path d="M16 2v4" />
              <rect width="18" height="18" x="3" y="4" rx="2" />
              <path d="M3 10h18" />
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
          <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
            <i class="fa-solid fa-chart-line text-yellow-600 text-2xl"></i>
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


        <!-- Recent Activities -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activities</h3>

          <div class="space-y-3 max-h-[20rem] overflow-y-auto scroll-area">
            <?php foreach ($activities as $act): ?>
              <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">

                <!-- LEFT SIDE (ICON + TEXT) -->
                <div class="flex items-center gap-3">

                  <!-- Icons by type -->
                  <?php if ($act['type'] == 'leave'): ?>
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                      <i class="fa-solid fa-plane-departure text-blue-600"></i>
                    </div>
                    <div>
                      <p class="font-medium text-gray-900">
                        Leave Request (<?= $act['data']['leave_type'] ?>)
                      </p>
                      <p class="text-sm text-gray-600">
                        <?= $act['data']['start_date'] ?> to <?= $act['data']['end_date'] ?>
                      </p>
                    </div>

                  <?php elseif ($act['type'] == 'salary'): ?>
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                      <i class="fa-solid fa-money-bill text-purple-600"></i>
                    </div>
                    <div>
                      <p class="font-medium text-gray-900">
                        Salary Generated
                      </p>
                      <p class="text-sm text-gray-600">
                        <?= date("F Y", strtotime($act['data']['month'])) ?> — ₹<?= number_format($act['data']['total']) ?>
                      </p>
                    </div>

                  <?php elseif ($act['type'] == 'profile'): ?>
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                      <i class="fa-solid fa-user-pen text-yellow-600"></i>
                    </div>
                    <div>
                      <p class="font-medium text-gray-900">Profile Updated</p>
                      <p class="text-sm text-gray-600">
                        Your profile was updated recently.
                      </p>
                    </div>

                  <?php elseif ($act['type'] == 'attendance'): ?>
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                      <i class="fa-solid fa-calendar-check text-green-600"></i>
                    </div>
                    <div>
                      <p class="font-medium text-gray-900">
                        Attendance: <?= $act['data']['status'] ?>
                      </p>
                      <p class="text-sm text-gray-600">
                        <?= !empty($act['data']['clock_in']) ? date("h:i A", strtotime($act['data']['clock_in'])) : "--" ?>
                        -
                        <?= !empty($act['data']['clock_out']) ? date("h:i A", strtotime($act['data']['clock_out'])) : "--" ?>
                      </p>
                    </div>
                  <?php endif; ?>
                </div>

                <!-- Activity Date -->
                <div class="text-xs text-gray-500">
                  <?= date("M d, Y", strtotime($act['date'])) ?>
                </div>

              </div>
            <?php endforeach; ?>

            <?php if (count($activities) == 0): ?>
              <p class="text-gray-500 text-sm text-center py-4">No recent activities found.</p>
            <?php endif; ?>
          </div>
        </div>



      </div>

      <!-- Today’s Work Summary -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
          <!-- <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg> -->
          Today’s Work Summary
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">

          <!-- Clock In -->
          <div>
            <p class="text-gray-500 text-sm">Clock In</p>
            <p class="text-xl font-semibold text-gray-800 mt-1">
              <?= $clockIn ? date("h:i A", strtotime($clockIn)) : "—" ?>
            </p>
          </div>

          <!-- Clock Out -->
          <div>
            <p class="text-gray-500 text-sm">Clock Out</p>
            <p class="text-xl font-semibold text-gray-800 mt-1">
              <?= $clockOut ? date("h:i A", strtotime($clockOut)) : "—" ?>
            </p>
          </div>

          <!-- Work Hours -->
          <div>
            <p class="text-gray-500 text-sm">Work Hours</p>
            <p class="text-xl font-semibold text-gray-800 mt-1">
              <?= $workHours ?>
            </p>
          </div>

          <!-- Break Duration -->
          <div>
            <p class="text-gray-500 text-sm">Break Duration</p>
            <p class="text-xl font-semibold text-gray-800 mt-1"><?= $breakDuration ?></p>

            <?php if ($breakIn): ?>
              <p class="text-xs text-gray-500 mt-1">
                In: <?= date("h:i A", strtotime($breakIn)) ?>
                <?php if ($breakOut): ?>
                  | Out: <?= date("h:i A", strtotime($breakOut)) ?>
                <?php else: ?>
                  | Break Running...
                <?php endif; ?>
              </p>
            <?php endif; ?>
          </div>

          <!-- Late By -->
          <div>
            <p class="text-gray-500 text-sm">Late By</p>
            <p class="text-xl font-semibold text-red-600 mt-1">
              <?= $lateBy ?>
            </p>
          </div>

          <!-- Early Leave -->
          <div>
            <p class="text-gray-500 text-sm">Early Leave</p>
            <p class="text-xl font-semibold text-red-600 mt-1">
              <?= $earlyLeave ?>
            </p>
          </div>

          <!-- Work Deficit -->
          <div>
            <p class="text-gray-500 text-sm">Work Deficit</p>
            <p class="text-xl font-semibold text-red-600 mt-1">
              <?= $workDeficit ?>
            </p>
          </div>

          <!-- Overtime -->
          <div>
            <p class="text-gray-500 text-sm">Overtime</p>
            <p class="text-xl font-semibold text-green-600 mt-1">
              <?= $overtime ?>
            </p>
          </div>

        </div>
      </div>




    </main>
  </div>

  <script src="../assets/js/script.js"></script>
</body>

</html>