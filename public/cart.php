<?php
// Set page CSS variable for header.php
$page_css = 'cart-style.css';

// Load business logic & Database connection
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/handlers/cart-handler.php';

// Include Global Header
require_once __DIR__ . '/../includes/header.php';
?>

<div class="cart-page-wrapper">
    <div class="cart-container">
        <h1 class="page-title">Shopping Cart</h1>

        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <div class="empty-cart-icon">🛒</div>
                <h2>Your cart is empty</h2>
                <p>You haven't added any products to your cart yet.</p>
                <a href="shop.php" class="shop-btn">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-layout">

                <!-- CART ITEMS -->
                <div class="cart-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <div class="product-image">
                                <img src="<?= getProductImage($item['product_image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
                            </div>

                            <div class="item-details">
                                <h2 class="product-name"><?= htmlspecialchars($item['product_name']) ?></h2>

                                <?php if (!empty($item['generic_name'])): ?>
                                    <p class="generic-name"><?= htmlspecialchars($item['generic_name']) ?></p>
                                <?php endif; ?>

                                <?php if (!empty($item['sku'])): ?>
                                    <p class="sku">SKU: <?= htmlspecialchars($item['sku']) ?></p>
                                <?php endif; ?>

                                <div class="price">
                                    <?php if ($item['discount_percent'] > 0): ?>
                                        <span class="old-price">$<?= number_format($item['unit_price'], 2) ?></span>
                                        <span class="discount"><?= number_format($item['discount_percent'], 0) ?>% OFF</span>
                                    <?php endif; ?>
                                    <strong>$<?= number_format($item['discounted_price'], 2) ?></strong>
                                    <span class="per-item">/ item</span>
                                </div>

                                <?php if (!empty($item['requires_prescription'])): ?>
                                    <div class="prescription-warning">Prescription required</div>
                                <?php endif; ?>

                                <div class="quantity-section">
                                    <button type="button" class="quantity-btn" onclick="changeQuantity(<?= (int)$item['cart_item_id'] ?>, <?= max(1, $item['quantity'] - 1) ?>)">−</button>
                                    <span class="quantity"><?= (int)$item['quantity'] ?></span>
                                    <button type="button" class="quantity-btn" onclick="changeQuantity(<?= (int)$item['cart_item_id'] ?>, <?= (int)$item['quantity'] + 1 ?>)">+</button>
                                </div>

                                <button type="button" class="remove-btn" onclick="removeItem(<?= (int)$item['cart_item_id'] ?>)">Remove</button>
                            </div>

                            <div class="item-total">
                                $<?= number_format($item['item_subtotal'], 2) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- ORDER SUMMARY -->
                <div class="summary">
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

                    <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
                    <a href="shop.php" class="continue-btn">Continue Shopping</a>
                </div>

            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function changeQuantity(cartItemId, quantity) {
    if (quantity < 1) quantity = 1;

    const formData = new URLSearchParams();
    formData.append('cart_item_id', cartItemId);
    formData.append('quantity', quantity);

    fetch('cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Unable to update cart.');
        }
    })
    .catch(error => {
        console.error(error);
        alert('Something went wrong while updating the cart.');
    });
}

function removeItem(cartItemId) {
    const formData = new URLSearchParams();
    formData.append('cart_item_id', cartItemId);
    formData.append('quantity', 0);

    fetch('cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Unable to remove item.');
        }
    })
    .catch(error => {
        console.error(error);
        alert('Something went wrong while removing the item.');
    });
}
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>