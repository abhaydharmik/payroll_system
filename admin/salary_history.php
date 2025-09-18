<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

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
  <title>Salary History</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
  <div class="max-w-6xl mx-auto p-6">
    <h2 class="text-3xl font-bold text-center text-indigo-600 mb-6">💰 Salary History</h2>

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
            <?php while($row = $result->fetch_assoc()): ?>
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

    <div class="mt-6 text-center">
      <a href="dashboard.php" class="px-5 py-2 bg-gray-600 text-white rounded-lg shadow hover:bg-gray-700 transition">
        ⬅ Back to Dashboard
      </a>
    </div>
  </div>
</body>
</html>
