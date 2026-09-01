<?php

// ============================================================
// MEDIQUICK PHARMACY - SHOP PAGE
// ============================================================

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// ============================================================
// PROJECT PATH
// ============================================================

$projectDir = dirname(__DIR__);


// ============================================================
// DATABASE CONNECTION
// ============================================================

$host = '127.0.0.1';
$db   = 'mediquick_pharmacy';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=$charset",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

} catch (PDOException $e) {

    die(
        '<div style="
            margin:30px;
            padding:20px;
            background:#ffe5e5;
            border:1px solid #ff9999;
            color:#990000;
            font-family:Arial;
        ">
            <h3>Database Connection Error</h3>
            <p>' .
            htmlspecialchars($e->getMessage()) .
            '</p>
        </div>'
    );

}


// ============================================================
// SEARCH VALUE
// ============================================================

$search = isset($_GET['search'])
    ? trim($_GET['search'])
    : '';


// ============================================================
// CATEGORY VALUE
// ============================================================

$categoryId = isset($_GET['category'])
    ? (int)$_GET['category']
    : 0;


// ============================================================
// GET CATEGORIES
// ============================================================

$categories = [];

try {

    $categoryQuery = "
        SELECT
            category_id,
            category_name,
            description
        FROM categories
        ORDER BY category_name ASC
    ";

    $categoryStmt = $pdo->query($categoryQuery);

    $categories = $categoryStmt->fetchAll();

} catch (PDOException $e) {

    $categories = [];

}


// ============================================================
// GET PRODUCTS
// ============================================================

$products = [];

try {

    $sql = "
        SELECT
            p.product_id,
            p.product_name,
            p.description,
            p.sku,
            p.category_id,
            c.category_name,
            p.dosage_form,
            p.strength,
            p.unit_price,
            p.requires_prescription,
            p.reorder_level,
            p.status

        FROM products p

        LEFT JOIN categories c
            ON p.category_id = c.category_id

        WHERE p.status = 'active'
    ";

    $params = [];


    // --------------------------------------------------------
    // CATEGORY FILTER
    // --------------------------------------------------------

    if ($categoryId > 0) {

        $sql .= "
            AND p.category_id = :category_id
        ";

        $params['category_id'] = $categoryId;

    }


    // --------------------------------------------------------
    // SEARCH FILTER
    // --------------------------------------------------------

    if ($search !== '') {

        $sql .= "
            AND (
                p.product_name LIKE :search
                OR p.description LIKE :search
                OR p.sku LIKE :search
                OR p.dosage_form LIKE :search
                OR p.strength LIKE :search
                OR c.category_name LIKE :search
            )
        ";

        $params['search'] = '%' . $search . '%';

    }


    // --------------------------------------------------------
    // SORT PRODUCTS
    // --------------------------------------------------------

    $sql .= "
        ORDER BY p.product_id DESC
    ";


    // --------------------------------------------------------
    // EXECUTE
    // --------------------------------------------------------

    $productStmt = $pdo->prepare($sql);

    $productStmt->execute($params);

    $products = $productStmt->fetchAll();

} catch (PDOException $e) {

    die(
        '<div style="
            margin:30px;
            padding:20px;
            background:#ffe5e5;
            border:1px solid #ff9999;
            color:#990000;
            font-family:Arial;
        ">
            <h3>Product Loading Error</h3>
            <p>' .
            htmlspecialchars($e->getMessage()) .
            '</p>
        </div>'
    );

}


// ============================================================
// TOTAL ACTIVE PRODUCTS
// ============================================================

$totalProducts = 0;

