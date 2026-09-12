<?php

session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/head.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Database connection failed.");
}


/*
|--------------------------------------------------------------------------
| Checkout page CSS
|--------------------------------------------------------------------------
*/

$page_css = 'checkout-style.css';


/*
|--------------------------------------------------------------------------
| Require a logged-in customer
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['customer_id'])) {
    header('Location: login.php?redirect=checkout.php');
    exit;
}

$customer_id = (int) $_SESSION['customer_id'];


/*
|--------------------------------------------------------------------------
| Require a cart to exist
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['cart_id'])) {
    header('Location: cart.php');
    exit;
}

$cart_id = (int) $_SESSION['cart_id'];


/*
|--------------------------------------------------------------------------
| Load the customer's saved details
|--------------------------------------------------------------------------
*/

$customer_sql = "
    SELECT
        first_name,
        last_name,
        email,
        phone,
        address
    FROM customers
    WHERE customer_id = ?
    LIMIT 1
";

$customer_stmt = $conn->prepare($customer_sql);
$customer_stmt->bind_param("i", $customer_id);
$customer_stmt->execute();
$customer = $customer_stmt->get_result()->fetch_assoc();
$customer_stmt->close();

if (!$customer) {
    die("Customer record not found.");
}


/*
|--------------------------------------------------------------------------
| Load cart items fresh from the database
|--------------------------------------------------------------------------
| Prices, discounts, prescription flags, and status are always read from
| the database here (never trusted from POST data), so a customer can't
| tamper with the price they pay.
*/

function loadCartItems(mysqli $conn, int $cart_id): array
{
    $sql = "
        SELECT
            ci.cart_item_id,
            ci.product_id,
            ci.quantity,
            p.product_name,
            p.generic_name,
            p.sku,
            p.unit_price,
            p.discount_percent,
            p.product_image,
            p.requires_prescription,
            p.status
        FROM cart_items ci
        INNER JOIN products p
            ON p.product_id = ci.product_id
        WHERE ci.cart_id = ?
        ORDER BY ci.cart_item_id DESC
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Checkout query failed: " . $conn->error);
    }

    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $items    = [];
    $subtotal = 0;

    while ($row = $result->fetch_assoc()) {

        $unit_price = (float) $row['unit_price'];
        $discount   = (float) ($row['discount_percent'] ?? 0);
        $quantity   = (int) $row['quantity'];

        $discounted_price = $unit_price;

        if ($discount > 0) {
            $discounted_price = $unit_price - ($unit_price * $discount / 100);
        }

        $item_subtotal = $discounted_price * $quantity;

        $row['discounted_price'] = $discounted_price;
        $row['item_subtotal']    = $item_subtotal;

        $items[] = $row;

        if ($row['status'] === 'active') {
            $subtotal += $item_subtotal;
        }
    }

    $stmt->close();

    return [$items, $subtotal];
}

[$cart_items, $subtotal] = loadCartItems($conn, $cart_id);


/*
|--------------------------------------------------------------------------
| Make sure the cart isn't empty
|--------------------------------------------------------------------------
*/

if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Block checkout if any product in the cart is no longer active
|--------------------------------------------------------------------------
*/

$has_inactive_product = false;

foreach ($cart_items as $item) {
    if ($item['status'] !== 'active') {
        $has_inactive_product = true;
        break;
    }
}


/*
|--------------------------------------------------------------------------
| Shipping + totals
|--------------------------------------------------------------------------
*/

$shipping     = $subtotal > 0 ? 3.00 : 0.00;
$tax_amount   = 0.00;
$total        = $subtotal + $shipping + $tax_amount;


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function getProductImage($image)
{
    if (!empty($image)) {
        return 'assets/images/' . htmlspecialchars($image);
    }

    return 'assets/images/no-image.png';
}

function generateTransactionReference(): string
{
    return 'TXN-' . date('Ymd-His') . '-' . strtoupper(bin2hex(random_bytes(3)));
}

$errors = [];


