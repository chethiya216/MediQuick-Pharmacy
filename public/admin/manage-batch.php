<?php

session_start();
require_once '../../includes/auth.php';
requireAdmin();
require_once '../../includes/db.php';

$success = '';
$delete_error = '';

// Capture parameters
$product_id = isset($_GET['product_id']) && is_numeric($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$search = trim($_GET['search'] ?? '');
$expiry_filter = $_GET['expiry'] ?? 'all';

if (!in_array($expiry_filter, ['all', 'active', 'expiring', 'expired'], true)) {
    $expiry_filter = 'all';
}

// Fetch single product info if product_id is specified
$filter_product = null;
if ($product_id > 0) {
    $p_stmt = $conn->prepare("SELECT product_name, sku FROM products WHERE product_id = ?");
    $p_stmt->bind_param("i", $product_id);
    $p_stmt->execute();
    $filter_product = $p_stmt->get_result()->fetch_assoc();
    $p_stmt->close();
}

/*
|--------------------------------------------------------------------------
| Build Main Batches Query
|--------------------------------------------------------------------------
*/
$sql = "
    SELECT
        pb.batch_id,
        pb.product_id,
        pb.supplier_id,
        pb.batch_number,
        pb.quantity_on_hand,
        pb.purchase_price,
        pb.selling_price,
        pb.expiry_date,
        pb.received_date,
        pb.status,
        p.product_name,
        p.sku
    FROM product_batches pb
    INNER JOIN products p
        ON pb.product_id = p.product_id
    WHERE 1 = 1
";

$params = [];
$types = "";

if ($product_id > 0) {
    $sql .= " AND pb.product_id = ? ";
    $params[] = $product_id;
    $types .= "i";
}

if ($search !== '') {
    $sql .= "
        AND (
            pb.batch_number LIKE ?
            OR p.product_name LIKE ?
            OR p.sku LIKE ?
        )
    ";

    $search_value = "%" . $search . "%";
    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;
    $types .= "sss";
}

if ($expiry_filter === 'expired') {
    $sql .= " AND pb.expiry_date < CURDATE() ";
} elseif ($expiry_filter === 'expiring') {
    $sql .= " AND pb.expiry_date >= CURDATE() AND pb.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) ";
} elseif ($expiry_filter === 'active') {
    $sql .= " AND pb.expiry_date > DATE_ADD(CURDATE(), INTERVAL 30 DAY) ";
}

$sql .= " ORDER BY pb.expiry_date ASC ";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

/*
|--------------------------------------------------------------------------
| Build Statistics Query (Scoped to product_id if set)
|--------------------------------------------------------------------------
*/
$stats_sql = "
    SELECT
        COUNT(*) AS total_batches,
        COALESCE(SUM(quantity_on_hand), 0) AS total_quantity,
        COALESCE(
            SUM(
                CASE
                    WHEN expiry_date < CURDATE()
                    THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS expired_batches
    FROM product_batches
    WHERE 1 = 1
";

$stats_params = [];
$stats_types = "";

if ($product_id > 0) {
    $stats_sql .= " AND product_id = ? ";
    $stats_params[] = $product_id;
    $stats_types .= "i";
}

$stats_stmt = $conn->prepare($stats_sql);
if (!$stats_stmt) {
    die("Statistics error: " . $conn->error);
}

if (!empty($stats_params)) {
    $stats_stmt->bind_param($stats_types, ...$stats_params);
}

$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();
$stats_stmt->close();

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
<head>
    <?php require_once 'includes/head.php'; ?>
    <title>Product Batches - MediQuick</title>
</head>
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php require_once 'includes/sidebar.php'; ?>
            <div class="layout-page">
                <?php require_once 'includes/header.php'; ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <div class="batch-page-header d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h4 class="fw-bold py-3 mb-0">
                                    Product Batches 
                                    <?php if ($filter_product): ?>
                                        <span class="text-muted fw-light">/ <?= htmlspecialchars($filter_product['product_name']) ?> (<?= htmlspecialchars($filter_product['sku']) ?>)</span>
                                    <?php endif; ?>
                                </h4>
                            </div>
                            <div class="d-flex gap-2">
                                <?php if ($product_id > 0): ?>
                                    <a href="manage-batch.php" class="btn btn-outline-secondary">
                                        <i class="bx bx-left-arrow-alt me-1"></i> View All Batches
                                    </a>
                                <?php endif; ?>
                                <a href="add-product-batch.php<?= $product_id > 0 ? '?product_id=' . $product_id : '' ?>" class="btn btn-primary add-batch-btn">
                                    <i class="bx bx-plus me-1"></i> Add Batch
                                </a>
                            </div>
                        </div>

                        <?php if ($success !== ''): ?>
                            <div class="alert alert-success alert-dismissible" role="alert">
                                <i class="bx bx-check-circle me-2"></i>
                                <?= htmlspecialchars($success) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($delete_error !== ''): ?>
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                <i class="bx bx-error-circle me-2"></i>
                                <?= htmlspecialchars($delete_error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- STATS CARDS -->
                        <div class="row mb-4">
                            <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                                <div class="card batch-stat-card">
                                    <div class="card-body">
                                        <span class="fw-semibold d-block mb-1">Total Batches</span>
                                        <div class="batch-stat-number fs-3 fw-bold">
                                            <?= (int)$stats['total_batches'] ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                                <div class="card batch-stat-card">
                                    <div class="card-body">
                                        <span class="fw-semibold d-block mb-1">Total Quantity</span>
                                        <div class="batch-stat-number fs-3 fw-bold">
                                            <?= (int)$stats['total_quantity'] ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                                <div class="card batch-stat-card">
                                    <div class="card-body">
                                        <span class="fw-semibold d-block mb-1">Expired Batches</span>
                                        <div class="batch-stat-number fs-3 fw-bold text-danger">
                                            <?= (int)$stats['expired_batches'] ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- FILTER & SEARCH FORM -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <form method="GET" action="manage-batch.php">
                                    
                                    <!-- Retain product_id across searches -->
                                    <?php if ($product_id > 0): ?>
                                        <input type="hidden" name="product_id" value="<?= $product_id; ?>">
                                    <?php endif; ?>

                                    <div class="batch-filter-row d-flex flex-wrap gap-2">
                                        <div class="batch-search flex-grow-1">
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="bx bx-search"></i>
                                                </span>
                                                <input
                                                    type="text"
                                                    name="search"
                                                    class="form-control"
                                                    placeholder="Search by product / batch no."
                                                    value="<?= htmlspecialchars($search) ?>"
                                                >
                                            </div>
                                        </div>

                                        <div>
                                            <select name="expiry" class="form-select">
                                                <option value="all" <?= $expiry_filter === 'all' ? 'selected' : '' ?>>All Expiry Status</option>
                                                <option value="active" <?= $expiry_filter === 'active' ? 'selected' : '' ?>>Active</option>
                                                <option value="expiring" <?= $expiry_filter === 'expiring' ? 'selected' : '' ?>>Expiring Soon</option>
                                                <option value="expired" <?= $expiry_filter === 'expired' ? 'selected' : '' ?>>Expired</option>
                                            </select>
                                        </div>

                                        <div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-search me-1"></i> Search
                                            </button>
                                        </div>

                                        <div>
                                            <a href="manage-batch.php<?= $product_id > 0 ? '?product_id=' . $product_id : '' ?>" class="btn btn-outline-secondary">Reset</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- BATCHES TABLE CARD -->
                        <div class="card">
                            <h5 class="card-header">
                                Manage Product Batches
                                <?php if ($filter_product): ?>
                                    <span class="badge bg-label-primary ms-2"><?= htmlspecialchars($filter_product['product_name']); ?></span>
                                <?php endif; ?>
                            </h5>
                            <div class="table-responsive text-nowrap">
                                <table class="table batch-table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Batch No.</th>
                                            <th>Product</th>
                                            <th>SKU</th>
                                            <th>Qty Remaining</th>
                                            <th>Received Date</th>
                                            <th>Expiry Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($result->num_rows > 0): ?>
                                            <?php while ($row = $result->fetch_assoc()): ?>
                                                <?php
                                                $today = date('Y-m-d');
                                                $thirty_days = date('Y-m-d', strtotime('+30 days'));

                                                if ($row['expiry_date'] < $today) {
                                                    $status = 'Expired';
                                                    $badge_class = 'bg-label-danger';
                                                } elseif ($row['expiry_date'] <= $thirty_days) {
                                                    $status = 'Expiring Soon';
                                                    $badge_class = 'bg-label-warning';
                                                } else {
                                                    $status = 'Active';
                                                    $badge_class = 'bg-label-success';
                                                }
                                                ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= htmlspecialchars($row['batch_number']) ?></strong>
                                                    </td>
                                                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                                                    <td><code><?= htmlspecialchars($row['sku']) ?></code></td>
                                                    <td><?= (int)$row['quantity_on_hand'] ?></td>
                                                    <td><?= date('d/m/Y', strtotime($row['received_date'])) ?></td>
                                                    <td>
                                                        <strong><?= date('d/m/Y', strtotime($row['expiry_date'])) ?></strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge <?= $badge_class ?> expiry-badge">
                                                            <?= $status ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="batch-actions">
                                                            <button 
                                                                type="button" 
                                                                class="btn btn-sm btn-outline-danger" 
                                                                title="Delete"
                                                                onclick="openDeleteConfirm(
                                                                    event, 
                                                                    <?= (int)$row['batch_id']; ?>, 
                                                                    '<?= htmlspecialchars($row['batch_number'], ENT_QUOTES); ?>', 
                                                                    'handlers/product-batch-handler.php?action=delete&batch_id=<?= (int)$row['batch_id']; ?>'
                                                                )"
                                                            >
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center py-5">
                                                    <i class="bx bx-package display-4 text-muted"></i>
                                                    <h5 class="mt-3">No Product Batches Found</h5>
                                                    <p class="text-muted">
                                                        <?= $product_id > 0 ? 'No batches available for this specific product.' : 'You haven\'t added any product batches yet.'; ?>
                                                    </p>
                                                    <a href="add-product-batch.php<?= $product_id > 0 ? '?product_id=' . $product_id : '' ?>" class="btn btn-primary">
                                                        <i class="bx bx-plus me-1"></i> Add Batch
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <?php require_once 'includes/footer.php'; ?>
                    <?php require_once 'includes/delete-modal.php'; ?>

                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
</body>
</html>
<?php

$stmt->close();

?>