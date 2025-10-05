<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

$emp = $_SESSION['user'];

$message = '';
$messageType = 'success';

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['designation']);
    if (!empty($name)) {
        // Check for duplicate
        $stmt = $conn->prepare("SELECT id FROM designations WHERE name=?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows == 0 || isset($_POST['edit'])) {
            if (isset($_POST['edit'])) {
                $id = (int) $_POST['edit'];
                $stmt = $conn->prepare("UPDATE designations SET name=? WHERE id=?");
                $stmt->bind_param("si", $name, $id);
                $stmt->execute();
                $message = "✏️ Designation updated successfully!";
            } else {
                $stmt = $conn->prepare("INSERT INTO designations (name) VALUES (?)");
                $stmt->bind_param("s", $name);
                $stmt->execute();
                $message = "✅ Designation added successfully!";
            }
        } else {
            $message = "⚠️ Designation already exists!";
            $messageType = 'error';
        }
    }
    header("Location: designations.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM designations WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $message = "🗑️ Designation deleted!";
}

// Fetch Designations
$result = $conn->query("SELECT * FROM designations ORDER BY id DESC");

// Fetch for Edit
$edit = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM designations WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Manage Designations</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
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
            <a href="employees.php" class="block py-2 px-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 flex items-center">
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
            <a href="designations.php" class="block py-2 px-3 rounded-lg bg-blue-50 text-blue-600 border border-blue-200">
                <i class="fa-solid fa-briefcase mr-2"></i> Designations
            </a>
        </nav>
        <div class="p-4 border-t mt-4 border-blue-700">
            <p class="text-sm">&copy; <?php echo date("Y"); ?> <span class="font-semibold">Payroll System</span>. All rights reserved.</p>
        </div>
    </aside>

    <!-- Overlay for mobile -->
    <div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden z-30 md:hidden"></div>>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 ml-64 p-8">

        <header class="bg-white shadow px-6 py-4 flex justify-between items-center rounded">
            <h2 class="text-lg font-semibold text-gray-700">Manage Designations</h2>
            <div class="flex items-center space-x-4">
                <span class="text-gray-700"><i class="fas fa-user-circle text-blue-600 mr-1"></i><?php echo htmlspecialchars($emp['name']); ?></span>
                <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm"><i class="fas fa-sign-out-alt mr-1"></i>Logout</a>
            </div>
        </header>

        <div class="bg-white shadow-md rounded-lg p-6 mt-4">
            <!-- <h2 class="text-2xl font-bold mb-6 text-gray-700">💼 Manage Designations</h2> -->

            <!-- Message -->
            <?php if ($message): ?>
                <div class="p-3 mb-4 rounded <?= $messageType == 'error' ? 'bg-red-500 text-white' : 'bg-green-500 text-white' ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <!-- Add/Edit Form -->
            <form method="post" class="flex gap-2 mb-6">
                <input type="text" name="designation" placeholder="Enter Designation"
                    class="border p-2 rounded flex-grow" required
                    value="<?= $edit ? htmlspecialchars($edit['name']) : '' ?>">
                <?php if ($edit): ?>
                    <button type="submit" name="edit" value="<?= $edit['id'] ?>"
                        class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">Update</button>
                    <a href="designations.php" class="px-4 py-2 border rounded text-blue-600 hover:underline">Cancel</a>
                <?php else: ?>
                    <button type="submit" name="add"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">➕ Add</button>
                <?php endif; ?>
            </form>

            <!-- List of Designations -->
            <div class="overflow-x-auto">
                <table class="w-full bg-white shadow rounded">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="p-2 border">ID</th>
                            <th class="p-2 border">Designation</th>
                            <th class="p-2 border">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 text-center">
                                <td class="p-2 border"><?= $row['id'] ?></td>
                                <td class="p-2 border"><?= htmlspecialchars($row['name']) ?></td>
                                <td class="p-2 border space-x-2">
                                    <a href="?edit=<?= $row['id'] ?>"
                                        class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">✏ Edit</a>
                                    <a href="?delete=<?= $row['id'] ?>"
                                        onclick="return confirm('Delete this designation?')"
                                        class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">🗑 Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <a href="dashboard.php" class="text-blue-600 hover:underline flex items-center">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </main>
</body>

</html>