<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('../includes/db.php');

$currentPage = basename($_SERVER['PHP_SELF'] ?? '');

/* Load the real active categories from the database. */
$headerCategories = [];
$headerCategoryQuery = $conn->query("
    SELECT category_id, category_name
    FROM categories
    WHERE status = 'active'
    ORDER BY category_name ASC
");

if ($headerCategoryQuery) {
    while ($category = $headerCategoryQuery->fetch_assoc()) {
        $headerCategories[] = $category;
    }
}

$headerSelectedCategoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;

/* Calculate the current cart balance using the logged-in customer.
   Guest carts use customer_id = 0 in the current database structure. */
$headerCustomerId = !empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$headerCartTotal = 0.00;

$headerCartStmt = $conn->prepare("
    SELECT COALESCE(SUM(
        ci.quantity * (p.unit_price - (p.unit_price * COALESCE(p.discount_percent, 0) / 100))
    ), 0) AS cart_total
    FROM carts c
    LEFT JOIN cart_items ci ON ci.cart_id = c.cart_id
    LEFT JOIN products p ON p.product_id = ci.product_id
    WHERE c.customer_id = ?
");

if ($headerCartStmt) {
    $headerCartStmt->bind_param("i", $headerCustomerId);
    $headerCartStmt->execute();
    $headerCartResult = $headerCartStmt->get_result();

    if ($headerCartRow = $headerCartResult->fetch_assoc()) {
        $headerCartTotal = (float)$headerCartRow['cart_total'];
    }

    $headerCartStmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>MediQuick Pharmacy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MediQuick Pharmacy">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&family=Roboto:wght@400;500;700;800&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Template CSS -->
    <link href="assets/lib/animate/animate.min.css" rel="stylesheet">
    <link href="assets/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">

    <?php if (!empty($page_css)): ?>
        <link href="assets/css/<?= htmlspecialchars($page_css, ENT_QUOTES, 'UTF-8') ?>" rel="stylesheet">
    <?php endif; ?>

    <!-- =========================================================
         MEDIQUICK HEADER THEME
         Palette:
         #2720FF  Deep Blue
         #006AFF  Blue
         #008DFF  Bright Blue
         #00A3FF  Main Blue
         #00B4DA  Cyan
         #00C391  Green
    ========================================================== -->
    <style>
        :root {
            --mq-deep: #2720FF;
            --mq-blue-dark: #006AFF;
            --mq-blue: #00A3FF;
            --mq-cyan: #00B4DA;
            --mq-green: #00C391;
            --mq-light: #F2FAFF;
            --mq-border: #DCECF6;
            --mq-text: #334B5C;
            --mq-muted: #718394;
        }

        body {
            font-family: 'Open Sans', sans-serif;
            color: var(--mq-text);
            background: #fff;
        }

        /* ---------- Top mini bar ---------- */
        .mq-topbar {
            background: #fff;
            border-bottom: 1px solid var(--mq-border);
        }

        .mq-topbar-link {
            color: var(--mq-muted) !important;
            text-decoration: none;
            font-size: 14px;
            transition: .2s ease;
        }

        .mq-topbar-link:hover {
            color: var(--mq-blue) !important;
        }

        .mq-dashboard-toggle {
            font-weight: 600;
        }

        /* ---------- Main header ---------- */
        .mq-main-header {
            background: #fff;
            padding: 22px 0;
        }

        .mq-logo {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        }

        .mq-logo img {
            max-height: 76px;
            width: auto;
            transition: transform .25s ease;
        }

        .mq-logo:hover img {
            transform: scale(1.03);
        }

        /* ---------- Search ---------- */
        .mq-search {
            display: flex;
            align-items: stretch;
            background: #fff;
            border: 1px solid var(--mq-border);
            border-radius: 50px;
            overflow: hidden;
            box-shadow: 0 7px 24px rgba(0, 163, 255, .09);
        }

        .mq-search input {
            min-width: 0;
            flex: 1;
            border: 0 !important;
            outline: 0 !important;
            box-shadow: none !important;
            padding: 14px 20px;
            color: var(--mq-text);
        }

        .mq-search input::placeholder {
            color: #9aa9b5;
        }

        .mq-search select {
            width: 175px;
            border: 0;
            border-left: 1px solid var(--mq-border);
            outline: 0;
            color: var(--mq-text);
            background: #fff;
            padding-left: 15px;
            cursor: pointer;
        }

        .mq-search-btn {
            width: 58px;
            border: 0;
            color: #fff !important;
            background: var(--mq-blue) !important;
            transition: .25s ease;
        }

        .mq-search-btn:hover {
            background: var(--mq-blue-dark) !important;
            transform: none;
        }

        /* ---------- Header icons ---------- */
        .mq-quick-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
        }

        .mq-action {
            width: 48px;
            height: 48px;
            border: 1px solid var(--mq-border);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--mq-blue) !important;
            background: #fff;
            text-decoration: none;
            transition: .25s ease;
            box-shadow: 0 4px 14px rgba(0, 163, 255, .06);
        }

        .mq-action:hover {
            color: #fff !important;
            background: var(--mq-blue) !important;
            border-color: var(--mq-blue);
            transform: translateY(-2px);
        }

        .mq-cart-info {
            color: var(--mq-text);
            font-size: 14px;
            font-weight: 700;
            margin-left: 2px;
        }

        /* ---------- Navigation ---------- */
        .mq-navbar-wrap {
            background: linear-gradient(100deg, var(--mq-deep) 0%, var(--mq-blue-dark) 35%, var(--mq-blue) 68%, var(--mq-green) 100%);
            box-shadow: 0 7px 22px rgba(0, 118, 220, .20);
        }

        .mq-navbar {
            background: transparent !important;
            min-height: 66px;
        }

        .mq-nav-link {
            position: relative;
            color: #fff !important;
            font-weight: 600;
            padding: 22px 18px !important;
            transition: .2s ease;
        }

        .mq-nav-link::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: 11px;
            width: 0;
            height: 3px;
            border-radius: 10px;
            background: #fff;
            transform: translateX(-50%);
            transition: width .25s ease;
        }

        .mq-nav-link:hover,
        .mq-nav-link.active {
            color: #fff !important;
        }

        .mq-nav-link:hover::after,
        .mq-nav-link.active::after {
            width: 28px;
        }

        /* ---------- All Categories dropdown ---------- */
        .mq-all-categories {
            position: relative;
        }

        .mq-all-categories > .mq-nav-link {
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .mq-all-categories > .dropdown-menu {
            min-width: 270px;
            max-height: 420px;
            overflow-y: auto;
            margin-top: 0 !important;
        }

        .mq-all-categories > .dropdown-menu .dropdown-item {
            color: var(--mq-text);
            padding: 10px 13px;
            border-radius: 9px;
            transition: .2s ease;
        }

        .mq-all-categories > .dropdown-menu .dropdown-item:hover,
        .mq-all-categories > .dropdown-menu .dropdown-item.active {
            color: var(--mq-blue);
            background: var(--mq-light);
        }

        @media (min-width: 992px) {
            .mq-all-categories:hover > .dropdown-menu {
                display: block;
            }

            .mq-all-categories > .dropdown-menu {
                left: 0;
                top: 100%;
            }
        }

                /* ---------- Phone button ---------- */
        .mq-phone-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #fff !important;
            color: var(--mq-blue) !important;
            border: 0 !important;
            font-weight: 700;
            padding: 10px 18px !important;
            border-radius: 50px !important;
            box-shadow: 0 5px 15px rgba(0,0,0,.10);
            transition: .25s ease;
        }

        .mq-phone-btn:hover {
            color: #fff !important;
            background: var(--mq-blue-dark) !important;
            transform: translateY(-2px);
        }

        /* ---------- Category dropdown ---------- */
        .mq-category-menu {
            border: 0 !important;
            border-radius: 0 0 14px 14px !important;
            box-shadow: 0 15px 30px rgba(25,75,105,.16) !important;
            padding: 8px !important;
            background: #fff !important;
        }

        .mq-category-menu a {
            color: var(--mq-text) !important;
            padding: 10px 13px !important;
            border-radius: 9px;
            text-decoration: none;
            display: flex;
            justify-content: space-between;
            gap: 15px;
            transition: .2s ease;
        }

        .mq-category-menu a:hover {
            color: var(--mq-blue) !important;
            background: var(--mq-light) !important;
        }

        .mq-category-count {
            color: var(--mq-muted);
            font-size: 12px;
        }

        /* ---------- Dashboard Login / Logout ---------- */
        .mq-category-menu .dropdown-divider {
            margin: 6px 8px;
            border-color: var(--mq-border);
        }

        .mq-category-menu .text-danger {
            color: #dc3545 !important;
        }

        .mq-category-menu .text-danger:hover {
            color: #fff !important;
            background: #dc3545 !important;
        }

        /* ---------- Mobile ---------- */
        .mq-mobile-logo img {
            max-height: 48px;
            width: auto;
        }

        .mq-toggler {
            border: 1px solid rgba(255,255,255,.65) !important;
            color: #fff !important;
            border-radius: 10px !important;
            padding: 8px 11px !important;
        }

        .mq-toggler:focus {
            box-shadow: none !important;
        }

        @media (max-width: 991.98px) {
            .mq-main-header {
                padding: 15px 0;
            }

            .mq-search {
                margin-top: 15px;
                border-radius: 14px;
            }

            .mq-search select {
                width: 130px;
            }

            .mq-quick-actions {
                justify-content: center;
                margin-top: 15px;
            }

            .mq-navbar {
                padding: 8px 0;
            }

            .mq-nav-link {
                padding: 11px 14px !important;
            }

            .mq-nav-link::after {
                display: none;
            }

            .mq-phone-btn {
                margin: 10px 14px 5px;
            }
        }

        @media (max-width: 575.98px) {
            .mq-search select {
                display: none;
            }

            .mq-search input {
                padding-left: 15px;
            }

            .mq-action {
                width: 43px;
                height: 43px;
            }
        }
    </style>
