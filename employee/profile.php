<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'employee') {
    header('Location: ../index.php');
    exit;
}

$emp_id = $_SESSION['user']['id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("UPDATE users SET name=?, email=? WHERE id=?");
    $stmt->bind_param("ssi", $name, $email, $emp_id);

    if ($stmt->execute()) {
        $message = "<span class='text-green-600 font-medium'>Profile updated successfully!</span>";
        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['email'] = $email;
    } else {
        $message = "<span class='text-red-600 font-medium'>Error: " . $stmt->error . "</span>";
    }
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$emp = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center shadow-md">
        <h1 class="text-lg font-bold">My Profile</h1>
        <a href="dashboard.php" class="bg-white text-blue-600 px-4 py-2 rounded-md text-sm hover:bg-gray-100 transition">⬅ Back</a>
    </nav>

    <!-- Profile Form -->
    <div class="flex-1 p-6 flex justify-center items-center">
        <div class="w-full max-w-md bg-white rounded-xl shadow-md p-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Update Profile</h2>

            <form method="post" class="space-y-4">
                <div>
                    <label class="block text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($emp['name']); ?>" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($emp['email']); ?>" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-medium transition">
                    Update Profile
                </button>
            </form>

            <?php if ($message) { ?>
                <p class="mt-4 text-center"><?php echo $message; ?></p>
            <?php } ?>
        </div>
    </div>
</body>
</html>
