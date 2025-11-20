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

/* -------------------------
   Helper functions
   ------------------------- */

// Format seconds as "Hh Mm" (e.g. "01h 15m" or "15m")
function sec_to_hm_label($sec)
{
  if ($sec <= 0) return "0m";
  $h = floor($sec / 3600);
  $m = floor(($sec % 3600) / 60);
  if ($h > 0) return sprintf("%02dh %02dm", $h, $m);
  return sprintf("%02dm", $m);
}

// Format decimal hours (2.50) to "Hh Mm" for UI
function decimal_hours_to_label($decimal)
{
  $secs = round($decimal * 3600);
  return sec_to_hm_label($secs);
}

// fetch today's attendance row
function get_today_record($conn, $user_id, $today)
{
  $s = $conn->prepare("SELECT * FROM attendance WHERE user_id=? AND date=? LIMIT 1");
  $s->bind_param("is", $user_id, $today);
  $s->execute();
  return $s->get_result()->fetch_assoc();
}

// safe time string to datetime string for strtotime: combine date + time value (TIME column)
function time_to_datetime($date, $time)
{
  // $time expected HH:MM:SS or NULL
  if (empty($time)) return false;
  return $date . ' ' . $time;
}

/* -------------------------
   Load today's record (fresh)
   ------------------------- */
$todayRecord = get_today_record($conn, $user_id, $today);

/* -------------------------
   Handle form actions
   ------------------------- */

/* CLOCK IN */
if (isset($_POST['clock_in'])) {
  if ($todayRecord) {
    $message = "<span class='text-yellow-600'>You already clocked in today.</span>";
  } else {
    // store TIME only (as your column is TIME)
    $timeNow = date('H:i:s');
    $status = "Present";
    $ins = $conn->prepare("INSERT INTO attendance (user_id, date, clock_in, status) VALUES (?, ?, ?, ?)");
    $ins->bind_param("isss", $user_id, $today, $timeNow, $status);
    $ins->execute();

    $todayRecord = get_today_record($conn, $user_id, $today);
    $message = "<span class='text-green-600'>Clocked in successfully at " . date('h:i A', strtotime($today . ' ' . $timeNow)) . "</span>";
  }
}

/* START BREAK */
if (isset($_POST['break_start'])) {
  $todayRecord = get_today_record($conn, $user_id, $today);

  if (!$todayRecord || empty($todayRecord['clock_in'])) {
    $message = "<span class='text-red-600'>You must clock in before starting a break.</span>";
  } elseif (!empty($todayRecord['clock_out'])) {
    $message = "<span class='text-yellow-600'>You already clocked out — cannot start a break.</span>";
  } elseif (!empty($todayRecord['break_in']) && empty($todayRecord['break_out'])) {
    $message = "<span class='text-yellow-600'>A break is already in progress.</span>";
  } else {
    $now = date('H:i:s');
    $upd = $conn->prepare("UPDATE attendance SET break_in=? WHERE id=?");
    $upd->bind_param("si", $now, $todayRecord['id']);
    $upd->execute();

    $todayRecord = get_today_record($conn, $user_id, $today);
    $message = "<span class='text-green-600'>Break started at " . date('h:i A', strtotime($today . ' ' . $now)) . "</span>";
  }
}

/* END BREAK */
if (isset($_POST['break_end'])) {
  $todayRecord = get_today_record($conn, $user_id, $today);

  if (!$todayRecord || empty($todayRecord['clock_in'])) {
    $message = "<span class='text-red-600'>You must clock in before ending a break.</span>";
  } elseif (!empty($todayRecord['clock_out'])) {
    $message = "<span class='text-yellow-600'>You already clocked out — no active break.</span>";
  } elseif (empty($todayRecord['break_in'])) {
    $message = "<span class='text-yellow-600'>No break has been started.</span>";
  } elseif (!empty($todayRecord['break_out'])) {
    $message = "<span class='text-yellow-600'>Break already ended.</span>";
  } else {
    $breakOut = date('H:i:s');

    // compute duration seconds using today's date + times
    $breakInDt = time_to_datetime($today, $todayRecord['break_in']);
    $breakOutDt = time_to_datetime($today, $breakOut);
    $breakDurationSec = 0;
    if ($breakInDt && $breakOutDt) {
      $breakDurationSec = max(0, strtotime($breakOutDt) - strtotime($breakInDt));
    }

    $breakLabel = sec_to_hm_label($breakDurationSec);

    $u = $conn->prepare("UPDATE attendance SET break_out=?, break_duration=? WHERE id=?");
    $u->bind_param("ssi", $breakOut, $breakLabel, $todayRecord['id']);
    $u->execute();

    // refresh
    $todayRecord = get_today_record($conn, $user_id, $today);
    $message = "<span class='text-green-600'>Break ended at " . date('h:i A', strtotime($today . ' ' . $breakOut)) . " (Duration: $breakLabel)</span>";
  }
}

