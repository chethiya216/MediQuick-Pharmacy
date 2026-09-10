<?php

require_once '../../../includes/auth.php';
require_once '../../../includes/db.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../create-staff.php");
    exit();
}

$firstName       = trim($_POST['first_name'] ?? '');
$lastName        = trim($_POST['last_name'] ?? '');
$email           = strtolower(trim($_POST['email'] ?? ''));
$password        = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$role            = strtolower(trim($_POST['role'] ?? ''));

// Retain input fields on error
$_SESSION['staff_form_old'] = [
    'first_name' => $firstName,
    'last_name'  => $lastName,
    'email'      => $email,
    'role'       => $role
];

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

if ($firstName === '' || $lastName === '' || $email === '' || $password === '' || $confirmPassword === '' || $role === '') {
    $_SESSION['staff_form_message'] = "Please fill in all required fields.";
    $_SESSION['staff_form_message_type'] = "error";
    header("Location: ../create-staff.php");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['staff_form_message'] = "Please enter a valid email address.";
    $_SESSION['staff_form_message_type'] = "error";
    header("Location: ../create-staff.php");
    exit();
}

if ($password !== $confirmPassword) {
    $_SESSION['staff_form_message'] = "Passwords do not match.";
    $_SESSION['staff_form_message_type'] = "error";
    header("Location: ../create-staff.php");
    exit();
}

if (strlen($password) < 8) {
    $_SESSION['staff_form_message'] = "Password must be at least 8 characters.";
    $_SESSION['staff_form_message_type'] = "error";
    header("Location: ../create-staff.php");
    exit();
}

if (!in_array($role, ['admin', 'pharmacist', 'superadmin'], true)) {
    $_SESSION['staff_form_message'] = "Invalid staff role.";
    $_SESSION['staff_form_message_type'] = "error";
    header("Location: ../create-staff.php");
    exit();
}

if ($role === 'superadmin' && getUserRole() !== 'superadmin') {
    $_SESSION['staff_form_message'] = "Only a superadmin can create another superadmin.";
    $_SESSION['staff_form_message_type'] = "error";
    header("Location: ../create-staff.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Check Staff Email
|--------------------------------------------------------------------------
*/

$sql = "SELECT staff_id FROM staff WHERE email = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
$staffExists = $stmt->num_rows > 0;
$stmt->close();

if ($staffExists) {
    $_SESSION['staff_form_message'] = "This email is already registered to a staff account.";
    $_SESSION['staff_form_message_type'] = "error";
    header("Location: ../create-staff.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Create Staff (Includes Current Hire Date)
|--------------------------------------------------------------------------
*/

$staffId      = 'STF-' . strtoupper(bin2hex(random_bytes(4)));
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$hireDate     = date('Y-m-d'); // Automatically set current date

$sql = "INSERT INTO staff (staff_id, first_name, last_name, email, password_hash, role, status, hire_date) VALUES (?, ?, ?, ?, ?, ?, 'active', ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    $_SESSION['staff_form_message'] = "Database error: " . $conn->error;
    $_SESSION['staff_form_message_type'] = "error";
} else {
    // 7 string parameters ("sssssss")
    $stmt->bind_param("sssssss", $staffId, $firstName, $lastName, $email, $passwordHash, $role, $hireDate);

    if ($stmt->execute()) {
        $_SESSION['staff_form_message'] = "Staff account created successfully.";
        $_SESSION['staff_form_message_type'] = "success";
        unset($_SESSION['staff_form_old']); // Clear form values on success
    } else {
        $_SESSION['staff_form_message'] = "Failed to create staff account: " . $stmt->error;
        $_SESSION['staff_form_message_type'] = "error";
    }

    $stmt->close();
}

$message = $_SESSION['staff_form_message'] ?? '';
$messageType = $_SESSION['staff_form_message_type'] ?? '';
$old = $_SESSION['staff_form_old'] ?? [];

unset(
    $_SESSION['staff_form_message'],
    $_SESSION['staff_form_message_type'],
    $_SESSION['staff_form_old']
);

header("Location: ../../login.php");
exit();
