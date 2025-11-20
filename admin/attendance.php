<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

$emp = $_SESSION['user'];

/* Fetch attendance with break details */
$sql = "
SELECT 
  a.id, 
  u.name, 
  a.clock_in, 
  a.clock_out, 
  a.break_duration,
  a.hours_worked,
  DATE_FORMAT(a.date, '%d %b %Y (%a)') AS formatted_date
FROM attendance a
JOIN users u ON a.user_id = u.id
ORDER BY a.date DESC
";

$result = $conn->query($sql);

function format_work_hours_simple($decimal)
{
  if ($decimal === null || $decimal === '' || $decimal <= 0) {
    return "0m";
  }

  // total seconds
  $totalSeconds = round($decimal * 3600);
  $hours = floor($totalSeconds / 3600);
  $minutes = floor(($totalSeconds % 3600) / 60);

  if ($hours >= 1) {
    return $hours . "h";   // show only hours
  } else {
    return $minutes . "m"; // show only minutes
  }
}


$pageTitle = "Attendance";
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Attendance | Admin</title>
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
  <?php include_once '../includes/sidebar.php'; ?>

  <!-- Overlay for mobile -->
  <div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden z-30 md:hidden"></div>

  <!-- Main Content -->
  <div class="flex-1 flex flex-col min-h-screen md:ml-64">
    <?php include_once '../includes/header.php'; ?>

    <main class="flex-1 pt-20 px-4 md:px-8 pb-8">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0 mb-4">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Attendance Management</h2>
          <p class="text-gray-600">Monitor employee attendance and working hours</p>
        </div>
      </div>

      <!-- Attendance Card -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200">

        <!-- Header -->
        <div class="p-6 border-b border-gray-200">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
            <h3 class="text-lg font-semibold text-gray-900">Employee Attendance</h3>
            <div class="flex space-x-2">
              <a href="attendance_report_admin.php" target="_blank"
                class="bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition-all flex items-center space-x-2">
                <i class="fa-solid fa-file-pdf"></i>
                <span>Download PDF</span>
              </a>

            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">

          <!-- DESKTOP TABLE -->
          <table class="w-full text-sm hidden md:table">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Clock In</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Clock Out</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Break</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Worked Hours</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
              <?php if ($result && $result->num_rows > 0): ?>
                <?php $i = 1;
                while ($row = $result->fetch_assoc()): ?>

                  <?php
                  if (!$row['clock_in']) {
                    $status = "Absent";
                    $badge = "bg-red-100 text-red-700";
                  } elseif ($row['clock_in'] && !$row['clock_out']) {
                    $status = "In Progress";
                    $badge = "bg-yellow-100 text-yellow-700";
                  } else {
                    $status = "Present";
                    $badge = "bg-green-100 text-green-700";
                  }
                  ?>

                  <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-gray-500"><?= $i++ ?></td>
                    <td class="px-6 py-4 font-medium text-gray-900"><?= htmlspecialchars($row['name']) ?></td>
                    <td class="px-6 py-4 text-gray-700"><?= $row['formatted_date'] ?></td>
                    <td class="px-6 py-4 text-gray-700"><?= $row['clock_in'] ? date("h:i A", strtotime($row['clock_in'])) : '-' ?></td>
                    <td class="px-6 py-4 text-gray-700"><?= $row['clock_out'] ? date("h:i A", strtotime($row['clock_out'])) : '-' ?></td>
                    <td class="px-6 py-4 text-gray-700"><?= $row['break_duration'] ?: '--' ?></td>
                    <td class="px-6 py-4 text-gray-800 font-semibold"><?= format_work_hours_simple($row['hours_worked']) ?></td>

                    <td class="px-6 py-4">
                      <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $badge ?>">
                        <?= $status ?>
                      </span>
                    </td>
                  </tr>

                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" class="text-center text-gray-500 py-4">No attendance records found.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>



          <!-- MOBILE CARD VIEW -->
          <div class="md:hidden p-4 space-y-4">

            <?php if ($result->num_rows > 0): ?>
              <?php $result->data_seek(0);
              while ($row = $result->fetch_assoc()): ?>

                <?php
                // Determine status
                if (!$row['clock_in']) {
                  $status = "Absent";
                  $badge = "bg-red-100 text-red-700";
                } elseif ($row['clock_in'] && !$row['clock_out']) {
                  $status = "In Progress";
                  $badge = "bg-yellow-100 text-yellow-700";
                } else {
                  $status = "Present";
                  $badge = "bg-green-100 text-green-700";
                }
                ?>

                <div class="p-4 bg-white rounded-xl shadow border border-gray-200">

                  <!-- Avatar + Name -->
                  <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                      <span class="text-blue-600 font-semibold text-lg">
                        <?= strtoupper(substr($row['name'], 0, 1)) ?>
                      </span>
                    </div>

                    <div class="flex-1">
                      <p class="text-base font-semibold text-gray-900 leading-tight">
                        <?= htmlspecialchars($row['name']) ?>
                      </p>

                      <p class="text-xs inline-block mt-1 px-2 py-0.5 rounded-full <?= $badge ?>">
                        <?= $status ?>
                      </p>
                    </div>
                  </div>

                  <!-- Date -->
                  <div class="mt-3 text-sm border-l-4 border-blue-500 pl-3">
                    <p class="text-gray-700">
                      <span class="font-semibold">Date:</span>
                      <?= $row['formatted_date'] ?>
                    </p>
                  </div>

                  <!-- Details Grid -->
                  <div class="grid grid-cols-2 gap-3 mt-4 text-sm">
                    <p class="text-gray-700">
                      <span class="font-semibold">Clock In:</span><br>
                      <?= $row['clock_in'] ? date("h:i A", strtotime($row['clock_in'])) : '--' ?>
                    </p>

                    <p class="text-gray-700">
                      <span class="font-semibold">Clock Out:</span><br>
                      <?= $row['clock_out'] ? date("h:i A", strtotime($row['clock_out'])) : '--' ?>
                    </p>

                    <p class="text-gray-700">
                      <span class="font-semibold">Break:</span><br>
                      <?= $row['break_duration'] ?: '--' ?>
                    </p>

                    <p class="text-gray-700">
                      <span class="font-semibold">Worked:</span><br>
                      <?= format_work_hours_simple($row['hours_worked']) ?>
                    </p>
                  </div>

                </div>

              <?php endwhile; ?>
            <?php else: ?>
              <p class="text-center text-gray-500 py-5">No attendance records found.</p>
            <?php endif; ?>

          </div>

        </div>


        <!-- Footer -->
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