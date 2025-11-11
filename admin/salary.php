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

  if ($stmt->execute()) {

    // ✅ Fetch employee name
    $getName = $conn->prepare("SELECT name FROM users WHERE id=?");
    $getName->bind_param("i", $user_id);
    $getName->execute();
    $getName->bind_result($empName);
    $getName->fetch();
    $getName->close();

    // ✅ Log activity (who generated the salary)
    $adminId = $_SESSION['user']['id'];
    $adminName = $_SESSION['user']['name'];
    $activity_desc = "Generated salary for $empName ($month)";

    $log = $conn->prepare("INSERT INTO activities (user_id, description, user_name, created_at) VALUES (?, ?, ?, NOW())");
    $log->bind_param("iss", $adminId, $activity_desc, $adminName);
    $log->execute();

    $message = "✅ Salary generated successfully!";
  } else {
    $message = "❌ Error: " . $stmt->error;
  }
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
    <?php include '../includes/header.php'; ?>

    <main class="flex-1 pt-[4rem] px-4 md:px-8 pb-8">

      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-2 sm:space-y-0 mb-6">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Salary Management</h2>
          <p class="text-gray-600 text-sm">Generate and manage employee salary records</p>
        </div>
      </div>

      <div class="max-w-7xl mx-auto w-full">

        <!-- Message -->
        <?php if ($message): ?>
          <div class="mb-4 p-3 rounded <?= str_contains($message, '✅') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
            <?= $message ?>
          </div>
        <?php endif; ?>

        <!-- Salary Form -->
        <form method="post" class="space-y-4 bg-white p-5 rounded-xl shadow-sm border border-gray-200">

          <div>
            <label class="block font-medium mb-1">Employee</label>
            <select name="user_id" required class="w-full border rounded-xl p-2 focus:ring-2 focus:ring-blue-500">
              <option value="">Select Employee</option>
              <?php while ($e = $employees->fetch_assoc()): ?>
                <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div>
            <label class="block font-medium mb-1">Month</label>
            <input type="text" name="month" placeholder="e.g., Jan 2025" required class="w-full border rounded-xl p-2 focus:ring-2 focus:ring-blue-500">
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block font-medium mb-1">Basic Salary</label>
              <input type="number" step="0.01" name="basic" required class="w-full border rounded-xl p-2">
            </div>

            <div>
              <label class="block font-medium mb-1">Deductions</label>
              <input type="number" step="0.01" name="deductions" value="0" class="w-full border rounded-xl p-2">
            </div>

            <div>
              <label class="block font-medium mb-1">Overtime Hours</label>
              <input type="number" step="0.01" name="overtime_hours" value="0" class="w-full border rounded-xl p-2">
            </div>

            <div>
              <label class="block font-medium mb-1">Overtime Rate</label>
              <input type="number" step="0.01" name="overtime_rate" value="0" class="w-full border rounded-xl p-2">
            </div>
          </div>

          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl transition">
            Generate Salary
          </button>
        </form>

        <!-- Salary History -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mt-6">
          <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Salary History</h3>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Month</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Basic</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Overtime</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deductions</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Generated</th>
                </tr>
              </thead>

              <tbody class="divide-y">
                <?php $i = 1;
                if ($result->num_rows > 0): ?>
                  <?php while ($row = $result->fetch_assoc()): ?>
                    <tr class="hover:bg-blue-50 transition">
                      <td class="px-6 py-4 font-medium"><?= $i++ ?></td>
                      <td class="px-6 py-4"><?= $row['name'] ?></td>
                      <td class="px-6 py-4"><?= $row['month'] ?></td>
                      <td class="px-6 py-4">₹<?= number_format($row['basic'], 2) ?></td>
                      <td class="px-6 py-4"><?= $row['overtime_hours'] ?> hrs @ ₹<?= number_format($row['overtime_rate'], 2) ?></td>
                      <td class="px-6 py-4 text-red-600">-₹<?= number_format($row['deductions'], 2) ?></td>
                      <td class="px-6 py-4 text-green-600 font-semibold">₹<?= number_format($row['total'], 2) ?></td>
                      <td class="px-6 py-4 text-gray-500"><?= date("d M Y, h:i A", strtotime($row['generated_at'])) ?></td>
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

          <div class="p-6 border-t border-gray-200">
            <a href="dashboard.php" class="text-blue-600 hover:text-blue-800 flex items-center text-sm">
              <i class="fa-solid fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
          </div>
        </div>

      </div>
    </main>
  </div>

  <script src="../assets/js/script.js"></script>
</body>

</html>