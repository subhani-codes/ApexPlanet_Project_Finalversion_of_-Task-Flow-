<?php
require_once '../includes/auth_helper.php';
// 🔒 SECURITY GUARD: Restrict access strictly to authenticated Admins
enforce_permission(['admin']);

require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_user_id = (int)$_POST['user_id'];
    $current_status = sanitize_input($_POST['current_status']);
    
    // Defensive Check: Prevent the logged-in admin from accidentally suspending themselves
    if ($target_user_id === (int)$_SESSION['user_id']) {
        header("Location: dashboard.php?error=Operation Denied: You cannot suspend your own admin account!");
        exit();
    }
    
    // Calculate new target state
    $new_status = ($current_status === 'active') ? 'suspended' : 'active';
    
    // Execute parameterized state modification query
    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $target_user_id]);
    
    header("Location: dashboard.php?success=Account status modified successfully to " . $new_status);
    exit();
} else {
    header("Location: dashboard.php");
    exit();
}