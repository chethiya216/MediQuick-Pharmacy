<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once __DIR__ . '/../../includes/auth.php';

requireLogin();

require_once __DIR__ . '/../../includes/db.php';

$pageTitle = "Category Management - MediQuick";

/*
|--------------------------------------------------------------------------
| Pagination Setup
|--------------------------------------------------------------------------
*/
$limit = 20; 
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) { $page = 1; }
$offset = ($page - 1) * $limit;

// Preserve existing search or filter parameters in pagination links
$queryParams = $_GET;

// 1. Get total number of products
$countSql = "SELECT COUNT(*) AS total FROM categories";
$countResult = $conn->query($countSql);
$totalProducts = $countResult->fetch_assoc()['total'] ?? 0;

$totalPages = ceil($totalProducts / $limit);
if ($totalPages < 1) { $totalPages = 1; }
if ($page > $totalPages) { $page = $totalPages; }

// 2. Fetch paginated products with category details
$sql = "
    SELECT *
    FROM categories 
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$categoryResult = $stmt->get_result();

// Function to generate page URLs while retaining existing search/filter query parameters
function getPageUrl($pageNumber, $queryParams) {
    $queryParams['page'] = $pageNumber;
    return '?' . http_build_query($queryParams);
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

                    <!-- PAGE TITLE -->
                    <div class="d-flex justify-content-between align-items-center py-3 mb-4">
                        <h4 class="fw-bold m-0">
                            <span class="text-muted fw-light">Categories /</span> Category Management
                        </h4>
                        <a href="add-products.php" class="btn btn-primary">
                            <i class="bx bx-plus me-1"></i> Add New Category
                        </a>
                    </div>

                    <!-- PRODUCT CARD -->
                    <div class="card">

                        <!-- CARD HEADER -->
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">All Categories</h5>
                        </div>

                        <!-- TABLE -->
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover">

                                <!-- TABLE HEADER -->
                                <thead>
                                    <tr>
                                        <th>Category ID</th>
                                        <th>Category Name</th>
                                        <!-- <th>Created By</th> -->
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>

                                <!-- TABLE BODY -->
                                <tbody class="table-border-bottom-0">

                                <?php if ($categoryResult && $categoryResult->num_rows > 0): ?>

                                    <?php while ($row = $categoryResult->fetch_assoc()): ?>

                                        <tr>

                                            <!-- Category ID -->
                                            <td>
                                                <strong>
                                                    #<?= htmlspecialchars($row['category_id']); ?>
                                                </strong>
                                            </td>

                                            <!-- CATEGORY NAME -->
                                            <td>
                                                <strong>
                                                    <?= htmlspecialchars($row['category_name']); ?>
                                                </strong>
                                            </td>

                                            <!-- CREATED AT -->
                                            <td>
                                                <?= htmlspecialchars($row['created_at'] ?? 'N/A'); ?>
                                            </td>

                                            <!-- ACTIONS -->
                                            <td>
                                                <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="add-categories.php?id=<?= $row['category_id']; ?>">
                                                            <i class="bx bx-edit-alt me-1"></i> Edit
                                                        </a>
                                                        <a class="dropdown-item text-danger" href="product-delete.php?id=<?= $row['category_id']; ?>" onclick="return confirm('Are you sure you want to delete this Category?');">
                                                            <i class="bx bx-trash me-1"></i> Delete
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>

                                        </tr>

                                    <?php endwhile; ?>

                                <?php else: ?>

                                    <!-- NO PRODUCTS FOUND -->
                                    <tr>
                                        <td colspan="9" class="text-center">
                                            No products found.
                                        </td>
                                    </tr>

                                <?php endif; ?>

                                </tbody>

                            </table>
                            
                        </div>
                        
                    </div>

                    <!-- PAGINATION NAVIGATION -->
                    <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center mt-4">
                            
                            <!-- Previous Button -->
                            <li class="page-item prev <?= ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?= ($page > 1) ? getPageUrl($page - 1, $queryParams) : 'javascript:void(0);'; ?>">
                                    <i class="tf-icon bx bx-chevrons-left"></i>
                                </a>
                            </li>

                            <!-- Page Numbers -->
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= ($i === $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?= getPageUrl($i, $queryParams); ?>">
                                        <?= $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next Button -->
                            <li class="page-item next <?= ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?= ($page < $totalPages) ? getPageUrl($page + 1, $queryParams) : 'javascript:void(0);'; ?>">
                                    <i class="tf-icon bx bx-chevrons-right"></i>
                                </a>
                            </li>

                        </ul>
                    </nav>
                    <?php endif; ?>

                    <!-- PAGINATION NAVIGATION -->
                    <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center mt-4">
                            
                            <!-- Previous Button -->
                            <li class="page-item prev <?= ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?= ($page > 1) ? getPageUrl($page - 1, $queryParams) : 'javascript:void(0);'; ?>">
                                    <i class="tf-icon bx bx-chevrons-left"></i>
                                </a>
                            </li>

                            <!-- Page Numbers -->
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= ($i === $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?= getPageUrl($i, $queryParams); ?>">
                                        <?= $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next Button -->
                            <li class="page-item next <?= ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?= ($page < $totalPages) ? getPageUrl($page + 1, $queryParams) : 'javascript:void(0);'; ?>">
                                    <i class="tf-icon bx bx-chevrons-right"></i>
                                </a>
                            </li>

                        </ul>
                    </nav>
                    <?php endif; ?>

                </div>

                <!-- FOOTER -->
                <?php require_once __DIR__ . '/includes/footer.php'; ?>

                <div class="content-backdrop fade"></div>

            </div>

        </div>

    </div>

    <!-- OVERLAY -->
    <div class="layout-overlay layout-menu-toggle"></div>

</div>

</body>
</html>