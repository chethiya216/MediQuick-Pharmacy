<?php
/**
 * MediQuick Pharmacy - My Orders (Customer Portal)
 * Location: public/my-orders.php
 */

session_start();

include_once __DIR__ . '/../includes/db.php';
include_once __DIR__ . '/../includes/auth.php';
include_once __DIR__ . '/../includes/head.php';

requireLogin();

$customerId = (int) $_SESSION['user_id'];

// Fetch customer orders matching exact database column names
$ordersSql = "
    SELECT 
        o.order_id,
        o.customer_id,
        o.prescription_id,
        o.order_date,
        o.status,
        o.subtotal,
        o.tax_amount,
        o.shipping_fee,
        o.total_amount,
        COUNT(oi.order_item_id) AS total_items
    FROM orders o
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    WHERE o.customer_id = ?
    GROUP BY 
        o.order_id,
        o.customer_id,
        o.prescription_id,
        o.order_date,
        o.status,
        o.subtotal,
        o.tax_amount,
        o.shipping_fee,
        o.total_amount
    ORDER BY o.order_date DESC
";

$stmt = $conn->prepare($ordersSql);
$stmt->bind_param('i', $customerId);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();

// Status badge helper function (handles your 'status' column values)
function getStatusBadge(string $status): string {
    switch (strtolower($status)) {
        case 'completed':
        case 'delivered':
            return '<span class="badge bg-success">Delivered</span>';
        case 'processing':
        case 'confirmed':
            return '<span class="badge bg-primary">Processing</span>';
        case 'pending':
            return '<span class="badge bg-warning text-dark">Pending</span>';
        case 'shipped':
        case 'out_for_delivery':
            return '<span class="badge bg-info text-dark">Out for Delivery</span>';
        case 'cancelled':
            return '<span class="badge bg-danger">Cancelled</span>';
        default:
            return '<span class="badge bg-secondary">' . htmlspecialchars(ucfirst($status)) . '</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - MediQuick Pharmacy</title>
    <style>
        :root {
            --mq-primary: #00C391;
            --mq-hover: #00B4DA;
            --mq-text: #294052;
        }

        body {
            background-color: #f8f9fa;
            color: var(--mq-text);
        }

        .card-order {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-order:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .btn-action-view {
            background-color: #ffffff;
            border: 1px solid var(--mq-primary);
            color: var(--mq-primary);
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .btn-action-view:hover {
            background-color: var(--mq-primary);
            color: #ffffff;
        }

        .empty-orders-icon {
            font-size: 4rem;
            color: #ced4da;
        }
    </style>
</head>
<body>

<?php include_once __DIR__ . '/../includes/header.php'; ?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">My Orders</h2>
            <p class="text-muted">View and track your previous prescription & medicine orders</p>
        </div>
        <a href="shop.php" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fa-solid fa-cart-shopping me-2"></i>Continue Shopping
        </a>
    </div>

    <?php if (empty($orders)): ?>
        <!-- Empty State -->
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <i class="fa-solid fa-box-open empty-orders-icon mb-3"></i>
                <h4>No orders found</h4>
                <p class="text-muted">You haven't placed any orders with MediQuick yet.</p>
                <a href="shop.php" class="btn btn-success px-4 rounded-pill" style="background-color: var(--mq-primary); border-color: var(--mq-primary);">
                    Start Shopping
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- Orders List -->
        <div class="row g-3">
            <?php foreach ($orders as $order): ?>
                <div class="col-12">
                    <div class="card card-order bg-white p-3">
                        <div class="row align-items-center">
                            <!-- Order Reference -->
                            <div class="col-md-3 col-6 mb-2 mb-md-0">
                                <span class="text-muted small d-block">Order ID</span>
                                <strong class="fs-6">#ORD-<?= str_pad($order['order_id'], 5, '0', STR_PAD_LEFT) ?></strong>
                                <?php if (!empty($order['prescription_id'])): ?>
                                    <span class="badge bg-warning text-dark ms-1" title="Prescription Order">
                                        <i class="fa-solid fa-file-prescription"></i> Rx
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Order Date -->
                            <div class="col-md-3 col-6 mb-2 mb-md-0">
                                <span class="text-muted small d-block">Date Placed</span>
                                <span><?= date('M d, Y - h:i A', strtotime($order['order_date'])) ?></span>
                            </div>

                            <!-- Items & Total -->
                            <div class="col-md-2 col-4">
                                <span class="text-muted small d-block">Total Amount</span>
                                <strong class="text-dark">Rs. <?= number_format($order['total_amount'], 2) ?></strong>
                                <small class="text-muted d-block">(<?= $order['total_items'] ?> <?= $order['total_items'] == 1 ? 'item' : 'items' ?>)</small>
                            </div>

                            <!-- Order Status -->
                            <div class="col-md-2 col-4 text-md-center">
                                <div>
                                    <?= getStatusBadge($order['status']) ?>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <div class="col-md-2 col-4 text-end">
                                <a href="order-details.php?id=<?= $order['order_id'] ?>" class="btn btn-action-view btn-sm w-100 py-2">
                                    View Details <i class="bi bi-chevron-right ms-1"></i></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>