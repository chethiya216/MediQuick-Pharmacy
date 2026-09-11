<?php
session_start();

require_once __DIR__ . '/../includes/db.php';

/*
|--------------------------------------------------------------------------
| DATABASE CHECK
|--------------------------------------------------------------------------
*/
if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Database connection failed.");
}

/*
|--------------------------------------------------------------------------
| LOGIN CHECK
|--------------------------------------------------------------------------
*/
if (empty($_SESSION['customer_id'])) {
    header("Location: login.php?redirect=checkout.php");
    exit;
}

$customer_id = (int) $_SESSION['customer_id'];

/*
|--------------------------------------------------------------------------
| SESSION / HANDLER MESSAGES
|--------------------------------------------------------------------------
*/
$error = $_SESSION['checkout_error'] ?? '';
unset($_SESSION['checkout_error']);

// Capture success state and order ID
$order_success = $_SESSION['order_success'] ?? false;
$order_id = $_SESSION['last_order_id'] ?? 0;

// FIX: Immediately clear them so refreshing or going back won't trap you on the success page[cite: 3]
if ($order_success) {
    unset($_SESSION['order_success']);
    unset($_SESSION['last_order_id']);
}

/*
|--------------------------------------------------------------------------
| VARIABLES
|--------------------------------------------------------------------------
*/
$customer = null;
$cart_items = [];

$subtotal = 0;
$shipping = 0;
$tax = 0;
$total = 0;

/*
|--------------------------------------------------------------------------
| GET CUSTOMER DETAILS
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare("
    SELECT
        customer_id,
        first_name,
        last_name,
        email,
        phone,
        address
    FROM customers
    WHERE customer_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $customer_id);
$stmt->execute();

$result = $stmt->get_result();
$customer = $result->fetch_assoc();

$stmt->close();

if (!$customer) {
    session_destroy();
    header("Location: login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| FIND CUSTOMER CART
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare("
    SELECT cart_id
    FROM carts
    WHERE customer_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $customer_id);
$stmt->execute();

$result = $stmt->get_result();
$cart = $result->fetch_assoc();

$stmt->close();

if (!$cart) {
    header("Location: cart.php");
    exit;
}

$cart_id = (int) $cart['cart_id'];

/*
|--------------------------------------------------------------------------
| GET CART ITEMS
|--------------------------------------------------------------------------
*/
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
        ON ci.product_id = p.product_id

    WHERE ci.cart_id = ?

    ORDER BY ci.cart_item_id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cart_id);
$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {

    $unit_price = (float) $row['unit_price'];
    $discount = (float) $row['discount_percent'];

    $discounted_price = $unit_price;

    if ($discount > 0) {
        $discounted_price =
            $unit_price - ($unit_price * $discount / 100);
    }

    $quantity = (int) $row['quantity'];

    $item_subtotal = $discounted_price * $quantity;

    $row['discounted_price'] = $discounted_price;
    $row['item_subtotal'] = $item_subtotal;

    $cart_items[] = $row;

    $subtotal += $item_subtotal;
}

$stmt->close();

if (empty($cart_items) && !$order_success) {
    header("Location: cart.php");
    exit;
}

$shipping = 3.00;
$tax = 0.00;
$total = $subtotal + $shipping + $tax;

$phone = $customer['phone'] ?? '';
$address = $customer['address'] ?? '';
$payment_method = 'card';

/*
|--------------------------------------------------------------------------
| INCLUDE HEADER
|--------------------------------------------------------------------------
*/
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Link to external stylesheet -->
<link rel="stylesheet" href="assets/css/checkout-style.css">

<!-- Inline Gateway Tweaks for Ultra-Clean Professional Look -->
<style>
.gateway-frame {
    border: 1px solid #e0e0e0;
    background: #fafbfc;
    border-radius: 10px;
    padding: 20px;
    margin-top: 15px;
}
.gateway-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}
.gateway-title {
    font-size: 14px;
    font-weight: 700;
    color: #4a5568;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.secure-badge {
    font-size: 12px;
    color: #2f855a;
    background: #e6fffa;
    padding: 3px 8px;
    border-radius: 12px;
    font-weight: 600;
}
.card-input-wrapper {
    position: relative;
}
.card-input-wrapper input {
    font-family: monospace;
    letter-spacing: 1px;
}
</style>


