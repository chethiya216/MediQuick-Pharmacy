<?php 
ob_start();
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/upload-helper.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// --- ACTION 1: DELETE BATCH ---
if ($action === 'delete' || isset($_POST['delete_batch'])) {
    
    $batch_id = (int)($_POST['id'] ?? $_POST['batch_id'] ?? $_GET['batch_id'] ?? 0);

    if ($batch_id > 0) {
        $stmt = $conn->prepare("DELETE FROM product_batches WHERE batch_id = ?");
        
        if (!$stmt) {
            $_SESSION['error'] = "Database error: " . $conn->error;
        } else {
            $stmt->bind_param("i", $batch_id);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Batch deleted successfully.";
            } else {
                $_SESSION['error'] = "Failed to delete batch: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $_SESSION['error'] = "Invalid Batch ID.";
    }

    ob_end_clean();
    header("Location: ../manage-batch.php");
    exit;
}

// --- ACTION 2: ADD PRODUCT BATCH ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $message = '';

    $product_id = (int)($_POST['product_id'] ?? 0);
    $supplier_id = (int)($_POST['supplier_id'] ?? 0);

    $batch_number = trim($_POST['batch_number'] ?? '');
    $purchase_reference = trim($_POST['purchase_reference'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    $quantity = (int)($_POST['quantity'] ?? 0);

    $purchase_price = (float)($_POST['purchase_price'] ?? 0);
    $selling_price = (float)($_POST['selling_price'] ?? 0);

    $manufacture_date = !empty($_POST['manufacture_date']) ? $_POST['manufacture_date'] : null;
    $expiry_date = $_POST['expiry_date'] ?? '';
    $received_date = $_POST['received_date'] ?? '';

    // Validations
    if ($product_id <= 0) {
        $message = 'Please select a product.';
    } elseif ($supplier_id <= 0) {
        $message = 'Please select a supplier.';
    } elseif ($batch_number === '') {
        $message = 'Please enter the batch number.';
    } elseif ($quantity <= 0) {
        $message = 'Quantity must be greater than 0.';
    } elseif ($purchase_price < 0) {
        $message = 'Purchase price cannot be negative.';
    } elseif ($selling_price < 0) {
        $message = 'Selling price cannot be negative.';
    } elseif (empty($expiry_date)) {
        $message = 'Please select the expiry date.';
    } elseif (empty($received_date)) {
        $message = 'Please select the received date.';
    } elseif (!empty($manufacture_date) && $expiry_date <= $manufacture_date) {
        $message = 'Expiry date must be after manufacture date.';
    } else {

        $invoice_file = null;

        // Process File Upload
        if (
            isset($_FILES['invoice_file']) &&
            $_FILES['invoice_file']['error'] !== UPLOAD_ERR_NO_FILE
        ) {
            $invoiceUpload = uploadInvoiceImage($_FILES['invoice_file']);

            if (!$invoiceUpload['success']) {
                $message = $invoiceUpload['error'];
            } else {
                $invoice_file = $invoiceUpload['filepath'];
            }
        }

        if ($message === '') {

            $sql = "
                INSERT INTO product_batches
                (
                    product_id,
                    supplier_id,
                    batch_number,
                    purchase_reference,
                    initial_quantity,
                    quantity_on_hand,
                    purchase_price,
                    selling_price,
                    manufacture_date,
                    expiry_date,
                    received_date,
                    invoice_file,
                    notes,
                    status
                )
                VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
            ";

            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                $_SESSION['error'] = 'Database error: ' . $conn->error;
                $message = $_SESSION['error'];
            } else {
                // FIXED: 13 parameters mapped correctly to 13 placeholders
                // i = product_id, i = supplier_id, s = batch_number, s = purchase_reference
                // i = initial_quantity, i = quantity_on_hand, d = purchase_price, d = selling_price
                // s = manufacture_date, s = expiry_date, s = received_date, s = invoice_file, s = notes
                $stmt->bind_param(
                    "iissiiddsssss",
                    $product_id,
                    $supplier_id,
                    $batch_number,
                    $purchase_reference,
                    $quantity,
                    $quantity,
                    $purchase_price,
                    $selling_price,
                    $manufacture_date,
                    $expiry_date,
                    $received_date,
                    $invoice_file,
                    $notes
                );

                if ($stmt->execute()) {
                    $stmt->close();
                    $_SESSION['success'] = "Batch added successfully.";
                    ob_end_clean();
                    header('Location: ../manage-batch.php');
                    exit;
                } else {
                    $_SESSION['error'] = 'Failed to add batch: ' . $stmt->error;
                    $message = $_SESSION['error'];
                    $stmt->close();
                }
            }
        }
    }

    // Redirect back to add-batch page if an error occurred during submission
    if ($message !== '') {
        $_SESSION['error'] = $message;
        ob_end_clean();
        header('Location: ../add-batch.php');
        exit;
    }
}

// Fallback redirect if no POST request occurred
ob_end_clean();
header("Location: ../manage-batch.php");
exit;