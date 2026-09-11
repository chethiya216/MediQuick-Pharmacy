<?php

session_start();

require_once __DIR__ . '/../includes/db.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Database connection failed.");
}

$page_css = 'checkout-style.css';


/*
|--------------------------------------------------------------------------
| Require a logged-in customer
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['customer_id'])) {
    header('Location: login.php');
    exit;
}

$customer_id = (int) $_SESSION['customer_id'];
$order_id    = (int) ($_GET['order_id'] ?? 0);

if ($order_id <= 0) {
    header('Location: shop.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Load the order — only if it belongs to this customer
|--------------------------------------------------------------------------
*/

$order_sql = "
    SELECT
        o.order_id,
        o.prescription_id,
        o.order_date,
        o.status,
        o.subtotal,
        o.tax_amount,
        o.shipping_fee,
        o.total_amount,
        c.address AS shipping_address,
        c.phone AS shipping_phone
    FROM orders o
    INNER JOIN customers c
        ON c.customer_id = o.customer_id
    WHERE o.order_id = ?
      AND o.customer_id = ?
    LIMIT 1
";

$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("ii", $order_id, $customer_id);
$order_stmt->execute();
$order = $order_stmt->get_result()->fetch_assoc();
$order_stmt->close();

if (!$order) {
    header('Location: shop.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Load order items
|--------------------------------------------------------------------------
*/

$items_sql = "
    SELECT
        oi.quantity,
        oi.unit_price_at_purchase,
        oi.item_subtotal,
        p.product_name,
        p.product_image
    FROM order_items oi
    INNER JOIN products p
        ON p.product_id = oi.product_id
    WHERE oi.order_id = ?
";

$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$order_items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$items_stmt->close();


/*
|--------------------------------------------------------------------------
| Load payment
|--------------------------------------------------------------------------
*/

$payment_sql = "
    SELECT
        payment_method,
        amount,
        transaction_reference,
        payment_status,
        paid_at
    FROM payments
    WHERE order_id = ?
    ORDER BY payment_id DESC
    LIMIT 1
";

$payment_stmt = $conn->prepare($payment_sql);
$payment_stmt->bind_param("i", $order_id);
$payment_stmt->execute();
$payment = $payment_stmt->get_result()->fetch_assoc();
$payment_stmt->close();


function getProductImage($image)
{
    if (!empty($image)) {
        return 'assets/images/' . htmlspecialchars($image);
    }

    return 'assets/images/no-image.png';
}


require_once __DIR__ . '/../includes/header.php';

?>


<div class="checkout-page-wrapper">

    <div class="checkout-container">

        <div class="order-confirmation">

            <div class="confirmation-icon">&#10003;</div>

            <h1>Thank you for your order!</h1>

            <p>
                Your order <strong>#<?= (int) $order['order_id'] ?></strong> has been placed
                and is currently <strong><?= htmlspecialchars(ucfirst($order['status'])) ?></strong>.
            </p>

            <?php if (!empty($order['prescription_id'])): ?>
                <p class="prescription-warning">
                    One or more items require prescription verification before your order ships.
                    Our pharmacist will review the prescription you uploaded.
                </p>
            <?php endif; ?>


            <div class="checkout-section">

                <h2>Order Items</h2>

                <div class="checkout-items">

                    <?php foreach ($order_items as $item): ?>

                        <div class="checkout-item">

                            <div class="product-image">
                                <img
                                    src="<?= getProductImage($item['product_image']) ?>"
                                    alt="<?= htmlspecialchars($item['product_name']) ?>"
                                >
                            </div>

                            <div class="item-details">
                                <h3 class="product-name">
                                    <?= htmlspecialchars($item['product_name']) ?>
                                </h3>
                                <p class="item-meta">
                                    Qty: <?= (int) $item['quantity'] ?>
                                    &times;
                                    $<?= number_format($item['unit_price_at_purchase'], 2) ?>
                                </p>
                            </div>

                            <div class="item-total">
                                $<?= number_format($item['item_subtotal'], 2) ?>
                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>


            <div class="checkout-section">

                <h2>Delivery</h2>

                <p><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
                <p><?= htmlspecialchars($order['shipping_phone']) ?></p>

            </div>


            <?php if ($payment): ?>

                <div class="checkout-section">

                    <h2>Payment</h2>

                    <p>
                        Method: <?= htmlspecialchars(ucwords(str_replace('_', ' ', $payment['payment_method']))) ?><br>
                        Reference: <?= htmlspecialchars($payment['transaction_reference']) ?><br>
                        Status: <?= htmlspecialchars(ucfirst($payment['payment_status'])) ?>
                    </p>

                </div>

            <?php endif; ?>


            <div class="checkout-summary">

                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>$<?= number_format($order['subtotal'], 2) ?></span>
                </div>

                <div class="summary-row">
                    <span>Shipping</span>
                    <span>$<?= number_format($order['shipping_fee'], 2) ?></span>
                </div>

                <div class="summary-row summary-total">
                    <span>Total</span>
                    <strong>$<?= number_format($order['total_amount'], 2) ?></strong>
                </div>

            </div>

            <a href="shop.php" class="place-order-btn">
                Continue Shopping
            </a>

        </div>

    </div>

</div>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>