/* CLOCK OUT */
if (isset($_POST['clock_out'])) {
  $todayRecord = get_today_record($conn, $user_id, $today);

  if (!$todayRecord || empty($todayRecord['clock_in'])) {
    $message = "<span class='text-red-600'>You must clock in first.</span>";
  } elseif (!empty($todayRecord['clock_out'])) {
    $message = "<span class='text-yellow-600'>You already clocked out today.</span>";
  } else {
    $clockOut = date('H:i:s');

    // If break started but not ended, auto end break at clock out
    if (!empty($todayRecord['break_in']) && empty($todayRecord['break_out'])) {
      $breakInDt = time_to_datetime($today, $todayRecord['break_in']);
      $breakOutDt = time_to_datetime($today, $clockOut);
      $breakDurationSec = max(0, strtotime($breakOutDt) - strtotime($breakInDt));
      $breakLabel = sec_to_hm_label($breakDurationSec);

      $uclose = $conn->prepare("UPDATE attendance SET break_out=?, break_duration=? WHERE id=?");
      $uclose->bind_param("ssi", $clockOut, $breakLabel, $todayRecord['id']);
      $uclose->execute();

      // refresh today's record after closing break
      $todayRecord = get_today_record($conn, $user_id, $today);
    } else {
      // if break was already ended, parse its break_duration into seconds if set; else zero
      if (!empty($todayRecord['break_duration'])) {
        // break_duration is label string e.g. "01h 15m" — safer to compute seconds from break_in/out if available
        if (!empty($todayRecord['break_in']) && !empty($todayRecord['break_out'])) {
          $breakInDt = time_to_datetime($today, $todayRecord['break_in']);
          $breakOutDt = time_to_datetime($today, $todayRecord['break_out']);
          $breakDurationSec = max(0, strtotime($breakOutDt) - strtotime($breakInDt));
        } else {
          $breakDurationSec = 0;
        }
      } else {
        $breakDurationSec = 0;
      }
    }

    // recompute values (ensure we have latest record)
    $todayRecord = get_today_record($conn, $user_id, $today);

    // compute total break seconds
    $breakSeconds = 0;
    if (!empty($todayRecord['break_in']) && !empty($todayRecord['break_out'])) {
      $breakSeconds = max(0, strtotime(time_to_datetime($today, $todayRecord['break_out'])) - strtotime(time_to_datetime($today, $todayRecord['break_in'])));
    }

    // compute raw worked seconds = clockOut - clockIn
    $rawWorkedSec = max(0, strtotime(time_to_datetime($today, $clockOut)) - strtotime(time_to_datetime($today, $todayRecord['clock_in'])));

    // actual worked seconds exclude breaks
    $actualWorkedSec = max(0, $rawWorkedSec - $breakSeconds);

    // store hours_worked as decimal (e.g., 7.50) to match DECIMAL(5,2)
    $hoursDecimal = round($actualWorkedSec / 3600, 2);

    // optional human-readable (we keep break_duration already set)
    $hoursLabel = decimal_hours_to_label($hoursDecimal);

    // update attendance row
    $u2 = $conn->prepare("UPDATE attendance SET clock_out=?, hours_worked=? WHERE id=?");
    $u2->bind_param("sdi", $clockOut, $hoursDecimal, $todayRecord['id']);
    $u2->execute();

    // refresh
    $todayRecord = get_today_record($conn, $user_id, $today);

    $message = "<span class='text-green-600'>Clocked out successfully at " . date('h:i A', strtotime($today . ' ' . $clockOut)) . " (Worked: $hoursLabel, Break: " . ($todayRecord['break_duration'] ?? '0m') . ")</span>";
  }
}

/* Optional: Undo break (delete values) */
if (isset($_POST['remove_break'])) {
  $todayRecord = get_today_record($conn, $user_id, $today);
  if ($todayRecord) {
    $u = $conn->prepare("UPDATE attendance SET break_in=NULL, break_out=NULL, break_duration=NULL WHERE id=?");
    $u->bind_param("i", $todayRecord['id']);
    $u->execute();
    $todayRecord = get_today_record($conn, $user_id, $today);
    $message = "<span class='text-green-600'>Break entry removed.</span>";
  }
}

/* -------------------------
   Fetch attendance history
   ------------------------- */
$attStmt = $conn->prepare("SELECT * FROM attendance WHERE user_id=? ORDER BY date DESC LIMIT 10");
$attStmt->bind_param("i", $user_id);
$attStmt->execute();
$attendance = $attStmt->get_result();

