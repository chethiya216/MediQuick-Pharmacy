<?php
session_start();
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

requirePharmacist();

$name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
$userRole = $_SESSION['role'] ?? '';
$isAdmin = in_array($userRole, ['admin', 'superadmin']);

// Fetch Pending Prescriptions (Accessible to both Pharmacists and Admins)
$pending_prescriptions = $conn->query("SELECT COUNT(*) AS total FROM prescriptions WHERE status = 'pending'")->fetch_assoc()['total'] ?? 0;

// Fetch financial and operational data only if Admin or Superadmin
if ($isAdmin) {
    // 1. Fetch Transactions List for Table
    $transactionsSQL = "SELECT payments.*, orders.order_id 
                        FROM payments 
                        JOIN orders ON payments.order_id = orders.order_id 
                        ORDER BY payments.payment_id DESC";
    $stmt = $conn->prepare($transactionsSQL);
    $stmt->execute();
    $transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // 2. Fetch Metrics for Financial Cards
    $salesSQL = "SELECT SUM(amount) AS total_sales FROM payments WHERE payment_status = 'completed'";
    $salesRes = $conn->query($salesSQL);
    $totalSales = $salesRes ? ($salesRes->fetch_assoc()['total_sales'] ?? 0) : 0;

    $paymentsSQL = "SELECT SUM(amount) AS total_payments FROM payments";
    $paymentsRes = $conn->query($paymentsSQL);
    $totalPayments = $paymentsRes ? ($paymentsRes->fetch_assoc()['total_payments'] ?? 0) : 0;

    $countSQL = "SELECT COUNT(*) AS total_count FROM payments";
    $countRes = $conn->query($countSQL);
    $totalTransactions = $countRes ? ($countRes->fetch_assoc()['total_count'] ?? 0) : 0;

    $profitSQL = "SELECT AVG(amount) AS avg_profit FROM payments";
    $profitRes = $conn->query($profitSQL);
    $avgProfit = $profitRes ? ($profitRes->fetch_assoc()['avg_profit'] ?? 0) : 0;

    // 3. Operational Metrics
    $pending_orders = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE status = 'pending'")->fetch_assoc()['total'] ?? 0;
    
    $total_orders = "SELECT COUNT(*) AS total FROM orders;";
    $total_orders_count = $conn->query($total_orders)->fetch_assoc()['total'] ?? 0;

    $unread_messages = $conn->query("SELECT COUNT(*) AS total FROM contact_messages WHERE status = 'unread'")->fetch_assoc()['total'] ?? 0;

    // 4. Inventory Alert Metrics
    $expiring_soon = $conn->query("
        SELECT COUNT(*) AS total 
        FROM product_batches 
        WHERE status = 'active' 
          AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ")->fetch_assoc()['total'] ?? 0;

    $out_of_stock = $conn->query("
        SELECT COUNT(p.product_id) AS total
        FROM products p
        LEFT JOIN (
            SELECT product_id, SUM(quantity_on_hand) AS total_stock 
            FROM product_batches 
            WHERE status = 'active' 
            GROUP BY product_id
        ) b ON p.product_id = b.product_id
        WHERE p.status = 'active' AND COALESCE(b.total_stock, 0) = 0
    ")->fetch_assoc()['total'] ?? 0;
}
?>

<!doctype html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="../assets/" data-template="vertical-menu-template-free">

<?php include 'includes/head.php'; ?>

<body>
    <!-- Standard Wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>

            <!-- Page Container -->
            <div class="layout-page">

                <!-- Header -->
                <?php include 'includes/header.php'; ?>

                <!-- Content Wrapper -->
                <div class="content-wrapper">
                    
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="row">
                            <!-- Left Column: Banner + Operational KPIs + Transactions -->
                            <div class="<?php echo $isAdmin ? 'col-lg-8 col-md-8' : 'col-12'; ?> order-0 mb-4">
                                <div class="row">
                                    <!-- 1. Welcome Card -->
                                    <div class="col-12 mb-4">
                                        <div class="card">
                                            <div class="d-flex align-items-end row">
                                                <div class="col-sm-7">
                                                    <div class="card-body">
                                                        <h5 class="card-title text-primary">
                                                            Welcome <b><?php echo htmlspecialchars(strtoupper($_SESSION['role'])); ?></b>🎉
                                                        </h5>
                                                        <p class="mb-1">
                                                            Here's what's happening with your store today...
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 2. Operational Priority Metric Cards -->
                                    <div class="col-12 mb-4">
                                        <div class="row g-3">
                                            <?php if ($isAdmin) : ?>
                                            <!-- Total Orders -->
                                            <div class="col-6 col-md-3">
                                                <div class="card h-100">
                                                    <div class="card-body p-3">
                                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                                            <div class="avatar flex-shrink-0">
                                                                <span class="avatar-initial rounded bg-label-danger"><i class="bx bx-error"></i></span>
                                                            </div>
                                                        </div>
                                                        <span class="fw-semibold d-block text-truncate small">Total Orders</span>
                                                        <h4 class="card-title mb-0 text-danger"><?php echo number_format($total_orders_count); ?></h4>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Pending Orders -->
                                            <div class="col-6 col-md-3">
                                                <div class="card h-100">
                                                    <div class="card-body p-3">
                                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                                            <div class="avatar flex-shrink-0">
                                                                <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-package"></i></span>
                                                            </div>
                                                        </div>
                                                        <span class="fw-semibold d-block text-truncate small">Pending Orders</span>
                                                        <h4 class="card-title mb-0 text-warning"><?php echo number_format($pending_orders); ?></h4>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>

                                            <!-- Pending Prescriptions (Visible to Pharmacists and Admins) -->
                                            <div class="col-6 <?php echo $isAdmin ? 'col-md-3' : 'col-md-6'; ?>">
                                                <div class="card h-100">
                                                    <div class="card-body p-3">
                                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                                            <div class="avatar flex-shrink-0">
                                                                <span class="avatar-initial rounded bg-label-info"><i class="bx bx-file"></i></span>
                                                            </div>
                                                        </div>
                                                        <span class="fw-semibold d-block text-truncate small">Pending Prescriptions</span>
                                                        <h4 class="card-title mb-0 text-info"><?php echo number_format($pending_prescriptions); ?></h4>
                                                    </div>
                                                </div>
                                            </div>

                                            <?php if ($isAdmin) : ?>
                                            <!-- Unread Messages -->
                                            <div class="col-6 col-md-3">
                                                <div class="card h-100">
                                                    <div class="card-body p-3">
                                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                                            <div class="avatar flex-shrink-0">
                                                                <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-envelope"></i></span>
                                                            </div>
                                                        </div>
                                                        <span class="fw-semibold d-block text-truncate small">Unread Msgs</span>
                                                        <h4 class="card-title mb-0 text-primary"><?php echo number_format($unread_messages); ?></h4>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <?php if ($isAdmin) : ?>
                                    <!-- 3. Transactions Card -->
                                    <div class="col-12 mb-4">
                                        <div class="card">
                                            <div class="card-header d-flex align-items-center justify-content-between">
                                                <h5 class="card-title m-0 me-2">Transactions</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
                                                    <table class="table align-middle m-0">
                                                        <thead class="sticky-top bg-white" style="z-index: 1;">
                                                            <tr>
                                                                <th>Order ID</th>
                                                                <th>Payment Method</th>
                                                                <th>Transaction Reference</th>
                                                                <th class="text-end">Amount</th>
                                                                <th class="text-center">Payment Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php if (!empty($transactions)) : ?>
                                                                <?php foreach ($transactions as $transaction) : ?>
                                                                <tr>
                                                                    <td>
                                                                        <small class="text-muted d-block">#<?php echo htmlspecialchars($transaction['order_id']); ?></small>
                                                                    </td>
                                                                    <td>
                                                                        <h6 class="mb-0 text-truncate" style="max-width: 180px;">
                                                                            <?php echo htmlspecialchars(str_replace('_', ' ', strtoupper($transaction['payment_method'] ?? 'N/A'))); ?>
                                                                        </h6>
                                                                    </td>
                                                                    <td>
                                                                        <small class="text-muted d-block text-truncate" style="max-width: 200px;">
                                                                            <?php echo htmlspecialchars($transaction['transaction_reference'] ?? 'N/A'); ?>
                                                                        </small>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <div class="user-progress d-flex align-items-center justify-content-end gap-1">
                                                                            <span class="text-muted">LKR</span>
                                                                            <h6 class="mb-0"><?php echo number_format($transaction['amount'], 2); ?></h6>
                                                                        </div>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <span class="badge bg-label-<?php echo ($transaction['payment_status'] ?? '') === 'completed' ? 'success' : 'warning'; ?>">
                                                                            <?php echo ucfirst(htmlspecialchars($transaction['payment_status'] ?? 'Completed')); ?>
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                                <?php endforeach; ?>
                                                            <?php else : ?>
                                                                <tr>
                                                                    <td colspan="5" class="text-center py-3">No transactions found.</td>
                                                                </tr>
                                                            <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if ($isAdmin) : ?>
                            <!-- Right Column: Financial & Inventory Sidebar Cards (Admins Only) -->
                            <div class="col-lg-4 col-md-4 order-1 mb-4">
                                <div class="row">
                                    <!-- Card 1: Avg Order -->
                                    <div class="col-6 mb-4">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <div class="card-title d-flex align-items-start justify-content-between mb-2">
                                                    <div class="avatar flex-shrink-0">
                                                        <span class="avatar-initial rounded bg-label-info">
                                                            <i class="bx bx-calculator fs-4"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                                <span class="fw-semibold d-block mb-1 text-muted small">Avg Order</span>
                                                <h4 class="card-title text-nowrap mb-1 fs-5 fw-bold">LKR <?php echo number_format($avgProfit, 2); ?></h4>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Card 2: Total Sales -->
                                    <div class="col-6 mb-4">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <div class="card-title d-flex align-items-start justify-content-between mb-2">
                                                    <div class="avatar flex-shrink-0">
                                                        <span class="avatar-initial rounded bg-label-success">
                                                            <i class="bx bx-trending-up fs-4"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                                <span class="fw-semibold d-block mb-1 text-muted small">Sales</span>
                                                <h4 class="card-title text-nowrap mb-1 fs-5 fw-bold text-success">LKR <?php echo number_format($totalSales, 2); ?></h4>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Card 3: Payments -->
                                    <div class="col-6 mb-4">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <div class="card-title d-flex align-items-start justify-content-between mb-2">
                                                    <div class="avatar flex-shrink-0">
                                                        <span class="avatar-initial rounded bg-label-primary">
                                                            <i class="bx bx-dollar-circle fs-4"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                                <span class="fw-semibold d-block mb-1 text-muted small">Payments</span>
                                                <h4 class="card-title text-nowrap mb-1 fs-5 fw-bold">LKR <?php echo number_format($totalPayments, 2); ?></h4>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Card 4: Transactions Metric -->
                                    <div class="col-6 mb-4">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <div class="card-title d-flex align-items-start justify-content-between mb-2">
                                                    <div class="avatar flex-shrink-0">
                                                        <span class="avatar-initial rounded bg-label-warning">
                                                            <i class="bx bx-credit-card fs-4"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                                <span class="fw-semibold d-block mb-1 text-muted small">Transactions</span>
                                                <h4 class="card-title text-nowrap mb-1 fs-5 fw-bold"><?php echo number_format($totalTransactions); ?></h4>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Card 5: Expiring Soon Alert -->
                                    <div class="col-6 mb-4">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <div class="card-title d-flex align-items-start justify-content-between mb-2">
                                                    <div class="avatar flex-shrink-0">
                                                        <span class="avatar-initial rounded bg-label-warning">
                                                            <i class="bx bx-time-five fs-4"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                                <span class="fw-semibold d-block mb-1 text-muted small">Expiring (30d)</span>
                                                <h4 class="card-title text-nowrap mb-1 fs-5 fw-bold text-warning"><?php echo number_format($expiring_soon); ?></h4>
                                                <small class="text-muted">Batches near expiry</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Card 6: Out of Stock Alert -->
                                    <div class="col-6 mb-4">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <div class="card-title d-flex align-items-start justify-content-between mb-2">
                                                    <div class="avatar flex-shrink-0">
                                                        <span class="avatar-initial rounded bg-label-danger">
                                                            <i class="bx bx-block fs-4"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                                <span class="fw-semibold d-block mb-1 text-muted small">Out of Stock</span>
                                                <h4 class="card-title text-nowrap mb-1 fs-5 fw-bold text-danger"><?php echo number_format($out_of_stock); ?></h4>
                                                <small class="text-muted">Zero inventory items</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Footer -->
                    <?php include 'includes/footer.php'; ?>

                    <div class="content-backdrop fade"></div>
                </div>
            </div>

        </div>

        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

</body>
</html>