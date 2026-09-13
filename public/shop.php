<?php
/**
 * MediQuick Pharmacy - Shop Page
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Paths & Dependencies
$baseDir = __DIR__;
$rootDir = dirname(__DIR__);

require_once $rootDir . '/includes/db.php';
require_once $baseDir . '/handlers/shop-handler.php';
require_once $rootDir . '/includes/functions.php';

// Page Settings
$pageTitle   = 'MediQuick Pharmacy - Shop';
$activePage  = 'shop.php';
$pageHeading = 'Shop';

$breadcrumb = [
    ['label' => 'Home', 'url' => 'index.php'],
    ['label' => 'Pages', 'url' => '#'],
    ['label' => 'Shop', 'url' => '#']
];

// Default Values
$products      = $products ?? [];
$categories    = $categories ?? [];
$totalProducts = $totalProducts ?? 0;
$totalPages    = $totalPages ?? 1;
$currentPage   = $currentPage ?? 1;
$search        = $search ?? '';
$categoryId    = $categoryId ?? 0;
$minPrice      = $minPrice ?? null;
$maxPrice      = $maxPrice ?? null;
$prescription  = $prescription ?? '';
$sort          = $sort ?? 'newest';

// Helper Functions
function shopProductImage($image)
{
    $image = trim((string) $image);
    return $image === '' ? 'img/product-placeholder.jpg' : $image;
}

function shopFormatPrice($price)
{
    return number_format((float) $price, 2);
}

// Include Header
require_once $rootDir . '/includes/header.php';
?>

<style>
    :root {
        --mq-shop-primary: #00C391;
        --mq-shop-primary-hover: #00B4DA;
        --mq-shop-danger: #00A3FF;
        --mq-shop-danger-hover: #008FE0;
        --mq-shop-text: #294052;
    }

    /* Apply Filters & Add To Cart */
    .mq-shop-page .shop-filter-btn,
    .mq-shop-page .shop-add-btn {
        background: var(--mq-shop-primary) !important;
        border: 1px solid var(--mq-shop-primary) !important;
        color: #fff !important;
        font-weight: 600;
        transition: all .25s ease;
    }

    .mq-shop-page .shop-filter-btn:hover,
    .mq-shop-page .shop-filter-btn:focus,
    .mq-shop-page .shop-add-btn:hover,
    .mq-shop-page .shop-add-btn:focus {
        background: var(--mq-shop-primary-hover) !important;
        border-color: var(--mq-shop-primary-hover) !important;
        color: #fff !important;
        box-shadow: none !important;
    }

    /* Clear Filters & View Product */
    .mq-shop-page .shop-clear-btn,
    .mq-shop-page .shop-view-btn {
        background: #fff !important;
        border: 1px solid var(--mq-shop-primary) !important;
        color: var(--mq-shop-primary) !important;
        font-weight: 600;
        transition: all .25s ease;
    }

    .mq-shop-page .shop-clear-btn:hover,
    .mq-shop-page .shop-clear-btn:focus,
    .mq-shop-page .shop-view-btn:hover,
    .mq-shop-page .shop-view-btn:focus {
        background: var(--mq-shop-primary) !important;
        border-color: var(--mq-shop-primary) !important;
        color: #fff !important;
        box-shadow: none !important;
    }

    /* Out of Stock */
    .mq-shop-page .shop-out-stock-btn,
    .mq-shop-page .shop-out-stock-btn:disabled {
        background: var(--mq-shop-danger) !important;
        border: 1px solid var(--mq-shop-danger) !important;
        color: #fff !important;
        font-weight: 600;
        opacity: 1 !important;
        cursor: not-allowed !important;
    }

    /* Button Dimensions */
    .mq-shop-page .shop-filter-btn,
    .mq-shop-page .shop-clear-btn,
    .mq-shop-page .shop-view-btn,
    .mq-shop-page .shop-add-btn,
    .mq-shop-page .shop-out-stock-btn {
        border-radius: 10px !important;
        min-height: 42px;
    }
</style>

