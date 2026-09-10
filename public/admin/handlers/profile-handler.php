<?php

require_once '../../../includes/auth.php';
require_once '../../../includes/db.php';

requireAdmin();

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
| Profile Update (Existing Logic)
|--------------------------------------------------------------------------
*/
$firstName       = trim($_POST['first_name'] ?? '');
$lastName        = trim($_POST['last_name'] ?? '');
$email           = strtolower(trim($_POST['email'] ?? ''));
$password        = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['profile_errors'] = ["Please enter a valid email address."];
    header("Location: ../profile.php");
    exit();
}

if ($password !== '') {
    if (strlen($password) < 8) {
        $_SESSION['profile_errors'] = ["New password must be at least 8 characters long."];
        header("Location: ../profile.php");
        exit();
    }

    if ($password !== $confirmPassword) {
        $_SESSION['profile_errors'] = ["New passwords do not match."];
        header("Location: ../profile.php");
        exit();
    }
}

if ($password !== '') {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
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