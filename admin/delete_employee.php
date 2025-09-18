<?php
require '../config.php';
require '../includes/auth.php';
checkRole('admin');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id=? AND role='employee'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}
header("Location: employees.php");
exit;
