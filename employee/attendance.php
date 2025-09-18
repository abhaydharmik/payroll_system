<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'employee') {
    header('Location: ../index.php');
    exit;
}

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
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center shadow-md">
        <h1 class="text-lg font-bold">Attendance</h1>
        <a href="dashboard.php" class="bg-white text-blue-600 px-4 py-2 rounded-md text-sm hover:bg-gray-100 transition">⬅ Back</a>
    </nav>

    <!-- Main Content -->
    <div class="flex-1 p-6 flex flex-col items-center">
        <div class="w-full max-w-lg bg-white rounded-xl shadow-md p-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Mark Attendance</h2>
            
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
