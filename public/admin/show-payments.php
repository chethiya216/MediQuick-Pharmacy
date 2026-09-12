<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

if (!isset($conn)) {
    die("Database connection failed: \$conn is not defined.");
}

// Fetch payment records with associated order and customer details
$sql = "
    SELECT
        p.payment_id,
        p.order_id,
        c.first_name,
        c.last_name,
        p.payment_method,
        p.amount,
        p.payment_status,
        p.paid_at
    FROM payments p
    INNER JOIN orders o ON p.order_id = o.order_id
    INNER JOIN customers c ON o.customer_id = c.customer_id
    ORDER BY p.payment_id DESC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Payment query failed:<br>" . htmlspecialchars(mysqli_error($conn)));
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

            <!-- Sidebar -->
            <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

            <div class="layout-page">

                <!-- Header -->
                <?php require_once __DIR__ . '/includes/header.php'; ?>

                <!-- Content Wrapper -->
                <div class="content-wrapper">

                    <div class="container-xxl flex-grow-1 container-p-y">

                        <!-- Page Title -->
                        <h4 class="fw-bold py-3 mb-4">
                            <span class="text-muted fw-light">Payments /</span> Payment Management
                        </h4>

                        <!-- Payment Table Card -->
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h5 class="mb-0">Payment Management</h5>
                            </div>

                            <div class="table-responsive text-nowrap">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Payment ID</th>
                                            <th>Order ID</th>
                                            <th>Customer</th>
                                            <th>Method</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Paid Date</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-border-bottom-0">
                                        <?php if (mysqli_num_rows($result) > 0): ?>
                                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                                <?php
                                                    // Map status to dynamic Bootstrap badge colors
                                                    $status = strtolower(trim($row['payment_status'] ?? ''));
                                                    $badge = match ($status) {
                                                        'paid', 'completed' => 'bg-label-success',
                                                        'pending'           => 'bg-label-warning',
                                                        'failed', 'cancelled'=> 'bg-label-danger',
                                                        default             => 'bg-label-secondary',
                                                    };
                                                ?>
                                                <tr>
                                                    <!-- Payment ID -->
                                                    <td>
                                                        <strong>#<?= htmlspecialchars($row['payment_id']); ?></strong>
                                                    </td>

                                                    <!-- Order ID -->
                                                    <td>
                                                        <span class="fw-semibold">#<?= htmlspecialchars($row['order_id']); ?></span>
                                                    </td>

                                                    <!-- Customer Name -->
                                                    <td>
                                                        <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>
                                                    </td>

                                                    <!-- Payment Method -->
                                                    <td>
                                                        <?= htmlspecialchars(ucwords(str_replace('_', ' ', $row['payment_method'] ?? 'N/A'))); ?>
                                                    </td>

                                                    <!-- Amount -->
                                                    <td>
                                                        <strong>Rs. <?= number_format((float) $row['amount'], 2); ?></strong>
                                                    </td>

                                                    <!-- Status Badge -->
                                                    <td>
                                                        <span class="badge <?= $badge; ?>">
                                                            <?= htmlspecialchars(ucfirst($row['payment_status'])); ?>
                                                        </span>
                                                    </td>

                                                    <!-- Paid Date -->
                                                    <td>
                                                        <?php if (!empty($row['paid_at'])): ?>
                                                            <?= htmlspecialchars(date('M d, Y h:i A', strtotime($row['paid_at']))); ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <em class="text-muted">No payment records found.</em>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    <!-- Footer -->
                    <?php require_once __DIR__ . '/includes/footer.php'; ?>

                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>

        <!-- Layout Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
</body>
</html>