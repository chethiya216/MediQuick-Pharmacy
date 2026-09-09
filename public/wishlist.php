<?php
session_start();

/*
|--------------------------------------------------------------------------
| Database Connection
|--------------------------------------------------------------------------
*/
require_once '../includes/db.php';

/*
|--------------------------------------------------------------------------
| Wishlist Without Login
|--------------------------------------------------------------------------
| Login is NOT required.
|
| We use customer_id = 1 for the wishlist.
| If you want to use another customer, change this number.
|--------------------------------------------------------------------------
*/
$customer_id = 1;


/*
|--------------------------------------------------------------------------
| Add Product To Wishlist
|--------------------------------------------------------------------------
*/
if (isset($_GET['add'])) {

    $product_id = (int) $_GET['add'];

    if ($product_id > 0) {

        /*
        | Check if product exists
        */
        $product_check = $conn->prepare("
            SELECT product_id
            FROM products
            WHERE product_id = ?
            LIMIT 1
        ");

        $product_check->bind_param("i", $product_id);
        $product_check->execute();

        $product_result = $product_check->get_result();

        if ($product_result->num_rows > 0) {

            /*
            | Check if product is already in wishlist
            */
            $check = $conn->prepare("
                SELECT wishlist_id
                FROM wishlist
                WHERE customer_id = ?
                AND product_id = ?
                LIMIT 1
            ");

            $check->bind_param(
                "ii",
                $customer_id,
                $product_id
            );

            $check->execute();

            $wishlist_result = $check->get_result();

            /*
            | Add only if it doesn't already exist
            */
            if ($wishlist_result->num_rows == 0) {

                $insert = $conn->prepare("
                    INSERT INTO wishlist
                    (customer_id, product_id)
                    VALUES (?, ?)
                ");

                $insert->bind_param(
                    "ii",
                    $customer_id,
                    $product_id
                );

                $insert->execute();

                $insert->close();
            }

            $check->close();
        }

        $product_check->close();
    }

    header("Location: wishlist.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Remove Product From Wishlist
|--------------------------------------------------------------------------
*/
if (isset($_GET['remove'])) {

    $wishlist_id = (int) $_GET['remove'];

    if ($wishlist_id > 0) {

        $delete = $conn->prepare("
            DELETE FROM wishlist
            WHERE wishlist_id = ?
            AND customer_id = ?
        ");

        $delete->bind_param(
            "ii",
            $wishlist_id,
            $customer_id
        );

        $delete->execute();
        $delete->close();
    }

    header("Location: wishlist.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/
$per_page = 5;

$page = isset($_GET['page'])
    ? (int) $_GET['page']
    : 1;

if ($page < 1) {
    $page = 1;
}

$offset = ($page - 1) * $per_page;


/*
|--------------------------------------------------------------------------
| Count Wishlist Items
|--------------------------------------------------------------------------
*/
$count_stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM wishlist
    WHERE customer_id = ?
");

$count_stmt->bind_param(
    "i",
    $customer_id
);

$count_stmt->execute();

$count_result = $count_stmt->get_result();

$count_row = $count_result->fetch_assoc();

$total_items = (int) $count_row['total'];

$total_pages = $total_items > 0
    ? (int) ceil($total_items / $per_page)
    : 1;

$count_stmt->close();


/*
|--------------------------------------------------------------------------
| Get Wishlist Products
|--------------------------------------------------------------------------
|
| IMPORTANT:
| products table uses:
|
| unit_price
| discount_percent
| product_image
|
| NOT:
|
| price
| image
|
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare("
    SELECT

        w.wishlist_id,
        w.product_id,
        w.created_at,

        p.product_name,
        p.generic_name,
        p.description,

        p.unit_price,
        p.discount_percent,
        p.product_image,

        p.dosage_form,
        p.strength,
        p.status,

        COALESCE(
            SUM(
                CASE
                    WHEN pb.status = 'active'
                    AND pb.expiry_date >= CURDATE()
                    THEN pb.quantity_on_hand
                    ELSE 0
                END
            ),
            0
        ) AS stock_quantity

    FROM wishlist w

    INNER JOIN products p
        ON w.product_id = p.product_id

    LEFT JOIN product_batches pb
        ON pb.product_id = p.product_id

    WHERE w.customer_id = ?

    GROUP BY

        w.wishlist_id,
        w.product_id,
        w.created_at,

        p.product_name,
        p.generic_name,
        p.description,

        p.unit_price,
        p.discount_percent,
        p.product_image,

        p.dosage_form,
        p.strength,
        p.status

    ORDER BY w.created_at DESC

    LIMIT ? OFFSET ?
");

$stmt->bind_param(
    "iii",
    $customer_id,
    $per_page,
    $offset
);

$stmt->execute();

$wishlist = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        My Wishlist - MediQuick Pharmacy
    </title>


    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <style>

        body {

            background: #f5f7fa;

            font-family: Arial, sans-serif;

        }


        .wishlist-section {

            padding: 40px 0 70px;

            min-height: 70vh;

        }


        .wishlist-panel {

            background: #ffffff;

            border-radius: 16px;

            border: 1px solid #eee2e2;

            overflow: hidden;

            box-shadow:
                0 4px 18px rgba(0, 0, 0, 0.04);

        }


        /*
        |--------------------------------------------------------------------------
        | Title
        |--------------------------------------------------------------------------
        */

        .wishlist-titlebar {

            background: #e7e2e2;

            padding: 18px 26px;

        }


        .wishlist-title {

            font-size: 20px;

            font-weight: 700;

            color: #3a3232;

            margin: 0;

            font-style: italic;

        }


        .wishlist-title i {

            margin-right: 8px;

            font-style: normal;

        }


        /*
        |--------------------------------------------------------------------------
        | Toolbar
        |--------------------------------------------------------------------------
        */

        .wishlist-toolbar {

            padding: 22px 26px 6px;

            display: flex;

            gap: 16px;

            align-items: center;

            justify-content: space-between;

            flex-wrap: wrap;

        }


        .wishlist-search {

            flex: 1;

            min-width: 220px;

            position: relative;

        }


        .wishlist-search i {

            position: absolute;

            left: 16px;

            top: 50%;

            transform: translateY(-50%);

            color: #8b7d7d;

        }


        .wishlist-search input {

            width: 100%;

            border: none;

            outline: none;

            background: #e7e2e2;

            border-radius: 30px;

            padding: 12px 18px 12px 45px;

            font-size: 14px;

        }


        .wishlist-search input:focus {

            box-shadow:
                0 0 0 2px rgba(180, 138, 134, 0.25);

        }


        .add-all-btn {

            border: none;

            background: #b48a86;

            color: white;

            border-radius: 25px;

            padding: 11px 20px;

            font-size: 14px;

            font-weight: 600;

            cursor: pointer;

            transition: 0.2s;

        }


        .add-all-btn:hover {

            background: #9c6f6b;

        }


        /*
        |--------------------------------------------------------------------------
        | Table
        |--------------------------------------------------------------------------
        */

        .wishlist-table-wrap {

            padding: 20px 26px 10px;

            overflow-x: auto;

        }


        table.wishlist-table {

            width: 100%;

            border-collapse: separate;

            border-spacing: 0 10px;

        }


        table.wishlist-table thead th {

            background: #c9aaa7;

            color: #ffffff;

            padding: 12px 20px;

            font-size: 13px;

            font-weight: 600;

            border: none;

        }


        table.wishlist-table thead th:first-child {

            border-top-left-radius: 30px;

            border-bottom-left-radius: 30px;

        }


        table.wishlist-table thead th:last-child {

            border-top-right-radius: 30px;

            border-bottom-right-radius: 30px;

            text-align: right;

        }


        table.wishlist-table tbody tr {

            background: #e4cdcb;

        }


        table.wishlist-table tbody tr:nth-child(even) {

            background: #dcc2c0;

        }


        table.wishlist-table tbody td {

            padding: 14px 20px;

            border: none;

            vertical-align: middle;

            color: #4a3f3f;

            font-size: 14px;

        }


        table.wishlist-table tbody td:first-child {

            border-top-left-radius: 30px;

            border-bottom-left-radius: 30px;

        }


        table.wishlist-table tbody td:last-child {

            border-top-right-radius: 30px;

            border-bottom-right-radius: 30px;

            text-align: right;

        }


        /*
        |--------------------------------------------------------------------------
        | Product
        |--------------------------------------------------------------------------
        */

        .row-product {

            display: flex;

            align-items: center;

            gap: 12px;

        }


        .row-product img {

            width: 42px;

            height: 42px;

            object-fit: contain;

            background: #ffffff;

            border-radius: 8px;

            padding: 4px;

            flex-shrink: 0;

        }


        .row-product-name {

            font-weight: 600;

            color: #3a3232;

        }


        .row-product-name:hover {

            color: #8f6662;

        }


        .row-product-generic {

            font-size: 12px;

            color: #7a6d6d;

            margin-top: 2px;

        }


        /*
        |--------------------------------------------------------------------------
        | Price
        |--------------------------------------------------------------------------
        */

        .old-price {

            font-size: 12px;

            color: #8a7d7d;

            text-decoration: line-through;

            margin-left: 6px;

        }


        /*
        |--------------------------------------------------------------------------
        | Stock
        |--------------------------------------------------------------------------
        */

        .stock-in {

            color: #2f6b3d;

            font-weight: 600;

        }


        .stock-out {

            color: #a33a3a;

            font-weight: 600;

        }


        /*
        |--------------------------------------------------------------------------
        | Actions
        |--------------------------------------------------------------------------
        */

        .row-actions {

            display: flex;

            justify-content: flex-end;

            align-items: center;

            gap: 10px;

        }


        .cart-btn {

            background: #b48a86;

            border: none;

            color: #ffffff;

            border-radius: 20px;

            padding: 7px 18px;

            font-size: 13px;

            font-weight: 600;

            white-space: nowrap;

            transition: 0.2s;

        }


        .cart-btn:hover {

            background: #9c6f6b;

            color: #ffffff;

        }


        .cart-btn:disabled {

            background: #d8caca;

            cursor: not-allowed;

        }


        .remove-icon-btn {

            width: 30px;

            height: 30px;

            border-radius: 50%;

            background: #ffffff;

            border: 1px solid #e0d3d3;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            color: #a5837f;

            flex-shrink: 0;

            transition: 0.2s;

            text-decoration: none;

        }


        .remove-icon-btn:hover {

            background: #a5837f;

            color: #ffffff;

        }


        /*
        |--------------------------------------------------------------------------
        | Empty Wishlist
        |--------------------------------------------------------------------------
        */

        .empty-wishlist {

            text-align: center;

            padding: 70px 20px;

        }


        .empty-wishlist i {

            font-size: 70px;

            color: #c9aaa7;

            margin-bottom: 20px;

        }


        .empty-wishlist h3 {

            color: #3a3232;

            font-weight: 700;

        }


        .empty-wishlist p {

            color: #7a6d6d;

        }


        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        .wishlist-pagination {

            display: flex;

            justify-content: center;

            align-items: center;

            gap: 7px;

            padding: 20px 0 30px;

        }


        .wishlist-pagination a,
        .wishlist-pagination span {

            width: 34px;

            height: 34px;

            border-radius: 50%;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            text-decoration: none;

            color: #5d4f4f;

            background: #e7e2e2;

            font-size: 13px;

        }


        .wishlist-pagination span.active {

            background: #b48a86;

            color: #ffffff;

        }


        .wishlist-pagination a:hover {

            background: #c9aaa7;

            color: #ffffff;

        }


        /*
        |--------------------------------------------------------------------------
        | Responsive
        |--------------------------------------------------------------------------
        */

        @media (max-width: 768px) {

            .wishlist-section {

                padding: 20px 10px 50px;

            }


            .wishlist-titlebar {

                padding: 16px 18px;

            }


            .wishlist-toolbar {

                padding: 18px 18px 5px;

            }


            .wishlist-table-wrap {

                padding: 15px 10px;

            }


            table.wishlist-table {

                min-width: 750px;

            }

        }

    </style>

</head>


<body>


<?php
/*
|--------------------------------------------------------------------------
| Header
|--------------------------------------------------------------------------
*/
include '../includes/header.php';
?>


<section class="wishlist-section">

    <div class="container">

        <div class="wishlist-panel">


            <!-- =====================================================
                 TITLE
            ====================================================== -->

            <div class="wishlist-titlebar">

                <h2 class="wishlist-title">

                    <i class="fas fa-heart"></i>

                    My Wishlist

                </h2>

            </div>


            <?php if ($total_items > 0): ?>


                <!-- =================================================
                     TOOLBAR
                ================================================== -->

                <div class="wishlist-toolbar">


                    <!-- Search -->

                    <div class="wishlist-search">

                        <i class="fas fa-search"></i>

                        <input
                            type="text"
                            id="wishlistSearchInput"
                            placeholder="Search products"
                            autocomplete="off"
                        >

                    </div>


                    <!-- Add all to cart -->

                    <button
                        type="button"
                        id="addAllToCartBtn"
                        class="add-all-btn"
                    >

                        <i class="fas fa-cart-plus"></i>

                        Add all to cart

                    </button>

                </div>


                <!-- =================================================
                     TABLE
                ================================================== -->

                <div class="wishlist-table-wrap">

                    <table
                        class="wishlist-table"
                        id="wishlistTable"
                    >

                        <thead>

                            <tr>

                                <th>
                                    Product Name
                                </th>

                                <th>
                                    Price
                                </th>

                                <th>
                                    Added on
                                </th>

                                <th>
                                    Stock
                                </th>

                                <th></th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php while (
                            $product = $wishlist->fetch_assoc()
                        ): ?>


                            <?php

                            /*
                            |--------------------------------------------------------------------------
                            | Price Calculation
                            |--------------------------------------------------------------------------
                            */

                            $price = (float)
                                $product['unit_price'];

                            $discount = (float)
                                $product['discount_percent'];

                            if ($discount > 0) {

                                $final_price =
                                    $price -
                                    (
                                        $price *
                                        $discount /
                                        100
                                    );

                            } else {

                                $final_price = $price;

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Product Image
                            |--------------------------------------------------------------------------
                            */

                            $image = trim(
                                (string)
                                $product['product_image']
                            );


                            if (!empty($image)) {

                                if (
                                    strpos(
                                        $image,
                                        '../'
                                    ) === 0
                                ) {

                                    $image_path = $image;

                                } else {

                                    $image_path =
                                        '../' . $image;

                                }

                            } else {

                                $image_path =
                                    '../img/no-image.png';

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Stock / Status
                            |--------------------------------------------------------------------------
                            */

                            $status = strtolower(
                                trim(
                                    (string)
                                    $product['status']
                                )
                            );


                            $stock_quantity =
                                (int)
                                $product['stock_quantity'];


                            $in_stock =
                                (
                                    $status === 'active'
                                    &&
                                    $stock_quantity > 0
                                );


                            /*
                            |--------------------------------------------------------------------------
                            | Added Date
                            |--------------------------------------------------------------------------
                            */

                            $added_on =
                                !empty(
                                    $product['created_at']
                                )
                                ? date(
                                    'M d, Y',
                                    strtotime(
                                        $product['created_at']
                                    )
                                )
                                : '-';

                            ?>


                            <tr
                                data-name="<?php
                                    echo htmlspecialchars(
                                        strtolower(
                                            $product['product_name']
                                        )
                                    );
                                ?>"
                                data-generic="<?php
                                    echo htmlspecialchars(
                                        strtolower(
                                            (string)
                                            $product['generic_name']
                                        )
                                    );
                                ?>"
                            >


                                <!-- =================================
                                     PRODUCT
                                ================================== -->

                                <td>

                                    <div class="row-product">


                                        <img
                                            src="<?php
                                                echo htmlspecialchars(
                                                    $image_path
                                                );
                                            ?>"
                                            alt="<?php
                                                echo htmlspecialchars(
                                                    $product['product_name']
                                                );
                                            ?>"
                                            onerror="
                                                this.onerror=null;
                                                this.src='../img/no-image.png';
                                            "
                                        >


                                        <div>


                                            <a
                                                href="product-details.php?id=<?php
                                                    echo (int)
                                                        $product['product_id'];
                                                ?>"
                                                class="row-product-name text-decoration-none"
                                            >

                                                <?php
                                                    echo htmlspecialchars(
                                                        $product['product_name']
                                                    );
                                                ?>

                                            </a>


                                            <?php if (
                                                !empty(
                                                    $product['generic_name']
                                                )
                                            ): ?>

                                                <div
                                                    class="row-product-generic"
                                                >

                                                    <?php
                                                        echo htmlspecialchars(
                                                            $product['generic_name']
                                                        );
                                                    ?>

                                                </div>

                                            <?php endif; ?>


                                        </div>

                                    </div>

                                </td>


                                <!-- =================================
                                     PRICE
                                ================================== -->

                                <td>

                                    Rs.

                                    <?php
                                        echo number_format(
                                            $final_price,
                                            2
                                        );
                                    ?>


                                    <?php if (
                                        $discount > 0
                                    ): ?>

                                        <span class="old-price">

                                            Rs.

                                            <?php
                                                echo number_format(
                                                    $price,
                                                    2
                                                );
                                            ?>

                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- =================================
                                     DATE
                                ================================== -->

                                <td>

                                    <?php
                                        echo htmlspecialchars(
                                            $added_on
                                        );
                                    ?>

                                </td>


                                <!-- =================================
                                     STOCK
                                ================================== -->

                                <td>

                                    <?php if (
                                        $in_stock
                                    ): ?>

                                        <span class="stock-in">

                                            In stock

                                            (<?php
                                                echo $stock_quantity;
                                            ?>)

                                        </span>

                                    <?php else: ?>

                                        <span class="stock-out">

                                            Out of stock

                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- =================================
                                     ACTIONS
                                ================================== -->

                                <td>

                                    <div class="row-actions">


                                        <!-- Add to cart -->

                                        <button
                                            type="button"
                                            class="cart-btn add-to-cart-btn"
                                            data-product-id="<?php
                                                echo (int)
                                                    $product['product_id'];
                                            ?>"
                                            <?php
                                                echo $in_stock
                                                    ? ''
                                                    : 'disabled';
                                            ?>
                                        >

                                            <i class="fas fa-cart-plus"></i>

                                            Add to cart

                                        </button>


                                        <!-- Remove -->

                                        <a
                                            href="wishlist.php?remove=<?php
                                                echo (int)
                                                    $product['wishlist_id'];
                                            ?>"
                                            class="remove-icon-btn"
                                            title="Remove from wishlist"
                                            onclick="
                                                return confirm(
                                                    'Remove this product from your wishlist?'
                                                );
                                            "
                                        >

                                            <i
                                                class="fas fa-trash fa-xs"
                                            ></i>

                                        </a>


                                    </div>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                        </tbody>

                    </table>


                    <!-- No search results -->

                    <div
                        id="wishlistNoResults"
                        class="text-center text-muted py-4"
                        style="display:none;"
                    >

                        No products match your search.

                    </div>

                </div>


                <!-- =================================================
                     PAGINATION
                ================================================== -->

                <?php if (
                    $total_pages > 1
                ): ?>

                    <div class="wishlist-pagination">


                        <!-- Previous -->

                        <?php if (
                            $page > 1
                        ): ?>

                            <a
                                href="wishlist.php?page=<?php
                                    echo $page - 1;
                                ?>"
                            >

                                &lsaquo;

                            </a>

                        <?php endif; ?>


                        <!-- Page numbers -->

                        <?php for (
                            $p = 1;
                            $p <= $total_pages;
                            $p++
                        ): ?>


                            <?php if (
                                $p == $page
                            ): ?>

                                <span class="active">

                                    <?php
                                        echo $p;
                                    ?>

                                </span>

                            <?php else: ?>

                                <a
                                    href="wishlist.php?page=<?php
                                        echo $p;
                                    ?>"
                                >

                                    <?php
                                        echo $p;
                                    ?>

                                </a>

                            <?php endif; ?>


                        <?php endfor; ?>


                        <!-- Next -->

                        <?php if (
                            $page < $total_pages
                        ): ?>

                            <a
                                href="wishlist.php?page=<?php
                                    echo $page + 1;
                                ?>"
                            >

                                &rsaquo;

                            </a>

                        <?php endif; ?>


                    </div>

                <?php endif; ?>


            <?php else: ?>


                <!-- =================================================
                     EMPTY WISHLIST
                ================================================== -->

                <div class="empty-wishlist">


                    <i class="far fa-heart"></i>


                    <h3>

                        Your Wishlist is Empty

                    </h3>


                    <p>

                        You haven't added any products
                        to your wishlist yet.

                    </p>


                    <a
                        href="index.php"
                        class="btn btn-primary"
                    >

                        <i class="fas fa-shopping-bag me-1"></i>

                        Start Shopping

                    </a>


                </div>


            <?php endif; ?>


        </div>

    </div>

</section>


<?php
/*
|--------------------------------------------------------------------------
| Footer
|--------------------------------------------------------------------------
*/
include '../includes/footer.php';
?>


<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


<script>

/*
|--------------------------------------------------------------------------
| Search Wishlist
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'DOMContentLoaded',
    function () {


        var searchInput =
            document.getElementById(
                'wishlistSearchInput'
            );


        var table =
            document.getElementById(
                'wishlistTable'
            );


        var noResults =
            document.getElementById(
                'wishlistNoResults'
            );


        if (
            searchInput &&
            table
        ) {


            searchInput.addEventListener(
                'input',
                function () {


                    var term =
                        this.value
                            .trim()
                            .toLowerCase();


                    var rows =
                        table.querySelectorAll(
                            'tbody tr'
                        );


                    var visibleCount = 0;


                    rows.forEach(
                        function (row) {


                            var name =
                                row.getAttribute(
                                    'data-name'
                                ) || '';


                            var generic =
                                row.getAttribute(
                                    'data-generic'
                                ) || '';


                            var matches =
                                name.indexOf(
                                    term
                                ) !== -1
                                ||
                                generic.indexOf(
                                    term
                                ) !== -1;


                            if (matches) {

                                row.style.display =
                                    '';

                                visibleCount++;

                            } else {

                                row.style.display =
                                    'none';

                            }

                        }
                    );


                    if (noResults) {


                        if (
                            visibleCount === 0
                            &&
                            term !== ''
                        ) {

                            noResults.style.display =
                                'block';

                        } else {

                            noResults.style.display =
                                'none';

                        }

                    }

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Add To Cart
        |--------------------------------------------------------------------------
        */

        var cartButtons =
            document.querySelectorAll(
                '.add-to-cart-btn'
            );


        cartButtons.forEach(
            function (button) {


                button.addEventListener(
                    'click',
                    function () {


                        var productId =
                            this.getAttribute(
                                'data-product-id'
                            );


                        if (productId) {

                            window.location.href =
                                'cart.php?add=' +
                                productId;

                        }

                    }
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Add All To Cart
        |--------------------------------------------------------------------------
        */

        var addAllButton =
            document.getElementById(
                'addAllToCartBtn'
            );


        if (addAllButton) {


            addAllButton.addEventListener(
                'click',
                function () {

                    window.location.href =
                        'cart.php?add_all=1';

                }
            );

        }

    }
);

</script>


</body>

</html>A