try {

    $countStmt = $pdo->query("
        SELECT COUNT(*)
        FROM products
        WHERE status = 'active'
    ");

    $totalProducts = (int)$countStmt->fetchColumn();

} catch (PDOException $e) {

    $totalProducts = 0;

}


// ============================================================
// LOAD EXISTING HEADER
// ============================================================
//
// Actual file:
//
// C:\wamp64\www\MediQuick-Pharmacy\includes\header.php
//
// ============================================================

$headerFile = $projectDir . '/includes/header.php';

if (!file_exists($headerFile)) {

    die(
        '<div style="
            margin:30px;
            padding:20px;
            background:#ffe5e5;
            border:1px solid #ff9999;
            color:#990000;
            font-family:Arial;
        ">
            <h3>Header File Not Found</h3>
            <p>Expected file:</p>
            <strong>' .
            htmlspecialchars($headerFile) .
            '</strong>
        </div>'
    );

}

require_once $headerFile;

?>


<!-- ============================================================
     SHOP PAGE
============================================================ -->

<div class="container-fluid py-5">

    <div class="container py-5">

        <div class="row g-4">


            <!-- ====================================================
                 SIDEBAR
            ===================================================== -->

            <div class="col-lg-3">

                <div class="bg-light rounded p-4">


                    <!-- =================================================
                         CATEGORIES
                    ================================================== -->

                    <h4 class="mb-4">
                        Product Categories
                    </h4>


                    <div class="category-list">


                        <!-- ALL PRODUCTS -->

                        <a
                            href="shop.php"
                            class="d-flex justify-content-between align-items-center py-2 border-bottom text-decoration-none"
                        >

                            <span>

                                <i class="fas fa-pills me-2"></i>

                                All Products

                            </span>

                            <span>

                                <?= $totalProducts ?>

                            </span>

                        </a>


                        <!-- DATABASE CATEGORIES -->

                        <?php foreach ($categories as $category): ?>

                            <a
                                href="shop.php?category=<?= (int)$category['category_id'] ?>"
                                class="d-block py-2 border-bottom text-decoration-none"
                            >

                                <i class="fas fa-capsules me-2"></i>

                                <?= htmlspecialchars(
                                    $category['category_name']
                                ) ?>

                            </a>

                        <?php endforeach; ?>


                    </div>


                    <!-- =================================================
                         SEARCH
                    ================================================== -->

                    <h4 class="mt-5 mb-3">
                        Search Products
                    </h4>


                    <form
                        action="shop.php"
                        method="GET"
                    >


                        <?php if ($categoryId > 0): ?>

                            <input
                                type="hidden"
                                name="category"
                                value="<?= $categoryId ?>"
                            >

                        <?php endif; ?>


                        <div class="input-group">


                            <input
                                type="text"
                                name="search"
                                class="form-control"
                                placeholder="Search medicine..."
                                value="<?= htmlspecialchars($search) ?>"
                            >


                            <button
                                type="submit"
                                class="btn btn-primary"
                            >

                                <i class="fas fa-search"></i>

                            </button>


                        </div>

                    </form>


                    <!-- CLEAR SEARCH -->

                    <?php if (
                        $search !== '' ||
                        $categoryId > 0
                    ): ?>

                        <a
                            href="shop.php"
                            class="btn btn-outline-secondary btn-sm w-100 mt-3"
                        >

                            <i class="fas fa-times me-1"></i>

                            Clear Filters

                        </a>

                    <?php endif; ?>


                </div>

            </div>


            <!-- ====================================================
                 PRODUCT SECTION
            ===================================================== -->

            <div class="col-lg-9">


                <!-- =================================================
                     PAGE TITLE
                ================================================== -->

                <div
                    class="d-flex justify-content-between align-items-center mb-4"
                >


                    <div>

                        <h2 class="mb-1">
                            Medicines & Products
                        </h2>


                        <p class="text-muted mb-0">

                            <?php if ($search !== ''): ?>

                                Search results for:

                                <strong>
                                    "<?= htmlspecialchars($search) ?>"
                                </strong>

                            <?php elseif ($categoryId > 0): ?>

                                Products from selected category

                            <?php else: ?>

                                Available medicines

                            <?php endif; ?>

                        </p>

                    </div>


                    <!-- PRODUCT COUNT -->

                    <span class="badge bg-primary fs-6">

                        <?= count($products) ?>

                        Products

                    </span>


                </div>


                <!-- =================================================
                     PRODUCTS GRID
                ================================================== -->

                <div class="row g-4">


                    <?php if (empty($products)): ?>


                        <!-- =============================================
                             NO PRODUCTS
                        ============================================== -->

                        <div class="col-12">

                            <div
                                class="bg-light rounded text-center p-5"
                            >

                                <i
                                    class="fas fa-pills fa-4x mb-4 text-primary"
                                ></i>


                                <h3>
                                    No Products Found
                                </h3>


                                <p class="text-muted">

                                    No active medicines were found
                                    matching your search.

                                </p>


                                <a
                                    href="shop.php"
                                    class="btn btn-primary"
                                >

                                    View All Products

                                </a>


                            </div>

                        </div>


                    <?php else: ?>


                        <!-- =============================================
                             PRODUCT LOOP
                        ============================================== -->

                        <?php foreach ($products as $product): ?>


                            <div class="col-md-6 col-xl-4">


                                <div
                                    class="card h-100 border-0 shadow-sm"
                                >


                                    <!-- ==================================
                                         PRODUCT IMAGE
                                    =================================== -->

                                    <div
                                        class="d-flex justify-content-center align-items-center bg-light"
                                        style="height:200px;"
                                    >

                                        <i
                                            class="fas fa-pills fa-4x text-primary"
                                        ></i>

                                    </div>


                                    <!-- ==================================
                                         PRODUCT BODY
                                    =================================== -->

                                    <div
                                        class="card-body d-flex flex-column"
                                    >


                                        <!-- CATEGORY -->

                                        <small
                                            class="text-muted mb-2"
                                        >

                                            <i
                                                class="fas fa-tag me-1"
                                            ></i>

                                            <?= htmlspecialchars(
                                                $product['category_name']
                                                ?? 'General'
                                            ) ?>

                                        </small>


                                        <!-- ==================================
                                             PRODUCT NAME FROM DATABASE
                                        =================================== -->

                                        <h5 class="card-title">

                                            <?= htmlspecialchars(
                                                $product['product_name']
                                            ) ?>

                                        </h5>


                                        <!-- SKU -->

                                        <?php if (
                                            !empty($product['sku'])
                                        ): ?>

                                            <small
                                                class="text-muted mb-2"
                                            >

                                                SKU:

                                                <?= htmlspecialchars(
                                                    $product['sku']
                                                ) ?>

                                            </small>

                                        <?php endif; ?>


                                        <!-- DESCRIPTION -->

                                        <?php if (
                                            !empty($product['description'])
                                        ): ?>

                                            <p
                                                class="card-text text-muted"
                                            >

                                                <?= htmlspecialchars(
                                                    $product['description']
                                                ) ?>

                                            </p>

                                        <?php endif; ?>


                                        <!-- STRENGTH -->

                                        <?php if (
                                            !empty($product['strength'])
                                        ): ?>

                                            <p class="mb-1">

                                                <strong>
                                                    Strength:
                                                </strong>

                                                <?= htmlspecialchars(
                                                    $product['strength']
                                                ) ?>

                                            </p>

                                        <?php endif; ?>


                                        <!-- DOSAGE FORM -->

                                        <?php if (
                                            !empty($product['dosage_form'])
                                        ): ?>

                                            <p class="mb-2">

                                                <strong>
                                                    Form:
                                                </strong>

                                                <?= htmlspecialchars(
                                                    $product['dosage_form']
                                                ) ?>

                                            </p>

                                        <?php endif; ?>


                                        <!-- PRESCRIPTION STATUS -->

                                        <div class="mb-3">


                                            <?php if (
                                                (int)$product[
                                                    'requires_prescription'
                                                ] === 1
                                            ): ?>


                                                <span
                                                    class="badge bg-warning text-dark"
                                                >

                                                    <i
                                                        class="fas fa-file-prescription me-1"
                                                    ></i>

                                                    Prescription Required

                                                </span>


                                            <?php else: ?>


                                                <span
                                                    class="badge bg-success"
                                                >

                                                    <i
                                                        class="fas fa-check me-1"
                                                    ></i>

                                                    No Prescription

                                                </span>


                                            <?php endif; ?>


                                        </div>


                                        <!-- ==================================
                                             PRICE + VIEW BUTTON
                                        =================================== -->

                                        <div
                                            class="d-flex justify-content-between align-items-center mt-auto pt-3"
                                        >


                                            <!-- PRICE -->

                                            <h5
                                                class="text-primary mb-0"
                                            >

                                                Rs.
                                                <?= number_format(
                                                    (float)$product['unit_price'],
                                                    2
                                                ) ?>

                                            </h5>


                                            <!-- VIEW BUTTON -->

                                            <a
                                                href="product-details.php?id=<?= (int)$product['product_id'] ?>"
                                                class="btn btn-primary btn-sm"
                                            >

                                                <i
                                                    class="fas fa-eye me-1"
                                                ></i>

                                                View

                                            </a>


                                        </div>


                                    </div>


                                </div>


                            </div>


                        <?php endforeach; ?>


                    <?php endif; ?>


                </div>


            </div>


        </div>

    </div>

</div>


<?php

// ============================================================
// LOAD FOOTER IF AVAILABLE
// ============================================================

$footerFile = $projectDir . '/includes/footer.php';

if (file_exists($footerFile)) {

    require_once $footerFile;

}

?>