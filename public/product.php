<?php
/**
 * MediQuick Pharmacy
 * Product Details Page
 *
 * This page uses the real products/categories/product_batches tables
 * from the MediQuick database.
 */

session_start();

require_once __DIR__ . '/../includes/db.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection is not available.');
}

/* -----------------------------------------------------------
   PRODUCT ID
   Supports:
   product.php?id=1
   product.php?product_id=1
----------------------------------------------------------- */
$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$productId) {
    $productId = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT);
}

$productId = (int) ($productId ?? 0);

if ($productId <= 0) {
    http_response_code(400);
    require_once __DIR__ . '/../includes/header.php';
    ?>
    <div class="container py-5">
        <div class="alert alert-danger">
            <h4 class="alert-heading">Invalid product</h4>
            <p class="mb-3">No valid product ID was provided.</p>
            <a href="shop.php" class="btn btn-primary">
                <i class="fas fa-store me-1"></i> Back to Shop
            </a>
        </div>
    </div>
    <?php
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

/* -----------------------------------------------------------
   ADD TO CART
   Uses the existing cart_id when available.

   The current database schema requires customer_id when a new
   cart is created. Therefore a new cart is created only when
   a logged-in customer_id exists in the session.
----------------------------------------------------------- */
$cartMessage = '';
$cartError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action'])
    && $_POST['action'] === 'add_to_cart'
) {
    $qty = filter_input(INPUT_POST, 'qty', FILTER_VALIDATE_INT);
    $qty = max(1, (int) ($qty ?: 1));

    /* Re-check product and stock before adding */
    $stockCheck = $conn->prepare("
        SELECT
            p.product_id,
            p.product_name,
            p.status,
            COALESCE((
                SELECT SUM(pb.quantity_on_hand)
                FROM product_batches pb
                WHERE pb.product_id = p.product_id
                  AND pb.status = 'active'
                  AND pb.expiry_date >= CURDATE()
            ), 0) AS stock_quantity
        FROM products p
        WHERE p.product_id = ?
        LIMIT 1
    ");

    if (!$stockCheck) {
        $cartError = 'Unable to check product stock.';
    } else {
        $stockCheck->bind_param('i', $productId);
        $stockCheck->execute();
        $stockResult = $stockCheck->get_result();
        $stockProduct = $stockResult->fetch_assoc();
        $stockCheck->close();

        if (!$stockProduct) {
            $cartError = 'Product not found.';
        } elseif ($stockProduct['status'] !== 'active') {
            $cartError = 'This product is not currently available.';
        } elseif ((int) $stockProduct['stock_quantity'] <= 0) {
            $cartError = 'This product is currently out of stock.';
        } else {
            $availableStock = (int) $stockProduct['stock_quantity'];
            $qty = min($qty, $availableStock);

            $cartId = !empty($_SESSION['cart_id'])
                ? (int) $_SESSION['cart_id']
                : 0;

            /* Create a cart if there is no current cart */
            if ($cartId <= 0) {
                $customerId = !empty($_SESSION['customer_id'])
                    ? (int) $_SESSION['customer_id']
                    : 0;

                if ($customerId <= 0) {
                    $cartError = 'Please log in before adding this product to your cart.';
                } else {
                    $createCart = $conn->prepare("
                        INSERT INTO carts (customer_id, created_at, updated_at)
                        VALUES (?, NOW(), NOW())
                    ");

                    if (!$createCart) {
                        $cartError = 'Unable to create your shopping cart.';
                    } else {
                        $createCart->bind_param('i', $customerId);

                        if ($createCart->execute()) {
                            $cartId = (int) $conn->insert_id;
                            $_SESSION['cart_id'] = $cartId;
                        } else {
                            $cartError = 'Unable to create your shopping cart.';
                        }

                        $createCart->close();
                    }
                }
            }

            if ($cartId > 0 && $cartError === '') {
                /* Check whether this product is already in the cart */
                $existing = $conn->prepare("
                    SELECT cart_item_id, quantity
                    FROM cart_items
                    WHERE cart_id = ? AND product_id = ?
                    LIMIT 1
                ");

                if (!$existing) {
                    $cartError = 'Unable to check your cart.';
                } else {
                    $existing->bind_param('ii', $cartId, $productId);
                    $existing->execute();
                    $existingResult = $existing->get_result();
                    $existingItem = $existingResult->fetch_assoc();
                    $existing->close();

                    if ($existingItem) {
                        $newQty = min(
                            $availableStock,
                            (int) $existingItem['quantity'] + $qty
                        );

                        $updateItem = $conn->prepare("
                            UPDATE cart_items
                            SET quantity = ?, updated_at = NOW()
                            WHERE cart_item_id = ?
                        ");

                        if (!$updateItem) {
                            $cartError = 'Unable to update your cart.';
                        } else {
                            $cartItemId = (int) $existingItem['cart_item_id'];
                            $updateItem->bind_param('ii', $newQty, $cartItemId);

                            if ($updateItem->execute()) {
                                $cartMessage = 'Product quantity updated in your cart.';
                            } else {
                                $cartError = 'Unable to update your cart.';
                            }

                            $updateItem->close();
                        }
                    } else {
                        $insertItem = $conn->prepare("
                            INSERT INTO cart_items
                                (cart_id, product_id, quantity, created_at, updated_at)
                            VALUES
                                (?, ?, ?, NOW(), NOW())
                        ");

                        if (!$insertItem) {
                            $cartError = 'Unable to add the product to your cart.';
                        } else {
                            $insertItem->bind_param(
                                'iii',
                                $cartId,
                                $productId,
                                $qty
                            );

                            if ($insertItem->execute()) {
                                $cartMessage = 'Product added to your cart.';
                            } else {
                                $cartError = 'Unable to add the product to your cart.';
                            }

                            $insertItem->close();
                        }
                    }
                }
            }
        }
    }
}

/* -----------------------------------------------------------
   LOAD PRODUCT
   Real product data comes from the database.
   Stock comes from non-expired active product batches.
----------------------------------------------------------- */
$stmt = $conn->prepare("
    SELECT
        p.product_id,
        p.product_name,
        p.generic_name,
        p.description,
        p.sku,
        p.barcode,
        p.category_id,
        p.dosage_form,
        p.strength,
        p.unit_price,
        p.discount_percent,
        p.requires_prescription,
        p.product_image,
        p.status,
        p.created_at,
        p.updated_at,
        c.category_name,
        COALESCE((
            SELECT SUM(pb.quantity_on_hand)
            FROM product_batches pb
            WHERE pb.product_id = p.product_id
              AND pb.status = 'active'
              AND pb.expiry_date >= CURDATE()
        ), 0) AS stock_quantity
    FROM products p
    LEFT JOIN categories c
        ON c.category_id = p.category_id
    WHERE p.product_id = ?
    LIMIT 1
");

if (!$stmt) {
    die('Failed to prepare product query: ' . htmlspecialchars($conn->error));
}

$stmt->bind_param('i', $productId);

if (!$stmt->execute()) {
    $stmt->close();
    die('Failed to load product: ' . htmlspecialchars($conn->error));
}

$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    http_response_code(404);
    require_once __DIR__ . '/../includes/header.php';
    ?>
    <div class="container py-5">
        <div class="alert alert-warning">
            <h4 class="alert-heading">Product not found</h4>
            <p class="mb-3">The product you are looking for does not exist or is no longer available.</p>
            <a href="shop.php" class="btn btn-primary">
                <i class="fas fa-store me-1"></i> Back to Shop
            </a>
        </div>
    </div>
    <?php
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

/* -----------------------------------------------------------
   PRODUCT VALUES
----------------------------------------------------------- */
$productName = (string) ($product['product_name'] ?? '');
$genericName = (string) ($product['generic_name'] ?? '');
$description = (string) ($product['description'] ?? '');
$sku = (string) ($product['sku'] ?? '');
$barcode = (string) ($product['barcode'] ?? '');
$categoryName = (string) ($product['category_name'] ?? '');
$dosageForm = (string) ($product['dosage_form'] ?? '');
$strength = (string) ($product['strength'] ?? '');
$unitPrice = (float) ($product['unit_price'] ?? 0);
$discountPercent = (float) ($product['discount_percent'] ?? 0);
$requiresPrescription = (int) ($product['requires_prescription'] ?? 0);
$stockQuantity = (int) ($product['stock_quantity'] ?? 0);
$status = (string) ($product['status'] ?? '');

$discountedPrice = $unitPrice;

if ($discountPercent > 0) {
    $discountedPrice = $unitPrice - (
        $unitPrice * ($discountPercent / 100)
    );
}

$discountedPrice = max(0, $discountedPrice);

/* Product image is stored in the DB as uploads/products/... */
$productImage = trim((string) ($product['product_image'] ?? ''));

if ($productImage === '') {
    $productImage = 'assets/img/product-placeholder.jpg';
}

/* Keep the image URL relative to /public */
if (
    !preg_match('~^(https?:)?//~i', $productImage)
    && strpos($productImage, '/') !== 0
) {
    $productImage = ltrim($productImage, './');
}

/* -----------------------------------------------------------
   RELATED PRODUCTS
----------------------------------------------------------- */
$relatedProducts = [];

if (!empty($product['category_id'])) {
    $relatedStmt = $conn->prepare("
        SELECT
            p.product_id,
            p.product_name,
            p.generic_name,
            p.unit_price,
            p.discount_percent,
            p.product_image,
            p.requires_prescription,
            COALESCE((
                SELECT SUM(pb.quantity_on_hand)
                FROM product_batches pb
                WHERE pb.product_id = p.product_id
                  AND pb.status = 'active'
                  AND pb.expiry_date >= CURDATE()
            ), 0) AS stock_quantity
        FROM products p
        WHERE p.category_id = ?
          AND p.product_id <> ?
          AND p.status = 'active'
        ORDER BY p.created_at DESC
        LIMIT 4
    ");

    if ($relatedStmt) {
        $categoryId = (int) $product['category_id'];
        $relatedStmt->bind_param('ii', $categoryId, $productId);
        $relatedStmt->execute();

        $relatedResult = $relatedStmt->get_result();

        while ($row = $relatedResult->fetch_assoc()) {
            $relatedProducts[] = $row;
        }

        $relatedStmt->close();
    }
}

/* -----------------------------------------------------------
   PAGE HEADER
----------------------------------------------------------- */
$currentPage = 'product.php';
$pageTitle = $productName . ' - MediQuick Pharmacy';

require_once __DIR__ . '/../includes/header.php';
?>

<style>
    .product-detail-page {
        background: #f5f8fb;
        padding: 45px 0 70px;
    }

    .product-detail-card {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 8px 30px rgba(31, 65, 85, .08);
        overflow: hidden;
    }

    .product-image-box {
        min-height: 500px;
        background: #f8fbfd;
        border: 1px solid #e5eef4;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 35px;
    }

    .product-image-box img {
        width: 100%;
        max-width: 460px;
        height: 460px;
        object-fit: contain;
    }

    .product-category {
        display: inline-block;
        color: #00A3FF;
        background: #EAF8FF;
        border-radius: 50px;
        padding: 6px 13px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 12px;
    }

    .product-title {
        color: #1d3557;
        font-size: 38px;
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 8px;
    }

    .generic-name {
        color: #71818d;
        font-size: 16px;
        margin-bottom: 20px;
    }

    .product-price-row {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .product-price {
        color: #198754;
        font-size: 30px;
        font-weight: 700;
    }

    .product-old-price {
        color: #9aa7af;
        font-size: 17px;
        text-decoration: line-through;
    }

    .discount-badge {
        background: #dc3545;
        color: #fff;
        padding: 5px 9px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 600;
    }

    .product-info-table {
        border-top: 1px solid #e8eef2;
        border-bottom: 1px solid #e8eef2;
        margin: 20px 0;
    }

    .product-info-row {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        padding: 12px 0;
        border-bottom: 1px solid #edf2f5;
    }

    .product-info-row:last-child {
        border-bottom: 0;
    }

    .product-info-label {
        color: #72818b;
        font-weight: 600;
    }

    .product-info-value {
        color: #263d4d;
        text-align: right;
    }

    .stock-available {
        color: #198754;
        font-weight: 700;
    }

    .stock-out {
        color: #dc3545;
        font-weight: 700;
    }

    .prescription-box {
        background: #fff6e8;
        border: 1px solid #f1d6a8;
        color: #946200;
        border-radius: 10px;
        padding: 13px 15px;
        margin-bottom: 20px;
    }

    .description-box {
        margin-top: 30px;
        padding-top: 25px;
        border-top: 1px solid #e8eef2;
    }

    .description-box h4,
    .related-title {
        color: #1d3557;
        font-weight: 700;
    }

    .description-box p {
        color: #6c7b85;
        line-height: 1.8;
        margin-bottom: 0;
    }

    .quantity-control {
        display: flex;
        align-items: center;
        border: 1px solid #d8e2e8;
        border-radius: 8px;
        overflow: hidden;
        width: fit-content;
        background: #fff;
    }

    .quantity-control button {
        width: 42px;
        height: 46px;
        border: 0;
        background: #f6f9fb;
        color: #1d3557;
        font-size: 20px;
        cursor: pointer;
    }

    .quantity-control button:hover {
        background: #eaf8ff;
        color: #00A3FF;
    }

    .quantity-control input {
        width: 55px;
        height: 46px;
        border: 0;
        text-align: center;
        font-weight: 600;
        outline: none;
    }

    .add-cart-btn {
        min-height: 46px;
        border-radius: 8px;
        font-weight: 700;
    }

    .related-section {
        margin-top: 45px;
    }

    .related-card {
        height: 100%;
        background: #fff;
        border: 1px solid #e5edf2;
        border-radius: 14px;
        overflow: hidden;
        transition: .2s ease;
    }

    .related-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(31, 65, 85, .10);
    }

    .related-image {
        height: 210px;
        background: #f8fbfd;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 18px;
    }

    .related-image img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .related-body {
        padding: 18px;
    }

    .related-body h5 {
        color: #1d3557;
        font-weight: 700;
        min-height: 48px;
    }

    .related-price {
        color: #198754;
        font-size: 18px;
        font-weight: 700;
    }

    .related-old-price {
        color: #999;
        font-size: 13px;
        text-decoration: line-through;
        margin-left: 7px;
    }

    @media (max-width: 991px) {
        .product-image-box {
            min-height: 400px;
        }

        .product-image-box img {
            height: 360px;
        }

        .product-title {
            font-size: 32px;
        }
    }

    @media (max-width: 575px) {
        .product-detail-page {
            padding: 25px 0 50px;
        }

        .product-image-box {
            min-height: 300px;
            padding: 20px;
        }

        .product-image-box img {
            height: 270px;
        }

        .product-title {
            font-size: 27px;
        }

        .product-price {
            font-size: 26px;
        }

        .product-info-row {
            align-items: flex-start;
            flex-direction: column;
            gap: 4px;
        }

        .product-info-value {
            text-align: left;
        }
    }
</style>

<div class="product-detail-page">
    <div class="container">

        <!-- Breadcrumb -->
        <div class="mb-4">
            <a href="index.php" class="text-muted text-decoration-none">
                <i class="fas fa-home me-1"></i> Home
            </a>
            <span class="text-muted mx-2">/</span>
            <a href="shop.php" class="text-muted text-decoration-none">
                Shop
            </a>
            <?php if ($categoryName !== ''): ?>
                <span class="text-muted mx-2">/</span>
                <span class="text-muted">
                    <?= htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8') ?>
                </span>
            <?php endif; ?>
            <span class="text-muted mx-2">/</span>
            <span class="text-dark">
                <?= htmlspecialchars($productName, ENT_QUOTES, 'UTF-8') ?>
            </span>
        </div>

        <?php if ($cartMessage !== ''): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?= htmlspecialchars($cartMessage, ENT_QUOTES, 'UTF-8') ?>
                <a href="cart.php" class="alert-link ms-2">View Cart</a>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($cartError !== ''): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= htmlspecialchars($cartError, ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="product-detail-card p-3 p-md-4 p-lg-5">
            <div class="row g-4 g-lg-5">

                <!-- Product Image -->
                <div class="col-lg-6">
                    <div class="product-image-box">
                        <img
                            src="<?= htmlspecialchars($productImage, ENT_QUOTES, 'UTF-8') ?>"
                            alt="<?= htmlspecialchars($productName, ENT_QUOTES, 'UTF-8') ?>"
                            onerror="this.onerror=null;this.src='assets/img/product-placeholder.jpg';"
                        >
                    </div>
                </div>

                <!-- Product Details -->
                <div class="col-lg-6">
                    <?php if ($categoryName !== ''): ?>
                        <span class="product-category">
                            <?= htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    <?php endif; ?>

                    <h1 class="product-title">
                        <?= htmlspecialchars($productName, ENT_QUOTES, 'UTF-8') ?>
                    </h1>

                    <?php if ($genericName !== ''): ?>
                        <div class="generic-name">
                            <?= htmlspecialchars($genericName, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>

                    <div class="product-price-row">
                        <span class="product-price">
                            Rs. <?= number_format($discountedPrice, 2) ?>
                        </span>

                        <?php if ($discountPercent > 0): ?>
                            <span class="product-old-price">
                                Rs. <?= number_format($unitPrice, 2) ?>
                            </span>

                            <span class="discount-badge">
                                -<?= number_format($discountPercent, 0) ?>%
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="product-info-table">
                        <?php if ($strength !== ''): ?>
                            <div class="product-info-row">
                                <span class="product-info-label">Strength</span>
                                <span class="product-info-value">
                                    <?= htmlspecialchars($strength, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <?php if ($dosageForm !== ''): ?>
                            <div class="product-info-row">
                                <span class="product-info-label">Dosage Form</span>
                                <span class="product-info-value">
                                    <?= htmlspecialchars($dosageForm, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <?php if ($sku !== ''): ?>
                            <div class="product-info-row">
                                <span class="product-info-label">SKU</span>
                                <span class="product-info-value">
                                    <?= htmlspecialchars($sku, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <?php if ($barcode !== ''): ?>
                            <div class="product-info-row">
                                <span class="product-info-label">Barcode</span>
                                <span class="product-info-value">
                                    <?= htmlspecialchars($barcode, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <div class="product-info-row">
                            <span class="product-info-label">Availability</span>
                            <span class="product-info-value">
                                <?php if ($stockQuantity > 0 && $status === 'active'): ?>
                                    <span class="stock-available">
                                        <i class="fas fa-check-circle me-1"></i>
                                        <?= number_format($stockQuantity) ?> available
                                    </span>
                                <?php else: ?>
                                    <span class="stock-out">
                                        <i class="fas fa-times-circle me-1"></i>
                                        Out of stock
                                    </span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <?php if ($requiresPrescription): ?>
                        <div class="prescription-box">
                            <i class="fas fa-prescription-bottle-alt me-2"></i>
                            <strong>Prescription required.</strong>
                            Please provide a valid prescription for this medicine when requested by the pharmacy.
                        </div>
                    <?php endif; ?>

                    <?php if ($stockQuantity > 0 && $status === 'active'): ?>
                        <form method="POST" class="d-flex flex-wrap align-items-center gap-3">
                            <input type="hidden" name="action" value="add_to_cart">

                            <div class="quantity-control">
                                <button
                                    type="button"
                                    onclick="changeProductQty(-1)"
                                    aria-label="Decrease quantity"
                                >−</button>

                                <input
                                    type="number"
                                    id="productQty"
                                    name="qty"
                                    value="1"
                                    min="1"
                                    max="<?= (int) $stockQuantity ?>"
                                    aria-label="Quantity"
                                >

                                <button
                                    type="button"
                                    onclick="changeProductQty(1)"
                                    aria-label="Increase quantity"
                                >+</button>
                            </div>

                            <button type="submit" class="btn btn-primary add-cart-btn px-4">
                                <i class="fas fa-shopping-cart me-2"></i>
                                Add to Cart
                            </button>

                            <a href="cart.php" class="btn btn-outline-secondary add-cart-btn px-4">
                                <i class="fas fa-shopping-bag me-2"></i>
                                View Cart
                            </a>
                        </form>
                    <?php else: ?>
                        <button type="button" class="btn btn-secondary px-4" disabled>
                            <i class="fas fa-ban me-2"></i>
                            Out of Stock
                        </button>
                    <?php endif; ?>

                    <?php if ($description !== ''): ?>
                        <div class="description-box">
                            <h4 class="mb-3">Product Description</h4>
                            <p>
                                <?= nl2br(htmlspecialchars($description, ENT_QUOTES, 'UTF-8')) ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($relatedProducts)): ?>
            <div class="related-section">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="related-title mb-1">Related Products</h3>
                        <p class="text-muted mb-0">
                            More products from the same category
                        </p>
                    </div>

                    <a href="shop.php" class="btn btn-outline-primary">
                        View All
                    </a>
                </div>

                <div class="row g-4">
                    <?php foreach ($relatedProducts as $related): ?>
                        <?php
                        $relatedId = (int) ($related['product_id'] ?? 0);
                        $relatedName = (string) ($related['product_name'] ?? '');
                        $relatedImage = trim((string) ($related['product_image'] ?? ''));

                        if ($relatedImage === '') {
                            $relatedImage = 'assets/img/product-placeholder.jpg';
                        }

                        $relatedPrice = (float) ($related['unit_price'] ?? 0);
                        $relatedDiscount = (float) ($related['discount_percent'] ?? 0);
                        $relatedFinalPrice = $relatedPrice;

                        if ($relatedDiscount > 0) {
                            $relatedFinalPrice = $relatedPrice - (
                                $relatedPrice * ($relatedDiscount / 100)
                            );
                        }

                        $relatedFinalPrice = max(0, $relatedFinalPrice);
                        ?>

                        <div class="col-sm-6 col-lg-3">
                            <div class="related-card">
                                <a href="product.php?id=<?= $relatedId ?>">
                                    <div class="related-image">
                                        <img
                                            src="<?= htmlspecialchars($relatedImage, ENT_QUOTES, 'UTF-8') ?>"
                                            alt="<?= htmlspecialchars($relatedName, ENT_QUOTES, 'UTF-8') ?>"
                                            onerror="this.onerror=null;this.src='assets/img/product-placeholder.jpg';"
                                        >
                                    </div>
                                </a>

                                <div class="related-body">
                                    <h5 class="mb-2">
                                        <?= htmlspecialchars($relatedName, ENT_QUOTES, 'UTF-8') ?>
                                    </h5>

                                    <div class="mb-3">
                                        <span class="related-price">
                                            Rs. <?= number_format($relatedFinalPrice, 2) ?>
                                        </span>

                                        <?php if ($relatedDiscount > 0): ?>
                                            <span class="related-old-price">
                                                Rs. <?= number_format($relatedPrice, 2) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <a
                                        href="product.php?id=<?= $relatedId ?>"
                                        class="btn btn-outline-primary w-100"
                                    >
                                        <i class="fas fa-eye me-1"></i>
                                        View Product
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
function changeProductQty(delta) {
    const input = document.getElementById('productQty');

    if (!input) {
        return;
    }

    const min = parseInt(input.min || '1', 10);
    const max = parseInt(input.max || '999999', 10);
    let value = parseInt(input.value || '1', 10);

    if (isNaN(value)) {
        value = min;
    }

    value += delta;
    value = Math.max(min, Math.min(max, value));

    input.value = value;
}
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
