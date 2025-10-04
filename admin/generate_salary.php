<?php
require '../config.php';
require '../includes/auth.php';
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

    if ($stmt->execute()) {
        $message = "✅ Salary generated successfully!";
    } else {
        $message = "❌ Error: " . $stmt->error;
    }
}

// Fetch employees
$employees = $conn->query("SELECT id, name FROM users WHERE role='employee'");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Generate Salary | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 flex">

    <!-- Sidebar -->
    <aside class="w-64 bg-blue-800 text-white flex flex-col fixed h-screen">
        <div class="p-6 border-b border-blue-700">
            <h1 class="text-2xl font-bold flex items-center">
                <i class="fa-solid fa-chart-line mr-2"></i> Admin Panel
            </h1>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="dashboard.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700">
                <i class="fa-solid fa-gauge mr-2"></i> Dashboard
            </a>
            <a href="employees.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700">
                <i class="fa-solid fa-users mr-2"></i> Employees
            </a>
            <a href="attendance.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700">
                <i class="fa-solid fa-calendar-check mr-2"></i> Attendance
            </a>
            <a href="leaves.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700">
                <i class="fa-solid fa-file-signature mr-2"></i> Leaves
            </a>
            <a href="generate_salary.php" class="block py-2 px-3 rounded-lg bg-blue-700">
                <i class="fa-solid fa-sack-dollar mr-2"></i> Generate Salary
            </a>
            <a href="salary_history.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700">
                <i class="fa-solid fa-file-invoice-dollar mr-2"></i> Salary History
            </a>
            <a href="departments.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700">
                <i class="fa-solid fa-building mr-2"></i> Departments
            </a>
            <a href="designations.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700">
                <i class="fa-solid fa-briefcase mr-2"></i> Designations
            </a>
        </nav>
        <div class="p-4 border-t border-blue-700">
        <p class="text-sm">&copy; <?php echo date("Y"); ?> <span class="font-semibold">Payroll System</span>. All rights reserved.</p>
    </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 ml-64 p-8">
        <header class="bg-white shadow px-6 py-4 flex justify-between items-center rounded">
            <h2 class="text-lg font-semibold text-gray-700">Generate Salary</h2>
            <div class="flex items-center space-x-4">
                <span class="text-gray-700"><i class="fas fa-user-circle text-blue-600 mr-1"></i><?php echo htmlspecialchars($emp['name']); ?></span>
                <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm"><i class="fas fa-sign-out-alt mr-1"></i>Logout</a>
            </div>
        </header>

        <div class="bg-white shadow-md rounded-lg p-6 mt-4">
            <!-- <h2 class="text-2xl font-bold text-gray-700 mb-6 flex items-center">
                <i class="fa-solid fa-sack-dollar text-blue-600 mr-2"></i> Generate Salary
            </h2> -->

            <?php if ($message): ?>
                <div class="mb-4 p-3 rounded <?= str_contains($message, '✅') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <form method="post" class="space-y-4">
                <div>
                    <label class="block font-medium">Employee</label>
                    <select name="user_id" required class="w-full border rounded p-2">
                        <option value="">Select Employee</option>
                        <?php while ($e = $employees->fetch_assoc()): ?>
                            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div>
                    <label class="block font-medium">Month</label>
                    <input type="text" name="month" placeholder="e.g. Sept 2025" required class="w-full border rounded p-2">
                </div>

                <div>
                    <label class="block font-medium">Basic Salary</label>
                    <input type="number" step="0.01" name="basic" required class="w-full border rounded p-2">
                </div>

                <div>
                    <label class="block font-medium">Overtime Hours</label>
                    <input type="number" step="0.01" name="overtime_hours" value="0" class="w-full border rounded p-2">
                </div>

                <div>
                    <label class="block font-medium">Overtime Rate</label>
                    <input type="number" step="0.01" name="overtime_rate" value="0" class="w-full border rounded p-2">
                </div>

                <div>
                    <label class="block font-medium">Deductions</label>
                    <input type="number" step="0.01" name="deductions" value="0" class="w-full border rounded p-2">
                </div>

                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Generate Salary
                </button>
            </form>

            <div class="mt-6">
                <a href="dashboard.php" class="text-blue-600 hover:underline flex items-center">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </main>
</body>
</html>
