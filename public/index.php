<?php
require_once('../includes/db.php');
require_once __DIR__ . '/../includes/header.php';

$sql = "SELECT
            p.product_id,
            p.product_name,
            p.unit_price,
            c.category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        WHERE p.status = 'active'
        ORDER BY p.product_id DESC";

$result = $conn->query($sql);

if (!$result) {
    die("Product query failed: " . $conn->error);
}

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

function productPriceData($product)
{
    $price = (float)$product['unit_price'];
    // The current database stores the product price in unit_price.
    // No discount_percent column is used.
    $discount = 0;
    $salePrice = $price;

    return [
        'price' => $price,
        'discount' => $discount,
        'sale_price' => $salePrice
    ];
}
?>


<style>
/* =========================================================
   MEDIQUICK HOMEPAGE REDESIGN
   Main colour: #00A3FF
   ========================================================= */
:root{
    --mq-primary:#00A3FF;
    --mq-primary-dark:#008DFF;
    --mq-deep:#006AFF;
    --mq-soft:#F3FAFF;
    --mq-line:#DCEFF8;
    --mq-text:#294052;

    /* Override the theme's own Bootstrap colour variables so every
       element that reads them (owl-carousel arrows, buttons, badges,
       etc.) switches over too, not just the ones targeted below. */
    --bs-primary:#00C391;
    --bs-primary-rgb:0,195,145;
}

