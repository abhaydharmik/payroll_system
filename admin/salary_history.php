<?php
require '../config.php';
require '../includes/auth.php';
include '../includes/sidebar.php';
checkRole('admin');

$emp = $_SESSION['user'];

$sql = "SELECT s.id, u.name, s.month, s.basic, s.overtime_hours, s.overtime_rate, s.deductions, s.total, s.generated_at
        FROM salaries s 
        JOIN users u ON s.user_id=u.id 
        ORDER BY s.generated_at ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Salary History | Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 flex flex-col md:flex-row min-h-screen">

  <!-- Main Content -->
  <main class="flex-1 p-4 md:p-8 md:ml-64">
    <header class="bg-white shadow px-4 py-4 flex flex-col md:flex-row justify-between items-start md:items-center rounded">
      <h2 class="text-lg font-semibold text-gray-700 mb-2 md:mb-0">Salary History</h2>
      <div class="flex flex-col md:flex-row items-start md:items-center space-y-2 md:space-y-0 md:space-x-4">
        <span class="text-gray-700"><i class="fas fa-user-circle text-blue-600 mr-1"></i><?= htmlspecialchars($emp['name']); ?></span>
        <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm flex items-center">
          <i class="fas fa-sign-out-alt mr-1"></i>Logout
        </a>
      </div>
    </header>

    <div class="bg-white shadow-md rounded-lg p-4 md:p-6 mt-4">
      <div class="overflow-x-auto">
        <table class="w-full table-auto border-collapse min-w-[700px]">
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

      <div class="mt-6 text-center md:text-left">
        <a href="dashboard.php" class="text-blue-600 hover:underline flex items-center justify-center md:justify-start">
          <i class="fa-solid fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
      </div>
    </div>
  </main>

  <script src="../assets/js/script.js"></script>
</body>
</html>
