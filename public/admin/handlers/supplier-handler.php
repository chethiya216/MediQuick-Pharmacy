<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../../../includes/db.php';

// --- 1. HANDLE DELETE ACTION ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['supplier_id'])) {
    $supplierId = (int)$_GET['supplier_id'];

    $stmt = $conn->prepare("DELETE FROM suppliers WHERE supplier_id = ?");
    $stmt->bind_param("i", $supplierId);

    if ($stmt->execute()) {
        $_SESSION['flash_success'] = "Supplier deleted successfully.";
    } else {
        $_SESSION['form_errors'] = ["Failed to delete supplier: " . $conn->error];
    }
    $stmt->close();

    header("Location: ../manage-suppliers.php");
    exit;
}

// --- 2. RESTRICT TO POST SUBMISSIONS FOR ADD/UPDATE ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../add-suppliers.php");
    exit;
}

// Extract form inputs (matching table structure: name, contact_person, address, phone, email)
$supplierId    = !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null;
$name          = trim($_POST['name'] ?? '');
$contactPerson = trim($_POST['contact_person'] ?? '');
$phone         = trim($_POST['phone'] ?? '');
$email         = trim($_POST['email'] ?? '');
$address       = trim($_POST['address'] ?? '');
$errors        = [];

// --- 3. VALIDATION ---
if (empty($name)) {
    $errors[] = "Supplier Name is required.";
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
}

// Check for duplicate supplier name
if (empty($errors)) {
    if ($supplierId) {
        $checkSql = "SELECT supplier_id FROM suppliers WHERE name = ? AND supplier_id != ? LIMIT 1";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("si", $name, $supplierId);
    } else {
        $checkSql = "SELECT supplier_id FROM suppliers WHERE name = ? LIMIT 1";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $name);
    }

    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows > 0) {
        $errors[] = "A supplier with this name already exists.";
    }
    $checkStmt->close();
}

// Redirect back if validation fails
if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    $_SESSION['old_input']   = $_POST;

    $redirectUrl = $supplierId ? "../add-suppliers.php?supplier_id={$supplierId}" : "../add-suppliers.php";
    header("Location: " . $redirectUrl);
    exit;
}

// --- 4. EXECUTE INSERT OR UPDATE TRANSACTION ---
$conn->begin_transaction();

try {
    if ($supplierId) {
        // UPDATE Existing Supplier
        $sql = "
            UPDATE suppliers SET
                name = ?, contact_person = ?, address = ?, phone = ?, email = ?, updated_at = NOW()
            WHERE supplier_id = ?
        ";
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

        $stmt->bind_param("sssssi", $name, $contactPerson, $address, $phone, $email, $supplierId);

        if (!$stmt->execute()) {
            throw new Exception("Failed to update supplier: " . $stmt->error);
        }
        $stmt->close();

        $_SESSION['flash_success'] = "Supplier \"{$name}\" updated successfully.";
    } else {
        // INSERT New Supplier
        $sql = "
            INSERT INTO suppliers (name, contact_person, address, phone, email, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())
        ";
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

        $stmt->bind_param("sssss", $name, $contactPerson, $address, $phone, $email);

        if (!$stmt->execute()) {
            throw new Exception("Failed to add supplier: " . $stmt->error);
        }
        $stmt->close();

        $_SESSION['flash_success'] = "Supplier \"{$name}\" added successfully.";
    }

    $conn->commit();
    header("Location: ../manage-suppliers.php");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['form_errors'] = [$e->getMessage()];
    $_SESSION['old_input']   = $_POST;

    $redirectUrl = $supplierId ? "../add-suppliers.php?supplier_id={$supplierId}" : "../add-suppliers.php";
    header("Location: " . $redirectUrl);
    exit;
}