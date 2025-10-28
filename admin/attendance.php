<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

$emp = $_SESSION['user'];

// ✅ Fetch attendance with employee info (latest first)
$sql = "
SELECT 
  a.id, 
  u.name, 
  a.clock_in, 
  a.clock_out, 
  a.hours_worked,
  DATE_FORMAT(a.created_at, '%d %b %Y (%a)') as formatted_date
FROM attendance a
JOIN users u ON a.user_id = u.id
ORDER BY a.created_at DESC
";
$result = $conn->query($sql);
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

    <!-- ✅ Navbar -->
    <header class="fixed top-0 left-0 right-0 md:left-64 bg-white shadow flex justify-between items-center px-4 py-3 z-40">
      <div class="flex items-center space-x-3">
        <button id="sidebarToggle" class="md:hidden text-gray-700 focus:outline-none">
          <i class="fa-solid fa-bars text-xl"></i>
        </button>
        <h1 class="text-lg font-semibold text-gray-700">Attendance Records</h1>
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

      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0 mb-4">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Attendance Management</h2>
          <p class="text-gray-600">Monitor employee attendance, timings, and worked hours</p>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
            <h3 class="text-lg font-semibold text-gray-900">Employee Attendance</h3>
            <div class="flex space-x-2">
              <select class="px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option>This Week</option>
                <option>This Month</option>
                <option>Last Month</option>
              </select>
              <button class="bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition-all flex items-center space-x-2">
                <i class="fa-solid fa-file-export"></i>
                <span>Export</span>
              </button>
            </div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Clock In</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Clock Out</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Worked Hours</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
              <?php if ($result && $result->num_rows > 0): ?>
                <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
                  <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-gray-500"><?= $i++ ?></td>
                    <td class="px-6 py-4 font-medium text-gray-900"><?= htmlspecialchars($row['name']) ?></td>
                    <td class="px-6 py-4 text-gray-700"><?= $row['formatted_date'] ?></td>
                    <td class="px-6 py-4 text-gray-700">
                      <?= $row['clock_in'] ? date("h:i A", strtotime($row['clock_in'])) : '-' ?>
                    </td>
                    <td class="px-6 py-4 text-gray-700">
                      <?= $row['clock_out'] ? date("h:i A", strtotime($row['clock_out'])) : '-' ?>
                    </td>
                    <td class="px-6 py-4 text-gray-800 font-semibold">
                      <?= $row['hours_worked'] ?: '-' ?>
                    </td>
                    <td class="px-6 py-4">
                      <?php if (!$row['clock_in']): ?>
                        <span class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded-full">Absent</span>
                      <?php elseif ($row['clock_in'] && !$row['clock_out']): ?>
                        <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded-full">In Progress</span>
                      <?php else: ?>
                        <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-full">Present</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" class="text-center text-gray-500 py-4">
                    No attendance records found.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <div class="p-6 border-t border-gray-200">
          <a href="dashboard.php" class="text-blue-600 hover:text-blue-800 flex items-center text-sm">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back to Dashboard
          </a>
        </div>
      </div>

    </main>
  </div>

  <script src="../assets/js/script.js"></script>
</body>
</html>
