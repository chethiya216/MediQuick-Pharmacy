<?php
session_start();
require_once(__DIR__ . '/../../includes/db.php');
require_once(__DIR__ . '/../../includes/auth.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and capture input values
    $customerId = !empty($_POST['customer_id']) ? intval($_POST['customer_id']) : NULL;
    $name       = trim($_POST['name'] ?? '');
    $email      = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $phone      = trim($_POST['phone'] ?? '');
    $subject    = trim($_POST['subject'] ?? '');
    $message    = trim($_POST['message'] ?? '');

    // Validate required fields
    if (!$name || !$email || !$message) {
        $_SESSION['error'] = 'Please fill in all required fields with a valid email.';
        header('Location: contact.php');
        exit();
    }

    // Insert into contact_messages database table including phone
    $sql = "INSERT INTO contact_messages (customer_id, name, email, phone, subject, message, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'unread')";

    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        // Types: i = integer, s = string (6 strings: name, email, phone, subject, message)
        mysqli_stmt_bind_param($stmt, "isssss", $customerId, $name, $email, $phone, $subject, $message);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = 'Your message has been sent successfully!';
        } else {
            $_SESSION['error'] = 'Failed to send message. Please try again later.';
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = 'Database error. Please try again later.';
    }

    header('Location: ../contact.php');
    exit();
} else {
    header('Location: ../contact.php');
    exit();
}