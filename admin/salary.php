<?php
// admin/salary.php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

$emp = $_SESSION['user'] ?? ['id' => 0, 'name' => 'Admin'];
$message = "";

// Handle form submission (server-side validation + prepared statements)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['month'])) {
  $user_id = intval($_POST['user_id']);
  $month = trim($_POST['month']);
  $basic = floatval($_POST['basic'] ?? 0);
  $overtime_hours = floatval($_POST['overtime_hours'] ?? 0);
  $overtime_rate = floatval($_POST['overtime_rate'] ?? 0);
  $deductions = floatval($_POST['deductions'] ?? 0);

  // simple validation
  if ($user_id <= 0 || $month === '' || $basic < 0) {
    $message = "Please provide valid inputs.";
  } else {
    $total = $basic + ($overtime_hours * $overtime_rate) - $deductions;
    $stmt = $conn->prepare("INSERT INTO salaries (user_id, month, basic, overtime_hours, overtime_rate, deductions, total, generated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("isddddd", $user_id, $month, $basic, $overtime_hours, $overtime_rate, $deductions, $total);

    if ($stmt->execute()) {
      // fetch employee name for activity log
      $getName = $conn->prepare("SELECT name FROM users WHERE id=? LIMIT 1");
      $getName->bind_param("i", $user_id);
      $getName->execute();
      $getName->bind_result($empName);
      $getName->fetch();
      $getName->close();

      // log activity
      $adminId = $_SESSION['user']['id'];
      $activity_desc = "Generated salary for " . ($empName ?? "Employee") . " ($month)";
      $log = $conn->prepare("INSERT INTO activities (user_id, description, user_name, created_at) VALUES (?, ?, ?, NOW())");
      $adminName = $_SESSION['user']['name'];
      $log->bind_param("iss", $adminId, $activity_desc, $adminName);
      $log->execute();

      $message = "Salary generated successfully.";
    } else {
      $message = "Error generating salary: " . $stmt->error;
    }
  }
}

// get employees (for select)
$employees = $conn->query("SELECT id, name FROM users WHERE role='employee' ORDER BY name");

// get salary history (recent first)
$sql = "SELECT s.id, u.name, s.month, s.basic, s.overtime_hours, s.overtime_rate, s.deductions, s.total, s.generated_at
        FROM salaries s 
        JOIN users u ON s.user_id=u.id 
        ORDER BY s.generated_at DESC";
