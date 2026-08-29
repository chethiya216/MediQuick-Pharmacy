<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../../../includes/db.php';

// Only accept POST submissions from the form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../add-products.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Collect + sanitize form input
|--------------------------------------------------------------------------
*/

$productId      = !empty($_POST['product_id']) ? (int)$_POST['product_id'] : null;
$isEdit         = !empty($productId);

$productName    = trim($_POST['product_name'] ?? '');
$genericName    = trim($_POST['generic_name'] ?? '') ?: null;
$description    = trim($_POST['description'] ?? '') ?: null;
$sku            = trim($_POST['sku'] ?? '');
$barcode        = trim($_POST['barcode'] ?? '') ?: null;
$categoryId     = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
$dosageForm     = trim($_POST['dosage_form'] ?? '');
$strength       = trim($_POST['strength'] ?? '') ?: null;
$unitPrice      = $_POST['unit_price'] ?? '';
$discountPct    = $_POST['discount_percent'] ?? '0';
$requiresRx     = isset($_POST['requires_prescription']) ? (int)$_POST['requires_prescription'] : 0;
$reorderLevel   = (int)($_POST['reorder_level'] ?? 0);
$status         = $_POST['status'] ?? 'active';
$supplierId     = !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null; 

// Initial batch fields (Only relevant when creating a new product)
$batchNumber    = trim($_POST['batch_number'] ?? '');
$batchQuantity  = (int)($_POST['batch_quantity'] ?? 0);
$expiryDate     = trim($_POST['expiry_date'] ?? '');

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

$errors = [];

if ($productName === '') {
    $errors[] = "Product name is required.";
}

if ($sku === '') {
    $errors[] = "SKU / product code is required.";
}

if ($dosageForm === '') {
    $errors[] = "Dosage form is required.";
}

if (empty($categoryId)) {
    $errors[] = "Please select a category.";
}

if ($unitPrice === '' || !is_numeric($unitPrice) || (float)$unitPrice < 0) {
    $errors[] = "Unit price must be a valid non-negative number.";
}

if (!in_array($status, ['active', 'draft', 'archived'], true)) {
    $errors[] = "Invalid status.";
}

// Batch validation (only evaluated on insert)
$batchProvided = (!$isEdit && ($batchNumber !== '' || $batchQuantity > 0 || $expiryDate !== ''));
if ($batchProvided) {
    if ($batchNumber === '') {
        $errors[] = "Batch number is required if adding an initial batch.";
    }
    if ($expiryDate === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $expiryDate)) {
        $errors[] = "A valid expiry date is required if adding an initial batch.";
    }
    if ($batchQuantity <= 0) {
        $errors[] = "Batch quantity must be greater than 0 if adding an initial batch.";
    }
}

/*
|--------------------------------------------------------------------------
| Check SKU uniqueness (ignore current product if editing)
|--------------------------------------------------------------------------
*/

if ($isEdit) {
    $checkSql = "SELECT product_id FROM products WHERE sku = ? AND product_id != ? LIMIT 1";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("si", $sku, $productId);
} else {
    $checkSql = "SELECT product_id FROM products WHERE sku = ? LIMIT 1";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $sku);
}

$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    $checkStmt->close();
    $errors[] = "This SKU is already in use by another product.";
} else {
    $checkStmt->close();
}

/*
|--------------------------------------------------------------------------
| Handle product image upload
|--------------------------------------------------------------------------
*/

$productImagePath = null;

