<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once __DIR__ . '/../../includes/auth.php';

requireLogin();

require_once __DIR__ . '/../../includes/db.php';

if (!isset($conn)) {
    die("Database connection failed: \$conn is not defined.");
}

// Fetch products with their category and supplier details
$sql = "
    SELECT 
        p.product_id,
        p.product_name,
        c.category_name,
        latest_supplier.supplier_name,
        p.unit_price,
        p.requires_prescription,
        p.status
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN (
        SELECT
            poi.product_id,
            s.name AS supplier_name,
            po.order_date,
            ROW_NUMBER() OVER (
                PARTITION BY poi.product_id
                ORDER BY po.order_date DESC
            ) AS rn
        FROM purchase_order_items poi
        JOIN purchase_orders po ON poi.po_id = po.po_id
        JOIN suppliers s ON po.supplier_id = s.supplier_id
    ) latest_supplier
        ON latest_supplier.product_id = p.product_id
        AND latest_supplier.rn = 1
    ORDER BY p.product_id DESC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die(
        "Product query failed:<br>" .
        htmlspecialchars(mysqli_error($conn))
    );
}

$pageTitle = "Product Management - MediQuick";
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
                        <a href="product-add.php" class="btn btn-primary">
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
                                        <th>Product Name</th>
                                        <th>SKU</th>
                                        <th>Category</th>
                                        <th>Supplier</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>

                                <!-- TABLE BODY -->
                                <tbody class="table-border-bottom-0">

                                <?php if (mysqli_num_rows($result) > 0): ?>

                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>

                                        <tr>

                                            <!-- PRODUCT ID -->
                                            <td>
                                                <strong>
                                                    #<?= htmlspecialchars($row['product_id']); ?>
                                                </strong>
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

                                            <!-- SUPPLIER -->
                                            <td>
                                                <?= htmlspecialchars($row['supplier_name'] ?? 'N/A'); ?>
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

                                            <!-- ACTIONS -->
                                            <td>
                                                <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="product-edit.php?id=<?= $row['product_id']; ?>">
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
                                        <td colspan="8" class="text-center">
                                            No products found.
                                        </td>
                                    </tr>

                                <?php endif; ?>

                                </tbody>

                            </table>
                        </div>

                    </div>

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