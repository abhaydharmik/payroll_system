<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');
date_default_timezone_set('Asia/Kolkata');

$sql = "
SELECT 
  s.id,
  u.name,
  s.month,
  s.basic,
  s.overtime_hours,
  s.overtime_rate,
  s.deductions,
  s.total,
  s.generated_at
FROM salaries s
JOIN users u ON s.user_id = u.id
ORDER BY s.generated_at DESC
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Salary Report</title>
<script src="https://cdn.tailwindcss.com"></script>

<style>
  @media print {
    .no-print { display: none !important; }
    body { background: white !important; }
  }
</style>
</head>

<body class="bg-gray-100 text-gray-800 p-4 md:p-10">

<div class="max-w-6xl mx-auto bg-white shadow rounded-2xl p-6 md:p-10 border">

  <!-- Header -->
  <div class="text-center border-b pb-4 mb-6">
    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Company Name Pvt. Ltd.</h1>
    <p class="text-gray-600">Employee Salary Report</p>
    <p class="text-sm text-gray-500 mt-1">
      Generated on: <?= date("d M Y, h:i A") ?>
    </p>
  </div>

  <!-- Print Button -->
  <div class="no-print text-center mb-6">
    <button onclick="window.print()" 
      class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg shadow-md">
      Print / Save as PDF
    </button>
  </div>

  <!-- Salary Table -->
  <div class="overflow-x-auto">
    <table class="w-full text-sm border border-gray-200 rounded-lg overflow-hidden">

      <thead class="bg-blue-600 text-white">
        <tr>
          <th class="px-4 py-3 text-left">Employee</th>
          <th class="px-4 py-3 text-left">Month</th>
          <th class="px-4 py-3 text-left">Basic</th>
          <th class="px-4 py-3 text-left">Overtime</th>
          <th class="px-4 py-3 text-left">Deductions</th>
          <th class="px-4 py-3 text-left">Total</th>
          <th class="px-4 py-3 text-left">Generated On</th>
        </tr>
      </thead>

      <tbody class="bg-white divide-y divide-gray-100">
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium text-gray-900">
              <?= htmlspecialchars($row['name']) ?>
            </td>

            <td class="px-4 py-3"><?= $row['month'] ?></td>

            <td class="px-4 py-3">₹<?= number_format($row['basic'], 2) ?></td>

            <td class="px-4 py-3">
              <?= $row['overtime_hours'] ?>h × ₹<?= number_format($row['overtime_rate'], 2) ?>
            </td>

            <td class="px-4 py-3 text-red-600">
              -₹<?= number_format($row['deductions'], 2) ?>
            </td>

            <td class="px-4 py-3 font-semibold text-green-600">
              ₹<?= number_format($row['total'], 2) ?>
            </td>

            <td class="px-4 py-3 text-gray-500">
              <?= date("d M Y, h:i A", strtotime($row['generated_at'])) ?>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>

    </table>
  </div>

  <p class="text-center text-gray-500 text-xs mt-6 border-t pt-4">
    © <?= date("Y") ?> Company Name Pvt. Ltd.
  </p>

</div>

<script>
  window.onload = () => window.print();
</script>

</body>
</html>
