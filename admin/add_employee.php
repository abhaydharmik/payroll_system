<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name  = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'employee')");
    $stmt->bind_param("sss", $name, $email, $password);

    if ($stmt->execute()) {
        header("Location: employees.php");
        exit;
    } else {
        $error = "Error: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Add Employee</title></head>
<body>
    <h2>Add Employee</h2>
    <form method="post">
        <input type="text" name="name" placeholder="Full Name" required><br><br>
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <button type="submit">Add</button>
    </form>
    <?php if(!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <br><a href="employees.php">⬅ Back</a>
</body>
</html>