<?php if ($order_success): ?>

<!-- =========================================================
     ORDER SUCCESS SCREEN
     ========================================================= -->

<div class="checkout-container">

    <div class="success-box">

        <div class="success-icon">
            ✓
        </div>

        <h1>Order Placed Successfully!</h1>

        <p class="success-text">
            Thank you for shopping with MediQuick.
            Your order has been successfully placed.
        </p>

        <div class="success-details">

            <h3>Order Details</h3>

            <div class="detail-row">
                <span class="detail-label">Order ID</span>
                <span class="detail-value">#<?= htmlspecialchars($order_id) ?></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Customer</span>
                <span class="detail-value">
                    <?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?>
                </span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Payment Method</span>
                <span class="detail-value">
                    <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $payment_method))) ?>
                </span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Payment Status</span>
                <span class="detail-value">
                    <span class="status-badge payment-completed">
                        <?= ($payment_method === 'cash') ? "Pending" : "Completed" ?>
                    </span>
                </span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Order Status</span>
                <span class="detail-value">
                    <span class="status-badge">Pending</span>
                </span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Subtotal</span>
                <span class="detail-value">$<?= number_format($subtotal, 2) ?></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Shipping</span>
                <span class="detail-value">$<?= number_format($shipping, 2) ?></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Tax</span>
                <span class="detail-value">$<?= number_format($tax, 2) ?></span>
            </div>

            <div class="detail-row success-total">
                <span>Total</span>
                <span>$<?= number_format($total, 2) ?></span>
            </div>

        </div>

        <div class="success-buttons">
            <a href="my-orders.php" class="success-btn success-btn-primary">View My Orders</a>
            <a href="shop.php" class="success-btn success-btn-secondary">Continue Shopping</a>
        </div>

    </div>

</div>


<?php else: ?>

<!-- =========================================================
     CHECKOUT FORM
     ========================================================= -->

<div class="checkout-container">

    <h1 class="checkout-title">Checkout</h1>

    <?php if ($error !== ''): ?>
        <div class="error-message">
            <strong>Error:</strong> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>


    <form method="POST" action="handlers/checkout-handler.php">

        <div class="checkout-grid">

            <!-- LEFT SIDE -->
            <div>

                <!-- CUSTOMER DETAILS -->
                <div class="checkout-card">
                    <h2>Delivery Information</h2>

                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" value="<?= htmlspecialchars($customer['first_name']) ?>" class="readonly-input" readonly>
                    </div>

                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" value="<?= htmlspecialchars($customer['last_name']) ?>" class="readonly-input" readonly>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" value="<?= htmlspecialchars($customer['email']) ?>" class="readonly-input" readonly>
                    </div>

                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($phone) ?>" placeholder="Enter your phone number" required>
                    </div>

                    <div class="form-group">
                        <label>Delivery Address</label>
                        <textarea name="address" placeholder="Enter your delivery address" required><?= htmlspecialchars($address) ?></textarea>
                    </div>
                </div>


                <!-- PAYMENT GATEWAY CONTAINER -->
                <div class="checkout-card">
                    <h2>Payment Method</h2>

                    <!-- CARD OPTION -->
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="card" <?= $payment_method === 'card' ? 'checked' : '' ?> onchange="showCardDetails()">
                        <strong>Credit / Debit Card</strong> <span style="font-size: 12px; color: #718096; float: right;">Visa, MasterCard, Amex</span>
                    </label>

                    <!-- CASH OPTION -->
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="cash" <?= $payment_method === 'cash' ? 'checked' : '' ?> onchange="hideCardDetails()">
                        <strong>Cash on Delivery</strong>
                    </label>

                    <!-- PROFESSIONAL GATEWAY SIMULATION WRAPPER -->
                    <div id="cardDetails" class="gateway-frame">
                        
                        <div class="gateway-header" style="margin-bottom: 0;">
                            <span class="gateway-title">Secure Card Details</span>
                            <span class="secure-badge">🔒 256-bit SSL Encrypted</span>
                        </div>

                        <div class="form-group" style="margin-top: 15px;">
                            <label>Card Information</label>
                            <div class="card-input-wrapper">
                                <input type="text" id="cardNumber" name="card_number" maxlength="19" placeholder="4242 4242 4242 4242" autocomplete="cc-number">
                            </div>
                        </div>

                        <div class="card-row">
                            <div class="form-group" style="margin-bottom:0;">
                                <label>Expiration Date</label>
                                <input type="text" id="cardExpiry" name="card_expiry" maxlength="7" placeholder="MM/YYYY" autocomplete="cc-exp">
                            </div>

                            <div class="form-group" style="margin-bottom:0;">
                                <label>CVV / CVC</label>
                                <input type="password" name="card_cvv" maxlength="4" placeholder="123" autocomplete="cc-csc">
                            </div>
                        </div>

                    </div>

                </div>

            </div>


            <!-- RIGHT SIDE - ORDER SUMMARY -->
            <div>

                <div class="checkout-card">
                    <h2>Your Order</h2>

                    <?php foreach ($cart_items as $item): ?>
                        <div class="summary-item">
                            <?php if (!empty($item['product_image'])): ?>
                                <img src="<?= htmlspecialchars($item['product_image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" class="product-image">
                            <?php else: ?>
                                <div class="product-image"></div>
                            <?php endif; ?>

                            <div class="product-info">
                                <div class="product-name"><?= htmlspecialchars($item['product_name']) ?></div>
                                <div class="product-qty">Qty: <?= (int)$item['quantity'] ?></div>
                            </div>

                            <div class="product-price">$<?= number_format($item['item_subtotal'], 2) ?></div>
                        </div>
                    <?php endforeach; ?>

                    <div style="margin-top:20px;">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <strong>$<?= number_format($subtotal, 2) ?></strong>
                        </div>

                        <div class="summary-row">
                            <span>Shipping</span>
                            <strong>$<?= number_format($shipping, 2) ?></strong>
                        </div>

                        <div class="summary-row">
                            <span>Tax</span>
                            <strong>$<?= number_format($tax, 2) ?></strong>
                        </div>

                        <div class="summary-row summary-total">
                            <span>Total</span>
                            <span>$<?= number_format($total, 2) ?></span>
                        </div>
                    </div>

                    <button type="submit" class="place-order-btn">
                        Pay $<?= number_format($total, 2) ?> Now
                    </button>

                </div>

            </div>

        </div>

    </form>

