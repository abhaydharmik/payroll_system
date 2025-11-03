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
    <header class="fixed top-0 left-0 right-0 md:left-64 bg-white shadow flex justify-between items-center px-4 py-3 z-40">
      <div class="flex items-center space-x-3">
        <!-- Mobile menu button -->
        <button id="sidebarToggle" class="md:hidden text-gray-700 focus:outline-none">
          <i class="fa-solid fa-bars text-xl"></i>
        </button>
        <h1 class="text-lg font-semibold text-gray-700">Employees</h1>
      </div>
      <div class="flex items-center space-x-3">
        <span class="text-gray-700 flex items-center">
          <i class="fas fa-user-circle text-blue-600 mr-1"></i>
          <?php echo htmlspecialchars($emp['name']); ?>
        </span>
        <a href="../logout.php" class="text-red-600 hover:text-red-800">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-log-out w-5 h-5">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16 17 21 12 16 7"></polyline>
            <line x1="21" x2="9" y1="12" y2="12"></line>
          </svg>
        </a>
      </div>
    </header>

    <!-- PAGE CONTENT -->
    <main class="flex-1 pt-20 px-4 md:px-8 pb-8">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0 mb-4">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Employees</h2>
          <p class="text-gray-600">Manage your workforce</p>
        </div>

        <div class="text-sm">
          <a href="add_employee.php" class="bg-blue-600 text-white p-2.5 rounded-lg hover:bg-blue-700 flex items-center w-full sm:w-auto justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus-icon lucide-plus mr-1">
              <path d="M5 12h14" />
              <path d="M12 5v14" />
            </svg> Add Employee
          </a>
        </div>
      </div>

      <div class="p-4 sm:p-6">

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
                          <a href="edit_employee.php?id=<?= $row['id'] ?>" class="text-blue-600 hover:text-blue-900 p-1 hover:bg-blue-50 rounded">
                            <i class="fa-solid fa-pen-to-square"></i>
                          </a>
                          <a href="performance.php?id=<?= $row['id'] ?>" class="text-yellow-600 hover:text-yellow-900 p-1 hover:bg-yellow-50 rounded">
                            <i class="fa-solid fa-chart-line"></i>
                          </a>
                          <a href="delete_employee.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this employee?')"
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