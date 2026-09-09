<?php
/**
 * MediQuick Pharmacy
 * Shop Page
 */

// ==================================================
// ERROR REPORTING
// ==================================================

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// ==================================================
// PATHS
// ==================================================

$baseDir = __DIR__;
$rootDir = dirname(__DIR__);


// ==================================================
// DATABASE CONNECTION
// includes/db.php
// ==================================================

require_once $rootDir . '/includes/db.php';


// ==================================================
// SHOP HANDLER
// public/handlers/shop-handler.php
// ==================================================

require_once $baseDir . '/handlers/shop-handler.php';


// ==================================================
// FUNCTIONS
// includes/functions.php
// ==================================================

require_once $rootDir . '/includes/functions.php';


// ==================================================
// PAGE SETTINGS
// ==================================================

$pageTitle  = 'MediQuick Pharmacy - Shop';
$activePage = 'shop.php';
$pageHeading = 'Shop';

$breadcrumb = [
    [
        'label' => 'Home',
        'url'   => 'index.php'
    ],
    [
        'label' => 'Pages',
        'url'   => '#'
    ],
    [
        'label' => 'Shop',
        'url'   => '#'
    ]
];


// ==================================================
// DEFAULT VALUES
// ==================================================

$products = $products ?? [];

$categories = $categories ?? [];

$totalProducts = $totalProducts ?? 0;

$totalPages = $totalPages ?? 1;

$currentPage = $currentPage ?? 1;

$search = $search ?? '';

$categoryId = $categoryId ?? 0;

$minPrice = $minPrice ?? null;

$maxPrice = $maxPrice ?? null;

$prescription = $prescription ?? '';

$sort = $sort ?? 'newest';


// ==================================================
// HELPER FUNCTIONS
// ==================================================

if (!function_exists('shopProductImage')) {

    function shopProductImage($image)
    {
        $image = trim((string) $image);

        if ($image === '') {
            return 'assets/img/product-placeholder.jpg';
        }

        return $image;
    }
}


if (!function_exists('shopFormatPrice')) {

    function shopFormatPrice($price)
    {
        return number_format((float) $price, 2);
    }
}


// ==================================================
// HEADER INCLUDE
// includes/header.php
// ==================================================

// IMPORTANT:
// shop.php is inside /public
// therefore ../includes/header.php is correct.

include '../includes/header.php';

?>


<!-- ==================================================
     SHOP PAGE
================================================== -->