</div>

<?php endif; ?>


<script>
function showCardDetails() {
    document.getElementById('cardDetails').style.display = 'block';
}

function hideCardDetails() {
    document.getElementById('cardDetails').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function () {
    const selectedPayment = document.querySelector('input[name="payment_method"]:checked');
    if (selectedPayment) {
        if (selectedPayment.value === 'card') {
            showCardDetails();
        } else {
            hideCardDetails();
        }
    }

    // Auto-space card number every 4 digits
    const cardNumberInput = document.getElementById('cardNumber');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            let formattedValue = '';
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) {
                    formattedValue += ' ';
                }
                formattedValue += value[i];
            }
            e.target.value = formattedValue;
        });
    }

    // Auto-slash expiration date with MM/YYYY formatting & past-date protection
    const cardExpiryInput = document.getElementById('cardExpiry');
    if (cardExpiryInput) {
        cardExpiryInput.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            
            // Limit total digits to 6 (MMYYYY)
            if (value.length > 6) {
                value = value.slice(0, 6);
            }
            
            if (value.length >= 2) {
                let month = parseInt(value.slice(0, 2), 10);
                if (month > 12) {
                    month = 12;
                    value = '12' + value.slice(2);
                } else if (month === 0) {
                    month = 1;
                    value = '01' + value.slice(2);
                }

                // Check year validity (Minimum year is 2026)
                if (value.length >= 6) {
                    let year = parseInt(value.slice(2, 6), 10);
                    let currentYear = 2026;
                    let currentMonth = 9;

                    if (year < currentYear) {
                        value = value.slice(0, 2) + currentYear;
                    } else if (year === currentYear && month < currentMonth) {
                        value = ('0' + currentMonth).slice(-2) + value.slice(2);
                    }
                }

                e.target.value = value.length > 2 ? value.slice(0, 2) + '/' + value.slice(2) : value;
            } else {
                e.target.value = value;
            }
        });
    }
});
</script>


<?php
require_once __DIR__ . '/../includes/footer.php';
?>