<?php
session_start();

require_once __DIR__ . '/../includes/db.php';


if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Database connection is not available.");
}


if (empty($_SESSION['cart_id'])) {

    // Guest cart - customer_id intentionally omitted
    $stmt = $conn->prepare("
        INSERT INTO carts (created_at, updated_at)
        VALUES (NOW(), NOW())
    ");

    if (!$stmt) {
        die("Failed to create cart: " . $conn->error);
    }

    if (!$stmt->execute()) {
        die("Failed to create cart: " . $stmt->error);
    }

    $_SESSION['cart_id'] = $conn->insert_id;

    $stmt->close();
}

$cartId = (int) $_SESSION['cart_id'];


$stmt = $conn->prepare("
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
");

if (!$stmt) {
    die("Failed to load cart: " . $conn->error);
}

$stmt->bind_param("i", $cartId);

if (!$stmt->execute()) {
    die("Failed to load cart items: " . $stmt->error);
}

$result = $stmt->get_result();

$cartItems = [];
$subtotal = 0;

while ($row = $result->fetch_assoc()) {

    $unitPrice = (float) $row['unit_price'];
    $discountPercent = (float) $row['discount_percent'];
    $quantity = (int) $row['quantity'];

  
    if ($discountPercent > 0) {

        $discountedPrice =
            $unitPrice - ($unitPrice * ($discountPercent / 100));

    } else {

        $discountedPrice = $unitPrice;

    }

    $discountedPrice = max(0, $discountedPrice);

 
    $itemSubtotal = $discountedPrice * $quantity;

    $subtotal += $itemSubtotal;

    $row['discounted_price'] = $discountedPrice;
    $row['item_subtotal'] = $itemSubtotal;

    $cartItems[] = $row;
}

$stmt->close();

/*
|--------------------------------------------------------------------------
| Shipping
|--------------------------------------------------------------------------
*/
$shipping = count($cartItems) > 0 ? 3.00 : 0.00;

/*
|--------------------------------------------------------------------------
| Final total
|--------------------------------------------------------------------------
*/
$total = $subtotal + $shipping;

/*
|--------------------------------------------------------------------------
| Helper function for product image
|--------------------------------------------------------------------------
*/
function getProductImage($image)
{
    if (!empty($image)) {
        return 'assets/images/' . htmlspecialchars($image);
    }

    return 'assets/images/no-image.png';
}

/*
|--------------------------------------------------------------------------
| Include Header
|--------------------------------------------------------------------------
*/
require_once __DIR__ . '/../includes/header.php';
?>

<!-- =========================================================
     CART PAGE
========================================================= -->

<style>

    /*
    |--------------------------------------------------------------------------
    | Cart Page Styles
    |--------------------------------------------------------------------------
    */

    .cart-page-wrapper {
        width: 100%;
        padding: 50px 0;
        background: #f5f7fa;
        min-height: 500px;
    }

    .cart-container {
        width: 90%;
        max-width: 1200px;
        margin: 0 auto;
    }

    .page-title {
        font-size: 32px;
        margin: 0 0 30px;
        color: #1d3557;
        font-weight: 700;
    }

    /*
    |--------------------------------------------------------------------------
    | Cart Layout
    |--------------------------------------------------------------------------
    */

    .cart-layout {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 25px;
        align-items: start;
    }

    /*
    |--------------------------------------------------------------------------
    | Cart Items
    |--------------------------------------------------------------------------
    */

    .cart-items {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
    }

    .cart-item {
        display: flex;
        gap: 20px;
        padding: 20px 0;
        border-bottom: 1px solid #eee;
        align-items: center;
    }

    .cart-item:last-child {
        border-bottom: none;
    }

    /*
    |--------------------------------------------------------------------------
    | Product Image
    |--------------------------------------------------------------------------
    */

    .product-image {
        width: 110px;
        height: 110px;
        object-fit: contain;
        border: 1px solid #eee;
        border-radius: 10px;
        background: #fff;
        padding: 8px;
        flex-shrink: 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Product Details
    |--------------------------------------------------------------------------
    */

    .item-details {
        flex: 1;
        min-width: 0;
    }

    .product-name {
        margin: 0 0 6px;
        font-size: 20px;
        color: #1d3557;
        font-weight: 600;
    }

    .generic-name {
        color: #777;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .sku {
        color: #999;
        font-size: 13px;
        margin-bottom: 10px;
    }

    /*
    |--------------------------------------------------------------------------
    | Price
    |--------------------------------------------------------------------------
    */

    .price {
        font-size: 18px;
        font-weight: bold;
        color: #198754;
    }

    .old-price {
        color: #999;
        text-decoration: line-through;
        font-size: 14px;
        margin-left: 8px;
    }

    .discount {
        display: inline-block;
        margin-left: 8px;
        background: #dc3545;
        color: #fff;
        padding: 3px 7px;
        border-radius: 4px;
        font-size: 12px;
    }

    /*
    |--------------------------------------------------------------------------
    | Prescription Warning
    |--------------------------------------------------------------------------
    */

    .prescription-warning {
        color: #dc3545;
        font-size: 13px;
        margin-top: 8px;
        font-weight: 500;
    }

    /*
    |--------------------------------------------------------------------------
    | Quantity
    |--------------------------------------------------------------------------
    */

    .quantity-section {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 15px;
        flex-wrap: wrap;
    }

    .quantity-btn {
        width: 32px;
        height: 32px;
        border: 1px solid #ccc;
        background: #fff;
        border-radius: 5px;
        cursor: pointer;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: 0.2s;
    }

    .quantity-btn:hover {
        background: #f0f0f0;
    }

    .quantity {
        min-width: 35px;
        text-align: center;
        font-weight: bold;
    }

    /*
    |--------------------------------------------------------------------------
    | Remove Button
    |--------------------------------------------------------------------------
    */

    .remove-btn {
        border: none;
        background: #dc3545;
        color: #fff;
        padding: 8px 12px;
        border-radius: 5px;
        cursor: pointer;
        margin-left: 10px;
        transition: 0.2s;
    }

    .remove-btn:hover {
        background: #bb2d3b;
    }

    /*
    |--------------------------------------------------------------------------
    | Item Total
    |--------------------------------------------------------------------------
    */

    .item-total {
        min-width: 120px;
        text-align: right;
        font-size: 18px;
        font-weight: bold;
        color: #1d3557;
    }

    /*
    |--------------------------------------------------------------------------
    | Order Summary
    |--------------------------------------------------------------------------
    */

    .summary {
        background: #fff;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
        position: sticky;
        top: 20px;
    }

    .summary h2 {
        margin-top: 0;
        margin-bottom: 25px;
        color: #1d3557;
        font-size: 24px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin: 15px 0;
        color: #555;
    }

    .summary-total {
        border-top: 1px solid #ddd;
        padding-top: 18px;
        font-size: 22px;
        font-weight: bold;
        color: #1d3557;
    }

    /*
    |--------------------------------------------------------------------------
    | Checkout Button
    |--------------------------------------------------------------------------
    */

    .checkout-btn {
        display: block;
        width: 100%;
        padding: 14px;
        background: #198754;
        color: #fff;
        text-align: center;
        text-decoration: none;
        border-radius: 7px;
        margin-top: 20px;
        font-weight: bold;
        border: none;
        cursor: pointer;
        font-size: 16px;
        transition: 0.2s;
    }

    .checkout-btn:hover {
        background: #157347;
        color: #fff;
    }

    /*
    |--------------------------------------------------------------------------
    | Continue Shopping
    |--------------------------------------------------------------------------
    */

    .continue-btn {
        display: block;
        width: 100%;
        padding: 12px;
        background: #fff;
        color: #1d3557;
        text-align: center;
        text-decoration: none;
        border: 1px solid #1d3557;
        border-radius: 7px;
        margin-top: 10px;
        transition: 0.2s;
    }

    .continue-btn:hover {
        background: #f0f4f8;
        color: #1d3557;
    }

    /*
    |--------------------------------------------------------------------------
    | Empty Cart
    |--------------------------------------------------------------------------
    */

    .empty-cart {
        background: #fff;
        border-radius: 12px;
        padding: 60px 20px;
        text-align: center;
        box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
    }

    .empty-cart h2 {
        color: #1d3557;
        margin-bottom: 10px;
    }

    .empty-cart p {
        color: #777;
        margin-bottom: 25px;
    }

    .shop-btn {
        display: inline-block;
        padding: 12px 25px;
        background: #198754;
        color: #fff;
        text-decoration: none;
        border-radius: 7px;
        transition: 0.2s;
    }

    .shop-btn:hover {
        background: #157347;
        color: #fff;
    }

    /*
    |--------------------------------------------------------------------------
    | Mobile Responsive
    |--------------------------------------------------------------------------
    */

    @media (max-width: 800px) {

        .cart-page-wrapper {
            padding: 30px 0;
        }

        .cart-container {
            width: 94%;
        }

        .cart-layout {
            grid-template-columns: 1fr;
        }

        .cart-item {
            flex-wrap: wrap;
            align-items: flex-start;
        }

        .product-image {
            width: 90px;
            height: 90px;
        }

        .item-details {
            width: calc(100% - 110px);
        }

        .item-total {
            width: 100%;
            text-align: left;
            padding-left: 0;
            margin-top: 5px;
        }

        .summary {
            position: static;
        }

    }

    @media (max-width: 500px) {

        .page-title {
            font-size: 26px;
        }

        .cart-items {
            padding: 15px;
        }

        .cart-item {
            gap: 12px;
        }

        .product-image {
            width: 75px;
            height: 75px;
        }

        .item-details {
            width: calc(100% - 87px);
        }

        .product-name {
            font-size: 17px;
        }

        .remove-btn {
            margin-left: 5px;
        }

    }

</style>


<div class="cart-page-wrapper">

    <div class="cart-container">

        <h1 class="page-title">
            Your Shopping Cart
        </h1>


        <?php if (empty($cartItems)): ?>

            <!-- =====================================================
                 EMPTY CART
            ====================================================== -->

            <div class="empty-cart">

                <h2>
                    Your cart is empty
                </h2>

                <p>
                    You haven't added any products to your cart yet.
                </p>

                <a
                    href="products.php"
                    class="shop-btn"
                >
                    Continue Shopping
                </a>

            </div>


        <?php else: ?>

            <!-- =====================================================
                 CART + SUMMARY
            ====================================================== -->

            <div class="cart-layout">


                <!-- =================================================
                     CART ITEMS
                ================================================== -->

                <div class="cart-items">

                    <?php foreach ($cartItems as $item): ?>

                        <div
                            class="cart-item"
                            data-cart-item-id="<?= (int) $item['cart_item_id'] ?>"
                        >

                            <!-- Product Image -->

                            <img
                                class="product-image"
                                src="<?= getProductImage($item['product_image']) ?>"
                                alt="<?= htmlspecialchars($item['product_name']) ?>"
                                onerror="this.src='assets/images/no-image.png'"
                            >


                            <!-- Product Details -->

                            <div class="item-details">

                                <h3 class="product-name">
                                    <?= htmlspecialchars($item['product_name']) ?>
                                </h3>


                                <?php if (!empty($item['generic_name'])): ?>

                                    <div class="generic-name">
                                        <?= htmlspecialchars($item['generic_name']) ?>
                                    </div>

                                <?php endif; ?>


                                <?php if (!empty($item['sku'])): ?>

                                    <div class="sku">
                                        SKU:
                                        <?= htmlspecialchars($item['sku']) ?>
                                    </div>

                                <?php endif; ?>


                                <!-- Price -->

                                <div class="price">

                                    $<?= number_format(
                                        $item['discounted_price'],
                                        2
                                    ) ?>


                                    <?php if ((float) $item['discount_percent'] > 0): ?>

                                        <span class="old-price">
                                            $<?= number_format(
                                                $item['unit_price'],
                                                2
                                            ) ?>
                                        </span>


                                        <span class="discount">
                                            -<?= number_format(
                                                $item['discount_percent'],
                                                0
                                            ) ?>%
                                        </span>

                                    <?php endif; ?>

                                </div>


                                <!-- Prescription -->

                                <?php if (!empty($item['requires_prescription'])): ?>

                                    <div class="prescription-warning">
                                        Prescription required
                                    </div>

                                <?php endif; ?>


                                <!-- Quantity -->

                                <div class="quantity-section">

                                    <!-- Decrease -->

                                    <button
                                        type="button"
                                        class="quantity-btn"
                                        onclick="changeQuantity(
                                            <?= (int) $item['cart_item_id'] ?>,
                                            <?= max(0, (int) $item['quantity'] - 1) ?>
                                        )"
                                    >
                                        −
                                    </button>


                                    <!-- Current Quantity -->

                                    <span class="quantity">
                                        <?= (int) $item['quantity'] ?>
                                    </span>


                                    <!-- Increase -->

                                    <button
                                        type="button"
                                        class="quantity-btn"
                                        onclick="changeQuantity(
                                            <?= (int) $item['cart_item_id'] ?>,
                                            <?= (int) $item['quantity'] + 1 ?>
                                        )"
                                    >
                                        +
                                    </button>


                                    <!-- Remove -->

                                    <button
                                        type="button"
                                        class="remove-btn"
                                        onclick="changeQuantity(
                                            <?= (int) $item['cart_item_id'] ?>,
                                            0
                                        )"
                                    >
                                        Remove
                                    </button>

                                </div>

                            </div>


                            <!-- Item Total -->

                            <div class="item-total">

                                $<?= number_format(
                                    $item['item_subtotal'],
                                    2
                                ) ?>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>


                <!-- =================================================
                     ORDER SUMMARY
                ================================================== -->

                <div class="summary">

                    <h2>
                        Order Summary
                    </h2>


                    <!-- Subtotal -->

                    <div class="summary-row">

                        <span>
                            Subtotal
                        </span>

                        <span>
                            $<?= number_format($subtotal, 2) ?>
                        </span>

                    </div>


                    <!-- Shipping -->

                    <div class="summary-row">

                        <span>
                            Shipping
                        </span>

                        <span>
                            $<?= number_format($shipping, 2) ?>
                        </span>

                    </div>


                    <!-- Total -->

                    <div class="summary-row summary-total">

                        <span>
                            Total
                        </span>

                        <span>
                            $<?= number_format($total, 2) ?>
                        </span>

                    </div>


                    <!-- Checkout -->

                    <a
                        href="checkout.php"
                        class="checkout-btn"
                    >
                        Proceed to Checkout
                    </a>


                    <!-- Continue Shopping -->

                    <a
                        href="products.php"
                        class="continue-btn"
                    >
                        Continue Shopping
                    </a>

                </div>

            </div>

        <?php endif; ?>

    </div>

</div>


<script>

/*
|--------------------------------------------------------------------------
| Change Cart Quantity
|--------------------------------------------------------------------------
*/
function changeQuantity(cartItemId, quantity) {

    fetch("update_cart.php", {

        method: "POST",

        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },

        body:
            "cart_item_id=" +
            encodeURIComponent(cartItemId) +
            "&quantity=" +
            encodeURIComponent(quantity)

    })

    .then(response => {

        if (!response.ok) {
            throw new Error("HTTP error " + response.status);
        }

        return response.json();

    })

    .then(data => {

        if (data.success) {

            location.reload();

        } else {

            alert(
                data.message ||
                "Unable to update cart."
            );

        }

    })

    .catch(error => {

        console.error("Cart update error:", error);

        alert(
            "Something went wrong while updating the cart."
        );

    });

}

</script>


<?php
/*
|--------------------------------------------------------------------------
| Include Footer
|--------------------------------------------------------------------------
*/
require_once __DIR__ . '/../includes/footer.php';
?>

</body>

</html>