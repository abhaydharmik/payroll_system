<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

$emp = $_SESSION['user'];
$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $user_id = $_POST['user_id'];
  $month = $_POST['month'];
  $basic = $_POST['basic'];
  $overtime_hours = $_POST['overtime_hours'];
  $overtime_rate = $_POST['overtime_rate'];
  $deductions = $_POST['deductions'];

  $total = $basic + ($overtime_hours * $overtime_rate) - $deductions;

  $stmt = $conn->prepare("INSERT INTO salaries 
        (user_id, month, basic, overtime_hours, overtime_rate, deductions, total) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("isddddd", $user_id, $month, $basic, $overtime_hours, $overtime_rate, $deductions, $total);

  $message = $stmt->execute() ? "✅ Salary generated successfully!" : "❌ Error: " . $stmt->error;
}

// Fetch employees
$employees = $conn->query("SELECT id, name FROM users WHERE role='employee'");


$sql = "SELECT s.id, u.name, s.month, s.basic, s.overtime_hours, s.overtime_rate, s.deductions, s.total, s.generated_at
        FROM salaries s 
        JOIN users u ON s.user_id=u.id 
        ORDER BY s.generated_at DESC";
$result = $conn->query($sql);

$pageTitle = "Salary";

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Generate Salary | Admin</title>
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
  <?php include '../includes/sidebar.php'; ?>

  <!-- Overlay for Mobile -->
  <div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden z-30 md:hidden"></div>

  <!-- Main Content -->
  <div class="flex-1 flex flex-col min-h-screen md:ml-64">

    <!-- Navbar -->
    <?php include_once '../includes/header.php'; ?>

    <!-- Page Content -->
    <main class="flex-1 pt-[4rem] px-4 md:px-8 pb-8">

      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0 mt-4 mb-4">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Salary Management</h2>
          <p class="text-gray-600">Manage employee salaries and payroll</p>
        </div>
      </div>

      <div class="max-w-7xl mx-auto w-full">
        <?php if ($message): ?>
          <div class="mb-4 p-3 rounded <?= str_contains($message, '✅') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
            <?= $message ?>
          </div>
        <?php endif; ?>

        <form method="post" class="space-y-4 bg-white p-4 rounded-lg shadow-sm">
          <div>
            <label class="block font-medium mb-1">Employee</label>
            <select name="user_id" required class="w-full border rounded p-2">
              <option value="">Select Employee</option>
              <?php while ($e = $employees->fetch_assoc()): ?>
                <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div>
            <label class="block font-medium mb-1">Month</label>
            <input type="text" name="month" placeholder="e.g. Sept 2025" required class="w-full border rounded p-2">
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block font-medium mb-1">Basic Salary</label>
              <input type="number" step="0.01" name="basic" required class="w-full border rounded p-2">
            </div>

            <div>
              <label class="block font-medium mb-1">Deductions</label>
              <input type="number" step="0.01" name="deductions" value="0" class="w-full border rounded p-2">
            </div>

            <div>
              <label class="block font-medium mb-1">Overtime Hours</label>
              <input type="number" step="0.01" name="overtime_hours" value="0" class="w-full border rounded p-2">
            </div>

            <div>
              <label class="block font-medium mb-1">Overtime Rate</label>
              <input type="number" step="0.01" name="overtime_rate" value="0" class="w-full border rounded p-2">
            </div>
          </div>

          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 w-full md:w-auto">
            Generate Salary
          </button>
        </form>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mt-6">
          <!-- Header -->
          <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Salary History</h3>
          </div>

          <!-- Table -->
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Basic</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Overtime</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deductions</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Generated</th>
                </tr>
              </thead>

              <tbody class="bg-white divide-y divide-gray-200 text-sm">
                <?php if ($result->num_rows > 0): $i = 1; ?>
                  <?php while ($row = $result->fetch_assoc()): ?>
                    <tr class="<?= $i % 2 == 0 ? 'bg-gray-50' : 'bg-white' ?> hover:bg-blue-50 transition">
                      <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900"><?= $i++ ?></td>
                      <td class="px-6 py-4 whitespace-nowrap text-gray-900"><?= htmlspecialchars($row['name']) ?></td>
                      <td class="px-6 py-4 whitespace-nowrap text-gray-900"><?= htmlspecialchars($row['month']) ?></td>
                      <td class="px-6 py-4 whitespace-nowrap text-gray-800">₹<?= number_format($row['basic'], 2) ?></td>
                      <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                        <?= $row['overtime_hours'] ?> hrs
                        <div class="text-xs text-gray-500">@ ₹<?= number_format($row['overtime_rate'], 2) ?></div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-red-600 font-medium">-₹<?= number_format($row['deductions'], 2) ?></td>
                      <td class="px-6 py-4 whitespace-nowrap text-green-600 font-semibold">₹<?= number_format($row['total'], 2) ?></td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= date("d M Y, h:i A", strtotime($row['generated_at'])) ?>
                      </td>
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

          <!-- Footer -->
          <div class="p-6 border-t border-gray-200">
            <a href="dashboard.php" class="text-blue-600 hover:text-blue-800 flex items-center text-sm md:text-base">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
              </svg>
              Back to Dashboard
            </a>
          </div>
        </div>



        <div class="mt-6 text-center md:text-left">
          <a href="dashboard.php" class="text-blue-600 hover:underline flex items-center justify-center md:justify-start">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg> Back to Dashboard
          </a>
        </div>
      </div>
    </main>
  </div>

  <script src="../assets/js/script.js"></script>
</body>

</html>