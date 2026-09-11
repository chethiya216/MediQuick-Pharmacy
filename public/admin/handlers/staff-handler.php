<?php

require_once '../../../includes/auth.php';
require_once '../../../includes/db.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../manage-staff.php");
    exit();
}

$action = $_POST['action'] ?? 'create_staff';

/*
|--------------------------------------------------------------------------
| Action: Update Staff Member
|--------------------------------------------------------------------------
*/
if ($action === 'update_staff') {
    $staffId         = trim($_POST['staff_id'] ?? '');
    $firstName       = trim($_POST['first_name'] ?? '');
    $lastName        = trim($_POST['last_name'] ?? '');
    $email           = strtolower(trim($_POST['email'] ?? ''));
    $password        = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role            = strtolower(trim($_POST['role'] ?? ''));
    $status          = strtolower(trim($_POST['status'] ?? 'active'));

    $redirectUrl = "../create-staff.php?staff_id=" . urlencode($staffId);

    if (empty($staffId) || $firstName === '' || $lastName === '' || $email === '' || $role === '') {
        $_SESSION['staff_form_message'] = "Please fill in all required fields.";
        $_SESSION['staff_form_message_type'] = "error";
        header("Location: " . $redirectUrl);
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['staff_form_message'] = "Please enter a valid email address.";
        $_SESSION['staff_form_message_type'] = "error";
        header("Location: " . $redirectUrl);
        exit();
    }

    // Email uniqueness check (excluding self)
    $sql = "SELECT staff_id FROM staff WHERE email = ? AND staff_id != ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $staffId);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        $_SESSION['staff_form_message'] = "This email is already in use by another staff member.";
        $_SESSION['staff_form_message_type'] = "error";
        header("Location: " . $redirectUrl);
        exit();
    }
    $stmt->close();

    // Password validation if provided
    $updatePassword = false;
    if (!empty($password)) {
        if ($password !== $confirmPassword) {
            $_SESSION['staff_form_message'] = "Passwords do not match.";
            $_SESSION['staff_form_message_type'] = "error";
            header("Location: " . $redirectUrl);
            exit();
        }
        if (strlen($password) < 8) {
            $_SESSION['staff_form_message'] = "Password must be at least 8 characters.";
            $_SESSION['staff_form_message_type'] = "error";
            header("Location: " . $redirectUrl);
            exit();
        }
        $updatePassword = true;
    }

    // Execute update query
    if ($updatePassword) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $sqlUpdate = "UPDATE staff SET first_name = ?, last_name = ?, email = ?, password_hash = ?, role = ?, status = ? WHERE staff_id = ?";
        $stmt = $conn->prepare($sqlUpdate);
        $stmt->bind_param("sssssss", $firstName, $lastName, $email, $passwordHash, $role, $status, $staffId);
    } else {
        $sqlUpdate = "UPDATE staff SET first_name = ?, last_name = ?, email = ?, role = ?, status = ? WHERE staff_id = ?";
        $stmt = $conn->prepare($sqlUpdate);
        $stmt->bind_param("ssssss", $firstName, $lastName, $email, $role, $status, $staffId);
    }

    if ($stmt->execute()) {
        $_SESSION['staff_success'] = "Staff account updated successfully.";
        $stmt->close();
        header("Location: ../manage-staff.php");
        exit();
    } else {
        $_SESSION['staff_form_message'] = "Update failed: " . $conn->error;
        $_SESSION['staff_form_message_type'] = "error";
        $stmt->close();
        header("Location: " . $redirectUrl);
        exit();
    }
}

if ($action === 'toggle_status') {
    $staffId = $_POST['staff_id'] ?? '';
    $status  = $_POST['status'] ?? '';

    $sqlUpdate = "UPDATE staff SET status = ? WHERE staff_id = ?";
    $stmt = $conn->prepare($sqlUpdate);
    $stmt->bind_param("ss", $status, $staffId);
    $stmt->execute();
    $stmt->close();
    header("Location: ../manage-staff.php");
    exit();
}