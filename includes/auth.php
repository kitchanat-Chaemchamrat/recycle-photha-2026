<?php
// includes/auth.php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function hasRole($role) {
    if (is_array($role)) {
        return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], $role);
    }
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

function requireRole($role) {
    if (!hasRole($role)) {
        die("Access Denied: You don't have permission to view this page.");
    }
}
?>
