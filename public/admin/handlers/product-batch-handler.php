<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/upload-helper.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Handle Delete Batch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_batch'])) {

    $batch_id = (int)($_POST['batch_id'] ?? 0);

    if ($batch_id > 0) {

        $delete_stmt = $conn->prepare("
            DELETE FROM product_batches
            WHERE batch_id = ?
        ");

        if (!$delete_stmt) {
            die("Database error: " . $conn->error);
        }

        $delete_stmt->bind_param("i", $batch_id);

        if ($delete_stmt->execute()) {
            $delete_stmt->close();
            header("Location: ../manage-batch.php?success=deleted");
            exit;
        } else {
            $delete_error = "Unable to delete product batch.";
            $delete_stmt->close();
        }
    }
}

// Handle Add Product Batch
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $message = '';
    $message_type = '';

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
        $message_type = 'danger';
    } elseif ($supplier_id <= 0) {
        $message = 'Please select a supplier.';
        $message_type = 'danger';
    } elseif ($batch_number === '') {
        $message = 'Please enter the batch number.';
        $message_type = 'danger';
    } elseif ($quantity <= 0) {
        $message = 'Quantity must be greater than 0.';
        $message_type = 'danger';
    } elseif ($purchase_price < 0) {
        $message = 'Purchase price cannot be negative.';
        $message_type = 'danger';
    } elseif ($selling_price < 0) {
        $message = 'Selling price cannot be negative.';
        $message_type = 'danger';
    } elseif (empty($expiry_date)) {
        $message = 'Please select the expiry date.';
        $message_type = 'danger';
    } elseif (empty($received_date)) {
        $message = 'Please select the received date.';
        $message_type = 'danger';
    } elseif (!empty($manufacture_date) && $expiry_date <= $manufacture_date) {
        $message = 'Expiry date must be after manufacture date.';
        $message_type = 'danger';
    } else {

        $invoice_file = null;

        // Process File Upload via uploadInvoiceImage()
        if (
            isset($_FILES['invoice_file']) &&
            $_FILES['invoice_file']['error'] !== UPLOAD_ERR_NO_FILE
        ) {
            $invoiceUpload = uploadInvoiceImage($_FILES['invoice_file']);

            if (!$invoiceUpload['success']) {
                $message = $invoiceUpload['error'];
                $message_type = 'danger';
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
                $message = 'Database error: ' . $conn->error;
                $message_type = 'danger';
            } else {
                // Binding 13 fields: iisssiddsssss
                $stmt->bind_param(
                    "iisssiddsssss",
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
                    header('Location: ../manage-batch.php?success=added');
                    exit;
                } else {
                    $message = 'Failed to add batch: ' . $stmt->error;
                    $message_type = 'danger';
                    $stmt->close();
                }
            }
        }
    }
}
?>