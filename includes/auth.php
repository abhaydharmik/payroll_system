<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';

// Redirect if user not logged in
if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit;
}

// Simple role check helper
function checkRole($role) {
    if ($_SESSION['user']['role'] !== $role) {
        header("Location: ../index.php");
        exit;
    }
}
