<?php
require_once('../includes/db.php');

// Detect the current public page so the correct navigation item is active.
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Medi Quick Pharmacy</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap"
        rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet"
        href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css"
        rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="assets/lib/animate/animate.min.css" rel="stylesheet">
    <link href="assets/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="assets/css/style.css" rel="stylesheet">


    <!-- ============================= -->
    <!-- MEDIQUICK #00A3FF THEME -->
    <!-- ============================= -->

    <style>
        :root {
            --mediquick-primary: #00A3FF;
            --mediquick-hover: #008DFF;
            --mediquick-light: #EAF8FF;
            --mediquick-text: #4d6270;
        }

        /* Main Bootstrap primary colour */
        .bg-primary {
            background-color: #00A3FF !important;
        }

        .text-primary {
            color: #00A3FF !important;
        }

        .border-primary {
            border-color: #00A3FF !important;
        }

        .btn-primary {
            background-color: #00A3FF !important;
            border-color: #00A3FF !important;
            color: #ffffff !important;
        }

        .btn-primary:hover,
        .btn-primary:focus,
        .btn-primary:active {
            background-color: #008DFF !important;
            border-color: #008DFF !important;
        }

        /* Top bar */
        .topbar {
            background: #ffffff !important;
            border-bottom: 1px solid #dff3ff !important;
        }

        .topbar a:hover {
            color: #00A3FF !important;
        }

        /* Search button */
        .btn-search,
        .search-btn {
            background: #00A3FF !important;
            border-color: #00A3FF !important;
            color: #ffffff !important;
        }

        .btn-search:hover,
        .search-btn:hover {
            background: #008DFF !important;
            border-color: #008DFF !important;
        }

        /* Navigation bar */
        .nav-bar,
        .navbar,
        .navbar.bg-primary {
            background: #00A3FF !important;
        }

        .navbar a,
        .nav-bar a {
            color: #ffffff !important;
        }

        .navbar a:hover,
        .nav-bar a:hover {
            color: #EAF8FF !important;
        }

        /* Categories */
        .all-categories,
        .category-header {
            background: #00A3FF !important;
            color: #ffffff !important;
        }

        /* Active menu */
        .navbar .active,
        .nav-bar .active {
            color: #ffffff !important;
            font-weight: 700;
        }

        /* Header icons */
        .header-icons i,
        .header-icon i {
            color: #00A3FF !important;
        }

        .header-icons a:hover i,
        .header-icon:hover i {
            color: #008DFF !important;
        }

        /* Contact button */
        .contact-btn,
        .header-contact {
            background: #00A3FF !important;
            border-color: #00A3FF !important;
            color: #ffffff !important;
        }

        .contact-btn:hover,
        .header-contact:hover {
            background: #008DFF !important;
            border-color: #008DFF !important;
        }

        /* Dropdown */
        .dropdown-menu {
            border: 1px solid #dff3ff !important;
        }

        .dropdown-menu a {
            color: var(--mediquick-text) !important;
        }

        .dropdown-menu a:hover {
            background: #EAF8FF !important;
            color: #00A3FF !important;
        }

        /* Spinner */
        #spinner .spinner-border {
            color: #00A3FF !important;
        }
    </style>


    <!-- Attractive MediQuick #00A3FF Design -->
    <style>
        :root {
            --mq-blue: #00A3FF;
            --mq-blue-dark: #008DFF;
            --mq-blue-deep: #006AFF;
            --mq-light: #F2FAFF;
            --mq-border: #DDEFF8;
            --mq-text: #334B5C;
        }

        body {
            background: #ffffff;
            color: var(--mq-text);
        }

        /* Top information bar */
        .topbar,
        .top-info,
        .header-top {
            background: #ffffff !important;
            border-bottom: 1px solid var(--mq-border) !important;
        }

        .topbar a,
        .top-info a,
        .header-top a {
            color: #607586 !important;
            transition: color .2s ease;
        }

        .topbar a:hover,
        .top-info a:hover,
        .header-top a:hover {
            color: var(--mq-blue) !important;
        }

        /* Main header */
        .header,
        .header-section,
        .main-header {
            background: #ffffff !important;
        }

        /* Logo area */
        .logo img,
        .navbar-brand img,
        .brand-logo img {
            transition: transform .25s ease;
        }

        .logo img:hover,
        .navbar-brand img:hover,
        .brand-logo img:hover {
            transform: scale(1.04);
        }

        /* Search box - clean modern pill */
        .search-box,
        .search-form,
        .search-area {
            border: 1px solid #D6EAF5 !important;
            border-radius: 16px !important;
            background: #ffffff !important;
            box-shadow: 0 6px 22px rgba(0, 163, 255, .08) !important;
            overflow: hidden;
        }

        .search-box input,
        .search-form input,
        .search-area input,
        .search-input {
            border: 0 !important;
            outline: 0 !important;
            background: #ffffff !important;
        }

        .search-box input:focus,
        .search-form input:focus,
        .search-area input:focus,
        .search-input:focus {
            box-shadow: none !important;
        }

        .btn-search,
        .search-btn {
            background: var(--mq-blue) !important;
            border: 0 !important;
            color: #ffffff !important;
            transition: all .25s ease;
        }

        .btn-search:hover,
        .search-btn:hover {
            background: var(--mq-blue-dark) !important;
            transform: translateY(-1px);
        }

        /* Header utility icons */
        .header-icons a,
        .header-icon,
        .header-icons .icon {
            transition: all .25s ease;
        }

        .header-icons a:hover,
        .header-icon:hover,
        .header-icons .icon:hover {
            color: var(--mq-blue) !important;
            transform: translateY(-2px);
        }

        .header-icons i,
        .header-icon i {
            color: var(--mq-blue) !important;
        }

        /* Main navigation */
        .nav-bar,
        .navbar,
        .main-navigation,
        .navbar.bg-primary {
            background: var(--mq-blue) !important;
            box-shadow: 0 5px 18px rgba(0, 163, 255, .22) !important;
        }

        .navbar a,
        .nav-bar a,
        .main-navigation a {
            color: #ffffff !important;
            transition: all .2s ease;
        }

        .navbar a:hover,
        .nav-bar a:hover,
        .main-navigation a:hover {
            color: #ffffff !important;
        }

        /* Navigation links */
        .navbar-nav > li > a,
        .nav-bar .nav-link {
            position: relative;
        }

        .navbar-nav > li > a::after,
        .nav-bar .nav-link::after {
            content: "";
            position: absolute;
            left: 50%;
            bottom: 5px;
            width: 0;
            height: 3px;
            border-radius: 10px;
            background: #ffffff;
            transform: translateX(-50%);
            transition: width .25s ease;
        }

        .navbar-nav > li > a:hover::after,
        .navbar-nav > li.active > a::after,
        .nav-bar .nav-link:hover::after,
        .nav-bar .nav-link.active::after {
            width: 24px;
        }

        /* All Categories */
        .all-categories,
        .category-header,
        .categories-menu {
            background: var(--mq-blue-dark) !important;
            color: #ffffff !important;
        }

        .all-categories:hover,
        .category-header:hover,
        .categories-menu:hover {
            background: var(--mq-blue-deep) !important;
        }

        /* Contact button */
        .contact-btn,
        .header-contact {
            background: #ffffff !important;
            color: var(--mq-blue) !important;
            border: 2px solid rgba(255,255,255,.8) !important;
            border-radius: 50px !important;
            box-shadow: 0 5px 15px rgba(0,0,0,.08);
            transition: all .25s ease;
        }

        .contact-btn:hover,
        .header-contact:hover {
            background: var(--mq-blue-dark) !important;
            color: #ffffff !important;
            border-color: var(--mq-blue-dark) !important;
            transform: translateY(-2px);
        }

        /* Dropdown menus */
        .dropdown-menu {
            border: 0 !important;
            border-radius: 12px !important;
            box-shadow: 0 12px 30px rgba(25, 75, 105, .14) !important;
            padding: 8px !important;
        }

        .dropdown-menu a {
            border-radius: 8px !important;
            color: var(--mq-text) !important;
            transition: all .2s ease;
        }

        .dropdown-menu a:hover {
            background: var(--mq-light) !important;
            color: var(--mq-blue) !important;
            padding-left: 18px !important;
        }

        /* Primary buttons across the template */
        .btn-primary,
        .bg-primary {
            background-color: var(--mq-blue) !important;
            border-color: var(--mq-blue) !important;
        }

        .btn-primary:hover {
            background-color: var(--mq-blue-dark) !important;
            border-color: var(--mq-blue-dark) !important;
        }

        /* Spinner */
        #spinner .spinner-border {
            color: var(--mq-blue) !important;
        }

        /* Mobile */
        @media (max-width: 991.98px) {
            .navbar-nav > li > a::after,
            .nav-bar .nav-link::after {
                display: none;
            }

            .search-box,
            .search-form,
            .search-area {
                border-radius: 12px !important;
            }
        }
    </style>