/*
|--------------------------------------------------------------------------
| Handle order placement
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {

    // Re-check everything fresh from the DB right before we commit,
    // in case another tab changed the cart in the meantime.
    [$cart_items, $subtotal] = loadCartItems($conn, $cart_id);

    if (empty($cart_items)) {
        $errors[] = "Your cart is empty.";
    }

    foreach ($cart_items as $item) {
        if ($item['status'] !== 'active') {
            $errors[] = "\"" . $item['product_name'] . "\" is no longer available. Please remove it from your cart.";
        }
    }

    $shipping   = $subtotal > 0 ? 3.00 : 0.00;
    $tax_amount = 0.00;
    $total      = $subtotal + $shipping + $tax_amount;

    // Delivery details — the `orders` table has no columns of its own for
    // this, so we save it back onto the customer's profile (customers.address
    // / customers.phone) and use that as the delivery info for this order.
    $delivery_address = trim($_POST['delivery_address'] ?? '');
    $delivery_phone   = trim($_POST['delivery_phone'] ?? '');

    if ($delivery_address === '') {
        $errors[] = "Please enter a delivery address.";
    }

    if ($delivery_phone === '') {
        $errors[] = "Please enter a contact phone number.";
    }

    // Payment method
    $payment_method = $_POST['payment_method'] ?? '';

    $allowed_payment_methods = ['card', 'cash', 'bank_transfer', 'mobile_wallet'];

    if (!in_array($payment_method, $allowed_payment_methods, true)) {
        $errors[] = "Please select a valid payment method.";
    }

    // If everything checks out, create the order
    if (empty($errors)) {

        $conn->begin_transaction();

        try {

            $prescription_id = null;

            /*
            |----------------------------------------------------------------
            | Save delivery details onto the customer's profile
            |----------------------------------------------------------------
            | The `orders` table has no address/phone columns of its own, so
            | we keep using the customer's saved details as the source of
            | truth and just update them here if the customer changed them
            | on this page.
            */

            $update_customer_sql = "
                UPDATE customers
                SET address = ?, phone = ?
                WHERE customer_id = ?
            ";

            $update_customer_stmt = $conn->prepare($update_customer_sql);
            $update_customer_stmt->bind_param(
                "ssi",
                $delivery_address,
                $delivery_phone,
                $customer_id
            );

            if (!$update_customer_stmt->execute()) {
                throw new Exception("Unable to save delivery details: " . $update_customer_stmt->error);
            }

            $update_customer_stmt->close();

            /*
            |----------------------------------------------------------------
            | Create the order
            |----------------------------------------------------------------
            */

            $order_sql = "
                INSERT INTO orders
                    (customer_id, prescription_id, status, subtotal, tax_amount, shipping_fee, total_amount)
                VALUES
                    (?, ?, 'pending', ?, ?, ?, ?)
            ";

            $order_stmt = $conn->prepare($order_sql);
            $order_stmt->bind_param(
                "iidddd",
                $customer_id,
                $prescription_id,
                $subtotal,
                $tax_amount,
                $shipping,
                $total
            );

            if (!$order_stmt->execute()) {
                throw new Exception("Unable to create order: " . $order_stmt->error);
            }

            $order_id = $order_stmt->insert_id;
            $order_stmt->close();

            /*
            |----------------------------------------------------------------
            | Create the order items (one row per purchased product)
            |----------------------------------------------------------------
            */

            $order_item_sql = "
                INSERT INTO order_items
                    (order_id, product_id, quantity, unit_price_at_purchase, item_subtotal)
                VALUES
                    (?, ?, ?, ?, ?)
            ";

            $order_item_stmt = $conn->prepare($order_item_sql);

            foreach ($cart_items as $item) {

                $product_id     = (int) $item['product_id'];
                $quantity       = (int) $item['quantity'];
                $unit_price_paid = $item['discounted_price'];
                $item_subtotal  = $item['item_subtotal'];

                $order_item_stmt->bind_param(
                    "iiidd",
                    $order_id,
                    $product_id,
                    $quantity,
                    $unit_price_paid,
                    $item_subtotal
                );

                if (!$order_item_stmt->execute()) {
                    throw new Exception("Unable to save order item: " . $order_item_stmt->error);
                }
            }

            $order_item_stmt->close();

            /*
            |----------------------------------------------------------------
            | Simulated payment
            |----------------------------------------------------------------
            | There is no real payment gateway wired up. We record the
            | chosen method and mark it completed immediately, then move
            | the order to "confirmed".
            */

            $transaction_reference = generateTransactionReference();

            $payment_sql = "
                INSERT INTO payments
                    (order_id, payment_method, amount, transaction_reference, payment_status, paid_at)
                VALUES
                    (?, ?, ?, ?, 'completed', NOW())
            ";

            $payment_stmt = $conn->prepare($payment_sql);
            $payment_stmt->bind_param(
                "isds",
                $order_id,
                $payment_method,
                $total,
                $transaction_reference
            );

            if (!$payment_stmt->execute()) {
                throw new Exception("Unable to record payment: " . $payment_stmt->error);
            }

            $payment_stmt->close();

            $update_order_status_sql = "
                UPDATE orders
                SET status = 'confirmed'
                WHERE order_id = ?
            ";

            $update_order_status_stmt = $conn->prepare($update_order_status_sql);
            $update_order_status_stmt->bind_param("i", $order_id);
            $update_order_status_stmt->execute();
            $update_order_status_stmt->close();

            /*
            |----------------------------------------------------------------
            | Clear the cart
            |----------------------------------------------------------------
            */

            $clear_cart_sql = "DELETE FROM cart_items WHERE cart_id = ?";
            $clear_cart_stmt = $conn->prepare($clear_cart_sql);
            $clear_cart_stmt->bind_param("i", $cart_id);
            $clear_cart_stmt->execute();
            $clear_cart_stmt->close();

            $conn->commit();

            header('Location: order-confirmation.php?order_id=' . $order_id);
            exit;

        } catch (Exception $e) {

            $conn->rollback();
            $errors[] = "We couldn't place your order: " . $e->getMessage();
        }
    }
}