<div class="container-fluid py-5">

    <div class="container">

        <div class="row g-4">


            <!-- ==================================================
                 SIDEBAR
            ================================================== -->

            <div class="col-lg-3">

                <div class="bg-light rounded p-4">

                    <!-- FILTER TITLE -->

                    <h4 class="mb-4">
                        Shop Filters
                    </h4>


                    <!-- FILTER FORM -->

                    <form action="shop.php" method="GET">


                        <!-- SEARCH -->

                        <div class="mb-4">

                            <label
                                for="search"
                                class="form-label fw-bold"
                            >
                                Search Products
                            </label>

                            <input
                                type="text"
                                id="search"
                                name="search"
                                class="form-control"
                                placeholder="Search medicine..."
                                value="<?= htmlspecialchars(
                                    (string) $search,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

                        </div>


                        <!-- CATEGORY -->

                        <div class="mb-4">

                            <label
                                for="category"
                                class="form-label fw-bold"
                            >
                                Category
                            </label>

                            <select
                                id="category"
                                name="category"
                                class="form-select"
                            >

                                <option value="0">
                                    All Categories
                                </option>


                                <?php if (!empty($categories)): ?>

                                    <?php foreach ($categories as $category): ?>

                                        <?php
                                        $catId = (int) (
                                            $category['category_id'] ?? 0
                                        );

                                        $catName = $category['category_name'] ?? '';
                                        ?>

                                        <option
                                            value="<?= $catId ?>"
                                            <?= (int) $categoryId === $catId
                                                ? 'selected'
                                                : '' ?>
                                        >
                                            <?= htmlspecialchars(
                                                $catName,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </option>

                                    <?php endforeach; ?>

                                <?php endif; ?>

                            </select>

                        </div>


                        <!-- PRICE RANGE -->

                        <div class="mb-4">

                            <label class="form-label fw-bold">
                                Price Range
                            </label>

                            <div class="row g-2">

                                <!-- MIN -->

                                <div class="col-6">

                                    <input
                                        type="number"
                                        name="min_price"
                                        class="form-control"
                                        placeholder="Min"
                                        min="0"
                                        step="0.01"
                                        value="<?= $minPrice !== null
                                            ? htmlspecialchars(
                                                (string) $minPrice,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            )
                                            : '' ?>"
                                    >

                                </div>


                                <!-- MAX -->

                                <div class="col-6">

                                    <input
                                        type="number"
                                        name="max_price"
                                        class="form-control"
                                        placeholder="Max"
                                        min="0"
                                        step="0.01"
                                        value="<?= $maxPrice !== null
                                            ? htmlspecialchars(
                                                (string) $maxPrice,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            )
                                            : '' ?>"
                                    >

                                </div>

                            </div>

                        </div>


                        <!-- PRESCRIPTION -->

                        <div class="mb-4">

                            <label
                                for="prescription"
                                class="form-label fw-bold"
                            >
                                Prescription
                            </label>

                            <select
                                id="prescription"
                                name="prescription"
                                class="form-select"
                            >

                                <option value="">
                                    All Products
                                </option>

                                <option
                                    value="required"
                                    <?= $prescription === 'required'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Prescription Required
                                </option>

                                <option
                                    value="not_required"
                                    <?= $prescription === 'not_required'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    No Prescription
                                </option>

                            </select>

                        </div>


                        <!-- SORT -->

                        <div class="mb-4">

                            <label
                                for="sort"
                                class="form-label fw-bold"
                            >
                                Sort By
                            </label>

                            <select
                                id="sort"
                                name="sort"
                                class="form-select"
                            >

                                <option
                                    value="newest"
                                    <?= $sort === 'newest'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Newest
                                </option>

                                <option
                                    value="name_asc"
                                    <?= $sort === 'name_asc'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Name A-Z
                                </option>

                                <option
                                    value="name_desc"
                                    <?= $sort === 'name_desc'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Name Z-A
                                </option>

                                <option
                                    value="price_low"
                                    <?= $sort === 'price_low'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Price Low to High
                                </option>

                                <option
                                    value="price_high"
                                    <?= $sort === 'price_high'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Price High to Low
                                </option>

                            </select>

                        </div>


                        <!-- BUTTONS -->

                        <div class="d-grid gap-2">

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                <i class="fas fa-filter me-1"></i>
                                Apply Filters
                            </button>

                            <a
                                href="shop.php"
                                class="btn btn-outline-secondary"
                            >
                                <i class="fas fa-times me-1"></i>
                                Clear Filters
                            </a>

                        </div>

                    </form>

                </div>

            </div>


            <!-- ==================================================
                 PRODUCT AREA
            ================================================== -->

            <div class="col-lg-9">


                <!-- SHOP HEADER -->

                <div
                    class="d-flex justify-content-between align-items-center mb-4"
                >

                    <div>

                        <h4 class="mb-1">
                            Pharmacy Products
                        </h4>

                        <p class="text-muted mb-0">

                            <?php if ($totalProducts > 0): ?>

                                <?= number_format($totalProducts) ?>

                                product<?= $totalProducts != 1 ? 's' : '' ?>

                                found

                            <?php else: ?>

                                No products found

                            <?php endif; ?>

                        </p>

                    </div>


                    <div>

                        <span class="text-muted">

                            Page
                            <?= (int) $currentPage ?>

                            of

                            <?= (int) $totalPages ?>

                        </span>

                    </div>

                </div>


                <!-- ==================================================
                     PRODUCTS
                ================================================== -->

                <?php if (!empty($products)): ?>

                    <div class="row g-4">

                        <?php foreach ($products as $product): ?>

                            <?php

                            // Product ID
                            $productId = (int) (
                                $product['product_id'] ?? 0
                            );

                            // Product name
                            $productName = $product['product_name'] ?? '';

                            // Description
                            $description = $product['description'] ?? '';

                            // Category
                            $categoryName = $product['category_name'] ?? '';

                            // Dosage form
                            $dosageForm = $product['dosage_form'] ?? '';

                            // Strength
                            $strength = $product['strength'] ?? '';

                            // Unit price
                            $unitPrice = (float) (
                                $product['unit_price'] ?? 0
                            );

                            // Stock
                            $stockQuantity = (int) (
                                $product['stock_quantity'] ?? 0
                            );

                            // Prescription
                            $requiresPrescription = (int) (
                                $product['requires_prescription'] ?? 0
                            );

                            // Image
                            $image = shopProductImage(
                                $product['product_image'] ?? ''
                            );

                            ?>


                            <!-- PRODUCT CARD -->

                            <div class="col-md-6 col-xl-4">

                                <div class="card h-100 border-0 shadow-sm">


                                    <!-- PRODUCT IMAGE -->

                                    <div
                                        class="position-relative bg-light"
                                        style="height:220px;"
                                    >

                                        <img
                                            src="<?= htmlspecialchars(
                                                $image,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                            alt="<?= htmlspecialchars(
                                                $productName,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                            class="w-100 h-100"
                                            style="object-fit:contain;"
                                            onerror="this.onerror=null;this.src='assets/img/product-placeholder.jpg';"
                                        >

                                    </div>


                                    <!-- PRODUCT BODY -->

                                    <div class="card-body d-flex flex-column">


                                        <!-- CATEGORY -->

                                        <?php if ($categoryName !== ''): ?>

                                            <small
                                                class="text-primary mb-2"
                                            >
                                                <?= htmlspecialchars(
                                                    $categoryName,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </small>

                                        <?php endif; ?>


                                        <!-- PRODUCT NAME -->

                                        <h5 class="card-title mb-2">

                                            <?= htmlspecialchars(
                                                $productName,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </h5>


                                        <!-- DESCRIPTION -->

                                        <?php if ($description !== ''): ?>

                                            <p
                                                class="text-muted small mb-3"
                                            >

                                                <?= htmlspecialchars(
                                                    $description,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>

                                            </p>

                                        <?php endif; ?>


                                        <!-- STRENGTH / DOSAGE -->

                                        <?php if (
                                            $strength !== '' ||
                                            $dosageForm !== ''
                                        ): ?>

                                            <div
                                                class="small text-muted mb-3"
                                            >

                                                <?php if ($strength !== ''): ?>

                                                    <span>
                                                        <?= htmlspecialchars(
                                                            $strength,
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ) ?>
                                                    </span>

                                                <?php endif; ?>


                                                <?php if (
                                                    $strength !== '' &&
                                                    $dosageForm !== ''
                                                ): ?>

                                                    <span class="mx-1">
                                                        •
                                                    </span>

                                                <?php endif; ?>


                                                <?php if ($dosageForm !== ''): ?>

                                                    <span>
                                                        <?= htmlspecialchars(
                                                            $dosageForm,
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ) ?>
                                                    </span>

                                                <?php endif; ?>

                                            </div>

                                        <?php endif; ?>


                                        <!-- PRESCRIPTION STATUS -->

                                        <div class="mb-3">

                                            <?php if (
                                                $requiresPrescription === 1
                                            ): ?>

                                                <span
                                                    class="badge bg-warning text-dark"
                                                >
                                                    <i class="fas fa-file-prescription me-1"></i>
                                                    Prescription Required
                                                </span>

                                            <?php else: ?>

                                                <span
                                                    class="badge bg-success"
                                                >
                                                    <i class="fas fa-check me-1"></i>
                                                    No Prescription
                                                </span>

                                            <?php endif; ?>

                                        </div>


                                        <!-- PRICE / STOCK / BUTTONS -->

                                        <div class="mt-auto">


                                            <!-- PRICE -->

                                            <h5 class="text-primary mb-3">

                                                Rs.
                                                <?= shopFormatPrice(
                                                    $unitPrice
                                                ) ?>

                                            </h5>


                                            <!-- STOCK -->

                                            <?php if ($stockQuantity > 0): ?>

                                                <p
                                                    class="text-success small mb-3"
                                                >

                                                    <i
                                                        class="fa fa-check-circle me-1"
                                                    ></i>

                                                    <?= number_format(
                                                        $stockQuantity
                                                    ) ?>

                                                    available

                                                </p>

                                            <?php else: ?>

                                                <p
                                                    class="text-danger small mb-3"
                                                >

                                                    <i
                                                        class="fa fa-times-circle me-1"
                                                    ></i>

                                                    Out of stock

                                                </p>

                                            <?php endif; ?>


                                            <!-- BUTTONS -->

                                            <div class="d-flex gap-2">


                                                <!-- VIEW -->

                                                <a
                                                    href="product-details.php?id=<?= $productId ?>"
                                                    class="btn btn-outline-primary flex-fill"
                                                >
                                                    <i class="fas fa-eye me-1"></i>
                                                    View
                                                </a>


                                                <!-- ADD TO CART -->

                                                <?php if ($stockQuantity > 0): ?>

                                                    <a
                                                        href="cart.php?action=add&product_id=<?= $productId ?>"
                                                        class="btn btn-primary flex-fill"
                                                    >

                                                        <i
                                                            class="fa fa-shopping-cart me-1"
                                                        ></i>

                                                        Add

                                                    </a>

                                                <?php else: ?>

                                                    <button
                                                        type="button"
                                                        class="btn btn-secondary flex-fill"
                                                        disabled
                                                    >
                                                        Out of Stock
                                                    </button>

                                                <?php endif; ?>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>


                <?php else: ?>


                    <!-- ==================================================
                         NO PRODUCTS
                    ================================================== -->

                    <div class="text-center py-5">

                        <div class="mb-4">

                            <i
                                class="fa fa-search fa-4x text-muted"
                            ></i>

                        </div>

                        <h4>
                            No products found
                        </h4>

                        <p class="text-muted">
                            Try changing your search or filter options.
                        </p>

                        <a
                            href="shop.php"
                            class="btn btn-primary"
                        >
                            <i class="fas fa-store me-1"></i>
                            View All Products
                        </a>

                    </div>

                <?php endif; ?>


                <!-- ==================================================
                     PAGINATION
                ================================================== -->

                <?php if ($totalPages > 1): ?>

                    <div
                        class="d-flex justify-content-center mt-5"
                    >

                        <nav aria-label="Shop pagination">

                            <ul class="pagination">


                                <!-- PREVIOUS -->

                                <?php if ($currentPage > 1): ?>

                                    <li class="page-item">

                                        <a
                                            class="page-link"
                                            href="<?= htmlspecialchars(
                                                shopPageUrl(
                                                    $currentPage - 1
                                                ),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                        >
                                            Previous
                                        </a>

                                    </li>

                                <?php else: ?>

                                    <li class="page-item disabled">

                                        <span class="page-link">
                                            Previous
                                        </span>

                                    </li>

                                <?php endif; ?>


                                <!-- PAGE NUMBERS -->

                                <?php

                                $startPage = max(
                                    1,
                                    $currentPage - 2
                                );

                                $endPage = min(
                                    $totalPages,
                                    $currentPage + 2
                                );

                                ?>


                                <?php for (
                                    $page = $startPage;
                                    $page <= $endPage;
                                    $page++
                                ): ?>

                                    <li
                                        class="page-item <?= $page === $currentPage
                                            ? 'active'
                                            : '' ?>"
                                    >

                                        <a
                                            class="page-link"
                                            href="<?= htmlspecialchars(
                                                shopPageUrl($page),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                        >
                                            <?= $page ?>
                                        </a>

                                    </li>

                                <?php endfor; ?>


                                <!-- NEXT -->

                                <?php if (
                                    $currentPage < $totalPages
                                ): ?>

                                    <li class="page-item">

                                        <a
                                            class="page-link"
                                            href="<?= htmlspecialchars(
                                                shopPageUrl(
                                                    $currentPage + 1
                                                ),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                        >
                                            Next
                                        </a>

                                    </li>

                                <?php else: ?>

                                    <li class="page-item disabled">

                                        <span class="page-link">
                                            Next
                                        </span>

                                    </li>

                                <?php endif; ?>

                            </ul>

                        </nav>

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </div>

</div>


<!-- ==================================================
     FOOTER
     includes/footer.php
================================================== -->

<?php

require_once $rootDir . '/includes/footer.php';

?>