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
  <title>Salary History | Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 flex">

  <body class="bg-gray-100 flex">

    <!-- Mobile Header -->
    <header class="fixed top-0 left-0 right-0 bg-white shadow-md flex items-center justify-between px-4 py-3 md:hidden z-50">
      <h1 class="text-lg font-bold text-gray-800 flex items-center">
        <i class="fa-solid fa-chart-line mr-2"></i> Admin Panel
      </h1>
      <button id="sidebarToggle" class="text-gray-800 text-2xl focus:outline-none">
        <i class="fa-solid fa-bars"></i>
      </button>
    </header>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-white flex flex-col transform -translate-x-full md:translate-x-0 transition-transform duration-300 z-40">
      <!-- <div id="hidden" class="p-6 border-b border-blue-700">
            <h1 class="text-2xl font-bold flex items-center">
                <i class="fa-solid fa-chart-line mr-2"></i> Admin Panel
            </h1>
        </div> -->
      <nav class="flex-1 px-4 py-7 mt-10 space-y-2 overflow-y-auto">
        <a href="dashboard.php" class="block py-2 px-3 flex items-center rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bar-chart3 w-5 h-5 mr-2">
            <path d="M3 3v18h18"></path>
            <path d="M18 17V9"></path>
            <path d="M13 17V5"></path>
            <path d="M8 17v-3"></path>
          </svg> Dashboard</a>
        <a href="employees.php" class="block py-2 px-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 flex items-center">
          <!-- <i class="fa-solid fa-users mr-2"></i> -->
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users w-5 h-5 mr-2">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
          </svg>
          Employees
        </a>
        <a href="attendance.php" class="block py-2 px-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900">
          <i class="fa-solid fa-calendar-check mr-2"></i> Attendance
        </a>
        <a href="leaves.php" class="block py-2 px-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900">
          <i class="fa-solid fa-file-signature mr-2"></i> Leaves
        </a>
        <a href="generate_salary.php" class="block py-2 px-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900">
          <i class="fa-solid fa-sack-dollar mr-2"></i> Generate Salary
        </a>
        <a href="salary_history.php" class="block py-2 px-3 rounded-lg bg-blue-50 text-blue-600 border border-blue-200">
          <i class="fa-solid fa-file-invoice-dollar mr-2"></i> Salary History
        </a>
        <a href="departments.php" class="block py-2 px-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900">
          <i class="fa-solid fa-building mr-2"></i> Departments
        </a>
        <a href="designations.php" class="block py-2 px-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900">
          <i class="fa-solid fa-briefcase mr-2"></i> Designations
        </a>
      </nav>
      <div class="p-4 border-t mt-4 border-blue-700">
        <p class="text-sm">&copy; <?php echo date("Y"); ?> <span class="font-semibold">Payroll System</span>. All rights reserved.</p>
      </div>
    </aside>

    <!-- Overlay for mobile -->
    <div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden z-30 md:hidden"></div>

    <!-- Main Content -->
    <main class="flex-1 ml-64 p-8">
      <header class="bg-white shadow px-6 py-4 flex justify-between items-center rounded">
        <h2 class="text-lg font-semibold text-gray-700">Salary History</h2>
        <div class="flex items-center space-x-4">
          <span class="text-gray-700"><i class="fas fa-user-circle text-blue-600 mr-1"></i><?php echo htmlspecialchars($emp['name']); ?></span>
          <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm"><i class="fas fa-sign-out-alt mr-1"></i>Logout</a>
        </div>
      </header>
      <div class="bg-white shadow-md rounded-lg p-6 mt-4">
        <!-- <h2 class="text-2xl font-bold text-gray-700 mb-6 flex items-center">
        <i class="fa-solid fa-file-invoice-dollar text-blue-600 mr-2"></i> Salary History
      </h2> -->

        <div class="overflow-x-auto bg-white shadow-lg rounded-2xl">
          <table class="w-full table-auto border-collapse">
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
                  <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3"><?= $row['id'] ?></td>
                    <td class="px-4 py-3 font-medium text-gray-700"><?= htmlspecialchars($row['name']) ?></td>
                    <td class="px-4 py-3"><?= htmlspecialchars($row['month']) ?></td>
                    <td class="px-4 py-3 text-gray-800">₹<?= number_format($row['basic'], 2) ?></td>
                    <td class="px-4 py-3 text-gray-600">
                      <?= $row['overtime_hours'] ?> hrs
                      <br><span class="text-sm text-gray-500">@ ₹<?= number_format($row['overtime_rate'], 2) ?></span>
                    </td>
                    <td class="px-4 py-3 text-red-600">-₹<?= number_format($row['deductions'], 2) ?></td>
                    <td class="px-4 py-3 font-bold text-green-600">₹<?= number_format($row['total'], 2) ?></td>
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

        <div class="mt-6">
          <a href="dashboard.php" class="text-blue-600 hover:underline flex items-center">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back to Dashboard
          </a>
        </div>
      </div>
    </main>
  </body>

</html>