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

$pageTitle = "My Profile";

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Profile</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="bg-gray-100">

  <!-- Sidebar -->
  <?php include '../includes/sidebaremp.php'; ?>

  <!-- Overlay for Mobile -->
  <div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden z-30 md:hidden"></div>

  <!-- Main Content -->
  <div class="flex-1 flex flex-col min-h-screen md:ml-64">

    <!-- Navbar -->

    <?php include_once '../includes/header.php'; ?>


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
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">

        <!-- LEFT COLUMN -->
        <div class="space-y-6">

          <!-- Profile Card -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-center">
            <div class="w-24 h-24 mx-auto rounded-full bg-blue-100 flex items-center justify-center text-3xl font-semibold text-blue-600">
              <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </div>

            <h3 class="mt-4 text-xl font-semibold text-gray-800"><?= htmlspecialchars($user['name']) ?></h3>
            <p class="text-gray-500"><?= htmlspecialchars($user['designation_name'] ?? 'Employee') ?></p>
            <p class="text-xs text-gray-400"><?= htmlspecialchars($user['department_name'] ?? 'Department') ?></p>

            <div class="mt-4 flex justify-center divide-x divide-gray-300 text-sm text-gray-600">
              <p class="px-3"><strong><?= rand(1, 5) ?></strong> Years</p>
              <p class="px-3"><strong><?= rand(80, 100) ?>%</strong> Attendance</p>
            </div>
          </div>

          <!-- Stats -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h4 class="font-semibold text-gray-800 mb-4">Quick Stats</h4>
            <ul class="text-gray-600 space-y-2 text-sm">
              <li><strong>Leave Balance:</strong> <?= $leaveBalance ?> days</li>
              <li><strong>Days Present:</strong> <?= $presentDays ?></li>
              <li><strong>Projects:</strong> <?= rand(1, 5) ?> Active</li>
            </ul>
          </div>

        </div>

        <!-- RIGHT COLUMN -->
        <div class="lg:col-span-2 space-y-6">

          <!-- Personal Information -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h4 class="font-semibold text-gray-800 mb-4">Personal Information</h4>

            <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">

              <div>
                <label class="text-sm text-gray-600">Full Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="w-full mt-1 border rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
              </div>

              <div>
                <label class="text-sm text-gray-600">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="w-full mt-1 border rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
              </div>

              <div>
                <label class="text-sm text-gray-600">Date of Birth</label>
                <input type="date" name="dob" value="<?= htmlspecialchars($user['dob']) ?>" class="w-full mt-1 border rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
              </div>

              <div>
                <label class="text-sm text-gray-600">Phone</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" class="w-full mt-1 border rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
              </div>

              <div class="md:col-span-2">
                <label class="text-sm text-gray-600">Address</label>
                <textarea name="address" class="w-full mt-1 border rounded-lg p-2 focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($user['address']) ?></textarea>
              </div>

              <div class="md:col-span-2">
                <button type="submit" name="update_profile" class="w-full md:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                  Update Profile
                </button>
              </div>

            </form>
          </div>

          <!-- Change Password -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h4 class="font-semibold text-gray-800 mb-4">Change Password</h4>

            <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <input type="password" name="current_password" placeholder="Current Password" class="border rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
              <input type="password" name="new_password" placeholder="New Password" class="border rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
              <input type="password" name="confirm_password" placeholder="Confirm Password" class="border rounded-lg p-2 focus:ring-2 focus:ring-blue-500">

              <div class="md:col-span-3">
                <button type="submit" name="change_password" class="w-full md:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                  Update Password
                </button>
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