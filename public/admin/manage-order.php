<?php
require_once('../../includes/db.php');

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
?>

<html
    lang="en"
    class="light-style layout-menu-fixed"
    dir="ltr"
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

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0">
                        Manage Orders
                    </h4>
                </div>

                <div class="card">

                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            Order List
                        </h5>
                    </div>

                    <div class="card-body">

                        <div class="table-responsive">

                            <table class="table table-hover table-bordered">

                                <thead class="table-light">
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Order Date</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>

                                <tbody>

                                <?php if($result && $result->num_rows > 0): ?>

                                    <?php while($row = $result->fetch_assoc()): ?>

                                        <tr>

                                            <td>
                                                #<?= $row['order_id']; ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($row['customer_name']); ?>
                                            </td>

                                            <td>
                                                <?= date(
                                                    'Y-m-d H:i',
                                                    strtotime($row['order_date'])
                                                ); ?>
                                            </td>

                                            <td>
                                                Rs.
                                                <?= number_format(
                                                    $row['total_amount'],
                                                    2
                                                ); ?>
                                            </td>

                                            <td>

                                                <form method="POST">

                                                    <input
                                                        type="hidden"
                                                        name="order_id"
                                                        value="<?= $row['order_id']; ?>"
                                                    >

                                                    <select
                                                        name="status"
                                                        class="form-select"
                                                        onchange="this.form.submit();"
                                                    >

                                                        <option
                                                            value="pending"
                                                            <?= ($row['status']=='pending') ? 'selected' : ''; ?>
                                                        >
                                                            Pending
                                                        </option>

                                                        <option
                                                            value="confirmed"
                                                            <?= ($row['status']=='confirmed') ? 'selected' : ''; ?>
                                                        >
                                                            Confirmed
                                                        </option>

                                                        <option
                                                            value="shipped"
                                                            <?= ($row['status']=='shipped') ? 'selected' : ''; ?>
                                                        >
                                                            Shipped
                                                        </option>

                                                        <option
                                                            value="delivered"
                                                            <?= ($row['status']=='delivered') ? 'selected' : ''; ?>
                                                        >
                                                            Delivered
                                                        </option>

                                                        <option
                                                            value="cancelled"
                                                            <?= ($row['status']=='cancelled') ? 'selected' : ''; ?>
                                                        >
                                                            Cancelled
                                                        </option>

                                                    </select>

                                                </form>

                                            </td>

                                        </tr>

                                    <?php endwhile; ?>

                                <?php else: ?>

                                    <tr>
                                        <td
                                            colspan="5"
                                            class="text-center"
                                        >
                                            No orders found
                                        </td>
                                    </tr>

                                <?php endif; ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

            <?php include 'includes/footer.php'; ?>

            <div class="content-backdrop fade"></div>

        </div>

    </div>

</div>
</div>

</body>
</html>