$res = $conn->query($sql);
$salaryRows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Salary Management | Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-gray-100">

  <?php include '../includes/sidebar.php'; ?>

  <div class="flex-1 flex flex-col min-h-screen md:ml-64">
    <?php include '../includes/header.php'; ?>

    <main class="flex-1 pt-20 px-4 md:px-8 pb-8">
      <div class="max-w-7xl mx-auto">

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
          <div>
            <h2 class="text-2xl font-bold text-gray-900">Salary Management</h2>
            <p class="text-gray-600">Generate and manage employee salary records</p>
          </div>
          <div class="flex items-center gap-3">
            <a href="salary_report_admin.php" target="_blank" class="bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 flex items-center gap-2">
              <i class="fa-solid fa-file-pdf"></i>
              <span>Download PDF</span>
            </a>
          </div>
        </div>

        <!-- Message -->
        <?php if ($message): ?>
          <div class="mb-4 p-3 rounded <?= strpos($message,'successfully') !== false ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800' ?>">
            <?= htmlspecialchars($message) ?>
          </div>
        <?php endif; ?>

        <!-- Salary Form -->
        <form method="post" class="bg-white rounded-xl p-5 card-shadow border border-gray-200 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
              <select name="user_id" required class="w-full border rounded-xl p-2 focus:ring-2 focus:ring-blue-500">
                <option value="">Select employee</option>
                <?php if ($employees): while ($e = $employees->fetch_assoc()): ?>
                  <option value="<?= intval($e['id']) ?>"><?= htmlspecialchars($e['name']) ?></option>
                <?php endwhile; endif; ?>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Month (label)</label>
              <input name="month" type="text" placeholder="e.g., Jan 2025" required class="w-full border rounded-xl p-2" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Basic Salary (₹)</label>
              <input name="basic" type="number" step="0.01" min="0" value="0" required class="w-full border rounded-xl p-2" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Overtime Hours</label>
              <input name="overtime_hours" type="number" step="0.01" min="0" value="0" class="w-full border rounded-xl p-2" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Overtime Rate (₹/hr)</label>
              <input name="overtime_rate" type="number" step="0.01" min="0" value="0" class="w-full border rounded-xl p-2" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Deductions (₹)</label>
              <input name="deductions" type="number" step="0.01" min="0" value="0" class="w-full border rounded-xl p-2" />
            </div>
          </div>

          <div class="mt-4">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700">Generate Salary</button>
          </div>
        </form>

        <!-- DESKTOP: Salary History Table -->
        <div class="hidden md:block bg-white rounded-xl card-shadow border border-gray-200 overflow-x-auto">
          <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Salary History</h3>
          </div>

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
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Generated At</th>
              </tr>
            </thead>

            <tbody class="divide-y">
              <?php $i=1; foreach ($salaryRows as $row): ?>
                <tr class="hover:bg-gray-50">
                  <td class="px-6 py-4 font-medium"><?= $i++ ?></td>
                  <td class="px-6 py-4"><?= htmlspecialchars($row['name']) ?></td>
                  <td class="px-6 py-4"><?= htmlspecialchars($row['month']) ?></td>
                  <td class="px-6 py-4">₹<?= number_format($row['basic'],2) ?></td>
                  <td class="px-6 py-4"><?= number_format($row['overtime_hours'],2) ?> hrs @ ₹<?= number_format($row['overtime_rate'],2) ?></td>
                  <td class="px-6 py-4 text-red-600">-₹<?= number_format($row['deductions'],2) ?></td>
                  <td class="px-6 py-4 text-green-600 font-semibold">₹<?= number_format($row['total'],2) ?></td>
                  <td class="px-6 py-4 text-gray-500"><?= date("d M Y, h:i A", strtotime($row['generated_at'])) ?></td>
                </tr>
              <?php endforeach; if (count($salaryRows)===0): ?>
                <tr><td colspan="8" class="p-6 text-center text-gray-500">No salary records found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>

          <div class="p-6 border-t">
            <a href="dashboard.php" class="text-blue-600 hover:text-blue-800 text-sm inline-flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
              Back to Dashboard
            </a>
          </div>
        </div>

        <!-- MOBILE: Cards list -->
        <div class="md:hidden space-y-4">
          <?php if (count($salaryRows) > 0):
            foreach ($salaryRows as $row): 
              $badge = 'bg-green-50 text-green-800';
          ?>
            <div class="p-4 bg-white rounded-xl card-shadow border border-gray-200">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <p class="text-base font-semibold text-gray-900"><?= htmlspecialchars($row['name']) ?></p>
                  <p class="text-sm text-gray-500"><?= htmlspecialchars($row['month']) ?></p>
                </div>
                <div class="text-right">
                  <p class="text-sm text-gray-500">Total</p>
                  <p class="text-lg font-semibold text-green-600">₹<?= number_format($row['total'],2) ?></p>
                </div>
              </div>

              <div class="mt-3 grid grid-cols-2 gap-2 text-sm text-gray-700">
                <div>Basic: <span class="font-medium">₹<?= number_format($row['basic'],2) ?></span></div>
                <div>Overtime: <span class="font-medium"><?= number_format($row['overtime_hours'],2) ?>h</span></div>
                <div>Rate: <span class="font-medium">₹<?= number_format($row['overtime_rate'],2) ?>/h</span></div>
                <div>Deductions: <span class="font-medium text-red-600">-₹<?= number_format($row['deductions'],2) ?></span></div>
              </div>

              <div class="mt-3 text-xs text-gray-500">Generated: <?= date("d M Y, h:i A", strtotime($row['generated_at'])) ?></div>
            </div>
          <?php endforeach; else: ?>
            <div class="p-4 bg-white rounded-xl border border-gray-200 text-center text-gray-500">No salary records found.</div>
          <?php endif; ?>
        </div>

      </div>
    </main>
  </div>

  <script src="../assets/js/script.js"></script>
</body>
</html>
