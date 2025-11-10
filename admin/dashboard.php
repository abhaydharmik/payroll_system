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
    LIMIT 5
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
    <!-- <header class="fixed top-0 left-0 right-0 md:left-64 bg-white shadow flex justify-between items-center px-4 py-3 z-40">
      <div class="flex items-center space-x-3">
        <button id="sidebarToggle" class="md:hidden text-gray-700 focus:outline-none">
          <i class="fa-solid fa-bars text-xl"></i>
        </button>
        <h1 class="text-lg font-semibold text-gray-700">Admin Dashboard</h1>
      </div>
      <div class="flex items-center space-x-3">
        <span class="text-gray-700 flex items-center">
          <i class="fas fa-user-circle text-blue-600 mr-1"></i>
          <?php echo htmlspecialchars($emp['name']); ?>
        </span>
        <a href="../logout.php" class="text-red-600 hover:text-red-800">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-log-out w-5 h-5">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16 17 21 12 16 7"></polyline>
            <line x1="21" x2="9" y1="12" y2="12"></line>
          </svg>
        </a>
      </div>
    </header> -->

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
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Activities -->
        <div class="bg-white rounded-xl shadow p-6">
          <h2 class="text-lg font-semibold mb-4 text-gray-800">Recent Activities</h2>

          <?php if ($activities->num_rows > 0): ?>
            <ul>
              <?php while ($a = $activities->fetch_assoc()): ?>
                <li class="py-3 mb-2">
                  <p class="text-gray-800 font-semibold"><?= htmlspecialchars($a['description']) ?></p>
                  <p class="text-sm text-gray-500">
                    <?= date('d M Y, h:i A', strtotime($a['created_at'])) ?>
                  </p>
                </li>
              <?php endwhile; ?>
            </ul>
          <?php else: ?>
            <p class="text-gray-500">No recent activities yet.</p>
          <?php endif; ?>
        </div>


        <!-- Department Overview -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold mb-4">Department Overview</h3>
          <ul>
            <?php while ($d = $departments->fetch_assoc()): ?>
              <li class="flex justify-between py-2 mb-2 ">
                <div>
                  <p class="font-medium "><?= htmlspecialchars($d['name']) ?></p>
                  <p class="text-sm text-gray-500 text-sm"><?= $d['employees'] ?> employees</p>
                </div>
                <p class="font-semibold text-gray-700">₹<?= number_format($d['payroll'] / 1000) ?>K</p>
              </li>
            <?php endwhile; ?>
          </ul>
        </div>
      </div>
    </main>
  </div>

  <script src="../assets/js/script.js"></script>
</body>

</html>