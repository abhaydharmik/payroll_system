<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $month = $_POST['month'];
    $basic = $_POST['basic'];
    $overtime_hours = $_POST['overtime_hours'];
    $overtime_rate = $_POST['overtime_rate'];
    $deductions = $_POST['deductions'];

    $total = $basic + ($overtime_hours * $overtime_rate) - $deductions;

    // ✅ Fixed bind_param types (i = int, s = string, d = double)
    $stmt = $conn->prepare("INSERT INTO salaries 
    (user_id, month, basic, overtime_hours, overtime_rate, deductions, total) 
    VALUES (?, ?, ?, ?, ?, ?, ?)");

    // i = int, s = string, d = double
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
<html>

<head>
    <title>Generate Salary</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="max-w-2xl mx-auto mt-10 bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold text-gray-700 mb-6">💰 Generate Salary</h2>

        <?php if ($message): ?>
            <div class="mb-4 p-3 rounded 
                <?= str_contains($message, '✅') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
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
            <a href="dashboard.php" class="text-blue-600 hover:underline">⬅ Back to Dashboard</a>
        </div>
    </div>
</body>

</html>