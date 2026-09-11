<?php
require_once('../includes/db.php');
require_once('../includes/header.php');

/*
 * Products are loaded directly from the products table.
 * Only the fields needed by this homepage are selected.
 */
$sql = "SELECT
            p.product_id,
            p.product_name,
            p.unit_price,
            p.discount_percent,
            p.product_image,
            c.category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        WHERE p.status = 'active'
        ORDER BY p.product_id DESC";

$result = $conn->query($sql);

if (!$result) {
    die("Product query failed: " . htmlspecialchars($conn->error));
}

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

function productImage($product)
{
    $image = trim((string)($product['product_image'] ?? ''));
    return $image !== '' ? $image : 'assets/img/product-3.png';
}
?>

<!-- Link to your separate stylesheet -->
<link rel="stylesheet" href="assets/css/index-style.css">
<style>
    .product-card {
        cursor: pointer;
        transition: .2s ease;
    }

    .product-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(31, 65, 85, .12) !important;
    }

    .product-category {
        font-size: 14px;
        font-weight: 500;
    }

    .discount-badge {
        display: inline-block;
        margin: 4px auto 0;
        padding: 5px 12px;
        border-radius: 20px;
        background: #fff3e0;
        color: #f79400;
        font-size: 12px;
        font-weight: 700;
        width: fit-content;
    }

    .product-price .text-muted {
        font-size: 16px;
        font-weight: 400;
    }
</style>

<!-- Hero -->
<div class="container-fluid home-hero px-0">
    <div class="container py-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-6 order-2 order-lg-1">
                <div class="hero-content">
                    <h4 class="text-uppercase fw-bold mb-3">Trusted Pharmacy Care</h4>
                    <h1 class="display-3 mb-4">For Your Everyday Health</h1>
                    <p class="text-muted mb-4">
                        Safe, reliable and convenient pharmacy service.
                    </p>
                    <a href="#products" class="btn btn-primary rounded-pill py-3 px-5">
                        Shop Now
                    </a>
                </div>
            </div>

            <div class="col-lg-6 order-1 order-lg-2 text-center">
                <img src="assets/img/carousel-1.png"
                     class="img-fluid w-100 hero-image"
                     alt="Pharmacy">
            </div>
        </div>
    </div>
</div>

<!-- Services -->
<div class="container-fluid px-0">
    <div class="row g-0">
        <div class="col-6 col-md-4 col-lg-2">
            <div class="service-box p-4">
                <i class="fa fa-sync-alt fa-2x text-primary"></i>
                <h6 class="text-uppercase mt-3 mb-1">Easy Returns</h6>
                <p class="mb-0 small text-muted">Simple and convenient service</p>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-2">
            <div class="service-box p-4">
                <i class="fas fa-truck fa-2x text-primary"></i>
                <h6 class="text-uppercase mt-3 mb-1">Fast Delivery</h6>
                <p class="mb-0 small text-muted">Fast delivery on orders</p>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-2">
            <div class="service-box p-4">
                <i class="fas fa-life-ring fa-2x text-primary"></i>
                <h6 class="text-uppercase mt-3 mb-1">Support</h6>
                <p class="mb-0 small text-muted">Helpful pharmacy support</p>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-2">
            <div class="service-box p-4">
                <i class="fas fa-lock fa-2x text-primary"></i>
                <h6 class="text-uppercase mt-3 mb-1">Secure Payment</h6>
                <p class="mb-0 small text-muted">Safe and secure checkout</p>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-2">
            <div class="service-box p-4">
                <i class="fas fa-pills fa-2x text-primary"></i>
                <h6 class="text-uppercase mt-3 mb-1">Quality Medicines</h6>
                <p class="mb-0 small text-muted">Trusted and quality products</p>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-2">
            <div class="service-box p-4">
                <i class="fas fa-clock fa-2x text-primary"></i>
                <h6 class="text-uppercase mt-3 mb-1">Quick Service</h6>
                <p class="mb-0 small text-muted">Fast and reliable pharmacy service</p>
            </div>
        </div>
    </div>
</div>

