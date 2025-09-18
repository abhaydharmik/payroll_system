<?php
session_start();
require 'config.php';

if (isset($_SESSION['user'])) {
    header("Location: " . ($_SESSION['user']['role'] == 'admin' ? "admin/dashboard.php" : "employee/dashboard.php"));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        header("Location: " . ($user['role'] == 'admin' ? "admin/dashboard.php" : "employee/dashboard.php"));
        exit;
    } else {
        $error = "Invalid email or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Payroll System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Login</h2>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-4">
            <div>
                <label class="block text-gray-600 text-sm mb-1">Email</label>
                <input type="email" name="email" required 
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-gray-600 text-sm mb-1">Password</label>
                <input type="password" name="password" required 
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <button type="submit" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-lg transition">
                Login
            </button>
        </form>

        <p class="text-center text-sm text-gray-600 mt-6">
            Don’t have an account? 
            <a href="register.php" class="text-blue-600 hover:underline">Register</a>
        </p>
    </div>
</body>
</html>
