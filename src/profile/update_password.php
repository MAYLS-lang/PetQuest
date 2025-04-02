<?php
require_once '../../config/config.php';
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify if new password meets requirements
    if (strlen($new_password) < 8 ||
        !preg_match("/[A-Z]/", $new_password) ||
        !preg_match("/[0-9]/", $new_password) ||
        !preg_match("/[!@#$%^&*]/", $new_password)) {
        header('Location: index.php?error=Password does not meet requirements');
        exit();
    }
    
    // Check if new passwords match
    if ($new_password !== $confirm_password) {
        header('Location: index.php?error=New passwords do not match');
        exit();
    }
    
    // Verify current password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!password_verify($current_password, $user['password'])) {
        header('Location: index.php?error=Current password is incorrect');
        exit();
    }
    
    // Update password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        header('Location: index.php?success=Password updated successfully');
    } else {
        header('Location: index.php?error=Failed to update password');
    }
} else {
    header('Location: index.php');
}
exit();