$pageTitle = "My Attendance";
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>My Attendance</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="bg-gray-100">

  <!-- Sidebar -->
  <?php include '../includes/sidebaremp.php'; ?>

  <!-- Overlay for Mobile -->
  <div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden z-30 md:hidden"></div>

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
        <div class="text-center sm:text-left w-full sm:w-auto">
          <h2 class="text-xl sm:text-2xl font-bold text-gray-900">My Attendance</h2>
          <p class="text-gray-600 text-sm sm:text-base">Track your attendance and working hours</p>
        </div>

        <!-- Actions -->
        <div class="flex gap-3 w-full sm:w-auto">
          <form method="POST" class="flex gap-3 w-full sm:w-auto">
            <button type="submit" name="clock_in"
              class="bg-green-600 text-white px-4 py-2 rounded-xl hover:bg-green-700 transition-all flex items-center justify-center gap-2 w-full sm:w-auto">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
              </svg>
              <span>Clock In</span>
            </button>

            <button type="submit" name="clock_out"
              class="bg-red-600 text-white px-4 py-2 rounded-xl hover:bg-red-700 transition-all flex items-center justify-center gap-2 w-full sm:w-auto">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
              </svg>
              <span>Clock Out</span>
            </button>
          </form>
        </div>
      </div>

      <!-- Today's Status -->
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
              <?= !empty($todayRecord['clock_in']) ? date('h:i A', strtotime($today . ' ' . $todayRecord['clock_in'])) : '--:--' ?>
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
              <?php
              if (!empty($todayRecord['hours_worked'])) {
                echo decimal_hours_to_label((float)$todayRecord['hours_worked']);
              } else {
                echo '--:--';
              }
              ?>
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
            <p class="text-xl font-bold <?= (!empty($todayRecord['status']) && $todayRecord['status'] == 'Present') ? 'text-green-600' : 'text-gray-500' ?>">
              <?= $todayRecord['status'] ?? 'Not Marked' ?>
            </p>
          </div>

        </div>

        <!-- Break Controls & Summary -->
        <div class="mt-6 border-t pt-4">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
              <h4 class="text-sm font-semibold text-gray-700">Break</h4>
              <?php
              $breakLabelDisplay = $todayRecord['break_duration'] ?? "0m";
              echo "<p class='text-sm text-gray-500'>Break time: <span class='font-medium text-gray-900'>$breakLabelDisplay</span></p>";
              ?>
            </div>

            <div class="flex gap-3">
              <!-- Start Break -->
              <form method="POST" class="inline">
                <button type="submit" name="break_start"
                  class="bg-yellow-500 text-white px-4 py-2 rounded-xl hover:bg-yellow-600 transition-all flex items-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 8v4l3 3"></path>
                    <circle cx="12" cy="12" r="10"></circle>
                  </svg>
                  <span>Start Break</span>
                </button>
              </form>

              <!-- End Break -->
              <form method="POST" class="inline">
                <button type="submit" name="break_end"
                  class="bg-yellow-700 text-white px-4 py-2 rounded-xl hover:bg-yellow-800 transition-all flex items-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 8v4l3 3"></path>
                    <circle cx="12" cy="12" r="10"></circle>
                  </svg>
                  <span>End Break</span>
                </button>
              </form>

              <!-- Remove break (undo) -->
              <form method="POST" class="inline" onsubmit="return confirm('Remove break entry?');">
                <button type="submit" name="remove_break" class="px-4 py-2 rounded-xl border border-gray-300 bg-white hover:bg-gray-50">
                  Remove Break
                </button>
              </form>
            </div>
          </div>

          <!-- Show break times (if present) -->
          <div class="mt-4 space-y-2">
            <?php if (!empty($todayRecord['break_in'])): ?>
              <div class="p-3 bg-gray-50 rounded-lg flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-gray-900">
                    <?= date('h:i A', strtotime($today . ' ' . $todayRecord['break_in'])) ?>
                    <?php if (!empty($todayRecord['break_out'])): ?>
                      &nbsp; - &nbsp; <?= date('h:i A', strtotime($today . ' ' . $todayRecord['break_out'])) ?>
                    <?php else: ?>
                      &nbsp; - &nbsp; <span class="text-gray-500">In progress</span>
                    <?php endif; ?>
                  </p>
                  <p class="text-xs text-gray-600">Duration: <?= $todayRecord['break_duration'] ?? '--' ?></p>
                </div>
                <div>
                  <!-- nothing for now -->
                </div>
              </div>
            <?php else: ?>
              <p class="text-sm text-gray-500">No break taken today.</p>
            <?php endif; ?>
          </div>
        </div>

      </div>

      <!-- Attendance History Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
          <div class="flex flex-col sm:flex-row  items-start sm:items-center space-y-4 sm:space-y-0">
            <h3 class="text-lg font-semibold text-gray-900">Attendance History</h3>
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
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Break</th>
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
                    <?= !empty($row['clock_in']) ? date('h:i A', strtotime($row['date'] . ' ' . $row['clock_in'])) : '--:--' ?>
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <?= !empty($row['clock_out']) ? date('h:i A', strtotime($row['date'] . ' ' . $row['clock_out'])) : '--:--' ?>
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    <?= !empty($row['hours_worked']) ? decimal_hours_to_label((float)$row['hours_worked']) : '--' ?>
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <?= $row['break_duration'] ?? '--' ?>
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