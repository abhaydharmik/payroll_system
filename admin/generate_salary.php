<?php
require '../config.php';
require '../includes/auth.php';
include '../includes/sidebar.php';
checkRole('admin');

$emp = $_SESSION['user'];
$message = "";

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Salary | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 flex flex-col md:flex-row min-h-screen">

    <!-- Sidebar is included from sidebar.php -->

    <!-- Main Content -->
    <main class="flex-1 p-4 md:p-8 md:ml-64">
        <header class="bg-white shadow px-4 py-4 flex flex-col md:flex-row justify-between items-start md:items-center rounded">
            <h2 class="text-lg font-semibold text-gray-700 mb-2 md:mb-0">Generate Salary</h2>
            <div class="flex flex-col md:flex-row items-start md:items-center space-y-2 md:space-y-0 md:space-x-4">
                <span class="text-gray-700"><i class="fas fa-user-circle text-blue-600 mr-1"></i><?php echo htmlspecialchars($emp['name']); ?></span>
                <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm flex items-center">
                    <i class="fas fa-sign-out-alt mr-1"></i>Logout
                </a>
            </div>
        </header>

        <div class="bg-white shadow-md rounded-lg p-6 mt-4 max-w-3xl mx-auto w-full">
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

    <script src="../assets/js/script.js"></script>
</body>
</html>
