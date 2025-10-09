<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

$emp = $_SESSION['user'];

$sql = "SELECT s.id, u.name, s.month, s.basic, s.overtime_hours, s.overtime_rate, s.deductions, s.total, s.generated_at
        FROM salaries s 
        JOIN users u ON s.user_id=u.id 
        ORDER BY s.generated_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Salary History | Payroll System</title>
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
<body class="bg-gray-100 flex">

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
        <h1 class="text-lg font-semibold text-gray-700">Salary History</h1>
      </div>
      <div class="flex items-center space-x-3">
        <span class="text-gray-700 flex items-center">
          <i class="fas fa-user-circle text-blue-600 mr-1"></i>
          <?= htmlspecialchars($emp['name']); ?>
        </span>
        <a href="../logout.php" class="text-red-600 hover:text-red-800">
          <i class="fas fa-sign-out-alt text-lg"></i>
        </a>
      </div>
    </header>

    <!-- Page Content -->
    <main class="flex-1 pt-20 px-4 md:px-8 pb-8">
      <div class="bg-white shadow-md rounded-lg p-4 md:p-6">
        <div class="overflow-x-auto">
          <table class="w-full table-auto border-collapse min-w-[750px]">
            <thead class="bg-indigo-600 text-white">
              <tr>
                <th class="px-4 py-3 text-left">ID</th>
                <th class="px-4 py-3 text-left">Employee</th>
                <th class="px-4 py-3 text-left">Month</th>
                <th class="px-4 py-3 text-left">Basic</th>
                <th class="px-4 py-3 text-left">Overtime</th>
                <th class="px-4 py-3 text-left">Deductions</th>
                <th class="px-4 py-3 text-left">Total</th>
                <th class="px-4 py-3 text-left">Generated</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                  <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3"><?= $row['id'] ?></td>
                    <td class="px-4 py-3 font-medium text-gray-800"><?= htmlspecialchars($row['name']) ?></td>
                    <td class="px-4 py-3"><?= htmlspecialchars($row['month']) ?></td>
                    <td class="px-4 py-3 text-gray-800">₹<?= number_format($row['basic'], 2) ?></td>
                    <td class="px-4 py-3 text-gray-700">
                      <?= $row['overtime_hours'] ?> hrs
                      <div class="text-sm text-gray-500">@ ₹<?= number_format($row['overtime_rate'], 2) ?></div>
                    </td>
                    <td class="px-4 py-3 text-red-600 font-medium">-₹<?= number_format($row['deductions'], 2) ?></td>
                    <td class="px-4 py-3 text-green-600 font-semibold">₹<?= number_format($row['total'], 2) ?></td>
                    <td class="px-4 py-3 text-sm text-gray-500"><?= date("d M Y, h:i A", strtotime($row['generated_at'])) ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" class="text-center py-6 text-gray-500">No salary records found.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Back to Dashboard -->
        <div class="mt-6 text-center md:text-left">
          <a href="dashboard.php" class="text-blue-600 hover:underline flex items-center justify-center md:justify-start">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back to Dashboard
          </a>
        </div>
      </div>
    </main>
  </div>

  <script src="../assets/js/script.js"></script>
</body>
</html>
