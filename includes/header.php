<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">

    <title>MediQuick Pharmacy - Shop</title>

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="MediQuick Pharmacy online medicine store"
    >


    <!-- Google Fonts -->

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap"
        rel="stylesheet"
    >


    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"
    >


    <!-- Bootstrap Icons -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css"
        rel="stylesheet"
    >


    <!-- Bootstrap -->

    <link
        href="/MediQuick-Pharmacy/public/assets/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Animate -->

    <link
        href="/MediQuick-Pharmacy/public/assets/lib/animate/animate.min.css"
        rel="stylesheet"
    >


    <!-- Owl Carousel -->

    <link
        href="/MediQuick-Pharmacy/public/assets/lib/owlcarousel/assets/owl.carousel.min.css"
        rel="stylesheet"
    >


    <!-- Main CSS -->

    <link
        href="/MediQuick-Pharmacy/public/assets/css/style.css"
        rel="stylesheet"
    >


    <!-- =====================================================
         HEADER CUSTOM CSS
    ====================================================== -->

    <style>

        /* =================================================
           GENERAL
        ================================================= */

        body {
            margin: 0;
            padding: 0;
            font-family: 'Open Sans', sans-serif;
            color: #333;
        }


        /* =================================================
           TOP BAR
        ================================================= */

        .custom-topbar {
            height: 44px;
            border-bottom: 1px solid #ddd;
            background: #fff;
            font-size: 14px;
        }

        .custom-topbar a {
            color: #667085;
            text-decoration: none;
        }

        .custom-topbar a:hover {
            color: #f79400;
        }

        .custom-topbar .separator {
            margin: 0 8px;
            color: #aaa;
        }

        .top-call {
            color: #555;
        }

        .top-right a {
            margin-left: 15px;
        }


        /* =================================================
           MAIN HEADER
        ================================================= */

        .custom-main-header {
            min-height: 101px;
            background: #fff;
            display: flex;
            align-items: center;
        }


        /* =================================================
           ELECTRO LOGO
        ================================================= */

        .electro-logo {
            text-decoration: none;
            display: flex;
            align-items: center;
            font-family: 'Roboto', sans-serif;
            font-size: 42px;
            font-weight: 700;
            color: #f79400;
            white-space: nowrap;
        }

        .electro-logo:hover {
            color: #f79400;
        }

        .electro-logo-icon {
            width: 44px;
            height: 44px;
            background: #f79400;
            color: white;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 8px;
            font-size: 27px;
            position: relative;
        }

        .electro-logo-icon:after {
            content: "";
            position: absolute;
            width: 13px;
            height: 5px;
            background: #f79400;
            top: -5px;
            border-radius: 5px 5px 0 0;
        }


        /* =================================================
           SEARCH
        ================================================= */

        .header-search {
            height: 54px;
            border: 1px solid #ddd;
            border-radius: 30px;
            display: flex;
            align-items: center;
            overflow: hidden;
            background: white;
        }

        .header-search input {
            border: none;
            outline: none;
            box-shadow: none;
            height: 52px;
            padding-left: 20px;
            flex: 1;
            font-size: 14px;
        }

        .header-search input:focus {
            box-shadow: none;
        }

        .header-category {
            height: 52px;
            min-width: 145px;
            border: none;
            border-left: 1px solid #ddd;
            padding: 0 15px;
            background: #fff;
            outline: none;
            color: #555;
        }

        .header-search-button {
            width: 105px;
            height: 52px;
            border: none;
            background: #f79400;
            color: white;
            font-size: 18px;
        }

        .header-search-button:hover {
            background: #e98600;
        }


        /* =================================================
           HEADER ICONS
        ================================================= */

        .header-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
        }

        .header-action {
            width: 44px;
            height: 44px;
            border: 1px solid #ddd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #65748b;
            text-decoration: none;
            font-size: 15px;
        }

        .header-action:hover {
            color: #f79400;
            border-color: #f79400;
        }

        .header-cart-price {
            color: #555;
            font-size: 14px;
            white-space: nowrap;
        }


        /* =================================================
           ORANGE NAVBAR
        ================================================= */

        .custom-navbar {
            min-height: 55px;
            background: #f79400;
        }

        .custom-navbar .container-fluid {
            padding-left: 45px;
            padding-right: 45px;
        }


        /* =================================================
           ALL CATEGORIES
        ================================================= */

        .all-categories {
            color: #222;
            font-size: 20px;
            text-decoration: none;
            display: flex;
            align-items: center;
            height: 55px;
            font-weight: 500;
        }

        .all-categories:hover {
            color: #fff;
        }

        .all-categories i {
            margin-right: 8px;
        }


        /* =================================================
           NAV LINKS
        ================================================= */

        .custom-nav-links {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            height: 55px;
        }

        .custom-nav-links a {
            color: #222;
            text-decoration: none;
            font-size: 15px;
            padding: 18px 15px;
            height: 55px;
        }

        .custom-nav-links a:hover,
        .custom-nav-links a.active {
            color: white;
        }


        /* =================================================
           PHONE BUTTON
        ================================================= */

        .nav-phone {
            background: #ff1e00;
            color: white !important;
            border-radius: 30px;
            padding: 10px 18px !important;
            height: auto !important;
            margin-left: 12px;
            font-weight: 600;
        }

        .nav-phone:hover {
            background: #d91a00;
            color: white !important;
        }


        /* =================================================
           RESPONSIVE
        ================================================= */

        @media (max-width: 991px) {

            .custom-main-header {
                padding: 20px 0;
            }

            .electro-logo {
                font-size: 32px;
                margin-bottom: 15px;
            }

            .header-search {
                margin-bottom: 15px;
            }

            .header-actions {
                justify-content: center;
            }

            .custom-navbar {
                padding: 5px 0;
            }

            .custom-navbar .container-fluid {
                padding-left: 15px;
                padding-right: 15px;
            }

            .custom-nav-links {
                justify-content: flex-start;
                flex-wrap: wrap;
                height: auto;
            }

        }


        @media (max-width: 576px) {

            .custom-topbar {
                height: auto;
                padding: 8px 0;
            }

            .top-call {
                display: none;
            }

            .electro-logo {
                font-size: 30px;
            }

            .header-category {
                display: none;
            }

            .header-search-button {
                width: 60px;
            }

        }

    </style>

