<?php

require_once '../../../includes/auth.php';
require_once '../../../includes/db.php';

requireLogin();
requirePharmacist();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../profile.php");
    exit();
}

$staffId = $_SESSION['staff_id'] ?? $_SESSION['user_id'] ?? null;

if (!$staffId) {
    header("Location: ../logout.php");
    exit();
}

$action = $_POST['action'] ?? 'update_profile';

/*
|--------------------------------------------------------------------------
| Account Deactivation
|--------------------------------------------------------------------------
*/
if ($action === 'deactivate_account') {
    $confirmed = isset($_POST['accountActivation']);

    if (!$confirmed) {
        $_SESSION['profile_errors'] = ["Please check the confirmation box to deactivate your account."];
        header("Location: ../profile.php");
        exit();
    }

    // Update account status to inactive
    $sql = "UPDATE staff SET status = 'inactive' WHERE staff_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $staffId);

    if ($stmt && $stmt->execute()) {
        $stmt->close();

        // Destroy session and redirect to logout/login page
        session_unset();
        session_destroy();
        
        session_start();
        $_SESSION['login_success'] = "Your account has been deactivated.";
        header("Location: ../../login.php");
        exit();
    } else {
        $_SESSION['profile_errors'] = ["Failed to deactivate account: " . $conn->error];
        header("Location: ../profile.php");
        exit();
    }
}

/*
|--------------------------------------------------------------------------
| Profile Update & Password Change Logic
|--------------------------------------------------------------------------
*/
$firstName = trim($_POST['first_name'] ?? '');
$lastName  = trim($_POST['last_name'] ?? '');
$email     = strtolower(trim($_POST['email'] ?? ''));

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['profile_errors'] = ["Please enter a valid email address."];
    header("Location: ../profile.php");
    exit();
}

// Check if email is already taken by another account
if ($email !== '') {
    $emailCheckSql = "SELECT staff_id FROM staff WHERE email = ? AND staff_id != ? LIMIT 1";
    $stmtCheck = $conn->prepare($emailCheckSql);
    $stmtCheck->bind_param("ss", $email, $staffId);
    $stmtCheck->execute();
    
    $stmtCheck->bind_result($existingStaffId);
    if ($stmtCheck->fetch()) {
        $stmtCheck->close();
        $_SESSION['profile_errors'] = ["This email is already in use by another user."];
        header("Location: ../profile.php");
        exit();
    }
    $stmtCheck->close();
}

// Password Change Verification
$changePassword  = isset($_POST['change_password_toggle']) && $_POST['change_password_toggle'] === '1';
$hashedPassword  = null;

if ($changePassword) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword     = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // 1. Verify all fields are present
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $_SESSION['profile_errors'] = ["Please fill in all password fields."];
        header("Location: ../profile.php");
        exit();
    }

    // 2. Fetch current password hash from database using bind_result for maximum PHP compatibility
    $stmtPass = $conn->prepare("SELECT password_hash FROM staff WHERE staff_id = ? LIMIT 1");
    $stmtPass->bind_param("s", $staffId);
    $stmtPass->execute();
    $stmtPass->bind_result($existingHash);
    
    if (!$stmtPass->fetch()) {
        $stmtPass->close();
        $_SESSION['profile_errors'] = ["User account not found."];
        header("Location: ../profile.php");
        exit();
    }
    $stmtPass->close();

    // 3. Verify current password
    if (!password_verify($currentPassword, $existingHash)) {
        $_SESSION['profile_errors'] = ["Your current password is incorrect."];
        header("Location: ../profile.php");
        exit();
    }

    // 4. Validate new password length
    if (strlen($newPassword) < 8) {
        $_SESSION['profile_errors'] = ["New password must be at least 8 characters long."];
        header("Location: ../profile.php");
        exit();
    }

    // 5. Verify match
    if ($newPassword !== $confirmPassword) {
        $_SESSION['profile_errors'] = ["New passwords do not match."];
        header("Location: ../profile.php");
        exit();
    }

    // Generate new hash
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
}

/*
|--------------------------------------------------------------------------
| Execute Database Update
|--------------------------------------------------------------------------
*/
if ($hashedPassword !== null) {
    $sql = "UPDATE staff SET first_name = ?, last_name = ?, email = ?, password_hash = ? WHERE staff_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $firstName, $lastName, $email, $hashedPassword, $staffId);
} else {
    $sql = "UPDATE staff SET first_name = ?, last_name = ?, email = ? WHERE staff_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $firstName, $lastName, $email, $staffId);
}

if ($stmt && $stmt->execute()) {
    $_SESSION['profile_success'] = "Profile updated successfully.";
} else {
    $_SESSION['profile_errors'] = ["Failed to update profile: " . $conn->error];
}

if ($stmt) {
    $stmt->close();
}

header("Location: ../profile.php");
exit();