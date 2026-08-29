<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once __DIR__ . '/../../includes/auth.php';

requireLogin();

require_once __DIR__ . '/../../includes/db.php';

$pageTitle = "Product Management - MediQuick";

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
$countSql = "SELECT COUNT(*) AS total FROM products";
$countResult = $conn->query($countSql);
$totalProducts = $countResult->fetch_assoc()['total'] ?? 0;

$totalPages = ceil($totalProducts / $limit);
if ($totalPages < 1) { $totalPages = 1; }
if ($page > $totalPages) { $page = $totalPages; }

// 2. Fetch paginated products with category details
$sql = "
    SELECT 
        p.product_id,
        p.product_name,
        p.product_image,
        p.sku,
        p.created_at,
        c.category_name,
        p.unit_price,
        p.requires_prescription,
        p.status
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    ORDER BY p.product_id DESC
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$productsResult = $stmt->get_result();

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
                            <span class="text-muted fw-light">Products /</span> Product Management
                        </h4>
                        <a href="add-products.php" class="btn btn-primary">
                            <i class="bx bx-plus me-1"></i> Add New Product
                        </a>
                    </div>

                    <!-- PRODUCT CARD -->
                    <div class="card">

                        <!-- CARD HEADER -->
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">All Products</h5>
                        </div>

                        <!-- TABLE -->
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover">

                                <!-- TABLE HEADER -->
                                <thead>
                                    <tr>
                                        <th>Product ID</th>
                                        <th>Product Image</th>
                                        <th>Product Name</th>
                                        <th>SKU</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>

                                <!-- TABLE BODY -->
                                <tbody class="table-border-bottom-0">

                                <?php if ($productsResult && $productsResult->num_rows > 0): ?>

                                    <?php while ($row = $productsResult->fetch_assoc()): ?>

                                        <tr>

                                            <!-- PRODUCT ID -->
                                            <td>
                                                <strong>
                                                    #<?= htmlspecialchars($row['product_id']); ?>
                                                </strong>
                                            </td>

                                            <!-- PRODUCT IMAGE -->
                                            <td>
                                                <?php if (!empty($row['product_image'])): ?>
                                                    <img src="../<?= htmlspecialchars($row['product_image']); ?>" 
                                                        alt="Product Image" 
                                                        class="img-thumbnail" 
                                                        style="width: 150px; height: 100px; object-fit: cover;">
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">No Image</span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- PRODUCT NAME -->
                                            <td>
                                                <strong>
                                                    <?= htmlspecialchars($row['product_name']); ?>
                                                </strong>
                                            </td>

                                            <!-- SKU -->
                                            <td>
                                                <code><?= htmlspecialchars($row['sku'] ?? 'N/A'); ?></code>
                                            </td>

                                            <!-- CATEGORY -->
                                            <td>
                                                <?= htmlspecialchars($row['category_name'] ?? 'Uncategorized'); ?>
                                            </td>

                                            <!-- PRICE -->
                                            <td>
                                                Rs. <?= number_format((float) $row['unit_price'], 2); ?>
                                            </td>

                                            <!-- STATUS -->
                                            <td>
                                                <?php
                                                $status = strtolower(trim($row['status'] ?? ''));

                                                if ($status === 'active' || $status === 'available') {
                                                    $badge = 'bg-label-success';
                                                } elseif ($status === 'discontinued' || $status === 'out_of_stock') {
                                                    $badge = 'bg-label-danger';
                                                } elseif ($status === 'draft' || $status === 'pending') {
                                                    $badge = 'bg-label-warning';
                                                } else {
                                                    $badge = 'bg-label-secondary';
                                                }
                                                ?>

                                                <span class="badge <?= $badge; ?>">
                                                    <?= htmlspecialchars(ucfirst($row['status'] ?? 'N/A')); ?>
                                                </span>
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
                                                        <a class="dropdown-item" href="add-products.php?id=<?= $row['product_id']; ?>">
                                                            <i class="bx bx-edit-alt me-1"></i> Edit
                                                        </a>
                                                        <a class="dropdown-item text-danger" href="product-delete.php?id=<?= $row['product_id']; ?>" onclick="return confirm('Are you sure you want to delete this product?');">
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