<?php
session_start();
require '../config.php';

// Only employees can access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'employee') {
  header('Location: ../index.php');
  exit;
}

$emp = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Employee Profile</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 flex">

  <!-- Sidebar -->
  <aside class="w-64 bg-blue-800 text-white flex flex-col fixed h-screen">
    <div class="p-6 border-b border-blue-700">
      <h1 class="text-xl font-bold flex items-center">
        <i class="fa-solid fa-chart-line mr-2"></i> Employee Panel
      </h1>
    </div>
    <nav class="flex-1 px-4 py-6 space-y-2">
      <a href="dashboard.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700">
        <i class="fa-solid fa-gauge mr-2"></i> Dashboard
      </a>
      <a href="profile.php" class="block py-2 px-3 rounded-lg bg-blue-700">
        <i class="fa-solid fa-users mr-2"></i> Profile
      </a>
      <a href="attendance.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700">
        <i class="fa-solid fa-calendar-check mr-2"></i> My Attendance
      </a>
      <a href="leaves.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700">
        <i class="fa-solid fa-file-signature mr-2"></i> My Leaves
      </a>
      <a href="salary.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700">
        <i class="fa-solid fa-sack-dollar mr-2"></i> Salary Slips
      </a>
      <a href="reports.php" class="block py-2 px-3 rounded-lg hover:bg-blue-700">
        <i class="fa-solid fa-file-invoice-dollar mr-2"></i> My Reports
      </a>
    </nav>
    <div class="p-4 border-t border-blue-700">
      <p class="text-sm">&copy; <?php echo date("Y"); ?> <span class="font-semibold">Payroll System</span>. All rights reserved.</p>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 ml-64 p-8">
    <!-- Top Navbar -->
    <header class="bg-white shadow px-6 py-4 flex justify-between items-center">
      <h2 class="text-lg font-semibold text-gray-700">My Profile</h2>
      <div class="flex items-center space-x-4">
        <span class="text-gray-700"><i class="fas fa-user-circle text-blue-600 mr-1"></i><?php echo htmlspecialchars($emp['name']); ?></span>
        <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm"><i class="fas fa-sign-out-alt mr-1"></i>Logout</a>
      </div>
    </header>

    <!-- Profile Section -->
    <main class="p-6 space-y-6">
      <!-- Profile Overview -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Left Card -->
        <div class="bg-white rounded-lg shadow p-6 text-center">
          <div class="w-20 h-20 rounded-full bg-blue-100 mx-auto flex items-center justify-center text-3xl font-bold text-blue-600">
            <?php echo strtoupper(substr($emp['name'], 0, 1)); ?>
          </div>
          <h3 class="mt-3 text-lg font-semibold"><?php echo htmlspecialchars($emp['name']); ?></h3>
          <p class="text-gray-500"><?php echo htmlspecialchars($emp['position'] ?? "Employee"); ?></p>
          <p class="text-sm text-gray-400"><?php echo htmlspecialchars($emp['department'] ?? "Department"); ?></p>
          <div class="mt-4 text-sm grid grid-cols-3 gap-2 text-gray-600">
            <div><strong>2.5</strong><br>Years</div>
            <div><strong>4.8</strong><br>Rating</div>
            <div><strong>99%</strong><br>Attendance</div>
          </div>
        </div>

        <!-- Right Details -->
        <div class="md:col-span-2 bg-white rounded-lg shadow p-6">
          <h4 class="text-lg font-semibold text-gray-700 mb-4">Personal Information</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div><label class="text-sm text-gray-500">First Name</label>
              <p class="font-medium"><?php echo explode(" ", $emp['name'])[0]; ?></p>
            </div>
            <div><label class="text-sm text-gray-500">Last Name</label>
              <p class="font-medium"><?php echo explode(" ", $emp['name'])[1] ?? ""; ?></p>
            </div>
            <div><label class="text-sm text-gray-500">Email</label>
              <p class="font-medium"><?php echo htmlspecialchars($emp['email']); ?></p>
            </div>
            <div><label class="text-sm text-gray-500">Phone</label>
              <p class="font-medium">+1 (555) 123-4567</p>
            </div>
            <div><label class="text-sm text-gray-500">Date of Birth</label>
              <p class="font-medium">15-05-1990</p>
            </div>
            <div><label class="text-sm text-gray-500">Address</label>
              <p class="font-medium">123 Main Street, Apt 4B, New York, NY 10001</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Employment Details -->
      <div class="bg-white rounded-lg shadow p-6">
        <h4 class="text-lg font-semibold text-gray-700 mb-4">Employment Details</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div><label class="text-sm text-gray-500">Employee ID</label>
            <p class="font-medium">EMP001</p>
          </div>
          <div><label class="text-sm text-gray-500">Join Date</label>
            <p class="font-medium">15-01-2022</p>
          </div>
          <div><label class="text-sm text-gray-500">Position</label>
            <p class="font-medium">Software Engineer</p>
          </div>
          <div><label class="text-sm text-gray-500">Department</label>
            <p class="font-medium">Engineering</p>
          </div>
          <div><label class="text-sm text-gray-500">Manager</label>
            <p class="font-medium">Sarah Wilson</p>
          </div>
          <div><label class="text-sm text-gray-500">Employment Type</label>
            <p class="font-medium">Full-time</p>
          </div>
        </div>
      </div>

      <!-- Change Password -->
      <div class="bg-white rounded-lg shadow p-6">
        <h4 class="text-lg font-semibold text-gray-700 mb-4">Change Password</h4>
        <form class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <input type="password" placeholder="Current Password" class="border rounded px-3 py-2">
          <input type="password" placeholder="New Password" class="border rounded px-3 py-2">
          <input type="password" placeholder="Confirm Password" class="border rounded px-3 py-2">
          <button type="submit" class="md:col-span-3 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Update Password</button>
        </form>
      </div>
    </main>
    </div>
</body>

</html>