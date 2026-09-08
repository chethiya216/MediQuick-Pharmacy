<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure session is active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../../../includes/db.php';

// handle deletion

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'delete') {
    // $customerId = !empty($_GET['customer_id']) ? (int)$_GET['customer_id'] : (!empty($_POST['customer_id']) ? (int)$_POST['id'] : 0);
    $customerId = !empty($_REQUEST['customer_id']) ? (int)$_REQUEST['customer_id'] : (!empty($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0);

    if ($customerId <= 0) {
        $_SESSION['form_errors'] = ["Invalid customer ID."];
        header("Location: ../manage-customers.php");
        exit;
    }

    try {

        // Delete customer database record
        $deleteStmt = $conn->prepare("DELETE FROM customers WHERE customer_id = ?");
        $deleteStmt->bind_param("i", $customerId);

        if ($deleteStmt->execute()) {
            $deleteStmt->close();
            $_SESSION['flash_success'] = "Customer deleted successfully.";
        } else {
            $_SESSION['form_errors'] = ["Failed to delete Customer."];
        }

    } catch (mysqli_sql_exception $e) {
        // Handle Foreign Key constraints
        if ($e->getCode() === 1451) {
            $_SESSION['form_errors'] = ["Cannot delete customer because they have existing orders."];
        } else {
            $_SESSION['form_errors'] = ["Database error: " . $e->getMessage()];
        }
    }

    header("Location: ../manage-customers.php");
    exit;
} 

// Only accept POST submissions from the form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../manage-customers.php");
    exit;
}

// 3. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $customer_id   = !empty($_POST['customer_id']) ? (int)$_POST['customer_id'] : 0;
    $first_name    = trim($_POST['first_name'] ?? '');
    $last_name     = trim($_POST['last_name'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $phone         = trim($_POST['phone'] ?? '');
    $address       = trim($_POST['address'] ?? '');
    $date_of_birth = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null;
    $status        = trim($_POST['status'] ?? 'active');
    $password      = $_POST['password'] ?? '';

    $errors = [];

    // Validations
    if ($customer_id <= 0) { $errors[] = "Invalid customer ID."; }
    if (empty($first_name)) { $errors[] = "First name is required."; }
    if (empty($last_name))  { $errors[] = "Last name is required."; }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) { 
        $errors[] = "A valid email address is required."; 
    }

    // Check for duplicate email (excluding current customer)
    if (empty($errors)) {
        $checkStmt = $conn->prepare("SELECT `customer_id` FROM `customers` WHERE `email` = ? AND `customer_id` != ?");
        $checkStmt->bind_param("si", $email, $customer_id);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            $errors[] = "The email address is already in use by another account.";
        }
        $checkStmt->close();
    }

    if (empty($errors)) {
        // Update query with optional password change
        if (!empty($password)) {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $updateSql = "UPDATE `customers` 
                          SET `first_name` = ?, `last_name` = ?, `email` = ?, `password_hash` = ?, `phone` = ?, `address` = ?, `date_of_birth` = ?, `status` = ?, `updated_at` = NOW() 
                          WHERE `customer_id` = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("ssssssssi", $first_name, $last_name, $email, $password_hash, $phone, $address, $date_of_birth, $status, $customer_id);
        } else {
            $updateSql = "UPDATE `customers` 
                          SET `first_name` = ?, `last_name` = ?, `email` = ?, `phone` = ?, `address` = ?, `date_of_birth` = ?, `status` = ?, `updated_at` = NOW() 
                          WHERE `customer_id` = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("sssssssi", $first_name, $last_name, $email, $phone, $address, $date_of_birth, $status, $customer_id);
        }

        if ($updateStmt->execute()) {
            $_SESSION['flash_success'] = "Customer updated successfully.";
            header("Location: ../manage-customers.php");
            exit();
        } else {
            $errors[] = "Failed to update customer. Please try again.";
        }

        $_SESSION['form_errors'] = $errors;
        header("Location: ../edit-customer.php?id=" . $customer_id);
        exit();

    }

    // Preserve form input on error
    $_SESSION['form_errors'] = $errors;
    $customer['first_name']    = $first_name;
    $customer['last_name']     = $last_name;
    $customer['email']         = $email;
    $customer['phone']         = $phone;
    $customer['address']       = $address;
    $customer['date_of_birth'] = $date_of_birth;
    $customer['status']        = $status;
}