</head>

<body>

    <!-- Spinner Start -->
    <div id="spinner"
        class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">

        <div class="spinner-border"
            style="width: 3rem; height: 3rem;"
            role="status">

            <span class="sr-only">Loading...</span>

        </div>

    </div>
    <!-- Spinner End -->

    <!-- Topbar Start -->
    <div class="container-fluid px-5 d-none border-bottom d-lg-block">
        <div class="row gx-0 align-items-center">
            <div class="col-lg-8 text-center text-lg-start mb-lg-0">

            </div>


            <div class="col-lg-4 text-center text-lg-end">
                <div class="d-inline-flex align-items-center" style="height: 45px;">

                    <div class="dropdown">
                        <a href="#" class="dropdown-toggle text-muted ms-2" data-bs-toggle="dropdown"><small><i
                                    class="fa fa-home me-2"></i> My Dashboard</small></a>
                        <div class="dropdown-menu rounded">
                            <a href="#" class="dropdown-item"> Login</a>
                            <a href="#" class="dropdown-item"> Wishlist</a>
                            <a href="cart.php" class="dropdown-item"> My Cart</a>
                            <a href="#" class="dropdown-item"> Notifications</a>
                            <a href="#" class="dropdown-item"> Account Settings</a>
                            <a href="manageaccount.php" class="dropdown-item <?= $currentPage === 'manageaccount.php' ? 'active' : '' ?>"> My Account</a>
                            <a href="#" class="dropdown-item"> Log Out</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid px-5 py-4 d-none d-lg-block">
        <div class="row gx-0 align-items-center text-center">
            <div class="col-md-4 col-lg-3 text-center text-lg-start">
                <div class="d-inline-flex align-items-center">
                    <a href="" class="navbar-brand p-0">
                        <img src="assets/img/medi-quick-logo.png"
                             alt="Medi Quick Pharmacy"
                             style="max-height: 75px; width: auto;">
                    </a>
                </div>
            </div>
            <div class="col-md-4 col-lg-6 text-center">
                <div class="position-relative ps-4">
                    <div class="d-flex border rounded-pill">
                        <input class="form-control border-0 rounded-pill w-100 py-3" type="text"
                            data-bs-target="#dropdownToggle123" placeholder="Search Looking For?">
                        <select class="form-select text-dark border-0 border-start rounded-0 p-3" style="width: 200px;">
                            <option value="All Category">All Category</option>
                            <option value="Pest Control-2">Category 1</option>
                            <option value="Pest Control-3">Category 2</option>
                            <option value="Pest Control-4">Category 3</option>
                            <option value="Pest Control-5">Category 4</option>
                        </select>
                        <button type="button" class="btn btn-primary rounded-pill py-3 px-5" style="border: 0;"><i
                                class="fas fa-search"></i></button>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3 text-center text-lg-end">
                <div class="d-inline-flex align-items-center">
                    <a href="#" class="text-muted d-flex align-items-center justify-content-center me-3"><span
                            class="rounded-circle btn-md-square border"><i class="fas fa-random"></i></i></a>
                    <a href="#" class="text-muted d-flex align-items-center justify-content-center me-3"><span
                            class="rounded-circle btn-md-square border"><i class="fas fa-heart"></i></a>
                    <a href="#" class="text-muted d-flex align-items-center justify-content-center"><span
                            class="rounded-circle btn-md-square border"><i class="fas fa-shopping-cart"></i></span>
                        <span class="text-dark ms-2">$0.00</span></a>
                </div>
            </div>
        </div>
    </div>
    <!-- Topbar End -->

    <!-- Navbar & Hero Start -->
    <div class="container-fluid nav-bar p-0">
        <div class="row gx-0 bg-primary px-5 align-items-center" style="background-color:#00C391!important;">
            <div class="col-lg-3 d-none d-lg-block">
                <nav class="navbar navbar-light position-relative" style="width: 250px;">
                   
                    <div class="collapse navbar-collapse rounded-bottom" id="allCat">
                        <div class="navbar-nav ms-auto py-0">
                            <ul class="list-unstyled categories-bars">
                                <li>
                                    <div class="categories-bars-item">
                                        <a href="#">Health & Wellness</a>
                                        <span>(3)</span>
                                    </div>
                                </li>
                                <li>
                                    <div class="categories-bars-item">
                                        <a href="#">Medicines</a>
                                        <span>(5)</span>
                                    </div>
                                </li>
                                <li>
                                    <div class="categories-bars-item">
                                        <a href="#">Vitamins & Supplements</a>
                                        <span>(2)</span>
                                    </div>
                                </li>
                                <li>
                                    <div class="categories-bars-item">
                                        <a href="#">Personal Care</a>
                                        <span>(8)</span>
                                    </div>
                                </li>
                                <li>
                                    <div class="categories-bars-item">
                                        <a href="#">Medical Supplies</a>
                                        <span>(5)</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>
            <div class="col-12 col-lg-9">
                <nav class="navbar navbar-expand-lg navbar-light bg-primary " style="background-color:#00C391!important;">
                    <a href="" class="navbar-brand d-block d-lg-none">
                        <img src="assets/img/medi-quick-logo.png"
                             alt="Medi Quick Pharmacy"
                             style="max-height: 55px; width: auto;">
                    </a>
                    <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarCollapse">
                        <span class="fa fa-bars fa-1x"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarCollapse">
                        <div class="navbar-nav ms-auto py-0">
                            <a href="index.php" class="nav-item nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>">Home</a>
                            <a href="shop.php" class="nav-item nav-link <?= $currentPage === 'shop.php' ? 'active' : '' ?>">Shop</a>
                            <a href="cart.php" class="nav-item nav-link <?= $currentPage === 'cart.php' ? 'active' : '' ?>">Cart</a>
                            <a href="contact.php" class="nav-item nav-link me-2 <?= $currentPage === 'contact.php' ? 'active' : '' ?>">Contact</a>
                            <div class="nav-item dropdown d-block d-lg-none mb-3">
                                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">All Category</a>
                                <div class="dropdown-menu m-0">
                                    <ul class="list-unstyled categories-bars">
                                        <li>
                                            <div class="categories-bars-item">
                                                <a href="#">Health & Wellness</a>
                                                <span>(3)</span>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="categories-bars-item">
                                                <a href="#">Medicines</a>
                                                <span>(5)</span>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="categories-bars-item">
                                                <a href="#">Vitamins & Supplements</a>
                                                <span>(2)</span>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="categories-bars-item">
                                                <a href="#">Personal Care</a>
                                                <span>(8)</span>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="categories-bars-item">
                                                <a href="#">Medical Supplies</a>
                                                <span>(5)</span>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <a href="" class="btn btn-secondary rounded-pill py-2 px-4 px-lg-3 mb-3 mb-md-3 mb-lg-0"><i
                                class="fa fa-mobile-alt me-2"></i> +0123 456 7890</a>
                    </div>
                </nav>
            </div>
        </div>
    </div>
    <!-- Navbar & Hero End -->

