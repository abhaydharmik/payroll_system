<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

$emp = $_SESSION['user'];

// Stats
$totalEmployees = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='employee'")->fetch_assoc()['total'];
$presentToday   = $conn->query("SELECT COUNT(*) as present FROM attendance WHERE date=CURDATE() AND status='Present'")->fetch_assoc()['present'];
$pendingLeaves  = $conn->query("SELECT COUNT(*) as leaves FROM leaves WHERE status='Pending'")->fetch_assoc()['leaves'];

// ✅ FIXED: Monthly Payroll (auto detects current month from generated_at column)
$monthlyPayrollQuery = $conn->query("
    SELECT COALESCE(SUM(total), 0) AS total
    FROM salaries
    WHERE MONTH(generated_at) = MONTH(CURDATE())
    AND YEAR(generated_at) = YEAR(CURDATE())
");
$monthlyPayroll = $monthlyPayrollQuery->fetch_assoc()['total'];

$currentMonth = date('F Y');

$activities = $conn->query("
    SELECT a.*, u.name AS user_name 
    FROM activities a
    LEFT JOIN users u ON a.user_id = u.id
    ORDER BY a.created_at DESC
    LIMIT 10
");

$departments = $conn->query("
    SELECT 
        d.name, 
        COUNT(DISTINCT u.id) AS employees, 
        COALESCE(SUM(s.total), 0) AS payroll
    FROM departments d
    LEFT JOIN users u ON u.department_id = d.id
    LEFT JOIN salaries s ON s.user_id = u.id 
        AND MONTH(s.generated_at) = MONTH(CURDATE()) 
        AND YEAR(s.generated_at) = YEAR(CURDATE())
    GROUP BY d.id
");

$pageTitle = "Dashboard"

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard | Payroll System</title>
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

    /* Smooth hover transition globally */
    .smooth {
      transition: all 0.25s ease;
    }

    /* Premium scrollbar */
    .scroll-area::-webkit-scrollbar {
      width: 6px;
    }

    .scroll-area::-webkit-scrollbar-thumb {
      background: #d1d5db;
      border-radius: 9999px;
    }

    .scroll-area::-webkit-scrollbar-thumb:hover {
      background: #9ca3af;
    }
  </style>
</head>

<body class="bg-gray-100">

  <!-- SIDEBAR -->
  <?php include_once '../includes/sidebar.php'; ?>

  <!-- Overlay for Mobile -->
  <div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden z-30 md:hidden"></div>

  <!-- MAIN CONTENT -->
  <div class="flex-1 flex flex-col min-h-screen md:ml-64">
    <!-- NAVBAR -->
    <?php include_once '../includes/header.php'; ?>

    <!-- Page Content -->
    <main class="flex-1 pt-20 px-4 md:px-8 pb-8">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm p-6 flex items-center justify-between border border-gray-200">
          <div>
            <h3 class="text-gray-500 text-sm mb-1">Total Employees</h3>
            <p class="text-3xl font-bold"><?= $totalEmployees ?></p>
          </div>
          <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
              <circle cx="9" cy="7" r="4"></circle>
              <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 flex items-center justify-between border border-gray-200">
          <div>
            <h3 class="text-gray-500 text-sm mb-1">Present Today</h3>
            <p class="text-3xl font-bold"><?= $presentToday ?></p>
          </div>
          <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M8 2v4"></path>
              <path d="M16 2v4"></path>
              <rect width="18" height="18" x="3" y="4" rx="2"></rect>
              <path d="M3 10h18"></path>
            </svg>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 flex items-center justify-between border border-gray-200">
          <div>
            <h3 class="text-gray-500 text-sm mb-1">Pending Leaves</h3>
            <p class="text-3xl font-bold"><?= $pendingLeaves ?></p>
          </div>
          <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
              <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
              <path d="M10 9H8"></path>
              <path d="M16 13H8"></path>
              <path d="M16 17H8"></path>
            </svg>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 flex items-center justify-between border border-gray-200">
          <div>
            <h3 class="text-gray-500 text-sm mb-1">Monthly Payroll</h3>
            <p class="text-3xl font-bold">₹<?= number_format($monthlyPayroll / 1000, 1) ?>K</p>
            <p class="text-xs text-gray-500"><?= $currentMonth ?></p>
          </div>
          <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <line x1="12" x2="12" y1="2" y2="22"></line>
              <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>
          </div>
        </div>
      </div>

      <!-- Bottom Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        <!-- Recent Activities -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-[1.6rem]">
          <div class="flex items-center justify-between mb-3">
            <h2 class="text-base font-semibold text-gray-900">Recent Activities</h2>
            <span class="text-[11px] px-2 py-1 rounded bg-blue-50 text-blue-600">Top <?= $activities->num_rows ?> records</span>
          </div>

          <?php if ($activities->num_rows > 0): ?>
            <div class="space-y-3 max-h-[23rem] overflow-y-auto scroll-area">
              <?php while ($a = $activities->fetch_assoc()): ?>
                <div class="p-3 bg-gray-50 rounded-md hover:bg-gray-100 transition-colors">
                  <p class="text-sm font-medium text-gray-800">
                    <?= htmlspecialchars($a['description']) ?>
                  </p>
                  <p class="text-xs text-gray-500 flex items-center mt-1">
                    <i class="fa-regular fa-clock mr-1"></i>
                    <?= date('d M Y, h:i A', strtotime($a['created_at'])) ?>
                  </p>
                </div>
              <?php endwhile; ?>
            </div>
          <?php else: ?>
            <div class="text-center py-6 text-gray-500">
              <i class="fa-regular fa-face-meh text-2xl mb-1"></i>
              <p class="text-sm">No recent activities found.</p>
            </div>
          <?php endif; ?>
        </div>

        <!-- Department Overview -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-[1.6rem]">
          <h3 class="text-base font-semibold text-gray-900 mb-3">Department Overview</h3>

          <?php if ($departments->num_rows > 0): ?>
            <div class="space-y-3">
              <?php while ($d = $departments->fetch_assoc()): ?>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md hover:bg-gray-100 transition-all">
                  <div>
                    <p class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($d['name']) ?></p>
                    <p class="text-xs text-gray-500"><?= $d['employees'] ?> employees</p>
                  </div>
                  <p class="text-sm font-bold text-green-600">
                    ₹<?= number_format($d['payroll'] / 1000) ?>K
                  </p>
                </div>
              <?php endwhile; ?>
            </div>
          <?php else: ?>
            <div class="text-center py-6 text-gray-500">
              <i class="fa-solid fa-building text-2xl mb-1"></i>
              <p class="text-sm">No departments found.</p>
            </div>
          <?php endif; ?>
        </div>

      </div>

    </main>
  </div>

  <script src="../assets/js/script.js"></script>
</body>

</html>