/* Hero area */
.container-fluid.carousel{
    background:linear-gradient(135deg,#F4FBFF 0%,#fff 62%)!important;
}
.header-carousel-item{
    min-height:430px;
}
.carousel-content{
    padding:45px!important;
}
.carousel-content h4{
    color:var(--mq-primary)!important;
    letter-spacing:2px!important;
    font-size:15px!important;
}
.carousel-content h1{
    color:#19384A!important;
    font-weight:800!important;
    line-height:1.12;
}
.carousel-content p{
    color:#657887!important;
}
.carousel-content .btn-primary{
    padding:13px 30px!important;
    border-radius:50px!important;
}

/* Right hero banner */
.carousel-header-banner{
    border-radius:22px!important;
    overflow:hidden;
    box-shadow:0 16px 40px rgba(14,76,105,.16);
}
.carousel-header-banner img{
    filter:saturate(.95);
}
.carousel-banner-offer{
    background:rgba(255,255,255,.94)!important;
    padding:14px 18px!important;
}
.carousel-banner-offer .bg-primary{
    background:var(--mq-primary)!important;
}
.carousel-banner-offer .text-primary{
    color:var(--mq-primary)!important;
}
.carousel-banner{
    background:linear-gradient(180deg,rgba(4,34,52,.50),rgba(4,34,52,.88))!important;
}
.carousel-banner a:first-child{
    color:#8DDCFF!important;
}
.carousel-banner .text-primary{
    color:#7DD5FF!important;
}

/* Services */
.container-fluid.px-0{
    background:#fff;
}
.container-fluid.px-0 .p-4{
    min-height:115px;
    transition:all .25s ease;
}
.container-fluid.px-0 .p-4:hover{
    background:var(--mq-soft);
    transform:translateY(-3px);
}
.container-fluid.px-0 i.text-primary{
    color:var(--mq-primary)!important;
}

/* Offer cards */
.container-fluid.bg-light{
    background:var(--mq-soft)!important;
}
.container-fluid.bg-light .border.bg-white{
    border:1px solid var(--mq-line)!important;
    border-radius:18px!important;
    box-shadow:0 8px 25px rgba(16,83,113,.06);
    transition:all .25s ease;
}
.container-fluid.bg-light .border.bg-white:hover{
    transform:translateY(-6px);
    box-shadow:0 15px 35px rgba(0,163,255,.13);
    border-color:#B9E6FA!important;
}
.container-fluid.bg-light h3.text-primary{
    color:var(--mq-primary)!important;
}

/* Product section */
.product{
    background:#fff!important;
}
.product h1{
    color:#19384A;
    font-weight:800;
}
.product .nav-pills a{
    border:1px solid var(--mq-line)!important;
    background:#fff!important;
    transition:all .22s ease;
}
.product .nav-pills a:hover{
    background:var(--mq-soft)!important;
    border-color:#B9E6FA!important;
}
.product .nav-pills a.active{
    background:var(--mq-primary)!important;
    border-color:var(--mq-primary)!important;
    box-shadow:0 7px 18px rgba(0,163,255,.18);
}
.product .nav-pills a.active span{
    color:#fff!important;
}

/* Product cards */
.product-item{
    transition:transform .25s ease;
}
.product-item:hover{
    transform:translateY(-7px);
}
.product-item-inner{
    border:1px solid var(--mq-line)!important;
    border-radius:18px!important;
    overflow:hidden;
    box-shadow:0 6px 20px rgba(16,83,113,.06);
    transition:box-shadow .25s ease;
}
.product-item:hover .product-item-inner{
    box-shadow:0 15px 35px rgba(0,163,255,.13);
}
.product-item-inner-item{
    background:var(--mq-soft)!important;
    position:relative;
}
.product-item-inner-item img{
    transition:transform .3s ease;
}
.product-item:hover .product-item-inner-item img{
    transform:scale(1.035);
}
.product-item .text-center{
    background:#fff;
}
.product-item .text-center > a:first-child{
    color:#78909C!important;
    font-size:13px;
    font-weight:600;
    text-transform:uppercase;
    letter-spacing:.5px;
}
.product-item .text-center > a.h4{
    color:#294052!important;
    font-weight:700;
}
.product-item .text-center > a.h4:hover{
    color:var(--mq-primary)!important;
}
.product-item .text-primary{
    color:var(--mq-primary)!important;
}
.product-item-add{
    background:#fff!important;
    border-color:var(--mq-line)!important;
}
.product-item-add .btn-primary{
    border:0!important;
}
.product-item-add .btn-sm-square{
    border-color:var(--mq-line)!important;
}
.product-item-add .text-primary{
    color:var(--mq-primary)!important;
}
.product-sale,.product-new{
    background:var(--mq-primary)!important;
    border-radius:8px!important;
    padding:6px 10px!important;
}

/* Banners */
.container-fluid.py-5 .bg-primary{
    background:var(--mq-primary)!important;
}
.container-fluid.py-5 .btn-secondary{
    background:#fff!important;
    border:0!important;
    color:var(--mq-primary)!important;
}
.container-fluid.py-5 .btn-secondary:hover{
    background:var(--mq-primary-dark)!important;
    color:#fff!important;
}
.container-fluid.py-5 .text-primary{
    color:var(--mq-primary)!important;
}

/* Product list */
.products.productList{
    background:linear-gradient(180deg,#fff 0%,#F5FBFF 100%)!important;
}
.products-mini-item{
    border:1px solid var(--mq-line)!important;
    border-radius:16px!important;
    overflow:hidden;
    background:#fff!important;
    box-shadow:0 7px 22px rgba(16,83,113,.05);
}
.products-mini-icon{
    background:var(--mq-primary)!important;
}
.products-mini-content .text-primary,
.products-mini-content a{
    color:var(--mq-primary)!important;
}
.products-mini-add{
    border-color:var(--mq-line)!important;
}

/* General headings */
.title-border-radius{
    border-color:var(--mq-primary)!important;
}
.text-primary{
    color:var(--mq-primary)!important;
}

/* Make old orange inline overlay blue */
[style*="rgba(242, 139, 0"],
[style*="rgba(242,139,0"]{
    background:rgba(0,163,255,.45)!important;
}

/* Buttons: Shop Now / Add To Cart / search etc — solid accent colour */
.btn-primary,
.btn-primary:visited{
    background:#00C391!important;
    border-color:#00C391!important;
    color:#fff!important;
}
.btn-primary:hover,
.btn-primary:focus,
.btn-primary:active{
    background:#00A67A!important;
    border-color:#00A67A!important;
    color:#fff!important;
}

/* Owl-Carousel prev/next arrows (rendered by JS, not covered by
   .btn-primary) and any other lingering theme-orange accents */
.owl-nav button,
.owl-nav .owl-prev,
.owl-nav .owl-next,
.owl-carousel .owl-nav button,
.owl-carousel .owl-nav [class*="owl-"],
.owl-theme .owl-nav [class*="owl-"]{
    background:#00C391!important;
    color:#fff!important;
}
.owl-nav button:hover,
.owl-nav .owl-prev:hover,
.owl-nav .owl-next:hover,
.owl-carousel .owl-nav button:hover,
.owl-carousel .owl-nav [class*="owl-"]:hover,
.owl-theme .owl-nav [class*="owl-"]:hover{
    background:#00A67A!important;
    color:#fff!important;
}
.bg-primary{
    background-color:#00C391!important;
}

/* Responsive */
@media(max-width:767.98px){
    .carousel-content{padding:25px!important;}
    .carousel-content h1{font-size:2.2rem!important;}
}
</style>

    <!-- Carousel Start -->
    <div class="container-fluid carousel bg-light px-0">
        <div class="row g-0 justify-content-end">
            <div class="col-12 col-lg-7 col-xl-9">
                <div class="header-carousel owl-carousel bg-light py-5">
                    <div class="row g-0 header-carousel-item align-items-center">
                        <div class="col-xl-6 carousel-img wow fadeInLeft" data-wow-delay="0.1s">
                            <img src="assets/img/carousel-1.png" class="img-fluid w-100" alt="Image">
                        </div>
                        <div class="col-xl-6 carousel-content p-4">
                            <h4 class="text-uppercase fw-bold mb-4 wow fadeInRight" data-wow-delay="0.1s"
                                style="letter-spacing: 3px;">Trusted Pharmacy Care</h4>
                            <h1 class="display-3 text-capitalize mb-4 wow fadeInRight" data-wow-delay="0.3s">For Your Everyday Health</h1>
                            <p class="text-dark wow fadeInRight" data-wow-delay="0.5s">Safe, reliable and convenient pharmacy service</p>
                            <a class="btn btn-primary rounded-pill py-3 px-5 wow fadeInRight" data-wow-delay="0.7s"
                                href="#">Shop Now</a>
                        </div>
                    </div>
                    <div class="row g-0 header-carousel-item align-items-center">
                        <div class="col-xl-6 carousel-img wow fadeInLeft" data-wow-delay="0.1s">
                            <img src="assets/img/carousel-2.png" class="img-fluid w-100" alt="Image">
                        </div>
                        <div class="col-xl-6 carousel-content p-4">
                            <h4 class="text-uppercase fw-bold mb-4 wow fadeInRight" data-wow-delay="0.1s"
                                style="letter-spacing: 3px;">Quality Medicines</h4>
                            <h1 class="display-3 text-capitalize mb-4 wow fadeInRight" data-wow-delay="0.3s">For Your Everyday Health</h1>
                            <p class="text-dark wow fadeInRight" data-wow-delay="0.5s">Safe, reliable and convenient pharmacy service</p>
                            <a class="btn btn-primary rounded-pill py-3 px-5 wow fadeInRight" data-wow-delay="0.7s"
                                href="#">Shop Now</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-5 col-xl-3 wow fadeInRight" data-wow-delay="0.1s">
                <div class="carousel-header-banner h-100">
                    <img src="assets/img/header-img.jpg" class="img-fluid w-100 h-100" style="object-fit: cover;" alt="Image">
                    <div class="carousel-banner-offer">
                        <p class="bg-primary text-white rounded fs-5 py-2 px-4 mb-0 me-3" style="background-color:#00C391!important;">Special Care</p>
                        <p class="text-primary fs-5 fw-bold mb-0">Pharmacy Offer</p>
                    </div>
                    <div class="carousel-banner">
                        <?php if (!empty($products)): ?>
                            <?php $bannerProduct = $products[0]; ?>
                            <?php $bannerPrice = productPriceData($bannerProduct); ?>

                            <div class="carousel-banner-content text-center p-4">
                                <a href="#" class="d-block mb-2">
                                    <?= htmlspecialchars($bannerProduct['category_name'] ?? 'Uncategorized') ?>
                                </a>
                                <a href="#" class="d-block text-white fs-3">
                                    <?= htmlspecialchars($bannerProduct['product_name']) ?>
                                </a>

                                <?php if ($bannerPrice['discount'] > 0): ?>
                                    <del class="me-2 text-white fs-5">
                                        $<?= number_format($bannerPrice['price'], 2) ?>
                                    </del>
                                    <span class="text-primary fs-5">
                                        $<?= number_format($bannerPrice['sale_price'], 2) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-primary fs-5">
                                        $<?= number_format($bannerPrice['price'], 2) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <a href="#" class="btn btn-primary rounded-pill py-2 px-4">
                                <i class="fas fa-shopping-cart me-2"></i> Add To Cart
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Carousel End -->

    <!-- Searvices Start -->
    <div class="container-fluid px-0">
        <div class="row g-0">
            <div class="col-6 col-md-4 col-lg-2 border-start border-end wow fadeInUp" data-wow-delay="0.1s">
                <div class="p-4">
                    <div class="d-inline-flex align-items-center">
                        <i class="fa fa-sync-alt fa-2x text-primary"></i>
                        <div class="ms-4">
                            <h6 class="text-uppercase mb-2">Easy Returns</h6>
                            <p class="mb-0">Simple and convenient service</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2 border-end wow fadeInUp" data-wow-delay="0.2s">
                <div class="p-4">
                    <div class="d-flex align-items-center">
                        <i class="fab fa-telegram-plane fa-2x text-primary"></i>
                        <div class="ms-4">
                            <h6 class="text-uppercase mb-2">Free Shipping</h6>
                            <p class="mb-0">Fast delivery on pharmacy orders</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2 border-end wow fadeInUp" data-wow-delay="0.3s">
                <div class="p-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-life-ring fa-2x text-primary"></i>
                        <div class="ms-4">
                            <h6 class="text-uppercase mb-2">Support 24/7</h6>
                            <p class="mb-0">We support online 24 hrs a day</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2 border-end wow fadeInUp" data-wow-delay="0.4s">
                <div class="p-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-credit-card fa-2x text-primary"></i>
                        <div class="ms-4">
                            <h6 class="text-uppercase mb-2">Trusted Care</h6>
                            <p class="mb-0">Helpful care whenever you need it</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2 border-end wow fadeInUp" data-wow-delay="0.5s">
                <div class="p-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-lock fa-2x text-primary"></i>
                        <div class="ms-4">
                            <h6 class="text-uppercase mb-2">Secure Payment</h6>
                            <p class="mb-0">We Value Your Security</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2 border-end wow fadeInUp" data-wow-delay="0.6s">
                <div class="p-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-blog fa-2x text-primary"></i>
                        <div class="ms-4">
                            <h6 class="text-uppercase mb-2">Online Pharmacy</h6>
                            <p class="mb-0">Easy online pharmacy service</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Searvices End -->

    <!-- Products Offer Start -->
    <div class="container-fluid bg-light py-5">
        <div class="container">
            <div class="row g-4">

                <?php foreach (array_slice($products, 0, 2) as $index => $product): ?>
                    <?php $offerPrice = productPriceData($product); ?>

                    <div class="col-lg-6">
                        <a href="#" class="d-flex align-items-center justify-content-between border bg-white rounded p-4">
                            <div>
                                <p class="text-muted mb-3">
                                    <?= htmlspecialchars($product['category_name'] ?? 'Product') ?>
                                </p>

                                <h3 class="text-primary">
                                    <?= htmlspecialchars($product['product_name']) ?>
                                </h3>

                                <?php if ($offerPrice['discount'] > 0): ?>
                                    <h1 class="display-3 text-secondary mb-0">
                                        <?= number_format($offerPrice['discount'], 0) ?>%
                                        <span class="text-primary fw-normal">Off</span>
                                    </h1>
                                <?php else: ?>
                                    <h1 class="display-3 text-secondary mb-0">
                                        $<?= number_format($offerPrice['price'], 2) ?>
                                    </h1>
                                <?php endif; ?>
                            </div>

                            <img src="<?= htmlspecialchars('assets/img/product-3.png') ?>"
                                class="img-fluid"
                                style="max-width: 180px; max-height: 180px; object-fit: contain;"
                                alt="<?= htmlspecialchars($product['product_name']) ?>">
                        </a>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>
    </div>
    <!-- Products Offer End -->


    <!-- Our Products Start -->
    <div class="container-fluid product py-5">
        <div class="container py-5">
            <div class="tab-class">
                <div class="row g-4">
                    <div class="col-lg-4 text-start">
                        <h1>Our Products</h1>
                    </div>
                    <div class="col-lg-8 text-end">
                        <ul class="nav nav-pills d-inline-flex text-center mb-5">
                            <li class="nav-item mb-4">
                                <a class="d-flex mx-2 py-2 bg-light rounded-pill active" data-bs-toggle="pill"
                                    href="#tab-1">
                                    <span class="text-dark" style="width: 130px;">All Products</span>
                                </a>
                            </li>
                            <li class="nav-item mb-4">
                                <a class="d-flex py-2 mx-2 bg-light rounded-pill" data-bs-toggle="pill" href="#tab-2">
                                    <span class="text-dark" style="width: 130px;">New Arrivals</span>
                                </a>
                            </li>
                            <li class="nav-item mb-4">
                                <a class="d-flex mx-2 py-2 bg-light rounded-pill" data-bs-toggle="pill" href="#tab-3">
                                    <span class="text-dark" style="width: 130px;">Featured</span>
                                </a>
                            </li>
                            <li class="nav-item mb-4">
                                <a class="d-flex mx-2 py-2 bg-light rounded-pill" data-bs-toggle="pill" href="#tab-4">
                                    <span class="text-dark" style="width: 130px;">Top Selling</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="tab-content">

                    <!-- All Products -->
                    <div id="tab-1" class="tab-pane fade show p-0 active">
                        <div class="row g-4">
                            <?php foreach ($products as $product): ?>
                                <?php $priceData = productPriceData($product); ?>

                                <div class="col-md-6 col-lg-4 col-xl-3">
                                    <div class="product-item rounded">
                                        <div class="product-item-inner border rounded">
                                            <div class="product-item-inner-item">
                                                <img src="<?= htmlspecialchars('assets/img/product-3.png') ?>"
                                                    class="img-fluid w-100 rounded-top"
                                                    alt="<?= htmlspecialchars($product['product_name']) ?>">

                                                <?php if ($priceData['discount'] > 0): ?>
                                                    <div class="product-sale">Sale</div>
                                                <?php endif; ?>

                                                <div class="product-details">
                                                    <a href="#">
                                                        <i class="fa fa-eye fa-1x"></i>
                                                    </a>
                                                </div>
                                            </div>

                                            <div class="text-center rounded-bottom p-4">
                                                <a href="#" class="d-block mb-2">
                                                    <?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?>
                                                </a>

                                                <a href="#" class="d-block h4">
                                                    <?= htmlspecialchars($product['product_name']) ?>
                                                </a>

                                                <?php if ($priceData['discount'] > 0): ?>
                                                    <del class="me-2 fs-5">
                                                        $<?= number_format($priceData['price'], 2) ?>
                                                    </del>
                                                    <span class="text-primary fs-5">
                                                        $<?= number_format($priceData['sale_price'], 2) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-primary fs-5">
                                                        $<?= number_format($priceData['price'], 2) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="product-item-add border border-top-0 rounded-bottom text-center p-4 pt-0">
                                            <a href="#"
                                                class="btn btn-primary border-secondary rounded-pill py-2 px-4 mb-4">
                                                <i class="fas fa-shopping-cart me-2"></i> Add To Cart
                                            </a>

                                            <div class="d-flex justify-content-end align-items-center">
                                                <div class="d-flex">
                                                    <a href="#"
                                                        class="text-primary d-flex align-items-center justify-content-center me-3">
                                                        <span class="rounded-circle btn-sm-square border">
                                                            <i class="fas fa-random"></i>
                                                        </span>
                                                    </a>

                                                    <a href="#"
                                                        class="text-primary d-flex align-items-center justify-content-center me-0">
                                                        <span class="rounded-circle btn-sm-square border">
                                                            <i class="fas fa-heart"></i>
                                                        </span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <?php if (empty($products)): ?>
                                <div class="col-12 text-center">
                                    <p class="text-muted">No active products found.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- New Arrivals -->
                    <div id="tab-2" class="tab-pane fade show p-0">
                        <div class="row g-4">
                            <?php foreach (array_slice($products, 0, 4) as $product): ?>
                                <?php $priceData = productPriceData($product); ?>

                                <div class="col-md-6 col-lg-4 col-xl-3">
                                    <div class="product-item rounded">
                                        <div class="product-item-inner border rounded">
                                            <div class="product-item-inner-item">
                                                <img src="<?= htmlspecialchars('assets/img/product-3.png') ?>"
                                                    class="img-fluid rounded-top"
                                                    alt="<?= htmlspecialchars($product['product_name']) ?>">

                                                <div class="product-new">New</div>

                                                <div class="product-details">
                                                    <a href="#"><i class="fa fa-eye fa-1x"></i></a>
                                                </div>
                                            </div>

                                            <div class="text-center rounded-bottom p-4">
                                                <a href="#" class="d-block mb-2">
                                                    <?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?>
                                                </a>

                                                <a href="#" class="d-block h4">
                                                    <?= htmlspecialchars($product['product_name']) ?>
                                                </a>

                                                <?php if ($priceData['discount'] > 0): ?>
                                                    <del class="me-2 fs-5">$<?= number_format($priceData['price'], 2) ?></del>
                                                    <span class="text-primary fs-5">$<?= number_format($priceData['sale_price'], 2) ?></span>
                                                <?php else: ?>
                                                    <span class="text-primary fs-5">$<?= number_format($priceData['price'], 2) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="product-item-add border border-top-0 rounded-bottom text-center p-4 pt-0">
                                            <a href="#" class="btn btn-primary border-secondary rounded-pill py-2 px-4 mb-4">
                                                <i class="fas fa-shopping-cart me-2"></i> Add To Cart
                                            </a>

                                            <div class="d-flex justify-content-end align-items-center">
                                                <div class="d-flex">
                                                    <a href="#" class="text-primary d-flex align-items-center justify-content-center me-3">
                                                        <span class="rounded-circle btn-sm-square border">
                                                            <i class="fas fa-random"></i>
                                                        </span>
                                                    </a>
                                                    <a href="#" class="text-primary d-flex align-items-center justify-content-center me-0">
                                                        <span class="rounded-circle btn-sm-square border">
                                                            <i class="fas fa-heart"></i>
                                                        </span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Featured -->
                    <div id="tab-3" class="tab-pane fade show p-0">
                        <div class="row g-4">
                            <?php
                            $featuredProducts = array_values(array_filter(
                                $products,
                                function ($product) {
                                    return false;
                                }
                            ));

                            if (empty($featuredProducts)) {
                                $featuredProducts = array_slice($products, 0, 4);
                            }
                            ?>

                            <?php foreach (array_slice($featuredProducts, 0, 4) as $product): ?>
                                <?php $priceData = productPriceData($product); ?>

                                <div class="col-md-6 col-lg-4 col-xl-3">
                                    <div class="product-item rounded">
                                        <div class="product-item-inner border rounded">
                                            <div class="product-item-inner-item">
                                                <img src="<?= htmlspecialchars('assets/img/product-3.png') ?>"
                                                    class="img-fluid w-100 rounded-top"
                                                    alt="<?= htmlspecialchars($product['product_name']) ?>">

                                                <?php if ($priceData['discount'] > 0): ?>
                                                    <div class="product-sale">Sale</div>
                                                <?php endif; ?>

                                                <div class="product-details">
                                                    <a href="#"><i class="fa fa-eye fa-1x"></i></a>
                                                </div>
                                            </div>

                                            <div class="text-center rounded-bottom p-4">
                                                <a href="#" class="d-block mb-2">
                                                    <?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?>
                                                </a>

                                                <a href="#" class="d-block h4">
                                                    <?= htmlspecialchars($product['product_name']) ?>
                                                </a>

                                                <?php if ($priceData['discount'] > 0): ?>
                                                    <del class="me-2 fs-5">$<?= number_format($priceData['price'], 2) ?></del>
                                                    <span class="text-primary fs-5">$<?= number_format($priceData['sale_price'], 2) ?></span>
                                                <?php else: ?>
                                                    <span class="text-primary fs-5">$<?= number_format($priceData['price'], 2) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="product-item-add border border-top-0 rounded-bottom text-center p-4 pt-0">
                                            <a href="#" class="btn btn-primary border-secondary rounded-pill py-2 px-4 mb-4">
                                                <i class="fas fa-shopping-cart me-2"></i> Add To Cart
                                            </a>

                                            <div class="d-flex justify-content-end align-items-center">
                                                <div class="d-flex">
                                                    <a href="#" class="text-primary d-flex align-items-center justify-content-center me-3">
                                                        <span class="rounded-circle btn-sm-square border">
                                                            <i class="fas fa-random"></i>
                                                        </span>
                                                    </a>
                                                    <a href="#" class="text-primary d-flex align-items-center justify-content-center me-0">
                                                        <span class="rounded-circle btn-sm-square border">
                                                            <i class="fas fa-heart"></i>
                                                        </span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Top Selling -->
                    <div id="tab-4" class="tab-pane fade show p-0">
                        <div class="row g-4">
                            <?php foreach (array_slice($products, 0, 4) as $product): ?>
                                <?php $priceData = productPriceData($product); ?>

                                <div class="col-md-6 col-lg-4 col-xl-3">
                                    <div class="product-item rounded">
                                        <div class="product-item-inner border rounded">
                                            <div class="product-item-inner-item">
                                                <img src="<?= htmlspecialchars('assets/img/product-3.png') ?>"
                                                    class="img-fluid w-100 rounded-top"
                                                    alt="<?= htmlspecialchars($product['product_name']) ?>">

                                                <div class="product-details">
                                                    <a href="#"><i class="fa fa-eye fa-1x"></i></a>
                                                </div>
                                            </div>

                                            <div class="text-center rounded-bottom p-4">
                                                <a href="#" class="d-block mb-2">
                                                    <?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?>
                                                </a>

                                                <a href="#" class="d-block h4">
                                                    <?= htmlspecialchars($product['product_name']) ?>
                                                </a>

                                                <?php if ($priceData['discount'] > 0): ?>
                                                    <del class="me-2 fs-5">$<?= number_format($priceData['price'], 2) ?></del>
                                                    <span class="text-primary fs-5">$<?= number_format($priceData['sale_price'], 2) ?></span>
                                                <?php else: ?>
                                                    <span class="text-primary fs-5">$<?= number_format($priceData['price'], 2) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="product-item-add border border-top-0 rounded-bottom text-center p-4 pt-0">
                                            <a href="#" class="btn btn-primary border-secondary rounded-pill py-2 px-4 mb-4">
                                                <i class="fas fa-shopping-cart me-2"></i> Add To Cart
                                            </a>

                                            <div class="d-flex justify-content-end align-items-center">
                                                <div class="d-flex">
                                                    <a href="#" class="text-primary d-flex align-items-center justify-content-center me-3">
                                                        <span class="rounded-circle btn-sm-square border">
                                                            <i class="fas fa-random"></i>
                                                        </span>
                                                    </a>
                                                    <a href="#" class="text-primary d-flex align-items-center justify-content-center me-0">
                                                        <span class="rounded-circle btn-sm-square border">
                                                            <i class="fas fa-heart"></i>
                                                        </span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- Our Products End -->

    <!-- Product Banner Start -->
    <div class="container-fluid py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-6">
                    <a href="#">
                        <div class="bg-primary rounded position-relative" style="background-color:#00C391!important;">
                            <img src="assets/img/product-banner.jpg" class="img-fluid w-100 rounded" alt="">
                            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center rounded p-4"
                                style="background: rgba(255, 255, 255, 0.5);">
                                <?php if (!empty($products)): ?>
                                    <?php $banner2Product = $products[1] ?? $products[0]; ?>
                                    <?php $banner2Price = productPriceData($banner2Product); ?>

                                    <h3 class="display-5 text-primary">
                                        <?= htmlspecialchars($banner2Product['product_name']) ?>
                                    </h3>

                                    <?php if ($banner2Price['discount'] > 0): ?>
                                        <p class="fs-4 text-muted">
                                            $<?= number_format($banner2Price['sale_price'], 2) ?>
                                            (<?= number_format($banner2Price['discount'], 0) ?>% Off)
                                        </p>
                                    <?php else: ?>
                                        <p class="fs-4 text-muted">
                                            $<?= number_format($banner2Price['price'], 2) ?>
                                        </p>
                                    <?php endif; ?>

                                <a href="#" class="btn btn-primary rounded-pill align-self-start py-2 px-4">Shop Now</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-6">
                    <a href="#">
                        <div class="text-center bg-primary rounded position-relative" style="background-color:#00C391!important;">
                            <img src="assets/img/product-banner-2.jpg" class="img-fluid w-100" alt="">
                            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center rounded p-4"
                                style="background: rgba(242, 139, 0, 0.5);">
                                <h2 class="display-2 text-secondary">SALE</h2>
                                <h4 class="display-5 text-white mb-4">Quality Medicines at Great Prices</h4>
                                <a href="#" class="btn btn-secondary rounded-pill align-self-center py-2 px-4">Shop
                                    Now</a>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Product Banner End -->

    <!-- Product List Satrt -->
    <div class="container-fluid products productList overflow-hidden">
        <div class="container products-mini py-5">
            <div class="mx-auto text-center mb-5" style="max-width: 900px;">
                <h4 class="text-primary border-bottom border-primary border-2 d-inline-block p-2 title-border-radius">Products</h4>
                <h1 class="mb-0 display-3">All Product Items</h1>
            </div>
            <div class="productList-carousel owl-carousel pt-4">

                <?php
                $productGroups = array_chunk($products, 4);
                foreach ($productGroups as $group):
                ?>
                    <div class="productImg-carousel owl-carousel productList-item">

                        <?php foreach ($group as $product): ?>
                            <?php $listPrice = productPriceData($product); ?>

                            <div class="productImg-item products-mini-item border">
                                <div class="row g-0">
                                    <div class="col-5">
                                        <div class="products-mini-img border-end h-100">
                                            <img src="<?= htmlspecialchars('assets/img/product-3.png') ?>"
                                                class="img-fluid w-100 h-100"
                                                alt="<?= htmlspecialchars($product['product_name']) ?>">

                                            <div class="products-mini-icon rounded-circle bg-primary" style="background-color:#00C391!important;">
                                                <a href="#">
                                                    <i class="fa fa-eye fa-1x text-white"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-7">
                                        <div class="products-mini-content p-3">
                                            <a href="#" class="d-block mb-2">
                                                <?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?>
                                            </a>

                                            <a href="#" class="d-block h4">
                                                <?= htmlspecialchars($product['product_name']) ?>
                                            </a>

                                            <?php if ($listPrice['discount'] > 0): ?>
                                                <del class="me-2 fs-5">
                                                    $<?= number_format($listPrice['price'], 2) ?>
                                                </del>
                                                <span class="text-primary fs-5">
                                                    $<?= number_format($listPrice['sale_price'], 2) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-primary fs-5">
                                                    $<?= number_format($listPrice['price'], 2) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="products-mini-add border p-3">
                                    <a href="#" class="btn btn-primary border-secondary rounded-pill py-2 px-4">
                                        <i class="fas fa-shopping-cart me-2"></i> Add To Cart
                                    </a>

                                    <div class="d-flex">
                                        <a href="#"
                                            class="text-primary d-flex align-items-center justify-content-center me-3">
                                            <span class="rounded-circle btn-sm-square border">
                                                <i class="fas fa-random"></i>
                                            </span>
                                        </a>

                                        <a href="#"
                                            class="text-primary d-flex align-items-center justify-content-center me-0">
                                            <span class="rounded-circle btn-sm-square border">
                                                <i class="fas fa-heart"></i>
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                    </div>
                <?php endforeach; ?>

            </div>
        </div>
    </div>
    <!-- Product List End -->

    <?php include('../includes/footer.php') ?> 
</body>

</html>