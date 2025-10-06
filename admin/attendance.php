<?php
require '../config.php';
require '../includes/auth.php';
include_once '../includes/sidebar.php';
checkRole('admin');

$emp = $_SESSION['user'];
// Fetch attendance with employee info
$sql = "SELECT a.id, u.name, a.date, a.status 
        FROM attendance a 
        JOIN users u ON a.user_id=u.id 
        ORDER BY a.date ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Attendance | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Custom CSS to handle sidebar transition and positioning */
        .sidebar {
            transition: transform 0.3s ease-in-out;
            /* Assume sidebar starts hidden on mobile */
            transform: translateX(-100%); 
        }

        /* Show sidebar on medium screens and up (md: 768px in Tailwind) */
        @media (min-width: 768px) { 
            .sidebar {
                transform: translateX(0);
            }
        }
    </style>
</head>

<body class="bg-gray-100 flex">

    <main class="flex-1 ml-0 md:ml-64 p-4 md:p-8 pt-24 md:pt-8 transition-all">
        
        <header class="bg-white shadow px-4 py-3 md:px-6 md:py-4 flex flex-col md:flex-row justify-between items-start md:items-center rounded mb-4">
            <h2 class="text-xl font-semibold text-gray-700 mb-2 md:mb-0">Attendance Records</h2>
            <div class="flex items-center space-x-3 text-sm">
                <span class="text-gray-700 whitespace-nowrap"><i class="fas fa-user-circle text-blue-600 mr-1"></i><?php echo htmlspecialchars($emp['name'] ?? 'Admin'); ?></span>
                <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs md:text-sm"><i class="fas fa-sign-out-alt mr-1"></i>Logout</a>
            </div>
        </header>

        <div class="bg-white shadow-md rounded-lg p-4 md:p-6 mt-4">

            <div class="overflow-x-auto shadow-sm rounded-lg border border-gray-200">
                <table class="min-w-[500px] w-full border-collapse">
                    <thead class="bg-blue-600 text-white text-sm">
                        <tr>
                            <th class="px-4 py-3 text-left w-1/12">No</th>
                            <th class="px-4 py-3 text-left w-5/12">Employee</th>
                            <th class="px-4 py-3 text-left w-3/12">Date</th>
                            <th class="px-4 py-3 text-left w-3/12">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php if ($result->num_rows > 0): ?>
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
    <script src="../assets/js/script.js"></script>
</body>
</html>