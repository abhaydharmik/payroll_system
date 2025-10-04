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
<body class="bg-gray-100 flex flex-col min-h-screen">

    <!-- Top Navbar -->
    <header class="w-full bg-white shadow px-4 py-3 flex justify-between items-center md:ml-64 fixed top-0 z-20">
        <!-- Hamburger for mobile -->
        <button id="menu-btn" class="text-gray-700 md:hidden text-2xl focus:outline-none">
            <i class="fa-solid fa-bars"></i>
        </button>

        <h1 class="text-lg font-semibold text-gray-700">Employee List</h1>

        <div class="flex items-center space-x-3">
            <span class="text-gray-700 text-sm hidden sm:flex items-center">
                <i class="fas fa-user-circle text-blue-600 mr-1"></i><?php echo htmlspecialchars($emp['name']); ?>
            </span>
            <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm flex items-center">
                <i class="fas fa-sign-out-alt mr-1"></i> Logout
            </a>
        </div>
    </header>

    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar w-64 bg-blue-800 text-white flex flex-col fixed h-full top-0 left-0 z-30 sidebar-closed md:translate-x-0 md:sidebar-closed:translate-x-0 md:block">
        <div class="p-6 border-b border-blue-700 flex justify-between items-center">
            <h1 class="text-2xl font-bold flex items-center">
                <i class="fa-solid fa-chart-line mr-2"></i> Admin
            </h1>
            <button id="close-btn" class="md:hidden text-white text-xl">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <a href="dashboard.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700"><i class="fa-solid fa-gauge mr-2"></i> Dashboard</a>
            <a href="employees.php" class="block py-2 px-3 rounded-lg bg-blue-700"><i class="fa-solid fa-users mr-2"></i> Employees</a>
            <a href="attendance.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700"><i class="fa-solid fa-calendar-check mr-2"></i> Attendance</a>
            <a href="leaves.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700"><i class="fa-solid fa-file-signature mr-2"></i> Leaves</a>
            <a href="generate_salary.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700"><i class="fa-solid fa-sack-dollar mr-2"></i> Generate Salary</a>
            <a href="salary_history.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700"><i class="fa-solid fa-file-invoice-dollar mr-2"></i> Salary History</a>
            <a href="departments.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700"><i class="fa-solid fa-building mr-2"></i> Departments</a>
            <a href="designations.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700"><i class="fa-solid fa-briefcase mr-2"></i> Designations</a>
        </nav>
        <div class="p-4 border-t border-blue-700 text-center text-xs">
            &copy; <?php echo date("Y"); ?> Payroll System
        </div>
    </aside>

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

    <script>
        const menuBtn = document.getElementById('menu-btn');
        const closeBtn = document.getElementById('close-btn');
        const sidebar = document.getElementById('sidebar');

        menuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('sidebar-closed');
        });
        closeBtn.addEventListener('click', () => {
            sidebar.classList.add('sidebar-closed');
        });
    </script>
</body>
</html>
