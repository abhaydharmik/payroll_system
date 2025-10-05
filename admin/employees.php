<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

$emp = $_SESSION['user'];
$departments = $conn->query("SELECT * FROM departments ORDER BY name ASC");
$selectedDept = $_GET['department_id'] ?? '';

$sql = "SELECT u.id, u.name, u.email, d.name AS department
        FROM users u
        LEFT JOIN departments d ON u.department_id = d.id
        WHERE u.role='employee'";
if (!empty($selectedDept)) {
    $sql .= " AND u.department_id = " . intval($selectedDept);
}
$sql .= " ORDER BY u.id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Employees | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Smooth slide-in for sidebar */
        .sidebar {
            transition: transform 0.3s ease-in-out;
        }

        .sidebar-closed {
            transform: translateX(-100%);
        }
    </style>
</head>

<body class="bg-gray-100 flex">

    <!-- Mobile Header -->
    <header class="fixed top-0 left-0 right-0 bg-white shadow-md flex items-center justify-between px-4 py-3 md:hidden z-50">
        <h1 class="text-lg font-bold text-gray-800 flex items-center">
            <i class="fa-solid fa-chart-line mr-2"></i> Admin Panel
        </h1>
        <button id="sidebarToggle" class="text-gray-800 text-2xl focus:outline-none">
            <i class="fa-solid fa-bars"></i>
        </button>
    </header>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-white flex flex-col transform -translate-x-full md:translate-x-0 transition-transform duration-300 z-40">
        <!-- <div id="hidden" class="p-6 border-b border-blue-700">
            <h1 class="text-2xl font-bold flex items-center">
                <i class="fa-solid fa-chart-line mr-2"></i> Admin Panel
            </h1>
        </div> -->
        <nav class="flex-1 px-4 py-7 mt-10 space-y-2 overflow-y-auto">
            <a href="dashboard.php" class="block py-2 px-3 flex items-center rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bar-chart3 w-5 h-5 mr-2">
                    <path d="M3 3v18h18"></path>
                    <path d="M18 17V9"></path>
                    <path d="M13 17V5"></path>
                    <path d="M8 17v-3"></path>
                </svg> Dashboard</a>
            <a href="employees.php" class="block py-2 px-3 rounded-lg  bg-blue-50 text-blue-600 border border-blue-200 flex items-center">
                <!-- <i class="fa-solid fa-users mr-2"></i> -->
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users w-5 h-5 mr-2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                Employees
            </a>
            <a href="attendance.php" class="block py-2 px-3 rounded-lg  text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                <i class="fa-solid fa-calendar-check mr-2"></i> Attendance
            </a>
            <a href="leaves.php" class="block py-2 px-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                <i class="fa-solid fa-file-signature mr-2"></i> Leaves
            </a>
            <a href="generate_salary.php" class="block py-2 px-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                <i class="fa-solid fa-sack-dollar mr-2"></i> Generate Salary
            </a>
            <a href="salary_history.php" class="block py-2 px-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                <i class="fa-solid fa-file-invoice-dollar mr-2"></i> Salary History
            </a>
            <a href="departments.php" class="block py-2 px-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                <i class="fa-solid fa-building mr-2"></i> Departments
            </a>
            <a href="designations.php" class="block py-2 px-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                <i class="fa-solid fa-briefcase mr-2"></i> Designations
            </a>
        </nav>
        <div class="p-4 border-t mt-4 border-blue-700">
            <p class="text-sm">&copy; <?php echo date("Y"); ?> <span class="font-semibold">Payroll System</span>. All rights reserved.</p>
        </div>
    </aside>

    <!-- Overlay for mobile -->
    <div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden z-30 md:hidden"></div>

    <!-- Main Content -->
    <main class="flex-1 pt-20 px-4 md:ml-64">
        <div class="bg-white shadow-md rounded-lg p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 space-y-3 sm:space-y-0">
                <a href="add_employee.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 flex items-center">
                    <i class="fa-solid fa-user-plus mr-1"></i> Add Employee
                </a>

                <form method="get" class="flex flex-col sm:flex-row sm:items-center w-full sm:w-auto space-y-2 sm:space-y-0 sm:space-x-2">
                    <label for="department_id" class="text-gray-700 font-medium">Filter:</label>
                    <select name="department_id" id="department_id" class="border border-gray-300 rounded px-3 py-2 w-full sm:w-auto">
                        <option value="">All Departments</option>
                        <?php while ($d = $departments->fetch_assoc()): ?>
                            <option value="<?= $d['id'] ?>" <?= ($selectedDept == $d['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <button type="submit" class="bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-900 w-full sm:w-auto">
                        <i class="fa-solid fa-filter mr-1"></i> Apply
                    </button>
                </form>
            </div>

            <!-- Responsive Table -->
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-300 min-w-[600px]">
                    <thead>
                        <tr class="bg-gray-200 text-sm sm:text-base">
                            <th class="border px-3 py-2">ID</th>
                            <th class="border px-3 py-2">Name</th>
                            <th class="border px-3 py-2">Email</th>
                            <th class="border px-3 py-2">Department</th>
                            <th class="border px-3 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-100 text-sm sm:text-base">
                                    <td class="border px-3 py-2"><?= $row['id'] ?></td>
                                    <td class="border px-3 py-2"><?= htmlspecialchars($row['name']) ?></td>
                                    <td class="border px-3 py-2"><?= htmlspecialchars($row['email']) ?></td>
                                    <td class="border px-3 py-2">
                                        <?= $row['department'] ? htmlspecialchars($row['department']) : '<span class="text-gray-400 italic">Not Assigned</span>' ?>
                                    </td>
                                    <td class="border px-3 py-2 space-x-3">
                                        <a href="edit_employee.php?id=<?= $row['id'] ?>" class="text-blue-600 hover:text-blue-800"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                        <a href="delete_employee.php?id=<?= $row['id'] ?>" class="text-red-600 hover:text-red-800" onclick="return confirm('Are you sure?')"><i class="fa-solid fa-trash"></i> Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-gray-500 py-4">No employees found.</td>
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
    <!-- <script>
        const menuBtn = document.getElementById('menu-btn');
        const closeBtn = document.getElementById('close-btn');
        const sidebar = document.getElementById('sidebar');

        menuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('sidebar-closed');
        });
        closeBtn.addEventListener('click', () => {
            sidebar.classList.add('sidebar-closed');
        });
    </script> -->
</body>

</html>