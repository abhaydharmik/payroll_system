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
$sql .= " ORDER BY u.id ASC";
$result = $conn->query($sql);

$pageTitle = "Employees";
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Employees | Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    /* Sidebar slide effect */
    @media (max-width: 767px) {
      #sidebar.mobile-hidden {
        transform: translateX(-100%);
      }
    }
  </style>
</head>

<body class="bg-gray-100">

  <!-- SIDEBAR -->
  <?php include_once '../includes/sidebar.php'; ?>

  <!-- Overlay for Mobile -->
  <div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden z-30 md:hidden"></div>

  <!-- MAIN CONTENT -->
  <div class="flex-1 flex flex-col min-h-screen md:ml-64">
    <!-- NAVBAR -->
    <?php include_once '../includes/header.php'; ?>

    <!-- PAGE CONTENT -->
    <main class="flex-1 pt-20 px-4 md:px-8 pb-8">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-0 mb-4 px-2 sm:px-0">
        <!-- Left Section: Title -->
        <div class="text-center sm:text-left w-full sm:w-auto">
          <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Employees</h2>
          <p class="text-gray-600 text-sm sm:text-base">Manage your workforce</p>
        </div>



        <!-- Right Section: Button -->
        <div class="w-full sm:w-auto">
          <a href="add_employee.php"
            class="flex items-center justify-center gap-1.5 bg-blue-600 text-white text-sm sm:text-base px-3 sm:px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-200 w-full sm:w-auto">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
              fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round" class="lucide lucide-plus">
              <path d="M5 12h14" />
              <path d="M12 5v14" />
            </svg>
            <span>Add Employee</span>
          </a>
        </div>
      </div>
      
      <?php include '../includes/breadcrumb.php'; ?>

      <div class="">

        <!-- Modern Employee Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
          <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
              <div class="flex-1 relative">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                  fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                  class="lucide lucide-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4">
                  <circle cx="11" cy="11" r="8"></circle>
                  <path d="m21 21-4.3-4.3"></path>
                </svg>
                <input type="text" id="searchInput" placeholder="Search employees..."
                  class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              </div>

              <form method="get" class="flex flex-col sm:flex-row sm:items-center w-full sm:w-auto space-y-2 sm:space-y-0 sm:space-x-2">
                <div class="relative">
                  <select name="department_id" id="department_id" class="appearance-none border border-gray-300 rounded-xl px-3 py-2 pr-10 text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 cursor-pointer w-full sm:w-auto">
                    <option value="">All Departments</option>
                    <?php while ($d = $departments->fetch_assoc()): ?>
                      <option value="<?= $d['id'] ?>" <?= ($selectedDept == $d['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($d['name']) ?>
                      </option>
                    <?php endwhile; ?>
                  </select>
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                  </svg>
                </div>
                <button type="submit" class="bg-blue-700 text-white px-4 py-2 rounded-xl hover:bg-blue-900 w-full sm:w-auto justify-center">
                  <i class="fa-solid fa-filter mr-1"></i> Apply
                </button>
              </form>

            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200" id="employeeTable">
                <?php if ($result->num_rows > 0): ?>
                  <?php while ($row = $result->fetch_assoc()): ?>
                    <tr class="employee-row">
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-sm font-medium text-blue-600">
                              <?= strtoupper(substr($row['name'], 0, 1)) ?>
                            </span>
                          </div>
                          <div class="ml-3">
                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($row['name']) ?></div>
                            <div class="text-sm text-gray-500"><?= htmlspecialchars($row['email']) ?></div>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?= $row['department'] ? htmlspecialchars($row['department']) : '<span class="text-gray-400 italic">N/A</span>' ?>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($row['email']) ?></td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                          <a href="edit_employee.php?id=<?= $row['id'] ?>" title="Edit" class="text-blue-600 hover:text-blue-900 p-1 hover:bg-blue-50 rounded">
                            <i class="fa-solid fa-pen-to-square"></i>
                          </a>
                          <a href="performance.php?id=<?= $row['id'] ?>" title="performance" class="text-yellow-600 hover:text-yellow-900 p-1 hover:bg-yellow-50 rounded">
                            <i class="fa-solid fa-chart-line"></i>
                          </a>
                          <a href="delete_employee.php?id=<?= $row['id'] ?>" title="Delete" onclick="return confirm('Delete this employee?')"
                            class="text-red-600 hover:text-red-900 p-1 hover:bg-red-50 rounded">
                            <i class="fa-solid fa-trash"></i>
                          </a>
                        </div>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="4" class="text-center text-gray-500 py-4">No employees found.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <div class="p-6 border-t border-gray-200">
            <a href="dashboard.php" class="text-blue-600 hover:text-blue-800 flex items-center text-sm">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
              </svg> Back to Dashboard
            </a>
          </div>

        </div>




      </div>
    </main>
  </div>

  <script src="../assets/js/script.js"></script>
</body>

</html>