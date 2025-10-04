<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'employee') {
    header('Location: ../index.php');
    exit;
}

$emp = $_SESSION['user'];
$emp_id = $_SESSION['user']['id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $today = date('Y-m-d');
    $status = $_POST['status'];

    $stmt = $conn->prepare("SELECT * FROM attendance WHERE user_id=? AND date=?");
    $stmt->bind_param("is", $emp_id, $today);
    $stmt->execute();
    $exists = $stmt->get_result()->fetch_assoc();

    if ($exists) {
        $message = "<span class='text-red-600 font-medium'>You already marked attendance today!</span>";
    } else {
        $stmt = $conn->prepare("INSERT INTO attendance (user_id, date, status) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $emp_id, $today, $status);
        if ($stmt->execute()) {
            $message = "<span class='text-green-600 font-medium'>Attendance marked successfully!</span>";
        } else {
            $message = "<span class='text-red-600 font-medium'>Error: " . $stmt->error . "</span>";
        }
    }
}

$stmt = $conn->prepare("SELECT * FROM attendance WHERE user_id=? ORDER BY date DESC LIMIT 10");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$attendance = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Mark Attendance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 flex">

    <!-- Sidebar -->
    <aside class="w-64 bg-blue-800 text-white flex flex-col fixed h-screen">
        <div class="p-6 border-b border-blue-700">
            <h1 class="text-xl font-bold flex items-center">
                <i class="fa-solid fa-chart-line mr-2"></i> Employee Panel
            </h1>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="dashboard.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700">
                <i class="fa-solid fa-gauge mr-2"></i> Dashboard
            </a>
            <a href="profile.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700">
                <i class="fa-solid fa-users mr-2"></i> Profile
            </a>
            <a href="attendance.php" class="block py-2 px-3 rounded-lg bg-blue-700">
                <i class="fa-solid fa-calendar-check mr-2"></i> My Attendance
            </a>
            <a href="leaves.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700">
                <i class="fa-solid fa-file-signature mr-2"></i> My Leaves
            </a>
            <a href="salary.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700">
                <i class="fa-solid fa-sack-dollar mr-2"></i> Salary Slips
            </a>
            <a href="reports.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700">
                <i class="fa-solid fa-file-invoice-dollar mr-2"></i> My Reports
            </a>
        </nav>
        <div class="p-4 border-t border-blue-700">
            <p class="text-sm">&copy; <?php echo date("Y"); ?> <span class="font-semibold">Payroll System</span>. All rights reserved.</p>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 ml-64 p-8">
        <header class="bg-white shadow px-6 py-4 flex justify-between items-center rounded">
            <h2 class="text-lg font-semibold text-gray-700">My Attendance</h2>
            <div class="flex items-center space-x-4">
                <span class="text-gray-700"><i class="fas fa-user-circle text-blue-600 mr-1"></i><?php echo htmlspecialchars($emp['name']); ?></span>
                <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm"><i class="fas fa-sign-out-alt mr-1"></i>Logout</a>
            </div>
        </header>
        <div class="w-full max-w-lg bg-white rounded-xl shadow-md p-6 mt-4">

            <form method="post" class="flex items-center gap-4 mb-4">
                <select name="status" class="flex-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="Present">✅ Present</option>
                    <option value="Leave">📄 Leave</option>
                    <option value="Absent">❌ Absent</option>
                </select>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">Submit</button>
            </form>

            <?php if ($message) { ?>
                <p class="mb-4"><?php echo $message; ?></p>
            <?php } ?>

            <h3 class="text-lg font-semibold text-gray-700 mb-2">Recent Attendance</h3>
            <div class="overflow-x-auto">
                <table class="w-full border border-gray-200 rounded-lg">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left">Date</th>
                            <th class="px-4 py-2 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $attendance->fetch_assoc()) { ?>
                            <tr class="border-t">
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['date']); ?></td>
                                <td class="px-4 py-2">
                                    <?php
                                    if ($row['status'] == "Present") echo "<span class='text-green-600 font-semibold'>✅ Present</span>";
                                    elseif ($row['status'] == "Leave") echo "<span class='text-yellow-600 font-semibold'>📄 Leave</span>";
                                    else echo "<span class='text-red-600 font-semibold'>❌ Absent</span>";
                                    ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        </div>
</body>

</html>