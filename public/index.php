<?php
require_once('../includes/db.php');
require_once('../includes/header.php');

/*
 * Products are loaded directly from the products table.
 * Only the fields needed by this homepage are selected.
 */
$sql = "SELECT product_id, product_name, unit_price, product_image
        FROM products
        WHERE status = 'active'
        ORDER BY product_id DESC";

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
        <div class="col-6 col-md-4 col-lg-3">
            <div class="service-box p-4">
                <i class="fa fa-sync-alt fa-2x text-primary"></i>
                <h6 class="text-uppercase mt-3 mb-1">Easy Returns</h6>
                <p class="mb-0 small text-muted">Simple and convenient service</p>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-3">
            <div class="service-box p-4">
                <i class="fas fa-truck fa-2x text-primary"></i>
                <h6 class="text-uppercase mt-3 mb-1">Fast Delivery</h6>
                <p class="mb-0 small text-muted">Fast delivery on orders</p>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-3">
            <div class="service-box p-4">
                <i class="fas fa-life-ring fa-2x text-primary"></i>
                <h6 class="text-uppercase mt-3 mb-1">Support</h6>
                <p class="mb-0 small text-muted">Helpful pharmacy support</p>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-3">
            <div class="service-box p-4">
                <i class="fas fa-lock fa-2x text-primary"></i>
                <h6 class="text-uppercase mt-3 mb-1">Secure Payment</h6>
                <p class="mb-0 small text-muted">Safe and secure checkout</p>
            </div>
        </div>
    </div>
</div>

<!-- Products -->
<div id="products" class="container-fluid product py-5">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h4 class="text-primary border-bottom border-primary border-2 d-inline-block p-2">
                Products
            </h4>
            <h1 class="display-4 mb-0">Our Products</h1>
        </div>

        <?php if (!empty($products)): ?>
            <div class="row g-4">
                <?php foreach ($products as $product): ?>
                    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                        <div class="product-card border rounded-4 overflow-hidden bg-white shadow-sm h-100 d-flex flex-column">
                            <div class="product-image p-3 bg-light d-flex align-items-center justify-content-center" style="height: 230px;">
                                <img src="<?= htmlspecialchars(productImage($product)) ?>"
                                     class="img-fluid h-100 object-fit-contain"
                                     alt="<?= htmlspecialchars($product['product_name']) ?>">
                            </div>

                            <div class="product-info p-3 text-center d-flex flex-column justify-content-between flex-grow-1">
                                <div class="product-name fw-bold text-dark fs-6 mb-2">
                                    <?= htmlspecialchars($product['product_name']) ?>
                                </div>

                                <div class="product-price text-primary fw-bold fs-5">
                                    $<?= number_format((float)$product['unit_price'], 2) ?>
                                </div>
                            </div>

                            <div class="product-action p-3 pt-0 text-center">
                                <a href="cart.php?product_id=<?= (int)$product['product_id'] ?>"
                                   class="btn btn-primary rounded-pill py-2 w-100">
                                    <i class="fas fa-shopping-cart me-2"></i>
                                    Add To Cart
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <p class="text-muted mb-0">No active products found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
</body>
</html>