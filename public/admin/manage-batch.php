<?php

session_start();

require_once '../../includes/db.php';

$success = '';
$delete_error = '';

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

            header("Location: manage-batch.php?success=deleted");
            exit;

        } else {

            $delete_error = "Unable to delete product batch.";
            $delete_stmt->close();
        }
    }
}

if (isset($_GET['success'])) {

    switch ($_GET['success']) {

        case 'added':
            $success = "Product batch added successfully.";
            break;

        case 'updated':
            $success = "Product batch updated successfully.";
            break;

        case 'deleted':
            $success = "Product batch deleted successfully.";
            break;
    }
}

$search = trim($_GET['search'] ?? '');

$expiry_filter = $_GET['expiry'] ?? 'all';

if (!in_array($expiry_filter, ['all', 'active', 'expiring', 'expired'], true)) {
    $expiry_filter = 'all';
}

$sql = "
    SELECT
        pb.batch_id,
        pb.product_id,
        pb.batch_number,
        pb.quantity_on_hand,
        pb.expiry_date,
        pb.received_date,
        p.product_name,
        p.sku
    FROM product_batches pb
    INNER JOIN products p
        ON pb.product_id = p.product_id
    WHERE 1 = 1
";

$params = [];
$types = "";

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

    $sql .= "
        AND pb.expiry_date < CURDATE()
    ";

} elseif ($expiry_filter === 'expiring') {

    $sql .= "
        AND pb.expiry_date >= CURDATE()
        AND pb.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ";

} elseif ($expiry_filter === 'active') {

    $sql .= "
        AND pb.expiry_date > DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ";
}

$sql .= "
    ORDER BY pb.expiry_date ASC
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$result = $stmt->get_result();

