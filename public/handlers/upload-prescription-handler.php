<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary authentication and database files
// Adjust relative paths depending on where this handler is stored relative to /includes/
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

// Helper function to redirect back with flash messages
function redirectWithError($message) {
    $_SESSION['flash_error'] = $message;
    header('Location: ../upload-prescription.php');
    exit;
}

// 1. Verify Request Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithError('Invalid request method.');
}

// 2. Verify CSRF Token
if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    redirectWithError('Invalid security token. Please try again.');
}

// 3. Capture & Sanitize Form Data
$issueDate = trim($_POST['issue_date'] ?? '');

// If issue date is provided, validate format (YYYY-MM-DD)
if (!empty($issueDate)) {
    $d = DateTime::createFromFormat('Y-m-d', $issueDate);
    if (!$d || $d->format('Y-m-d') !== $issueDate) {
        redirectWithError('Invalid issue date format.');
    }
    // Optional: Check if date is in the future
    if ($issueDate > date('Y-m-d')) {
        redirectWithError('Issue date cannot be in the future.');
    }
} else {
    $issueDate = null; // Or default to current date depending on your DB schema
}

// 4. Validate File Upload
if (!isset($_FILES['prescription_file']) || $_FILES['prescription_file']['error'] === UPLOAD_ERR_NO_FILE) {
    redirectWithError('Please select a prescription file to upload.');
}

$file = $_FILES['prescription_file'];

// Check for upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    redirectWithError('An error occurred during file upload. Error code: ' . $file['error']);
}

// Validate File Size (Max 5MB)
$maxSize = 5 * 1024 * 1024;
if ($file['size'] > $maxSize) {
    redirectWithError('File size exceeds the 5MB limit.');
}

// Validate Real MIME Type using Fileinfo
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);

$allowedMimes = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'application/pdf' => 'pdf'
];

if (!array_key_exists($mimeType, $allowedMimes)) {
    redirectWithError('Invalid file type. Only JPG, PNG, and PDF files are allowed.');
}

$extension = $allowedMimes[$mimeType];

// 5. Secure File Storage
// Ensure directory exists: public/uploads/prescriptions/
$uploadDir = __DIR__ . '/../uploads/prescriptions/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate unique, secure file name
$safeFileName = sprintf(
    'prescription_%d_%s_%s.%s',
    $customerId,
    date('Ymd_His'),
    bin2hex(random_bytes(4)),
    $extension
);

$destination = $uploadDir . $safeFileName;
// Relative path to store in database (matches what your view expects)
$dbFilePath = 'uploads/prescriptions/' . $safeFileName;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    redirectWithError('Failed to save the uploaded file. Please try again.');
}

// 6. Save Record to Database
$insertSql = "
    INSERT INTO prescriptions (customer_id, file_path, status, created_at)
    VALUES (?, ?, 'pending', NOW())
";

$stmt = $conn->prepare($insertSql);

if (!$stmt) {
    // Clean up uploaded file if DB prep fails
    if (file_exists($destination)) {
        unlink($destination);
    }
    redirectWithError('Database error occurred. Please try again later.');
}

$stmt->bind_param("is", $customerId, $dbFilePath);

if ($stmt->execute()) {
    $_SESSION['flash_success'] = 'Prescription uploaded successfully and is pending review.';
    header('Location: ../upload-prescription.php');
    exit;
} else {
    // Clean up uploaded file if DB execution fails
    if (file_exists($destination)) {
        unlink($destination);
    }
    redirectWithError('Failed to save prescription details to the database.');
}