<!-- Products -->
<div id="products" class="container-fluid product py-5">
    <div class="container py-4">
        <div class="text-center mb-4">
            <h4 class="text-primary border-bottom border-primary border-2 d-inline-block p-2">
                Products
            </h4>
            <h1 class="display-4 mb-3">Our Products</h1>

            <!-- Product Navigation -->
            <div class="d-flex justify-content-center gap-2 flex-wrap mt-3">
                <a href="#all-products" class="btn btn-primary rounded-pill px-4">
                    <i class="fas fa-th-large me-2"></i>All Products
                </a>
                <a href="#new-arrivals" class="btn btn-outline-primary rounded-pill px-4">
                    <i class="fas fa-star me-2"></i>New Arrivals
                </a>
            </div>
        </div>

        <?php
            // Products are already ordered by product_id DESC, so the latest
            // products are used as New Arrivals.
            $newArrivals = array_slice($products, 0, 8);

            function renderProductCard($product)
            {
                $originalPrice = (float)$product['unit_price'];
                $discountPercent = (float)($product['discount_percent'] ?? 0);
                $discountedPrice = $originalPrice - ($originalPrice * $discountPercent / 100);
                ?>
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                    <div class="product-card border rounded-4 overflow-hidden bg-white shadow-sm h-100 d-flex flex-column"
                         onclick="window.location.href='product.php?id=<?= (int)$product['product_id'] ?>'"
                         role="link"
                         tabindex="0"
                         onkeydown="if(event.key==='Enter' || event.key===' '){ event.preventDefault(); window.location.href='product.php?id=<?= (int)$product['product_id'] ?>'; }">
                        <div class="product-image p-3 bg-light d-flex align-items-center justify-content-center" style="height: 230px;">
                            <img src="<?= htmlspecialchars(productImage($product)) ?>"
                                 class="img-fluid h-100 object-fit-contain"
                                 alt="<?= htmlspecialchars($product['product_name']) ?>">
                        </div>

                        <div class="product-info p-3 text-center d-flex flex-column justify-content-between flex-grow-1">
                            <div class="product-category text-primary mb-2">
                                <?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?>
                            </div>

                            <div class="product-name fw-bold text-dark fs-5 mb-3">
                                <?= htmlspecialchars($product['product_name']) ?>
                            </div>

                            <div class="product-price mb-2">
                                <?php if ($discountPercent > 0): ?>
                                    <span class="text-muted text-decoration-line-through me-2">
                                        $<?= number_format($originalPrice, 2) ?>
                                    </span>
                                    <span class="text-primary fw-bold fs-5">
                                        $<?= number_format($discountedPrice, 2) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-primary fw-bold fs-5">
                                        $<?= number_format($originalPrice, 2) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <?php if ($discountPercent > 0): ?>
                                <div class="discount-badge">
                                    <?= number_format($discountPercent, 0) ?>% OFF
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="product-action p-3 pt-0 text-center">
                            <a href="cart.php?product_id=<?= (int)$product['product_id'] ?>"
                               class="btn btn-primary rounded-pill py-2 w-100"
                               onclick="event.stopPropagation();">
                                <i class="fas fa-shopping-cart me-2"></i>
                                Add To Cart
                            </a>
                        </div>
                    </div>
                </div>
                <?php
            }
        ?>

        <!-- New Arrivals -->
        <div id="new-arrivals" class="mb-5 pt-3">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h2 class="fw-bold mb-1">New Arrivals</h2>
                    <p class="text-muted mb-0">Check out our latest products</p>
                </div>
                <span class="badge bg-primary rounded-pill px-3 py-2">
                    Latest 8
                </span>
            </div>

            <?php if (!empty($newArrivals)): ?>
                <div class="row g-4">
                    <?php foreach ($newArrivals as $product): ?>
                        <?php renderProductCard($product); ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <p class="text-muted mb-0">No new arrivals found.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- All Products -->
        <div id="all-products" class="pt-3">
            <div class="text-center mb-4">
                <h2 class="fw-bold mb-1">All Products</h2>
                <p class="text-muted mb-0">Browse all available pharmacy products</p>
            </div>

            <?php if (!empty($products)): ?>
                <div class="row g-4">
                    <?php foreach ($products as $product): ?>
                        <?php renderProductCard($product); ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <p class="text-muted mb-0">No active products found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
</body>
</html>