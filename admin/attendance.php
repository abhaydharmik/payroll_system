<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

$emp = $_SESSION['user'];

// Fetch attendance with employee info
$sql = "SELECT a.id, u.name, a.date, a.status 
        FROM attendance a 
        JOIN users u ON a.user_id = u.id 
        ORDER BY a.date ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance | Admin</title>
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
    <?php include_once '../includes/sidebar.php'; ?>

    <!-- Overlay -->
    <div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden z-30 md:hidden"></div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-h-screen md:ml-64">

        <!-- ✅ Same Navbar as dashboard -->
        <header class="fixed top-0 left-0 right-0 md:left-64 bg-white shadow flex justify-between items-center px-4 py-3 z-40">
            <div class="flex items-center space-x-3">
                <!-- Mobile menu button -->
                <button id="sidebarToggle" class="md:hidden text-gray-700 focus:outline-none">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <h1 class="text-lg font-semibold text-gray-700">Attendance Records</h1>
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

            <div class="bg-white shadow-md rounded-lg p-4 md:p-6 mt-4">
                <div class="overflow-x-auto shadow-sm rounded-lg border border-gray-200">
                    <table class="min-w-max w-full border-collapse">
                        <thead class="bg-blue-600 text-white text-sm">
                            <tr>
                                <th class="px-2 py-3 text-left">No</th>
                                <th class="px-2 py-3 text-left ">Employee</th>
                                <th class="px-2 py-3 text-left ">Date</th>
                                <th class="px-2 py-3 text-left ">Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium text-xs md:text-sm"><?= $row['id'] ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap"><?= htmlspecialchars($row['name']) ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-xs md:text-sm"><?= $row['date'] ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <?php if ($row['status'] == 'Present'): ?>
                                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-medium">Present</span>
                                            <?php elseif ($row['status'] == 'Absent'): ?>
                                                <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-medium">Absent</span>
                                            <?php else: ?>
                                                <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs font-medium"><?= htmlspecialchars($row['status']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-gray-500 py-4">No attendance records found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    <a href="dashboard.php" class="text-blue-600 hover:text-blue-800 flex items-center">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/script.js"></script>
</body>

</html>