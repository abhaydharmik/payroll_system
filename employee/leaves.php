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
    $reason = trim($_POST['reason']);
    $stmt = $conn->prepare("INSERT INTO leaves (user_id, reason) VALUES (?, ?)");
    $stmt->bind_param("is", $emp_id, $reason);
    if ($stmt->execute()) {
        $message = "<span class='text-green-600 font-medium'>Leave request submitted!</span>";
    } else {
        $message = "<span class='text-red-600 font-medium'>Error: " . $stmt->error . "</span>";
    }
}

$stmt = $conn->prepare("SELECT * FROM leaves WHERE user_id=? ORDER BY applied_at DESC");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$leaves = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Apply for Leave</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center shadow-md">
        <h1 class="text-lg font-bold">Apply for Leave</h1>
        <a href="dashboard.php" class="bg-white text-blue-600 px-4 py-2 rounded-md text-sm hover:bg-gray-100 transition">⬅ Back</a>
    </nav>

    <!-- Content -->
    <div class="flex-1 p-6 flex flex-col items-center">
        <div class="w-full max-w-2xl bg-white rounded-xl shadow-md p-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Leave Application</h2>

            <!-- Leave Form -->
            <form method="post" class="space-y-4 mb-6">
                <textarea name="reason" placeholder="Reason for leave" required
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 h-24"></textarea>
                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded-lg transition">
                    Submit Request
                </button>
            </form>

            <?php if ($message) { ?>
                <p class="mb-4 text-center"><?php echo $message; ?></p>
            <?php } ?>

            <!-- Leave History -->
            <h3 class="text-lg font-semibold text-gray-700 mb-2">My Leave Requests</h3>
            <div class="overflow-x-auto">
                <table class="w-full border border-gray-200 rounded-lg">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left">Reason</th>
                            <th class="px-4 py-2 text-left">Status</th>
                            <th class="px-4 py-2 text-left">Applied At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $leaves->fetch_assoc()) { ?>
                            <tr class="border-t">
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['reason']); ?></td>
                                <td class="px-4 py-2">
                                    <?php 
                                        if ($row['status'] == "Approved") echo "<span class='text-green-600 font-semibold'>✔ Approved</span>";
                                        elseif ($row['status'] == "Rejected") echo "<span class='text-red-600 font-semibold'>✘ Rejected</span>";
                                        else echo "<span class='text-yellow-600 font-semibold'>⏳ Pending</span>";
                                    ?>
                                </td>
                                <td class="px-4 py-2"><?php echo $row['applied_at']; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
