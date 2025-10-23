<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'employee') {
  header('Location: ../index.php');
  exit;
}

$emp = $_SESSION['user'];
$user_id = $emp['id'];
$name = htmlspecialchars($emp['name']);

// ✅ Fetch salary records
$stmt = $conn->prepare("SELECT month, basic, deductions, total, generated_at FROM salaries WHERE user_id = ? ORDER BY generated_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Salary Report - <?= $name ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @media print {
      .no-print { display: none; }
      body { background: white; }
    }
  </style>
</head>
<body class="bg-gray-50 text-gray-800 p-4 md:p-10 font-sans">

  <div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-lg p-6 md:p-10 border border-gray-100">

    <!-- Header -->
    <div class="flex flex-col md:flex-row items-center justify-between border-b border-gray-200 pb-4 mb-6">
      <div class="flex items-center gap-3">
        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135673.png" alt="Logo" class="w-12 h-12">
        <div>
          <h1 class="text-2xl font-semibold text-gray-800">Company Name Pvt. Ltd.</h1>
          <p class="text-sm text-gray-500">Employee Payroll Management System</p>
        </div>
      </div>
      <div class="text-right mt-4 md:mt-0">
        <p class="text-sm text-gray-500">Generated on:</p>
        <p class="text-sm font-medium"><?= date("d M Y, h:i A") ?></p>
      </div>
    </div>

    <!-- Title -->
    <div class="text-center mb-6">
      <h2 class="text-2xl font-bold text-gray-800 mb-1">Salary Report</h2>
      <p class="text-gray-600">Employee: <span class="font-semibold text-gray-800"><?= $name ?></span></p>
    </div>

    <!-- Print Button -->
    <div class="no-print text-center mb-6">
      <button 
        onclick="window.print()" 
        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg shadow-md transition-all duration-200">
        🖨️ Print / Save as PDF
      </button>
    </div>

    <!-- Salary Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full border border-gray-200 text-sm rounded-lg overflow-hidden">
        <thead class="bg-gradient-to-r from-blue-600 to-blue-500 text-white text-left">
          <tr>
            <th class="py-3 px-4">Month</th>
            <th class="py-3 px-4 text-center">Basic Pay</th>
            <th class="py-3 px-4 text-center">Deductions</th>
            <th class="py-3 px-4 text-center">Net Pay</th>
            <th class="py-3 px-4 text-center">Generated On</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-100">
          <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr class="hover:bg-gray-50 transition">
                <td class="py-3 px-4 font-medium text-gray-800"><?= htmlspecialchars($row['month'] ?? '-') ?></td>
                <td class="py-3 px-4 text-center text-gray-700">₹<?= number_format($row['basic'] ?? 0, 2) ?></td>
                <td class="py-3 px-4 text-center text-red-600">₹<?= number_format($row['deductions'] ?? 0, 2) ?></td>
                <td class="py-3 px-4 text-center font-semibold text-green-700">₹<?= number_format($row['total'] ?? 0, 2) ?></td>
                <td class="py-3 px-4 text-center text-gray-600"><?= htmlspecialchars($row['generated_at'] ?? '-') ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="py-6 text-center text-gray-500">No salary records found for your account.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Footer -->
    <div class="text-center text-gray-500 text-xs mt-6 border-t border-gray-200 pt-4">
      <p>© <?= date("Y") ?> Company Name Pvt. Ltd. | Payroll Report System</p>
    </div>
  </div>

</body>
</html>
