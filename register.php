<?php
require 'config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $password, $role);

    if ($stmt->execute()) {
        $message = "<span class='text-green-600 font-medium'>Registration successful! <a href='index.php' class='text-blue-600 underline'>Login here</a></span>";
    } else {
        $message = "<span class='text-red-600 font-medium'>Error: " . $stmt->error . "</span>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register | Payroll System</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
  <div class="w-full max-w-md bg-white rounded-xl shadow-md p-8">
    <h2 class="text-2xl font-bold text-center text-gray-700 mb-6">Create Account</h2>
    
    <form method="post" class="space-y-4">
      <input type="text" name="name" placeholder="Full Name" required
        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">

      <input type="email" name="email" placeholder="Email" required
        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">

      <input type="password" name="password" placeholder="Password" required
        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">

      <select name="role" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
        <option value="employee">Employee</option>
        <option value="admin">Admin</option>
      </select>

      <button type="submit"
        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-lg transition">
        Register
      </button>
    </form>

    <p class="text-center text-gray-600 mt-4 text-sm">
      Already have an account? <a href="index.php" class="text-blue-600 font-medium hover:underline">Login</a>
    </p>

    <div class="mt-4 text-center">
      <?php echo $message; ?>
    </div>
  </div>
</body>
</html>
