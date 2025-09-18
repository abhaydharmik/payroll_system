<?php
// Database Connection
$host = "localhost";
$user = "root";   // change if using another DB user
$pass = "";       // change if your MySQL has a password
$db   = "payroll_system";

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>