if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['product_image'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Product image failed to upload.";
    } else {
        $allowedTypes = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp'];
        $mimeType = mime_content_type($file['tmp_name']);

        if (!isset($allowedTypes[$mimeType])) {
            $errors[] = "Product image must be a PNG, JPG, or WEBP file.";
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $errors[] = "Product image must be 2MB or smaller.";
        } else {
            $uploadDir = UPLOAD_BASE_PATH . 'products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filename = 'prod_' . bin2hex(random_bytes(8)) . '.' . $allowedTypes[$mimeType];
            $destination = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $productImagePath = 'uploads/products/' . $filename;
            } else {
                $errors[] = "Could not save the uploaded product image.";
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| If validation failed, bounce back to form
|--------------------------------------------------------------------------
*/

if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    $_SESSION['old_input'] = $_POST;

    $redirectUrl = $isEdit ? "../add-products.php?id=" . $productId : "../add-products.php";
    header("Location: " . $redirectUrl);
    exit;
}

/*
|--------------------------------------------------------------------------
| Save/Update Product (Database Transaction)
|--------------------------------------------------------------------------
*/

$conn->begin_transaction();

try {
    $unitPriceFloat = (float)$unitPrice;
    $discountPctFloat = (float)$discountPct;

    if ($isEdit) {
        // --- UPDATE EXISTING PRODUCT ---
        if ($productImagePath !== null) {
            // New image uploaded -> update image field
            $sql = "
                UPDATE products SET
                    product_name = ?, generic_name = ?, description = ?, sku = ?, barcode = ?,
                    category_id = ?, dosage_form = ?, strength = ?, unit_price = ?, discount_percent = ?,
                    requires_prescription = ?, reorder_level = ?, product_image = ?, status = ?
                WHERE product_id = ?
            ";
            $stmt = $conn->prepare($sql);
            if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

            $stmt->bind_param(
                "sssssissddiissi",
                $productName, $genericName, $description, $sku, $barcode,
                $categoryId, $dosageForm, $strength, $unitPriceFloat, $discountPctFloat,
                $requiresRx, $reorderLevel, $productImagePath, $status, $productId
            );
        } else {
            // Keep existing image -> skip updating product_image
            $sql = "
                UPDATE products SET
                    product_name = ?, generic_name = ?, description = ?, sku = ?, barcode = ?,
                    category_id = ?, dosage_form = ?, strength = ?, unit_price = ?, discount_percent = ?,
                    requires_prescription = ?, reorder_level = ?, status = ?
                WHERE product_id = ?
            ";
            $stmt = $conn->prepare($sql);
            if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

            $stmt->bind_param(
                "sssssissddiisi",
                $productName, $genericName, $description, $sku, $barcode,
                $categoryId, $dosageForm, $strength, $unitPriceFloat, $discountPctFloat,
                $requiresRx, $reorderLevel, $status, $productId
            );
        }

        if (!$stmt->execute()) {
            throw new Exception("Failed to update product: " . $stmt->error);
        }
        $stmt->close();

        $conn->commit();
        $_SESSION['flash_success'] = "Product \"{$productName}\" was updated successfully.";

    } else {
        // --- INSERT NEW PRODUCT ---
        $sql = "
            INSERT INTO products (
                product_name, generic_name, description, sku, barcode,
                category_id, dosage_form, strength, unit_price, discount_percent,
                requires_prescription, reorder_level, product_image, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

        $stmt->bind_param(
            "sssssissddiiss",
            $productName, $genericName, $description, $sku, $barcode,
            $categoryId, $dosageForm, $strength, $unitPriceFloat, $discountPctFloat,
            $requiresRx, $reorderLevel, $productImagePath, $status
        );

        if (!$stmt->execute()) {
            throw new Exception("Failed to save product: " . $stmt->error);
        }

        $productId = $stmt->insert_id;
        $stmt->close();

        // Insert Initial Batch if provided
        if ($batchProvided) {
            $batchSql = "
                INSERT INTO product_batches (
                    product_id, supplier_id, batch_number, initial_quantity,
                    quantity_on_hand, expiry_date, received_date
                ) VALUES (?, ?, ?, ?, ?, ?, CURDATE())
            ";

            $batchStmt = $conn->prepare($batchSql);
            if (!$batchStmt) throw new Exception("Prepare failed: " . $conn->error);

            $batchStmt->bind_param(
                "iisiis",
                $productId, $supplierId, $batchNumber, $batchQuantity,
                $batchQuantity, $expiryDate
            );

            if (!$batchStmt->execute()) {
                throw new Exception("Failed to save initial batch: " . $batchStmt->error);
            }

            $batchStmt->close();
        }

        $conn->commit();
        $_SESSION['flash_success'] = "Product \"{$productName}\" was created successfully.";
    }

    header("Location: ../manage-products.php");
    exit;

} catch (Exception $e) {
    $conn->rollback();

    // Remove uploaded image if transaction failed
    if ($productImagePath && file_exists(UPLOAD_BASE_PATH . 'products/' . basename($productImagePath))) {
        unlink(UPLOAD_BASE_PATH . 'products/' . basename($productImagePath));
    }

    $_SESSION['form_errors'] = [$e->getMessage()];
    $_SESSION['old_input'] = $_POST;

    $redirectUrl = $isEdit ? "../add-products.php?id=" . $productId : "../add-products.php";
    header("Location: " . $redirectUrl);
    exit;
}