</head>

<body>

    <!-- Loading Spinner -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center" style="z-index:9999;">
        <div class="spinner-border" style="width:3rem;height:3rem;color:#00A3FF;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    <!-- ================= TOP BAR ================= -->
    <div class="container-fluid mq-topbar px-5 d-none d-lg-block">
        <div class="row align-items-center" style="height:45px;">
            <div class="col-lg-7">
                <span class="mq-topbar-link">
                    <i class="fas fa-heart me-2" style="color:#00C391;"></i>
                    Your trusted online pharmacy
                </span>
            </div>

            <div class="col-lg-5 text-end">
                <div class="dropdown d-inline-block">
                    <a href="#" class="dropdown-toggle mq-topbar-link mq-dashboard-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-2"></i> My Dashboard
                    </a>

                    <div class="dropdown-menu dropdown-menu-end mq-category-menu mt-2">

                        <!-- My Account - Always visible -->
                        <a href="manageaccount.php" class="dropdown-item <?= $currentPage === 'manageaccount.php' ? 'active' : '' ?>">
                            <span><i class="fas fa-user me-2"></i> My Account</span>
                        </a>

                        <!-- My Cart - Always visible -->
                        <a href="cart.php" class="dropdown-item <?= $currentPage === 'cart.php' ? 'active' : '' ?>">
                            <span><i class="fas fa-shopping-cart me-2"></i> My Cart</span>
                        </a>

                        <div class="dropdown-divider"></div>

                        <?php if (!empty($_SESSION['user_id'])): ?>

                            <!-- Logout when logged in -->
                            <a href="logout.php" class="dropdown-item text-danger">
                                <span><i class="fas fa-sign-out-alt me-2"></i> Logout</span>
                            </a>

                        <?php else: ?>

                            <!-- Login when logged out -->
                            <a href="login.php" class="dropdown-item">
                                <span><i class="fas fa-sign-in-alt me-2"></i> Login</span>
                            </a>

                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= MAIN HEADER ================= -->
    <header class="mq-main-header">
        <div class="container-fluid px-4 px-lg-5">
            <div class="row align-items-center">

                <!-- Logo -->
                <div class="col-lg-3 col-md-4 text-center text-md-start">
                    <a href="index.php" class="mq-logo">
                        <img src="assets/img/medi-quick-logo.png" alt="MediQuick Pharmacy">
                    </a>
                </div>

                <!-- Search -->
                <div class="col-lg-6 col-md-8 mt-3 mt-md-0">
                    <form action="shop.php" method="get" class="mq-search" id="mqCategorySearchForm">
                        <input type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="Search medicines, health products..." aria-label="Search products">

                        <select name="category" aria-label="Select category" onchange="this.form.submit()">
                            <option value="0">All Categories</option>
                            <?php foreach ($headerCategories as $headerCategory): ?>
                                <option value="<?= (int)$headerCategory['category_id'] ?>"
                                    <?= $headerSelectedCategoryId === (int)$headerCategory['category_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($headerCategory['category_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <button type="submit" class="mq-search-btn" aria-label="Search">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>

                <!-- Quick actions -->
                <div class="col-lg-3 d-none d-lg-block">
                    <div class="mq-quick-actions">
                        <a href="cart.php" class="mq-action" title="Shopping Cart">
                            <i class="fas fa-shopping-cart"></i>
                        </a>
                        <span class="mq-cart-info">
                            Balance: $<?= number_format($headerCartTotal, 2) ?>
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </header>

    <!-- ================= NAVIGATION ================= -->
    <div class="mq-navbar-wrap">
        <div class="container-fluid px-3 px-lg-5">
            <nav class="navbar navbar-expand-lg mq-navbar navbar-dark">

                <!-- Mobile logo -->
                <a href="index.php" class="navbar-brand mq-mobile-logo d-lg-none">
                    <img src="assets/img/medi-quick-logo.png" alt="MediQuick Pharmacy">
                </a>

                <!-- Mobile menu button -->
                <button class="navbar-toggler mq-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <div class="navbar-nav me-auto align-items-lg-center">
                        <!-- All Categories -->
                        <div class="nav-item dropdown mq-all-categories">
                            <a href="shop.php"
                               class="nav-link mq-nav-link dropdown-toggle <?= $headerSelectedCategoryId > 0 ? 'active' : '' ?>"
                               id="allCategoriesDropdown"
                               role="button"
                               data-bs-toggle="dropdown"
                               aria-expanded="false">
                                <i class="fas fa-th-large me-1"></i> All Categories
                            </a>

                            <div class="dropdown-menu mq-category-menu" aria-labelledby="allCategoriesDropdown">
                                <a href="shop.php?category=0"
                                   class="dropdown-item <?= $headerSelectedCategoryId === 0 && $currentPage === 'shop.php' ? 'active' : '' ?>">
                                    <i class="fas fa-layer-group me-2"></i> All Categories
                                </a>

                                <div class="dropdown-divider"></div>

                                <?php if (!empty($headerCategories)): ?>
                                    <?php foreach ($headerCategories as $headerCategory): ?>
                                        <a href="shop.php?category=<?= (int)$headerCategory['category_id'] ?>"
                                           class="dropdown-item <?= $headerSelectedCategoryId === (int)$headerCategory['category_id'] ? 'active' : '' ?>">
                                            <i class="fas fa-chevron-right me-2" style="font-size:10px;"></i>
                                            <?= htmlspecialchars($headerCategory['category_name']) ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="dropdown-item text-muted">No categories available</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <a href="index.php" class="nav-item nav-link mq-nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>">
                            <i class="fas fa-home me-1"></i> Home
                        </a>
                        <a href="shop.php" class="nav-item nav-link mq-nav-link <?= $currentPage === 'shop.php' ? 'active' : '' ?>">
                            <i class="fas fa-pills me-1"></i> Shop
                        </a>
                        <a href="cart.php" class="nav-item nav-link mq-nav-link <?= $currentPage === 'cart.php' ? 'active' : '' ?>">
                            <i class="fas fa-shopping-cart me-1"></i> Cart
                        </a>
                        <a href="manageaccount.php" class="nav-item nav-link mq-nav-link <?= $currentPage === 'manageaccount.php' ? 'active' : '' ?>">
                            <i class="fas fa-user me-1"></i> My Account
                        </a>
                    </div>

                </div>
            </nav>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>