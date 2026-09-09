<?php
session_start();
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

requireAdmin();

$name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];

// 1. Fetch Transactions List for Table
$transactionsSQL = "SELECT payments.*, orders.order_id 
                    FROM payments 
                    JOIN orders ON payments.order_id = orders.order_id 
                    ORDER BY payments.payment_id DESC";
$stmt = $conn->prepare($transactionsSQL);
$stmt->execute();
$transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 2. Fetch Metrics for Summary Cards
// Total Sales / Revenue
$salesSQL = "SELECT SUM(amount) AS total_sales FROM payments WHERE payment_status = 'completed'";
$salesRes = $conn->query($salesSQL);
$totalSales = $salesRes ? ($salesRes->fetch_assoc()['total_sales'] ?? 0) : 0;

// Total Payments Amount
$paymentsSQL = "SELECT SUM(amount) AS total_payments FROM payments";
$paymentsRes = $conn->query($paymentsSQL);
$totalPayments = $paymentsRes ? ($paymentsRes->fetch_assoc()['total_payments'] ?? 0) : 0;

// Total Transactions Count
$countSQL = "SELECT COUNT(*) AS total_count FROM payments";
$countRes = $conn->query($countSQL);
$totalTransactions = $countRes ? ($countRes->fetch_assoc()['total_count'] ?? 0) : 0;

// Estimated Profit (Example calculation: Total Sales * 0.20 or aggregate query)
$profitSQL = "SELECT AVG(amount) AS avg_profit FROM payments";
$profitRes = $conn->query($profitSQL);
$avgProfit = $profitRes ? ($profitRes->fetch_assoc()['avg_profit'] ?? 0) : 0;
?>

<!doctype html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="../assets/" data-template="vertical-menu-template-free">

<?php include 'includes/head.php'; ?>

<body>
    <!-- Standard Wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

            <!-- Sidebar Included Here -->
            <?php include 'includes/sidebar.php'; ?>

            <!-- Page Container -->
            <div class="layout-page">

                <!-- Header Included Inside layout-page -->
                <?php include 'includes/header.php'; ?>

                <!-- Content Wrapper -->
                <div class="content-wrapper">
                    
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="row">
                            <!-- Left Column: Banner + Transactions -->
                            <div class="col-lg-8 col-md-8 order-0 mb-4">
                                <div class="row">
                                    <!-- 1. Congratulations Card -->
                                    <div class="col-12 mb-4">
                                        <div class="card">
                                            <div class="d-flex align-items-end row">
                                                <div class="col-sm-7">
                                                    <div class="card-body">
                                                        <h5 class="card-title text-primary">
                                                            Welcome <b><?php echo htmlspecialchars(strtoupper(($_SESSION['role'] ))); ?></b>🎉
                                                        </h5>
                                                        <p class="mb-1">
                                                            Here's what's happening with your store today...
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 2. Transactions Card -->
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
                                                                    <!-- Column 1: Order ID -->
                                                                    <td>
                                                                        <small class="text-muted d-block">#<?php echo htmlspecialchars($transaction['order_id']); ?></small>
                                                                    </td>

                                                                    <!-- Column 2: Payment Method -->
                                                                    <td>
                                                                        <h6 class="mb-0 text-truncate" style="max-width: 180px;">
                                                                            <?php echo htmlspecialchars(str_replace('_', ' ', strtoupper($transaction['payment_method'] ?? 'N/A'))); ?>
                                                                        </h6>
                                                                    </td>

                                                                    <!-- Column 3: Transaction Reference -->
                                                                    <td>
                                                                        <small class="text-muted d-block text-truncate" style="max-width: 200px;">
                                                                            <?php echo htmlspecialchars($transaction['transaction_reference'] ?? 'N/A'); ?>
                                                                        </small>
                                                                    </td>

                                                                    <!-- Column 4: Amount -->
                                                                    <td class="text-end">
                                                                        <div class="user-progress d-flex align-items-center justify-content-end gap-1">
                                                                            <span class="text-muted">LKR</span>
                                                                            <h6 class="mb-0"><?php echo number_format($transaction['amount'], 2); ?></h6>
                                                                        </div>
                                                                    </td>

                                                                    <!-- Column 5: Payment Status -->
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
                                </div>
                            </div>
                            
                            <!-- Right Column: 4 Small Cards + Profile Report -->
                            <div class="col-lg-4 col-md-4 order-1 mb-4">
                                <div class="row">
                                    <!-- Card 1: Avg Order -->
                                    <div class="col-6 mb-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <span class="fw-semibold d-block mb-1">Avg Order</span>
                                                <h3 class="card-title mb-2">LKR <?php echo number_format($avgProfit, 2); ?></h3>
                                                <!-- <small class="text-success fw-semibold"><i class="bx bx-up-arrow-alt"></i> +72.80%</small> -->
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Card 2: Total Sales -->
                                    <div class="col-6 mb-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <span class="fw-semibold d-block mb-1">Sales</span>
                                                <h3 class="card-title text-nowrap mb-1">LKR <?php echo number_format($totalSales, 2); ?></h3>
                                                <!-- <small class="text-success fw-semibold"><i class="bx bx-up-arrow-alt"></i> +28.42%</small> -->
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Card 3: Payments -->
                                    <div class="col-6 mb-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <span class="d-block mb-1">Payments</span>
                                                <h3 class="card-title text-nowrap mb-2">LKR <?php echo number_format($totalPayments, 2); ?></h3>
                                                <!-- <small class="text-danger fw-semibold"><i class="bx bx-down-arrow-alt"></i> -14.82%</small> -->
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Card 4: Transactions Metric -->
                                    <div class="col-6 mb-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <span class="fw-semibold d-block mb-1">Transactions</span>
                                                <h3 class="card-title mb-2"><?php echo number_format($totalTransactions); ?></h3>
                                                <!-- <small class="text-success fw-semibold"><i class="bx bx-up-arrow-alt"></i> +28.14%</small> -->
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Profile Report Card -->
                                    <!-- <div class="col-12 mb-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between flex-sm-row flex-column gap-3">
                                                    <div class="d-flex flex-sm-column flex-row align-items-start justify-content-between">
                                                        <div class="card-title">
                                                            <h5 class="text-nowrap mb-2">Profile Report</h5>
                                                            <span class="badge bg-label-warning rounded-pill">Year <?php echo date('Y'); ?></span>
                                                        </div>
                                                        <div class="mt-sm-auto">
                                                            <small class="text-success text-nowrap fw-semibold"><i class="bx bx-chevron-up"></i> 68.2%</small>
                                                            <h3 class="mb-0">LKR <?php echo number_format($totalSales, 2); ?></h3>
                                                        </div>
                                                    </div>
                                                    <div id="profileReportChart"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div> -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Included Here -->
                    <?php include 'includes/footer.php'; ?>

                    <div class="content-backdrop fade"></div>
                </div>
            </div>

        </div>

        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

</body>

</html>