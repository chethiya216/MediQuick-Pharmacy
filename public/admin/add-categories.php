<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
require_once __DIR__ . '/../../includes/db.php';

$pageTitle = "Category - MediQuick";

// Initialize default variables FIRST
$category_id  = null;
$category     = [];
$categoryName = '';
$description  = '';
$status       = 'active';

// THEN check $_GET and fetch data from DB
if (isset($_GET['category_id']) && is_numeric($_GET['category_id'])) {
    $category_id = (int)$_GET['category_id'];

    $stmt = $conn->prepare("SELECT * FROM categories WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $category     = $row;
        $categoryName = $row['category_name'] ?? '';
        $description  = $row['description'] ?? '';
        $status       = $row['status'] ?? 'active';
    }
}
?>

<!DOCTYPE html>
<html
    lang="en"
    class="light-style layout-menu-fixed"
    dir="ltr"
    data-theme="theme-default"
    data-assets-path="../admin-assets/assets/"
    data-template="vertical-menu-template-free"
>

<?php require_once __DIR__ . '/includes/head.php'; ?>

<body>

<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">

        <!-- SIDEBAR -->
        <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

        <div class="layout-page">

            <!-- HEADER -->
            <?php require_once __DIR__ . '/includes/header.php'; ?>

            <!-- CONTENT WRAPPER -->
            <div class="content-wrapper">

                <!-- CONTENT -->
                <div class="container-xxl flex-grow-1 container-p-y">

                    <!-- BREADCRUMB -->
                    <div class="d-flex justify-content-between align-items-center py-3 mb-4">
                        <h4 class="fw-bold m-0">
                            <span class="text-muted fw-light">Categories /</span>
                            <?= $category_id ? 'Edit Category' : 'Add Category'; ?>
                        </h4>
                        <a href="manage-categories.php" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Back to Categories
                        </a>
                    </div>

                    <!-- ERROR ALERTS -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <ul class="mb-0">
                                <?php foreach ($errors as $err): ?>
                                    <li><?= htmlspecialchars($err); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- FORM CONTAINER -->
                    <div class="row">
                        <div class="col-12 col-md-8 col-lg-6 mx-auto">
                            <div class="card border">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <?= $category_id ? 'Edit Category Information' : 'Add New Category'; ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="../admin/handlers/category-handler.php">
                                        
                                        <!-- HIDDEN ID FIELD (CRITICAL FOR UPDATE) -->
                                        <?php if ($category_id): ?>
                                            <input type="hidden" name="category_id" value="<?= $category_id; ?>" />
                                        <?php endif; ?>

                                        <!-- CATEGORY NAME -->
                                        <div class="mb-3">
                                            <label for="category_name" class="form-label">Category Name <span class="text-danger">*</span></label>
                                            <input 
                                                type="text" 
                                                class="form-control" 
                                                id="category_name" 
                                                name="category_name" 
                                                value="<?= htmlspecialchars($categoryName); ?>" 
                                                placeholder="e.g. Pain Relief, Antibiotics" 
                                                required 
                                            />
                                        </div>

                                        <!-- DESCRIPTION -->
                                        <div class="mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea 
                                                class="form-control" 
                                                id="description" 
                                                name="description" 
                                                rows="4" 
                                                placeholder="Enter category details or notes..."
                                            ><?= htmlspecialchars($description); ?></textarea>
                                        </div>

                                        <!-- STATUS -->
                                        <div class="mb-4">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="active" <?= $status === 'active' ? 'selected' : ''; ?>>Active</option>
                                                <option value="inactive" <?= $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                            </select>
                                        </div>

                                        <!-- SUBMIT BUTTONS -->
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <?= $category_id ? 'Update Category' : 'Save Category'; ?>
                                            </button>
                                            <a href="manage-categories.php" class="btn btn-outline-secondary">
                                                Cancel
                                            </a>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- FOOTER -->
                <?php require_once __DIR__ . '/includes/footer.php'; ?>
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
</div>

</body>
</html>