<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../../../includes/db.php';

// Only accept POST submissions from the form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/add-categories.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryId   = (int)($_POST['category_id'] ?? 0);
    $categoryName = trim($_POST['category_name'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $status       = $_POST['status'] ?? 'active';

    // Validation
    if (empty($categoryName)) {
        $errors[] = "Category Name is required.";
    }

    if (!in_array($status, ['active', 'inactive'])) {
        $errors[] = "Invalid status selected.";
    }

    // Check for duplicate category name
    if (empty($errors)) {
        $checkSql  = "SELECT category_id FROM categories WHERE category_name = ? LIMIT 1";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $categoryName);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            $errors[] = "A category with this name already exists.";
        }
    }

    // Insert into database
    if (empty($errors)) {
        $insertSql = "
            INSERT INTO categories (category_name, description, status, created_at, updated_at) 
            VALUES (?, ?, ?, NOW(), NOW())
        ";

        $stmt = $conn->prepare($insertSql);
        $stmt->bind_param("sss", $categoryName, $description, $status);

        if ($stmt->execute()) {
            $_SESSION['flash_success'] = "Category '{$categoryName}' added successfully!";
            header("Location: ../manage-categories.php");
            exit;
        } else {
            $errors[] = "Database error: " . $conn->error;
        }
    }
}

// 3. Perform Insert or Update
$conn->begin_transaction();

try {
    if ($categoryId) {
        // UPDATE Existing Category
        $sql = "UPDATE categories SET category_name = ?, description = ?, status = ?, updated_at = NOW() WHERE category_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

        $stmt->bind_param("sssi", $categoryName, $description, $status, $categoryId);

        if (!$stmt->execute()) {
            throw new Exception("Failed to update category: " . $stmt->error);
        }
        $stmt->close();
        
        $_SESSION['flash_success'] = "Category \"{$categoryName}\" updated successfully.";
    } else {
        // INSERT New Category
        $sql = "INSERT INTO categories (category_name, description, status, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

        $stmt->bind_param("sss", $categoryName, $description, $status);

        if (!$stmt->execute()) {
            throw new Exception("Failed to add category: " . $stmt->error);
        }
        $stmt->close();

        $_SESSION['flash_success'] = "Category \"{$categoryName}\" added successfully.";
    }

    $conn->commit();
    header("Location: ../manage-categories.php");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['form_errors'] = [$e->getMessage()];
    $_SESSION['old_input']   = $_POST;

    $redirectUrl = $categoryId ? "../add-categories.php?category_id={$categoryId}" : "../add-categories.php";
    header("Location: " . $redirectUrl);
    exit;
}