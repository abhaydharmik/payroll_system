<?php
require '../config.php';
require '../includes/auth.php';
include_once '../includes/sidebar.php';
checkRole('admin');

$emp = $_SESSION['user'];
$departments = $conn->query("SELECT * FROM departments ORDER BY name ASC");
$selectedDept = $_GET['department_id'] ?? '';

// Using prepared statements is highly recommended for security.
// The code below maintains the original SQL structure for continuity.
$sql = "SELECT u.id, u.name, u.email, d.name AS department
        FROM users u
        LEFT JOIN departments d ON u.department_id = d.id
        WHERE u.role='employee'";
if (!empty($selectedDept)) {
    // SECURITY NOTE: intval() helps sanitize, but prepared statements are superior.
    $sql .= " AND u.department_id = " . intval($selectedDept);
}
$sql .= " ORDER BY u.id ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Employees | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        /*
         * Custom CSS to ensure sidebar and main content responsiveness
         * using the standard Tailwind 'md' (768px) breakpoint.
         */
        
        /* Sidebar positioning for mobile/desktop transition */
        .sidebar {
            transition: transform 0.3s ease-in-out;
            /* Default: Hidden off-screen on small devices */
            transform: translateX(-100%); 
        }

        /* For desktop/tablet (md breakpoint) - sidebar becomes visible */
        @media (min-width: 768px) { 
            .sidebar {
                transform: translateX(0);
            }
        }

        .sidebar-closed {
            transform: translateX(-100%);
        }
        
        .sidebar-open {
            transform: translateX(0);
        }

        /* Main Content positioning based on sidebar state */
        .main-content {
            /* Full width on small screens */
            width: 100%;
            margin-left: 0;
        }

        @media (min-width: 768px) {
            .main-content {
                /* Adds left margin for the fixed/sticky sidebar (64 units = 16rem) */
                margin-left: 16rem; 
            }
        }
    </style>
</head>

<body class="bg-gray-100 flex">
    
    <main class="main-content flex-1 pt-20 px-4">
        <div class="bg-white shadow-md rounded-lg p-4 sm:p-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Employee Directory</h2>
            
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 space-y-3 sm:space-y-0">
                
                <a href="add_employee.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 flex items-center w-full sm:w-auto justify-center">
                    <i class="fa-solid fa-user-plus mr-1"></i> Add Employee
                </a>

                <form method="get" class="flex flex-col sm:flex-row sm:items-center w-full sm:w-auto space-y-2 sm:space-y-0 sm:space-x-2">
                    <label for="department_id" class="text-gray-700 font-medium whitespace-nowrap">Filter by Dept:</label>
                    <select name="department_id" id="department_id" class="border border-gray-300 rounded px-3 py-2 w-full sm:w-auto">
                        <option value="">All Departments</option>
                        <?php while ($d = $departments->fetch_assoc()): ?>
                            <option value="<?= $d['id'] ?>" <?= ($selectedDept == $d['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <button type="submit" class="bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-900 w-full sm:w-auto justify-center">
                        <i class="fa-solid fa-filter mr-1"></i> Apply
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto shadow-sm rounded-lg border border-gray-200">
                <table class="w-full border-collapse border border-gray-300 min-w-[650px]">
                    <thead>
                        <tr class="bg-gray-200 text-xs sm:text-sm md:text-base uppercase tracking-wider">
                            <th class="border px-3 py-2 text-left">ID</th>
                            <th class="border px-3 py-2 text-left">Name</th>
                            <th class="border px-3 py-2 text-left hidden sm:table-cell">Email</th> <th class="border px-3 py-2 text-left">Department</th>
                            <th class="border px-3 py-2 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-100 text-sm sm:text-base">
                                    <td class="border px-3 py-2 text-xs sm:text-sm font-medium"><?= $row['id'] ?></td>
                                    <td class="border px-3 py-2"><?= htmlspecialchars($row['name']) ?></td>
                                    <td class="border px-3 py-2 hidden sm:table-cell text-sm text-gray-600"><?= htmlspecialchars($row['email']) ?></td>
                                    <td class="border px-3 py-2">
                                        <?= $row['department'] ? htmlspecialchars($row['department']) : '<span class="text-gray-400 italic text-sm">N/A</span>' ?>
                                    </td>
                                    <td class="border px-3 py-2 space-x-2 text-center whitespace-nowrap">
                                        <a href="edit_employee.php?id=<?= $row['id'] ?>" class="text-blue-600 hover:text-blue-800 text-sm sm:text-base">
                                            <i class="fa-solid fa-pen-to-square"></i> <span class="hidden sm:inline">Edit</span>
                                        </a>
                                        <a href="delete_employee.php?id=<?= $row['id'] ?>" class="text-red-600 hover:text-red-800 text-sm sm:text-base" onclick="return confirm('Are you sure?')">
                                            <i class="fa-solid fa-trash"></i> <span class="hidden sm:inline">Delete</span>
                                        </a>
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
</body>

</html>