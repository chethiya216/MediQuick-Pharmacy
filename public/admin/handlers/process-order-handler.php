<?php
// public/admin/handlers/process-order-handler.php
session_start();

// Relative imports going up 1 level to access public/admin/includes/
require_once __DIR__ . '/../../../includes/auth.php';
requirePharmacist();

require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../includes/prescription-functions.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['place_order'])) {
    header("Location: ../manage-prescriptions.php");
    exit;
}

$prescription_id = isset($_POST['prescription_id']) ? (int)$_POST['prescription_id'] : 0;
$selected_items  = json_decode($_POST['items_json'] ?? '[]', true);

$shippingAddress = [
    'line1'       => trim($_POST['shipping_address_line1'] ?? ''),
    'line2'       => trim($_POST['shipping_address_line2'] ?? ''),
    'city'        => trim($_POST['shipping_city'] ?? ''),
    'state'       => trim($_POST['shipping_state'] ?? ''),
    'postal_code' => trim($_POST['shipping_postal_code'] ?? ''),
    'country'     => trim($_POST['shipping_country'] ?? 'Sri Lanka')
];

// Input Validation
if ($prescription_id <= 0) {
    $_SESSION['flash_error'] = "Invalid Prescription ID.";
    header("Location: ../manage-prescriptions.php");
    exit;
}

$prescription = getConfirmedPrescriptionById($conn, $prescription_id);
if (!$prescription) {
    $_SESSION['flash_error'] = "Prescription not found or not yet confirmed by customer.";
    header("Location: ../manage-prescriptions.php");
    exit;
}

if (empty($selected_items)) {
    $_SESSION['flash_error'] = "Please select at least one product before placing the order.";
    header("Location: ../process-prescription-order.php?id=" . $prescription_id);
    exit;
}

if (empty($shippingAddress['line1']) || empty($shippingAddress['city']) || empty($shippingAddress['state']) || empty($shippingAddress['postal_code'])) {
    $_SESSION['flash_error'] = "Please provide all required shipping address fields.";
    header("Location: ../process-prescription-order.php?id=" . $prescription_id);
    exit;
}

// Order Creation Execution
try {
    $order_id = createPrescriptionOrder(
        $conn, 
        (int)$prescription['customer_id'], 
        $prescription_id, 
        $selected_items, 
        $shippingAddress
    );

    header("Location: ../manage-order.php?success=order_created&order_id=" . $order_id);
    exit;

} catch (Exception $e) {
    $_SESSION['flash_error'] = "Order processing failed: " . $e->getMessage();
    header("Location: ../process-prescription-order.php?id=" . $prescription_id);
    exit;
}