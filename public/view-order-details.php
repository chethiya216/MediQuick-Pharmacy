<?php
/**
 * MediQuick Pharmacy - Order Details (Customer Portal)
 * Location: public/order-details.php
 */

session_start();

include_once __DIR__ . '/../includes/db.php';
include_once __DIR__ . '/../includes/auth.php';
include_once __DIR__ . '/../includes/head.php';

requireLogin();

$customerId = (int) $_SESSION['user_id'];
$orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($orderId <= 0) {
    header('Location: my-orders.php');
    exit;
}

// 1. Fetch Order Master Details
$orderSql = "
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
        o.shipping_address_line1,
        o.shipping_address_line2,
        o.shipping_city,
        o.shipping_state,
        o.shipping_postal_code,
        o.shipping_country,
        o.updated_at
    FROM orders o
    WHERE o.order_id = ? AND o.customer_id = ?
";

$stmt = $conn->prepare($orderSql);
$stmt->bind_param('ii', $orderId, $customerId);
$stmt->execute();
$orderResult = $stmt->get_result();
$order = $orderResult->fetch_assoc();
$stmt->close();

// If order doesn't exist or doesn't belong to this customer, redirect
if (!$order) {
    header('Location: my-orders.php');
    exit;
}

// 2. Fetch Order Items
$itemsSql = "
    SELECT 
        oi.order_item_id,
        oi.product_id,
        oi.quantity,
        oi.unit_price_at_purchase,
        oi.item_subtotal,
        p.product_name,
        p.product_image
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
";

$itemsStmt = $conn->prepare($itemsSql);
$itemsStmt->bind_param('i', $orderId);
$itemsStmt->execute();
$itemsResult = $itemsStmt->get_result();

$orderItems = [];
while ($row = $itemsResult->fetch_assoc()) {
    $orderItems[] = $row;
}
$itemsStmt->close();

// Helper for status badge
function getStatusBadge(string $status): string {
    switch (strtolower($status)) {
        case 'completed':
        case 'delivered':
            return '<span class="badge bg-success px-3 py-2 fs-6">Delivered</span>';
        case 'processing':
        case 'confirmed':
            return '<span class="badge bg-primary px-3 py-2 fs-6">Processing</span>';
        case 'pending':
            return '<span class="badge bg-warning text-dark px-3 py-2 fs-6">Pending</span>';
        case 'shipped':
        case 'out_for_delivery':
            return '<span class="badge bg-info text-dark px-3 py-2 fs-6">Out for Delivery</span>';
        case 'cancelled':
            return '<span class="badge bg-danger px-3 py-2 fs-6">Cancelled</span>';
        default:
            return '<span class="badge bg-secondary px-3 py-2 fs-6">' . htmlspecialchars(ucfirst($status)) . '</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #ORD-<?= str_pad($order['order_id'], 5, '0', STR_PAD_LEFT) ?> - MediQuick</title>
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

        .card-custom {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        }

        .item-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            background-color: #f1f3f5;
        }

        .btn-back {
            background-color: #ffffff;
            border: 1px solid #ced4da;
            color: var(--mq-text);
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .btn-back:hover {
            background-color: #e9ecef;
            color: var(--mq-text);
        }
    </style>
</head>
<body>

<?php include_once __DIR__ . '/../includes/header.php'; ?>

<div class="container py-5">
    <!-- Top Action Row -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="my-orders.php" class="btn btn-back btn-sm mb-2">
                <i class="bx bx-arrow-back me-1"></i>  Back to Orders
            </a>
            <h2 class="fw-bold mb-0">
                Order #ORD-<?= str_pad($order['order_id'], 5, '0', STR_PAD_LEFT) ?>
                <?php if (!empty($order['prescription_id'])): ?>
                    <span class="badge bg-warning text-dark ms-2 fs-6" title="Prescription Order">
                        <i class="fa-solid fa-file-prescription"></i> Rx Attached
                    </span>
                <?php endif; ?>
            </h2>
            <p class="text-muted small mb-0">Placed on <?= date('F d, Y \a\t h:i A', strtotime($order['order_date'])) ?></p>
        </div>
        <div>
            <?= getStatusBadge($order['status']) ?>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Side: Order Items -->
        <div class="col-lg-8">
            <div class="card card-custom bg-white p-4">
                <h5 class="fw-bold mb-3 border-bottom pb-2">Ordered Items</h5>
                
                <?php if (empty($orderItems)): ?>
                    <p class="text-muted">No item breakdown available for this order.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <thead class="table-light text-muted small">
                                <tr>
                                    <th scope="col">Product</th>
                                    <th scope="col" class="text-center">Price</th>
                                    <th scope="col" class="text-center">Qty</th>
                                    <th scope="col" class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orderItems as $item): ?>
                                    <tr class="border-bottom">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($item['product_image'])): ?>
                                                    <img src="<?= htmlspecialchars($item['product_image']) ?>" alt="Product" class="item-img me-3">
                                                <?php else: ?>
                                                    <div class="item-img me-3 d-flex align-items-center justify-content-center text-muted">
                                                        <i class="fa-solid fa-pills fs-4"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <strong class="d-block text-dark"><?= htmlspecialchars($item['product_name'] ?? 'Medicine / Item') ?></strong>
                                                    <small class="text-muted">ID: #<?= $item['product_id'] ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">Rs. <?= number_format($item['unit_price_at_purchase'], 2) ?></td>
                                        <td class="text-center"><?= $item['quantity'] ?></td>
                                        <td class="text-end fw-bold">Rs. <?= number_format($item['item_subtotal'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Side: Shipping & Cost Breakdown -->
        <div class="col-lg-4">
            <!-- Shipping Details -->
            <div class="card card-custom bg-white p-4 mb-4">
                <h5 class="fw-bold mb-3 border-bottom pb-2">
                    <i class="fa-solid fa-location-dot me-2 text-muted"></i>Shipping Address
                </h5>
                <address class="mb-0 text-secondary lh-base">
                    <?= htmlspecialchars($order['shipping_address_line1']) ?><br>
                    <?php if (!empty($order['shipping_address_line2'])): ?>
                        <?= htmlspecialchars($order['shipping_address_line2']) ?><br>
                    <?php endif; ?>
                    <?= htmlspecialchars($order['shipping_city']) ?>, <?= htmlspecialchars($order['shipping_state']) ?> <?= htmlspecialchars($order['shipping_postal_code']) ?><br>
                    <strong><?= htmlspecialchars($order['shipping_country']) ?></strong>
                </address>
            </div>

            <!-- Financial Summary -->
            <div class="card card-custom bg-white p-4">
                <h5 class="fw-bold mb-3 border-bottom pb-2">Order Summary</h5>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal</span>
                    <span>Rs. <?= number_format($order['subtotal'], 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Tax</span>
                    <span>Rs. <?= number_format($order['tax_amount'], 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Shipping Fee</span>
                    <span>Rs. <?= number_format($order['shipping_fee'], 2) ?></span>
                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center mb-0">
                    <strong class="fs-5">Grand Total</strong>
                    <strong class="fs-5" style="color: var(--mq-primary);">Rs. <?= number_format($order['total_amount'], 2) ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>