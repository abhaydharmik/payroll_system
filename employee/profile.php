<?php
session_start();
require '../config.php';

// Restrict access to employees
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'employee') {
  header('Location: ../index.php');
  exit;
}

$emp = $_SESSION['user'];
$user_id = $emp['id'];

// Fetch user info
$query = $conn->prepare("
    SELECT u.*, d.name AS department_name, g.title AS designation_name 
    FROM users u
    LEFT JOIN departments d ON u.department_id = d.id
    LEFT JOIN designations g ON u.designation_id = g.id
    WHERE u.id = ?
");
$query->bind_param("i", $user_id);
$query->execute();
$user = $query->get_result()->fetch_assoc();

// Quick Stats
$leaveBalanceQuery = $conn->prepare("SELECT COUNT(*) AS total FROM leaves WHERE user_id=? AND status='Approved'");
$leaveBalanceQuery->bind_param("i", $user_id);
$leaveBalanceQuery->execute();
$leaveBalance = $leaveBalanceQuery->get_result()->fetch_assoc()['total'] ?? 0;

$presentDaysQuery = $conn->prepare("SELECT COUNT(*) AS present FROM attendance WHERE user_id=? AND status='Present'");
$presentDaysQuery->bind_param("i", $user_id);
$presentDaysQuery->execute();
$presentDays = $presentDaysQuery->get_result()->fetch_assoc()['present'] ?? 0;

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $dob = trim($_POST['dob']);
  $phone = trim($_POST['phone']);
  $address = trim($_POST['address']);

  $update = $conn->prepare("UPDATE users SET name=?, email=?, dob=?, phone=?, address=? WHERE id=?");
  $update->bind_param("sssssi", $name, $email, $dob, $phone, $address, $user_id);

  if ($update->execute()) {
    $_SESSION['user']['name'] = $name;
    $_SESSION['user']['email'] = $email;
    $success = "Profile updated successfully.";
    $user['name'] = $name;
    $user['email'] = $email;
    $user['dob'] = $dob;
    $user['phone'] = $phone;
    $user['address'] = $address;
  } else {
    $error = "Error updating profile: " . $conn->error;
  }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
  $current = $_POST['current_password'];
  $new = $_POST['new_password'];
  $confirm = $_POST['confirm_password'];

  if ($new !== $confirm) {
    $error = "New passwords do not match.";
  } else {
    $check = $conn->prepare("SELECT password FROM users WHERE id=?");
    $check->bind_param("i", $user_id);
    $check->execute();
    $hashed = $check->get_result()->fetch_assoc()['password'];

    if (password_verify($current, $hashed)) {
      $new_hashed = password_hash($new, PASSWORD_DEFAULT);
      $updatePass = $conn->prepare("UPDATE users SET password=? WHERE id=?");
      $updatePass->bind_param("si", $new_hashed, $user_id);
      $updatePass->execute();
      $success = "Password updated successfully.";
    } else {
      $error = "Current password incorrect.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Profile</title>
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
  <?php include '../includes/sidebaremp.php'; ?>

  <!-- Overlay for Mobile -->
  <div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden z-30 md:hidden"></div>

  <!-- Main Content -->
  <div class="flex-1 flex flex-col min-h-screen md:ml-64">

    <!-- Navbar -->
    <header class="fixed top-0 left-0 right-0 md:left-64 bg-white shadow flex justify-between items-center px-4 py-3 z-40">
      <div class="flex items-center space-x-3">
        <button id="sidebarToggle" class="md:hidden text-gray-700 focus:outline-none">
          <i class="fa-solid fa-bars text-xl"></i>
        </button>
        <h1 class="text-lg font-semibold text-gray-700">My Profile</h1>
      </div>
      <div class="flex items-center space-x-3">
        <span class="text-gray-700 flex items-center">
          <i class="fas fa-user-circle text-blue-600 mr-1"></i>
          <?= htmlspecialchars($emp['name']) ?>
        </span>
        <a href="../logout.php" class="text-red-600 hover:text-red-800">
          <i class="fas fa-sign-out-alt text-lg"></i>
        </a>
      </div>
    </header>

    <!-- Profile Page -->
    <main class="flex-1 pt-20 px-4 md:px-8 pb-8 transition-all duration-300">

      <?php if (!empty($success)): ?>
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4 text-sm sm:text-base"><?= $success ?></div>
      <?php elseif (!empty($error)): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm sm:text-base"><?= $error ?></div>
      <?php endif; ?>

      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">My Profile</h2>
          <p class="text-gray-600">Manage your personal information and settings</p>
        </div>
        <!-- <button class="bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition-all flex items-center space-x-2" fdprocessedid="7hhzq">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-credit-card w-4 h-4">
            <rect width="20" height="14" x="2" y="5" rx="2"></rect>
            <line x1="2" x2="22" y1="10" y2="10"></line>
          </svg><span>Edit Profile</span></button> -->
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 mt-4 gap-6">

        <!-- Left Section -->
        <div class="space-y-6">
          <div class="bg-white shadow rounded-lg p-6 text-center">
            <div class="w-20 sm:w-24 h-20 sm:h-24 mx-auto rounded-full bg-blue-100 flex items-center justify-center text-xl sm:text-2xl font-semibold text-blue-600">
              <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </div>
            <h3 class="mt-4 text-lg sm:text-xl font-semibold text-gray-800 break-words"><?= htmlspecialchars($user['name']) ?></h3>
            <p class="text-gray-500 text-sm sm:text-base"><?= htmlspecialchars($user['designation_name'] ?? 'Employee') ?></p>
            <p class="text-xs sm:text-sm text-gray-400"><?= htmlspecialchars($user['department_name'] ?? 'Department') ?></p>
            <div class="mt-4 border-t pt-4 text-xs sm:text-sm text-gray-600 flex flex-col sm:flex-row justify-around gap-2 sm:gap-0">
              <p><strong><?= rand(1, 5) ?></strong> Years</p>
              <p><strong><?= rand(80, 100) ?>%</strong> Attendance</p>
            </div>
          </div>

          <div class="bg-white shadow rounded-lg p-6">
            <h4 class="font-semibold mb-4 text-gray-700 text-base sm:text-lg">Quick Stats</h4>
            <ul class="text-gray-600 space-y-2 text-sm sm:text-base">
              <li><strong>Leave Balance:</strong> <?= $leaveBalance ?> days</li>
              <li><strong>Days Present:</strong> <?= $presentDays ?></li>
              <li><strong>Projects:</strong> <?= rand(1, 5) ?> Active</li>
            </ul>
          </div>
        </div>

        <!-- Right Section -->
        <div class="lg:col-span-2 space-y-6">

          <!-- Personal Info -->
          <div class="bg-white shadow rounded-lg p-6">
            <h4 class="font-semibold mb-4 text-gray-700 text-base sm:text-lg">Personal Information</h4>
            <form method="POST" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="text-xs sm:text-sm text-gray-600">Full Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="w-full border rounded p-2 mt-1 text-sm sm:text-base">
              </div>
              <div>
                <label class="text-xs sm:text-sm text-gray-600">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="w-full border rounded p-2 mt-1 text-sm sm:text-base">
              </div>
              <div>
                <label class="text-xs sm:text-sm text-gray-600">Date of Birth</label>
                <input type="date" name="dob" value="<?= htmlspecialchars($user['dob']) ?>" class="w-full border rounded p-2 mt-1 text-sm sm:text-base">
              </div>
              <div>
                <label class="text-xs sm:text-sm text-gray-600">Phone</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" class="w-full border rounded p-2 mt-1 text-sm sm:text-base">
              </div>
              <div class="sm:col-span-2">
                <label class="text-xs sm:text-sm text-gray-600">Address</label>
                <textarea name="address" class="w-full border rounded p-2 mt-1 text-sm sm:text-base"><?= htmlspecialchars($user['address']) ?></textarea>
              </div>
              <div class="sm:col-span-2">
                <button type="submit" name="update_profile" class="bg-blue-600 text-white px-4 py-2 rounded mt-3 hover:bg-blue-700 text-sm sm:text-base">Update Profile</button>
              </div>
            </form>
          </div>

          <!-- Change Password -->
          <div class="bg-white shadow rounded-lg p-6">
            <h4 class="font-semibold mb-4 text-gray-700 text-base sm:text-lg">Change Password</h4>
            <form method="POST" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <input type="password" name="current_password" placeholder="Current Password" class="border rounded p-2 text-sm sm:text-base">
              <input type="password" name="new_password" placeholder="New Password" class="border rounded p-2 text-sm sm:text-base">
              <input type="password" name="confirm_password" placeholder="Confirm Password" class="border rounded p-2 text-sm sm:text-base">
              <div class="sm:col-span-3">
                <button type="submit" name="change_password" class="bg-green-600 text-white px-4 py-2 rounded mt-3 hover:bg-green-700 text-sm sm:text-base">Update Password</button>
              </div>
            </form>
          </div>

        </div>
      </div>
    </main>
  </div>

  <!-- JS for Sidebar Toggle -->
  <script src="../assets/js/script.js"></script>

</body>

</html>