$stats_query = $conn->query("
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
");

if (!$stats_query) {
    die("Statistics error: " . $conn->error);
}

$stats = $stats_query->fetch_assoc();

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

    <style>

        .batch-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .batch-page-header h4 {
            margin: 0;
        }

        .add-batch-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .batch-stat-card {
            height: 100%;
        }

        .batch-stat-number {
            font-size: 28px;
            font-weight: 600;
        }

        .batch-filter-row {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .batch-search {
            flex: 1;
        }

        .batch-table th {
            white-space: nowrap;
        }

        .batch-table td {
            vertical-align: middle;
        }

        .expiry-badge {
            font-size: 12px;
            padding: 5px 10px;
            border-radius: 20px;
        }

        .batch-actions {
            display: flex;
            gap: 6px;
            white-space: nowrap;
        }

        .empty-batches {
            text-align: center;
            padding: 50px 20px !important;
            color: #8592a3;
        }

        .empty-batches i {
            font-size: 45px;
        }

        @media (max-width: 768px) {

            .batch-page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .batch-filter-row {
                flex-direction: column;
                align-items: stretch;
            }

            .batch-search {
                width: 100%;
            }

        }

    </style>

</head>

<body>

<div class="layout-wrapper layout-content-navbar">

    <div class="layout-container">

        <?php require_once 'includes/sidebar.php'; ?>

        <div class="layout-page">

            <?php require_once 'includes/header.php'; ?>

            <div class="content-wrapper">

                <div class="container-xxl flex-grow-1 container-p-y">

                    <div class="batch-page-header">

                        <div>
                            <h4 class="fw-bold py-3 mb-0">
                                Product Batches
                            </h4>
                        </div>

                        <a
                            href="add-product-batch.php"
                            class="btn btn-primary add-batch-btn"
                        >
                            <i class="bx bx-plus"></i>
                            Add Batch
                        </a>

                    </div>

                    <?php if ($success !== ''): ?>

                        <div
                            class="alert alert-success alert-dismissible"
                            role="alert"
                        >

                            <i class="bx bx-check-circle me-2"></i>

                            <?= htmlspecialchars($success) ?>

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="alert"
                            ></button>

                        </div>

                    <?php endif; ?>

                    <?php if ($delete_error !== ''): ?>

                        <div
                            class="alert alert-danger alert-dismissible"
                            role="alert"
                        >

                            <i class="bx bx-error-circle me-2"></i>

                            <?= htmlspecialchars($delete_error) ?>

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="alert"
                            ></button>

                        </div>

                    <?php endif; ?>

                    <div class="row mb-4">

                        <div class="col-lg-4 col-md-6 col-sm-12 mb-4">

                            <div class="card batch-stat-card">

                                <div class="card-body">

                                    <span class="fw-semibold d-block mb-1">
                                        Total Batches
                                    </span>

                                    <div class="batch-stat-number">
                                        <?= (int)$stats['total_batches'] ?>
                                    </div>

                                </div>

                            </div>

                        </div>

                        <div class="col-lg-4 col-md-6 col-sm-12 mb-4">

                            <div class="card batch-stat-card">

                                <div class="card-body">

                                    <span class="fw-semibold d-block mb-1">
                                        Total Quantity
                                    </span>

                                    <div class="batch-stat-number">
                                        <?= (int)$stats['total_quantity'] ?>
                                    </div>

                                </div>

                            </div>

                        </div>

                        <div class="col-lg-4 col-md-6 col-sm-12 mb-4">

                            <div class="card batch-stat-card">

                                <div class="card-body">

                                    <span class="fw-semibold d-block mb-1">
                                        Expired Batches
                                    </span>

                                    <div class="batch-stat-number">
                                        <?= (int)$stats['expired_batches'] ?>
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="card mb-4">

                        <div class="card-body">

                            <form
                                method="GET"
                                action="manage-batch.php"
                            >

                                <div class="batch-filter-row">

                                    <div class="batch-search">

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

                                        <select
                                            name="expiry"
                                            class="form-select"
                                        >

                                            <option
                                                value="all"
                                                <?= $expiry_filter === 'all' ? 'selected' : '' ?>
                                            >
                                                All Expiry Status
                                            </option>

                                            <option
                                                value="active"
                                                <?= $expiry_filter === 'active' ? 'selected' : '' ?>
                                            >
                                                Active
                                            </option>

                                            <option
                                                value="expiring"
                                                <?= $expiry_filter === 'expiring' ? 'selected' : '' ?>
                                            >
                                                Expiring Soon
                                            </option>

                                            <option
                                                value="expired"
                                                <?= $expiry_filter === 'expired' ? 'selected' : '' ?>
                                            >
                                                Expired
                                            </option>

                                        </select>

                                    </div>

                                    <div>

                                        <button
                                            type="submit"
                                            class="btn btn-primary"
                                        >
                                            <i class="bx bx-search me-1"></i>
                                            Search
                                        </button>

                                    </div>

                                    <div>

                                        <a
                                            href="manage-batch.php"
                                            class="btn btn-outline-secondary"
                                        >
                                            Reset
                                        </a>

                                    </div>

                                </div>

                            </form>

                        </div>

                    </div>

                    <div class="card">

                        <h5 class="card-header">
                            Manage Product Batches
                        </h5>

                        <div class="table-responsive text-nowrap">

                            <table class="table batch-table">

                                <thead>

                                <tr>

                                    <th>
                                        Batch No.
                                    </th>

                                    <th>
                                        Product
                                    </th>

                                    <th>
                                        SKU
                                    </th>

                                    <th>
                                        Qty Remaining
                                    </th>

                                    <th>
                                        Received Date
                                    </th>

                                    <th>
                                        Expiry Date
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                    <th>
                                        Actions
                                    </th>

                                </tr>

                                </thead>

                                <tbody>

                                <?php if ($result->num_rows > 0): ?>

                                    <?php while ($row = $result->fetch_assoc()): ?>

                                        <?php

                                        $today = date('Y-m-d');

                                        $thirty_days = date(
                                            'Y-m-d',
                                            strtotime('+30 days')
                                        );

                                        if ($row['expiry_date'] < $today) {

                                            $status = 'Expired';
                                            $badge_class = 'bg-label-danger';

                                        } elseif (
                                            $row['expiry_date'] <= $thirty_days
                                        ) {

                                            $status = 'Expiring Soon';
                                            $badge_class = 'bg-label-warning';

                                        } else {

                                            $status = 'Active';
                                            $badge_class = 'bg-label-success';

                                        }

                                        ?>

                                        <tr>

                                            <td>

                                                <strong>
                                                    <?= htmlspecialchars(
                                                        $row['batch_number']
                                                    ) ?>
                                                </strong>

                                            </td>

                                            <td>

                                                <?= htmlspecialchars(
                                                    $row['product_name']
                                                ) ?>

                                            </td>

                                            <td>

                                                <?= htmlspecialchars(
                                                    $row['sku']
                                                ) ?>

                                            </td>

                                            <td>

                                                <?= (int)$row['quantity_on_hand'] ?>

                                            </td>

                                            <td>

                                                <?= date(
                                                    'd/m/Y',
                                                    strtotime(
                                                        $row['received_date']
                                                    )
                                                ) ?>

                                            </td>

                                            <td>

                                                <strong>
                                                    <?= date(
                                                        'd/m/Y',
                                                        strtotime(
                                                            $row['expiry_date']
                                                        )
                                                    ) ?>
                                                </strong>

                                            </td>

                                            <td>

                                                <span
                                                    class="badge <?= $badge_class ?> expiry-badge"
                                                >
                                                    <?= $status ?>
                                                </span>

                                            </td>

                                            <td>

                                                <div class="batch-actions">

                                                    <form
                                                        method="POST"
                                                        style="display:inline;"
                                                        onsubmit="return confirm('Are you sure you want to delete this product batch?');"
                                                    >

                                                        <input
                                                            type="hidden"
                                                            name="batch_id"
                                                            value="<?= (int)$row['batch_id'] ?>"
                                                        >

                                                        <button
                                                            type="submit"
                                                            name="delete_batch"
                                                            class="btn btn-sm btn-outline-danger"
                                                            title="Delete"
                                                        >

                                                            <i class="bx bx-trash"></i>

                                                        </button>

                                                    </form>

                                                </div>

                                            </td>

                                        </tr>

                                    <?php endwhile; ?>

                                <?php else: ?>

                                    <tr>

                                        <td
                                            colspan="8"
                                            class="empty-batches"
                                        >

                                            <i class="bx bx-package"></i>

                                            <br><br>

                                            <h5>
                                                No Product Batches Found
                                            </h5>

                                            <p>
                                                You haven't added any product batches yet.
                                            </p>

                                            <a
                                                href="add-product-batch.php"
                                                class="btn btn-primary"
                                            >
                                                <i class="bx bx-plus me-1"></i>
                                                Add Batch
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