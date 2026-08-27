<?php

include __DIR__ . '/../../includes/db.php';

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
    JOIN orders o ON p.order_id = o.order_id
    JOIN customers c ON o.customer_id = c.customer_id
    ORDER BY p.payment_id DESC
";

$result = mysqli_query($conn, $sql);

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

<?php include 'head.php'; ?>

<body>

    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">

        <div class="layout-container">

            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>
            <!-- / Sidebar -->


            <!-- Layout page -->
            <div class="layout-page">

                <!-- Header -->
                <?php include 'header.php'; ?>
                <!-- / Header -->


                <!-- Content wrapper -->
                <div class="content-wrapper">

                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <h4 class="fw-bold py-3 mb-4">
                            <span class="text-muted fw-light">Payments /</span>
                            Payment Management
                        </h4>

                        <div class="card">

                            <div class="card-header">
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

                                        <?php while ($row = mysqli_fetch_assoc($result)) { ?>

                                            <tr>

                                                <td>
                                                    <strong>
                                                        <?= htmlspecialchars($row['payment_id']); ?>
                                                    </strong>
                                                </td>

                                                <td>
                                                    #<?= htmlspecialchars($row['order_id']); ?>
                                                </td>

                                                <td>
                                                    <?= htmlspecialchars(
                                                        $row['first_name'] . ' ' . $row['last_name']
                                                    ); ?>
                                                </td>

                                                <td>
                                                    <?= htmlspecialchars($row['payment_method']); ?>
                                                </td>

                                                <td>
                                                    Rs. <?= number_format($row['amount'], 2); ?>
                                                </td>

                                                <td>

                                                    <?php
                                                    $status = strtolower($row['payment_status']);

                                                    if ($status == 'paid' || $status == 'completed') {
                                                        $badge = 'bg-label-success';
                                                    } elseif ($status == 'pending') {
                                                        $badge = 'bg-label-warning';
                                                    } elseif ($status == 'failed' || $status == 'cancelled') {
                                                        $badge = 'bg-label-danger';
                                                    } else {
                                                        $badge = 'bg-label-secondary';
                                                    }
                                                    ?>

                                                    <span class="badge <?= $badge; ?>">
                                                        <?= htmlspecialchars($row['payment_status']); ?>
                                                    </span>

                                                </td>

                                                <td>
                                                    <?= htmlspecialchars($row['paid_at']); ?>
                                                </td>

                                            </tr>

                                        <?php } ?>

                                    </tbody>

                                </table>

                            </div>
                        </div>

                    </div>
                    <!-- / Content -->


                    <!-- Footer -->
                    <?php include 'footer.php'; ?>
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>

                </div>
                <!-- / Content wrapper -->

            </div>
            <!-- / Layout page -->

        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>

    </div>
    <!-- / Layout wrapper -->

</body>
</html>