/*
|--------------------------------------------------------------------------
| Header
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/header.php';

?>


<div class="checkout-page-wrapper">

    <div class="checkout-container">

        <h1 class="page-title">
            Checkout
        </h1>


        <?php if (!empty($errors)): ?>

            <div class="checkout-errors">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

        <?php endif; ?>


        <?php if ($has_inactive_product): ?>

            <div class="checkout-warning">
                One or more items in your cart are no longer available.
                Please <a href="cart.php">go back to your cart</a> and remove them before checking out.
            </div>

        <?php endif; ?>


        <form method="POST" class="checkout-layout">

            <div class="checkout-main">

                <!-- =============================================
                     ORDER ITEMS
                ============================================== -->

                <div class="checkout-section">

                    <h2>Your Order</h2>

                    <div class="checkout-items">

                        <?php foreach ($cart_items as $item): ?>

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

                                    <?php if (!empty($item['requires_prescription'])): ?>
                                        <div class="prescription-warning">
                                            Prescription required
                                        </div>
                                    <?php endif; ?>

                                    <p class="item-meta">
                                        Qty: <?= (int) $item['quantity'] ?>
                                        &times;
                                        $<?= number_format($item['discounted_price'], 2) ?>
                                    </p>

                                </div>

                                <div class="item-total">
                                    $<?= number_format($item['item_subtotal'], 2) ?>
                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                </div>


                <!-- =============================================
                     DELIVERY DETAILS
                ============================================== -->

                <div class="checkout-section">

                    <h2>Delivery Details</h2>

                    <div class="form-group">
                        <label for="delivery_address">Delivery address *</label>
                        <textarea
                            id="delivery_address"
                            name="delivery_address"
                            rows="3"
                            required
                        ><?= htmlspecialchars($_POST['delivery_address'] ?? $customer['address'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="delivery_phone">Contact phone *</label>
                        <input
                            type="text"
                            id="delivery_phone"
                            name="delivery_phone"
                            value="<?= htmlspecialchars($_POST['delivery_phone'] ?? $customer['phone'] ?? '') ?>"
                            required
                        >
                    </div>

                </div>


                <!-- =============================================
                     PAYMENT METHOD
                ============================================== -->

                <div class="checkout-section">

                    <h2>Payment Method</h2>

                    <p class="form-note">
                        This is a simulated checkout — no real payment is processed.
                    </p>

                    <div class="payment-options">

                        <?php
                        $payment_labels = [
                            'card'          => 'Credit / Debit Card',
                            'cash'          => 'Cash on Delivery',
                            'bank_transfer' => 'Bank Transfer',
                            'mobile_wallet' => 'Mobile Wallet',
                        ];

                        $selected_method = $_POST['payment_method'] ?? 'card';
                        ?>

                        <?php foreach ($payment_labels as $value => $label): ?>

                            <label class="payment-option">
                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="<?= $value ?>"
                                    <?= $selected_method === $value ? 'checked' : '' ?>
                                >
                                <?= $label ?>
                            </label>

                        <?php endforeach; ?>

                    </div>

                </div>

            </div>


            <!-- =============================================
                 ORDER SUMMARY
            ============================================== -->

            <div class="checkout-summary">

                <h2>Order Summary</h2>

                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>$<?= number_format($subtotal, 2) ?></span>
                </div>

                <div class="summary-row">
                    <span>Shipping</span>
                    <span>$<?= number_format($shipping, 2) ?></span>
                </div>

                <div class="summary-row summary-total">
                    <span>Total</span>
                    <strong>$<?= number_format($total, 2) ?></strong>
                </div>

                <button
                    type="submit"
                    name="place_order"
                    value="1"
                    class="place-order-btn"
                    <?= $has_inactive_product ? 'disabled' : '' ?>
                >
                    Place Order
                </button>

                <a href="cart.php" class="back-to-cart-btn">
                    Back to Cart
                </a>

            </div>

        </form>

    </div>

</div>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>