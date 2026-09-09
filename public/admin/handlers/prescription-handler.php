<?php
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../manage-prescriptions.php");
    exit;
}

$prescriptionId = (int) ($_POST['id'] ?? 0);

if ($prescriptionId <= 0) {
    $_SESSION['flash_error'] = "Invalid item.";
    header("Location: ../manage-prescriptions.php");
    exit;
}

$stmt = $conn->prepare("DELETE FROM prescriptions WHERE prescription_id = ?");
$stmt->bind_param("i", $prescriptionId);
 
if ($stmt->execute()) {
    $_SESSION['flash_success'] = "Prescription deleted.";
} else {
    $_SESSION['flash_error'] = "Could not delete this prescription. (" . $stmt->error . ") It may be referenced by a prescription item.";
}
 
$stmt->close();
 
header("Location: ../manage-prescriptions.php");
exit;