<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
requireLogin();

// Ensure customer access
if (function_exists('requireCustomer')) {
    requireCustomer();
} elseif (empty($_SESSION['customer_id'])) {
    header('Location: /login.php');
    exit;
}

$customerId = (int) $_SESSION['customer_id'];

function redirectWithError($message) {
    $_SESSION['flash_error'] = $message;
    header('Location: ../upload-prescription.php');
    exit;
}

// 1. Validate Request Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithError('Invalid request method.');
}

// 2. Validate CSRF Token
if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    redirectWithError('Invalid security token. Please try again.');
}

// 3. Get Prescription ID
$prescriptionId = (int) ($_POST['prescription_id'] ?? 0);

if ($prescriptionId <= 0) {
    redirectWithError('Invalid prescription selection.');
}

// 4. Update Database (Only when pharmacist status is 'verified' and customer status is 'pending')
$updateSql = "
    UPDATE prescriptions 
    SET customer_status = 'confirmed',
        customer_confirmed_at = NOW()
    WHERE prescription_id = ? 
      AND customer_id = ? 
      AND status = 'verified' 
      AND customer_status = 'pending'
";

$stmt = $conn->prepare($updateSql);

if (!$stmt) {
    redirectWithError('Database error occurred. Please try again later.');
}

$stmt->bind_param("ii", $prescriptionId, $customerId);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    $_SESSION['flash_success'] = 'Order confirmed successfully! A pharmacist will process your items now.';
} else {
    redirectWithError('Unable to confirm order. The prescription may already be confirmed or has not yet been verified by a pharmacist.');
}

$stmt->close();
header('Location: ../upload-prescription.php');
exit;