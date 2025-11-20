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
$name = htmlspecialchars($emp['name']);

$stmt = $conn->prepare("SELECT * FROM attendance WHERE user_id=? ORDER BY date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// shift timings (9 AM – 5 PM)
$shiftStart = strtotime("09:00:00");
$shiftEnd   = strtotime("17:00:00");
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Attendance Report - <?= $name ?></title>
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
    @media print {
      .no-print {
        display: none;
      }

      body {
        background: white;
      }
    }
  </style>
</head>

<body class="bg-gray-50 text-gray-800 p-4 md:p-10 font-sans">

  <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-sm p-6 md:p-10 border border-gray-100">

    <!-- Header -->
    <div class="flex flex-col md:flex-row items-center justify-between border-b pb-4 mb-6">
      <div class="flex items-center gap-3">
        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135673.png" class="w-12 h-12">
        <div>
          <h1 class="text-2xl font-semibold">Company Name Pvt. Ltd.</h1>
          <p class="text-sm text-gray-500">Employee Attendance Report</p>
        </div>
      </div>
      <div class="text-right mt-3 md:mt-0">
        <p class="text-sm text-gray-500">Generated on:</p>
        <p class="text-sm font-medium"><?= date("d M Y, h:i A") ?></p>
      </div>
    </div>

    <div class="text-center mb-6">
      <h2 class="text-2xl font-bold">Attendance Report</h2>
      <p class="text-gray-600">Employee: <span class="font-semibold"><?= $name ?></span></p>
    </div>

    <!-- Print button -->
    <div class="no-print text-center mb-6">
      <button onclick="window.print()"
        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg shadow-md">
        🖨️ Print / Save as PDF
      </button>
    </div>

    <!-- Attendance Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full border border-gray-200 text-sm rounded-lg overflow-hidden">

        <thead class="bg-blue-600 text-white">
          <tr>
            <th class="py-3 px-4">Date</th>
            <th class="py-3 px-4">Clock In</th>
            <th class="py-3 px-4">Clock Out</th>
            <th class="py-3 px-4">Break In</th>
            <th class="py-3 px-4">Break Out</th>
            <th class="py-3 px-4 text-center">Break Duration</th>
            <th class="py-3 px-4 text-center">Work Hours</th>
            <th class="py-3 px-4 text-center">Late By</th>
            <th class="py-3 px-4 text-center">Early Leave</th>
            <th class="py-3 px-4 text-center">Deficit</th>
            <th class="py-3 px-4 text-center">Status</th>
          </tr>
        </thead>

        <tbody class="bg-white divide-y divide-gray-100">

          <?php while ($row = $result->fetch_assoc()): ?>

            <?php
            // clock timestamps
            $clockIn  = $row['clock_in'] ? strtotime($row['clock_in']) : null;
            $clockOut = $row['clock_out'] ? strtotime($row['clock_out']) : null;

            // break timestamps
            $breakIn  = $row['break_in'] ? strtotime($row['date'] . ' ' . $row['break_in']) : null;
            $breakOut = $row['break_out'] ? strtotime($row['date'] . ' ' . $row['break_out']) : null;

            // break duration (seconds)
            $breakSeconds = 0;
            if ($breakIn && $breakOut) {
              $breakSeconds = $breakOut - $breakIn;
            }

            // work hours (seconds)
            $workSeconds = 0;
            if ($clockIn && $clockOut) {
              $workSeconds = ($clockOut - $clockIn) - $breakSeconds;
            }

            // convert work hours to h:m format
            $workHours =
              floor($workSeconds / 3600) . "h " .
              floor(($workSeconds % 3600) / 60) . "m";

            // late by
            $lateBy = "0m";
            if ($clockIn && $clockIn > $shiftStart) {
              $lateSec = $clockIn - $shiftStart;
              $lateBy = floor($lateSec / 3600) . "h " . floor(($lateSec % 3600) / 60) . "m";
            }

            // early leave
            $earlyLeave = "0m";
            if ($clockOut && $clockOut < $shiftEnd) {
              $earlySec = $shiftEnd - $clockOut;
              $earlyLeave = floor($earlySec / 3600) . "h " . floor(($earlySec % 3600) / 60) . "m";
            }

            // deficit
            $required = 8 * 3600;
            $deficit = "0m";
            if ($workSeconds < $required) {
              $defSec = $required - $workSeconds;
              $deficit = floor($defSec / 3600) . "h " . floor(($defSec % 3600) / 60) . "m";
            }

            // status badge color
            $status = strtolower($row['status']);
            $statusColor = match ($status) {
              'present' => 'bg-green-100 text-green-700',
              'absent' => 'bg-red-100 text-red-700',
              'leave', 'half-day' => 'bg-yellow-100 text-yellow-700',
              default => 'bg-gray-100 text-gray-600'
            };
            ?>

            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3"><?= date("d M Y", strtotime($row['date'])) ?></td>
              <td class="px-4 py-3"><?= $row['clock_in'] ?: '--' ?></td>
              <td class="px-4 py-3"><?= $row['clock_out'] ?: '--' ?></td>
              <td class="px-4 py-3"><?= $row['break_in'] ?: '--' ?></td>
              <td class="px-4 py-3"><?= $row['break_out'] ?: '--' ?></td>

              <td class="px-4 py-3 text-center">
                <?= $row['break_duration'] ?: '--' ?>
              </td>

              <td class="px-4 py-3 text-center font-semibold text-gray-700">
                <?= $row['clock_out'] ? $workHours : '--' ?>
              </td>

              <td class="px-4 py-3 text-center text-red-600"><?= $lateBy ?></td>
              <td class="px-4 py-3 text-center text-red-600"><?= $earlyLeave ?></td>
              <td class="px-4 py-3 text-center text-red-600"><?= $deficit ?></td>

              <td class="px-4 py-3 text-center">
                <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $statusColor ?>">
                  <?= ucfirst($status) ?>
                </span>
              </td>
            </tr>

          <?php endwhile; ?>
        </tbody>

      </table>
    </div>

    <div class="text-center text-gray-500 text-xs mt-6 border-t pt-4">
      <p>© <?= date("Y") ?> Company Name Pvt. Ltd.</p>
    </div>

  </div>

</body>

</html>