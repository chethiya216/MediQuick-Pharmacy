<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('../includes/db.php');
require_once('../includes/auth.php');
require_once('../includes/head.php');




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



<body>
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
                        <a href="manage-account.php" class="dropdown-item <?= $currentPage === 'manage-account.php' ? 'active' : '' ?>">
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
                        <?php if(isLoggedIn()): ?>
                        <a href="manage-account.php" class="nav-item nav-link mq-nav-link <?= $currentPage === 'manage-account.php' ? 'active' : '' ?>">
                            <i class="fas fa-user me-1"></i> My Account
                        </a>
                        <?php else: ?>
                        <a href="login.php" class="nav-item nav-link mq-nav-link">
                            <i class="fas fa-sign-in-alt me-1"></i> My Account
                        </a>
                        <?php endif; ?>
                    </div>

                </div>
            </nav>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>