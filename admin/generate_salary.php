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
    <header class="fixed top-0 left-0 right-0 md:left-64 bg-white shadow flex justify-between items-center px-4 py-3 z-40">
      <div class="flex items-center space-x-3">
        <!-- Toggle Button for Mobile -->
        <button id="sidebarToggle" class="md:hidden text-gray-700 focus:outline-none">
          <i class="fa-solid fa-bars text-xl"></i>
        </button>
        <h1 class="text-lg font-semibold text-gray-700">Generate Salary</h1>
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
      <div class="bg-white shadow-md rounded-lg p-6 max-w-3xl mx-auto w-full">
        <?php if ($message): ?>
          <div class="mb-4 p-3 rounded <?= str_contains($message, '✅') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
            <?= $message ?>
          </div>
        <?php endif; ?>

        <form method="post" class="space-y-4">
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
