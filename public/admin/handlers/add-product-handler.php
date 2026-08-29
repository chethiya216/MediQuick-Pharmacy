<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Fix 1: Ensure clean relative includes without double-slash errors
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
$requiresRx     = isset($_POST['requires_prescription']) ? 1 : 0;
$reorderLevel   = (int)($_POST['reorder_level'] ?? 0);
$status         = $_POST['status'] ?? 'active';
$supplierId     = !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null; 

// Initial batch fields
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

// Batch validation
$batchProvided = ($batchNumber !== '' || $batchQuantity > 0 || $expiryDate !== '');
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
            $uploadDir = __DIR__ . '/uploads/products/';
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
| If validation failed, bounce back to add-products.php
|--------------------------------------------------------------------------
*/

if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    $_SESSION['old_input'] = $_POST;

    header("Location: ../add-products.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Check SKU uniqueness
|--------------------------------------------------------------------------
*/

$checkSql = "SELECT product_id FROM products WHERE sku = ? LIMIT 1";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("s", $sku);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    $checkStmt->close();
    $_SESSION['form_errors'] = ["This SKU is already in use by another product."];
    $_SESSION['old_input'] = $_POST;
    header("Location: ../add-products.php");
    exit;
}
$checkStmt->close();


/*
|--------------------------------------------------------------------------
| Insert product + optional initial batch (Database Transaction)
|--------------------------------------------------------------------------
*/

$conn->begin_transaction();

try {

    $sql = "
        INSERT INTO products (
            product_name, generic_name, description, sku, barcode,
            category_id, dosage_form, strength, unit_price, discount_percent,
            requires_prescription, reorder_level, product_image, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    // Fix 3: Strict type specifiers matching column types
    // Fix 3: Strict type specifiers matching column types
    // s = string, i = integer, d = double/decimal
    $unitPriceFloat = (float)$unitPrice;
    $discountPctFloat = (float)$discountPct;

    $stmt->bind_param(
        "sssssissddiiss",
        $productName,
        $genericName,
        $description,
        $sku,
        $barcode,
        $categoryId,
        $dosageForm,
        $strength,
        $unitPriceFloat,
        $discountPctFloat,
        $requiresRx,
        $reorderLevel,
        $productImagePath,
        $status
    );

    if (!$stmt->execute()) {
        throw new Exception("Failed to save product: " . $stmt->error);
    }

    $productId = $stmt->insert_id;
    $stmt->close();

    // Insert Initial Batch if provided
    if ($batchProvided) {

        // Fix 4: Insert initial_quantity and supplier_id matching product_batches table structure
        $batchSql = "
            INSERT INTO product_batches (
                product_id, supplier_id, batch_number, initial_quantity,
                quantity_on_hand, expiry_date, received_date
            ) VALUES (?, ?, ?, ?, ?, ?, CURDATE())
        ";

        $batchStmt = $conn->prepare($batchSql);

        if (!$batchStmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $batchStmt->bind_param(
            "iisiis",
            $productId,
            $supplierId,
            $batchNumber,
            $batchQuantity,
            $batchQuantity,
            $expiryDate
        );

        if (!$batchStmt->execute()) {
            throw new Exception("Failed to save initial batch: " . $batchStmt->error);
        }

        $batchStmt->close();
    }

    $conn->commit();

    $_SESSION['flash_success'] = "Product \"{$productName}\" was created successfully.";
    header("Location: ../manage-products.php");
    exit;

} catch (Exception $e) {

    $conn->rollback();

    // Delete uploaded image if database insert failed
    if ($productImagePath && file_exists(__DIR__ . '/' . $productImagePath)) {
        unlink(__DIR__ . '/' . $productImagePath);
    }

    $_SESSION['form_errors'] = [$e->getMessage()];
    $_SESSION['old_input'] = $_POST;
    header("Location: ../add-products.php");
    exit;
}