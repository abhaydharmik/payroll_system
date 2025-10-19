<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

$emp = $_SESSION['user'];

// Stats
$totalEmployees = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='employee'")->fetch_assoc()['total'];
$presentToday   = $conn->query("SELECT COUNT(*) as present FROM attendance WHERE date=CURDATE() AND status='Present'")->fetch_assoc()['present'];
$pendingLeaves  = $conn->query("SELECT COUNT(*) as leaves FROM leaves WHERE status='Pending'")->fetch_assoc()['leaves'];

$currentMonth = date('F Y');
$monthlyPayroll = $conn->query("SELECT COALESCE(SUM(total),0) as total FROM salaries WHERE month='$currentMonth'")->fetch_assoc()['total'];

$activities = $conn->query("SELECT action, user_name, created_at FROM activities ORDER BY created_at DESC LIMIT 5");

$departments = $conn->query("
    SELECT d.name, COUNT(u.id) as employees, COALESCE(SUM(s.total),0) as payroll
    FROM departments d
    LEFT JOIN users u ON u.department_id=d.id
    LEFT JOIN salaries s ON s.user_id=u.id AND s.month='$currentMonth'
    GROUP BY d.id
");
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

    /* Sidebar hidden only on mobile by default */
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
    <header class="fixed top-0 left-0 right-0 md:left-64 bg-white shadow flex justify-between items-center px-4 py-3 z-40">
      <div class="flex items-center space-x-3">
        <!-- Mobile menu button -->
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
          <i class="fas fa-sign-out-alt text-lg"></i>
        </a>
      </div>
    </header>

    <!-- Page Content -->
    <main class="flex-1 pt-20 px-4 md:px-8 pb-8">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-5 flex items-center justify-between border-l-4 border-blue-600">
          <div>
            <h3 class="text-gray-500 text-sm">Total Employees</h3>
            <p class="text-2xl font-bold"><?= $totalEmployees ?></p>
          </div>
          <i class="fa-solid fa-users text-blue-600 text-3xl"></i>
        </div>
        <div class="bg-white rounded-lg shadow p-5 flex items-center justify-between border-l-4 border-green-600">
          <div>
            <h3 class="text-gray-500 text-sm">Present Today</h3>
            <p class="text-2xl font-bold"><?= $presentToday ?></p>
          </div>
          <i class="fa-solid fa-calendar-check text-green-600 text-3xl"></i>
        </div>
        <div class="bg-white rounded-lg shadow p-5 flex items-center justify-between border-l-4 border-yellow-600">
          <div>
            <h3 class="text-gray-500 text-sm">Pending Leaves</h3>
            <p class="text-2xl font-bold"><?= $pendingLeaves ?></p>
          </div>
          <i class="fa-solid fa-file-signature text-yellow-600 text-3xl"></i>
        </div>
        <div class="bg-white rounded-lg shadow p-5 flex items-center justify-between border-l-4 border-purple-600">
          <div>
            <h3 class="text-gray-500 text-sm">Monthly Payroll</h3>
            <p class="text-2xl font-bold">₹<?= number_format($monthlyPayroll / 1000, 1) ?>K</p>
          </div>
          <i class="fa-solid fa-sack-dollar text-purple-600 text-3xl"></i>
        </div>
      </div>

      <!-- Bottom Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Activities -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-bold mb-4">Recent Activities</h3>
          <ul class="space-y-4">
            <?php if ($activities && $activities->num_rows > 0): ?>
              <?php while ($a = $activities->fetch_assoc()): ?>
                <li class="flex items-start space-x-3">
                  <div class="bg-blue-100 text-blue-600 p-2 rounded-full">
                    <i class="fa-solid fa-user"></i>
                  </div>
                  <div>
                    <p class="font-medium"><?= htmlspecialchars($a['action']) ?></p>
                    <p class="text-sm text-gray-500">
                      <?= htmlspecialchars($a['user_name']) ?> • <?= date("M d, H:i", strtotime($a['created_at'])) ?>
                    </p>
                  </div>
                </li>
              <?php endwhile; ?>
            <?php else: ?>
              <p class="text-gray-500">No recent activities.</p>
            <?php endif; ?>
          </ul>
        </div>

        <!-- Department Overview -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-bold mb-4">Department Overview</h3>
          <ul class="divide-y">
            <?php while ($d = $departments->fetch_assoc()): ?>
              <li class="flex justify-between py-2">
                <div>
                  <p class="font-medium"><?= htmlspecialchars($d['name']) ?></p>
                  <p class="text-sm text-gray-500"><?= $d['employees'] ?> employees</p>
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
  <!-- <script>
    const sidebar = document.getElementById("sidebar");
    const toggleBtn = document.getElementById("sidebarToggle");
    const overlay = document.getElementById("overlay");

    toggleBtn.addEventListener("click", () => {
      sidebar.classList.toggle("mobile-hidden");
      overlay.classList.toggle("hidden");
    });

    overlay.addEventListener("click", () => {
      sidebar.classList.add("mobile-hidden");
      overlay.classList.add("hidden");
    });
  </script> -->
</body>

</html>