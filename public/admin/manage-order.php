<?php
require_once('../../includes/db.php');
require_once('../../includes/auth.php');
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {

    $order_id = (int)$_POST['order_id'];
    $status = trim($_POST['status']);

    $allowedStatuses = [
        'pending',
        'confirmed',
        'shipped',
        'delivered',
        'cancelled'
    ];

    if (in_array($status, $allowedStatuses)) {

        $stmt = $conn->prepare("
            UPDATE orders
            SET status = ?
            WHERE order_id = ?
        ");

        $stmt->bind_param("si", $status, $order_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: manage-order.php");
    exit();
}

$sql = "
SELECT
    o.order_id,
    o.customer_id,
    o.order_date,
    o.status,
    o.total_amount,
    CONCAT(c.first_name, ' ', c.last_name) AS customer_name
FROM orders o
LEFT JOIN customers c
ON o.customer_id = c.customer_id
ORDER BY o.order_id DESC
";

$result = $conn->query($sql);

// Helper array to assign Sneat template badge colors
$statusBadges = [
    'pending'   => 'bg-label-warning',
    'confirmed' => 'bg-label-info',
    'shipped'   => 'bg-label-primary',
    'delivered' => 'bg-label-success',
    'cancelled' => 'bg-label-danger'
];
?>

<!DOCTYPE html>
<html
    lang="en"
    class="light-style layout-menu-fixed"
    dir="ltr"
    data-theme="theme-default"
    data-assets-path="../assets/"
    data-template="vertical-menu-template-free"
>

<head>
    <?php include 'includes/head.php'; ?>
</head>

<body>

<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">

        <?php include 'includes/sidebar.php'; ?>

        <div class="layout-page">

            <?php include 'includes/header.php'; ?>

            <div class="content-wrapper">

                <div class="container-xxl flex-grow-1 container-p-y">

                    <h4 class="fw-bold py-3 mb-4">
                        <span class="text-muted fw-light">Management /</span> Orders
                    </h4>

                    <div class="card">
                        <h5 class="card-header">Order List</h5>

                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Order Date</th>
                                        <th>Total Amount</th>
                                        <th>Status Badge</th>
                                        <th>Update Status</th>
                                    </tr>
                                </thead>
                                <tbody class="table-border-bottom-0">

                                <?php if($result && $result->num_rows > 0): ?>

                                    <?php while($row = $result->fetch_assoc()): 
                                        $currentStatus = strtolower($row['status']);
                                        $badgeClass = $statusBadges[$currentStatus] ?? 'bg-label-secondary';
                                    ?>

                                        <tr>
                                            <td>
                                                <strong>#<?= $row['order_id']; ?></strong>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($row['customer_name']); ?>
                                            </td>

                                            <td>
                                                <?= date('M d, Y H:i', strtotime($row['order_date'])); ?>
                                            </td>

                                            <td>
                                                Rs. <?= number_format($row['total_amount'], 2); ?>
                                            </td>

                                            <td>
                                                <span class="badge <?= $badgeClass; ?> me-1">
                                                    <?= ucfirst($row['status']); ?>
                                                </span>
                                            </td>

                                            <td>
                                                <form method="POST" class="d-flex align-items-center">
                                                    <input type="hidden" name="order_id" value="<?= $row['order_id']; ?>">
                                                    <select
                                                        name="status"
                                                        class="form-select form-select-sm w-auto"
                                                        onchange="this.form.submit();"
                                                    >
                                                        <option value="pending" <?= ($currentStatus=='pending') ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="confirmed" <?= ($currentStatus=='confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                                        <option value="shipped" <?= ($currentStatus=='shipped') ? 'selected' : ''; ?>>Shipped</option>
                                                        <option value="delivered" <?= ($currentStatus=='delivered') ? 'selected' : ''; ?>>Delivered</option>
                                                        <option value="cancelled" <?= ($currentStatus=='cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                                    </select>
                                                </form>
                                            </td>
                                        </tr>

                                    <?php endwhile; ?>

                                <?php else: ?>

                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            No orders found
                                        </td>
                                    </tr>

                                <?php endif; ?>

                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <?php include 'includes/footer.php'; ?>

                <div class="content-backdrop fade"></div>

            </div>

        </div>

    </div>

    <div class="layout-overlay layout-menu-toggle"></div>
</div>

</body>
</html>