</head>


<body>


<!-- =========================================================
     TOP BAR
========================================================== -->

<div class="custom-topbar">

    <div class="container-fluid px-5">

        <div
            class="row align-items-center"
            style="height:44px;"
        >


            <!-- LEFT -->

            <div class="col-lg-4">

                <a href="#">
                    Help
                </a>

                <span class="separator">
                    /
                </span>

                <a href="#">
                    Support
                </a>

                <span class="separator">
                    /
                </span>

                <a href="contact.php">
                    Contact
                </a>

            </div>


            <!-- CENTER -->

            <div class="col-lg-4 text-center top-call">

                Call Us:(+012) 1234 567890

            </div>


            <!-- RIGHT -->

            <div class="col-lg-4 text-end top-right">

                <a href="#">
                    USD
                    <i class="fas fa-caret-down"></i>
                </a>

                <a href="#">
                    English
                    <i class="fas fa-caret-down"></i>
                </a>

                <a href="#">
                    <i class="fas fa-home me-1"></i>
                    My Dashboard
                    <i class="fas fa-caret-down"></i>
                </a>

            </div>

        </div>

    </div>

</div>



<!-- =========================================================
     MAIN HEADER
========================================================== -->

<div class="custom-main-header">

    <div class="container-fluid px-5">

        <div class="row align-items-center">


            <!-- LOGO -->

            <div class="col-lg-3">

                <a
                    href="index.php"
                    class="electro-logo"
                >

                    <span class="electro-logo-icon">

                        <i class="fas fa-shopping-bag"></i>

                    </span>

                    Electro

                </a>

            </div>



            <!-- SEARCH -->

            <div class="col-lg-6">

                <form
                    action="shop.php"
                    method="GET"
                    class="header-search"
                >

                    <input
                        type="text"
                        name="search"
                        placeholder="Search Looking For?"
                        value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>"
                    >


                    <select
                        name="category"
                        class="header-category"
                    >

                        <option value="">

                            All Category

                        </option>


                        <?php

                        /*
                         * Categories are loaded here only if
                         * $categories already exists in shop.php.
                         */

                        if (isset($categories) && is_array($categories)):

                            foreach ($categories as $headerCategory):

                        ?>

                            <option
                                value="<?= (int)$headerCategory['category_id'] ?>"
                                <?= (
                                    isset($_GET['category']) &&
                                    (int)$_GET['category'] ===
                                    (int)$headerCategory['category_id']
                                )
                                    ? 'selected'
                                    : ''
                                ?>
                            >

                                <?= htmlspecialchars(
                                    $headerCategory['category_name']
                                ) ?>

                            </option>

                        <?php

                            endforeach;

                        endif;

                        ?>

                    </select>


                    <button
                        type="submit"
                        class="header-search-button"
                    >

                        <i class="fas fa-search"></i>

                    </button>

                </form>

            </div>



            <!-- ACTION ICONS -->

            <div class="col-lg-3">

                <div class="header-actions">


                    <!-- COMPARE -->

                    <a
                        href="#"
                        class="header-action"
                        title="Compare"
                    >

                        <i class="fas fa-random"></i>

                    </a>


                    <!-- WISHLIST -->

                    <a
                        href="#"
                        class="header-action"
                        title="Wishlist"
                    >

                        <i class="fas fa-heart"></i>

                    </a>


                    <!-- CART -->

                    <a
                        href="cart.php"
                        class="header-action"
                        title="Shopping Cart"
                    >

                        <i class="fas fa-shopping-cart"></i>

                    </a>


                    <span class="header-cart-price">

                        $0.00

                    </span>


                </div>

            </div>


        </div>

    </div>

</div>



<!-- =========================================================
     ORANGE NAVIGATION BAR
========================================================== -->

<div class="custom-navbar">

    <div class="container-fluid">

        <div class="row align-items-center">


            <!-- ALL CATEGORIES -->

            <div class="col-lg-4">

                <a
                    href="shop.php"
                    class="all-categories"
                >

                    <i class="fas fa-bars"></i>

                    All Categories

                </a>

            </div>



            <!-- NAVIGATION -->

            <div class="col-lg-8">

                <div class="custom-nav-links">


                    <a
                        href="index.php"
                    >

                        Home

                    </a>


                    <a
                        href="shop.php"
                        class="active"
                    >

                        Shop

                    </a>


                    <a
                        href="single-page.php"
                    >

                        Single Page

                    </a>


                    <a
                        href="#"
                    >

                        Pages

                        <i class="fas fa-chevron-down ms-1"></i>

                    </a>


                    <a
                        href="contact.php"
                    >

                        Contact

                    </a>


                    <a
                        href="#"
                        class="nav-phone"
                    >

                        <i class="fas fa-mobile-alt me-2"></i>

                        +0123 456 7890

                    </a>


                </div>

            </div>


        </div>

    </div>

</div>