<div class="mq-shop-page">
    <div class="container-fluid py-5">
        <div class="container">
            <div class="row g-4">

                <!-- Sidebar Filters -->
                <div class="col-lg-3">
                    <div class="bg-light rounded p-4">
                        <h4 class="mb-4">Shop Filters</h4>

                        <form action="shop.php" method="GET">
                            <!-- Search -->
                            <div class="mb-4">
                                <label for="search" class="form-label fw-bold">Search Products</label>
                                <input type="text" id="search" name="search" class="form-control" placeholder="Search medicine..." value="<?= htmlspecialchars($search) ?>">
                            </div>

                            <!-- Category -->
                            <div class="mb-4">
                                <label for="category" class="form-label fw-bold">Category</label>
                                <select id="category" name="category" class="form-select">
                                    <option value="0">All Categories</option>
                                    <?php if (!empty($categories)): ?>
                                        <?php foreach ($categories as $category): ?>
                                            <?php $catId = (int) ($category['category_id'] ?? 0); ?>
                                            <option value="<?= $catId ?>" <?= $categoryId === $catId ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($category['category_name'] ?? '') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <!-- Price Range -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Price Range</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" name="min_price" class="form-control" placeholder="Min" min="0" step="0.01" value="<?= $minPrice !== null ? htmlspecialchars((string) $minPrice) : '' ?>">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" name="max_price" class="form-control" placeholder="Max" min="0" step="0.01" value="<?= $maxPrice !== null ? htmlspecialchars((string) $maxPrice) : '' ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Prescription Filter -->
                            <div class="mb-4">
                                <label for="prescription" class="form-label fw-bold">Prescription</label>
                                <select id="prescription" name="prescription" class="form-select">
                                    <option value="">All Products</option>
                                    <option value="required" <?= $prescription === 'required' ? 'selected' : '' ?>>Prescription Required</option>
                                    <option value="not_required" <?= $prescription === 'not_required' ? 'selected' : '' ?>>No Prescription</option>
                                </select>
                            </div>

                            <!-- Sort -->
                            <div class="mb-4">
                                <label for="sort" class="form-label fw-bold">Sort By</label>
                                <select id="sort" name="sort" class="form-select">
                                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
                                    <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Name A-Z</option>
                                    <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Name Z-A</option>
                                    <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price Low to High</option>
                                    <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price High to Low</option>
                                </select>
                            </div>

                            <!-- Filter Buttons -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary shop-filter-btn">Apply Filters</button>
                                <a href="shop.php" class="btn btn-outline-secondary shop-clear-btn">Clear Filters</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Product Area -->
                <div class="col-lg-9">
                    <!-- Shop Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="mb-1">Pharmacy Products</h4>
                            <p class="text-muted mb-0">
                                <?php if ($totalProducts > 0): ?>
                                    <?= number_format($totalProducts) ?> product<?= $totalProducts != 1 ? 's' : '' ?> found
                                <?php else: ?>
                                    No products found
                                <?php endif; ?>
                            </p>
                        </div>
                        <div>
                            <span class="text-muted">
                                Page <?= (int) $currentPage ?> of <?= (int) $totalPages ?>
                            </span>
                        </div>
                    </div>

                    <!-- Products Grid -->
                    <?php if (!empty($products)): ?>
                        <div class="row g-4">
                            <?php foreach ($products as $product): ?>
                                <?php
                                $productId            = (int) ($product['product_id'] ?? 0);
                                $productName          = $product['product_name'] ?? '';
                                $description          = $product['description'] ?? '';
                                $categoryName         = $product['category_name'] ?? '';
                                $dosageForm           = $product['dosage_form'] ?? '';
                                $strength             = $product['strength'] ?? '';
                                $unitPrice            = (float) ($product['unit_price'] ?? 0);
                                $stockQuantity        = (int) ($product['stock_quantity'] ?? 0);
                                $requiresPrescription = (int) ($product['requires_prescription'] ?? 0);
                                $image                = shopProductImage($product['product_image'] ?? '');
                                ?>

                                <!-- Product Card -->
                                <div class="col-md-6 col-xl-4">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <!-- Product Image -->
                                        <div class="position-relative bg-light" style="height: 220px;">
                                            <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($productName) ?>" class="w-100 h-100" style="object-fit: contain;" onerror="this.onerror=null;this.src='img/product-placeholder.jpg';">
                                        </div>

                                        <!-- Product Body -->
                                        <div class="card-body d-flex flex-column">
                                            <?php if ($categoryName !== ''): ?>
                                                <small class="text-primary mb-2"><?= htmlspecialchars($categoryName) ?></small>
                                            <?php endif; ?>

                                            <h5 class="card-title mb-2"><?= htmlspecialchars($productName) ?></h5>

                                            <?php if ($description !== ''): ?>
                                                <p class="text-muted small mb-3"><?= htmlspecialchars($description) ?></p>
                                            <?php endif; ?>

                                            <!-- Strength / Dosage -->
                                            <?php if ($strength !== '' || $dosageForm !== ''): ?>
                                                <div class="small text-muted mb-3">
                                                    <?php if ($strength !== ''): ?>
                                                        <span><?= htmlspecialchars($strength) ?></span>
                                                    <?php endif; ?>

                                                    <?php if ($strength !== '' && $dosageForm !== ''): ?>
                                                        <span class="mx-1">•</span>
                                                    <?php endif; ?>

                                                    <?php if ($dosageForm !== ''): ?>
                                                        <span><?= htmlspecialchars($dosageForm) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Prescription Badge -->
                                            <?php if ($requiresPrescription === 1): ?>
                                                <div class="mb-3">
                                                    <span class="badge bg-warning text-dark">Prescription Required</span>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Price & Cart Actions -->
                                            <div class="mt-auto">
                                                <h5 class="text-primary mb-3">Rs. <?= shopFormatPrice($unitPrice) ?></h5>

                                                <?php if ($stockQuantity > 0): ?>
                                                    <p class="text-success small mb-3">
                                                        <i class="fa fa-check-circle me-1"></i><?= number_format($stockQuantity) ?> available
                                                    </p>
                                                <?php else: ?>
                                                    <p class="text-danger small mb-3">
                                                        <i class="fa fa-times-circle me-1"></i>Out of stock
                                                    </p>
                                                <?php endif; ?>

                                                <div class="d-flex gap-2">
                                                    <a href="product.php?id=<?= $productId ?>" class="btn btn-outline-primary flex-fill shop-view-btn">View</a>
                                                    <?php if ($stockQuantity > 0): ?>
                                                        <a href="cart.php?action=add&product_id=<?= $productId ?>" class="btn btn-primary flex-fill shop-add-btn">
                                                            <i class="fa fa-shopping-cart me-1"></i>Add
                                                        </a>
                                                    <?php else: ?>
                                                        <button type="button" class="btn btn-secondary flex-fill shop-out-stock-btn" disabled>Out of Stock</button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                    <!-- Empty State -->
                    <?php else: ?>
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fa fa-search fa-4x text-muted"></i>
                            </div>
                            <h4>No products found</h4>
                            <p class="text-muted">Try changing your search or filter options.</p>
                            <a href="shop.php" class="btn btn-primary">View All Products</a>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>
                        
<?php
require_once $rootDir . '/includes/footer.php';
?>