<?php

session_start();

require_once __DIR__ . '/../includes/db.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Database connection failed.");
}


/*
|--------------------------------------------------------------------------
| Cart page CSS
|--------------------------------------------------------------------------
*/

$page_css = 'cart-style.css';


/*
|--------------------------------------------------------------------------
| Check customer login
|--------------------------------------------------------------------------
| A cart belongs to a customer, so the customer must be logged in.
*/

if (empty($_SESSION['customer_id'])) {

    header('Location: login.php?redirect=cart.php');
    exit;
}

$customer_id = (int) $_SESSION['customer_id'];

if ($customer_id <= 0) {
    die("Invalid customer account.");
}


/*
|--------------------------------------------------------------------------
| Find or create customer's cart
|--------------------------------------------------------------------------
*/

$cart_id = 0;


/*
|--------------------------------------------------------------------------
| Find existing cart
|--------------------------------------------------------------------------
*/

$cart_sql = "
    SELECT cart_id
    FROM carts
    WHERE customer_id = ?
    LIMIT 1
";

$cart_stmt = $conn->prepare($cart_sql);

if (!$cart_stmt) {
    die("Cart lookup failed: " . $conn->error);
}

$cart_stmt->bind_param("i", $customer_id);
$cart_stmt->execute();

$cart_result = $cart_stmt->get_result();

if ($cart_row = $cart_result->fetch_assoc()) {

    $cart_id = (int) $cart_row['cart_id'];

}

$cart_stmt->close();


/*
|--------------------------------------------------------------------------
| Create cart if customer does not have one
|--------------------------------------------------------------------------
*/

if ($cart_id <= 0) {

    $create_cart_sql = "
        INSERT INTO carts (
            customer_id,
            created_at,
            updated_at
        )
        VALUES (?, NOW(), NOW())
    ";

    $create_cart_stmt = $conn->prepare($create_cart_sql);

    if (!$create_cart_stmt) {
        die("Unable to prepare cart creation: " . $conn->error);
    }

    $create_cart_stmt->bind_param("i", $customer_id);

    if (!$create_cart_stmt->execute()) {

        /*
        |--------------------------------------------------------------
        | If another request already created the cart because of the
        | UNIQUE customer_id constraint, try finding it again.
        |--------------------------------------------------------------
        */

        $create_cart_stmt->close();

        $retry_cart_sql = "
            SELECT cart_id
            FROM carts
            WHERE customer_id = ?
            LIMIT 1
        ";

        $retry_cart_stmt = $conn->prepare($retry_cart_sql);

        if (!$retry_cart_stmt) {
            die("Unable to find customer cart.");
        }

        $retry_cart_stmt->bind_param("i", $customer_id);
        $retry_cart_stmt->execute();

        $retry_cart_result = $retry_cart_stmt->get_result();

        if ($retry_cart_row = $retry_cart_result->fetch_assoc()) {

            $cart_id = (int) $retry_cart_row['cart_id'];

        }

        $retry_cart_stmt->close();

    } else {

        $cart_id = $conn->insert_id;

        $create_cart_stmt->close();
    }
}


if ($cart_id <= 0) {
    die("Unable to create or find your shopping cart.");
}


/*
|--------------------------------------------------------------------------
| Keep cart ID in session
|--------------------------------------------------------------------------
*/

$_SESSION['cart_id'] = $cart_id;


/*
|--------------------------------------------------------------------------
| AJAX: update quantity or remove a cart item
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['cart_item_id'])
) {

    header('Content-Type: application/json');

    $post_cart_item_id = (int) $_POST['cart_item_id'];

    $post_quantity = isset($_POST['quantity'])
        ? (int) $_POST['quantity']
        : 0;


    /*
    |--------------------------------------------------------------------------
    | Validate cart item ID
    |--------------------------------------------------------------------------
    */

    if ($post_cart_item_id <= 0) {

        echo json_encode([
            'success' => false,
            'message' => 'Invalid cart item.'
        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Confirm item belongs to this customer's cart
    |--------------------------------------------------------------------------
    */

    $ajax_check_sql = "
        SELECT cart_item_id
        FROM cart_items
        WHERE cart_item_id = ?
          AND cart_id = ?
        LIMIT 1
    ";

    $ajax_check_stmt = $conn->prepare($ajax_check_sql);

    if (!$ajax_check_stmt) {

        echo json_encode([
            'success' => false,
            'message' => 'Unable to check cart item.'
        ]);

        exit;
    }

    $ajax_check_stmt->bind_param(
        "ii",
        $post_cart_item_id,
        $cart_id
    );

    $ajax_check_stmt->execute();

    $ajax_check_result = $ajax_check_stmt->get_result();


    if ($ajax_check_result->num_rows === 0) {

        $ajax_check_stmt->close();

        echo json_encode([
            'success' => false,
            'message' => 'That item is not in your cart.'
        ]);

        exit;
    }

    $ajax_check_stmt->close();


    /*
    |--------------------------------------------------------------------------
    | Remove item
    |--------------------------------------------------------------------------
    */

    if ($post_quantity <= 0) {

        $ajax_delete_sql = "
            DELETE FROM cart_items
            WHERE cart_item_id = ?
              AND cart_id = ?
        ";

        $ajax_delete_stmt = $conn->prepare($ajax_delete_sql);

        if (!$ajax_delete_stmt) {

            echo json_encode([
                'success' => false,
                'message' => 'Unable to remove item.'
            ]);

            exit;
        }

        $ajax_delete_stmt->bind_param(
            "ii",
            $post_cart_item_id,
            $cart_id
        );


        if (!$ajax_delete_stmt->execute()) {

            $ajax_delete_stmt->close();

            echo json_encode([
                'success' => false,
                'message' => 'Unable to remove item.'
            ]);

            exit;
        }

        $ajax_delete_stmt->close();

    } else {

        /*
        |--------------------------------------------------------------------------
        | Update quantity
        |--------------------------------------------------------------------------
        */

        $ajax_update_sql = "
            UPDATE cart_items
            SET quantity = ?
            WHERE cart_item_id = ?
              AND cart_id = ?
        ";

        $ajax_update_stmt = $conn->prepare($ajax_update_sql);

        if (!$ajax_update_stmt) {

            echo json_encode([
                'success' => false,
                'message' => 'Unable to update quantity.'
            ]);

            exit;
        }

        $ajax_update_stmt->bind_param(
            "iii",
            $post_quantity,
            $post_cart_item_id,
            $cart_id
        );


        if (!$ajax_update_stmt->execute()) {

            $ajax_update_stmt->close();

            echo json_encode([
                'success' => false,
                'message' => 'Unable to update quantity.'
            ]);

            exit;
        }

        $ajax_update_stmt->close();
    }


    /*
    |--------------------------------------------------------------------------
    | Update cart timestamp
    |--------------------------------------------------------------------------
    */

    $ajax_touch_sql = "
        UPDATE carts
        SET updated_at = NOW()
        WHERE cart_id = ?
    ";

    $ajax_touch_stmt = $conn->prepare($ajax_touch_sql);

    if ($ajax_touch_stmt) {

        $ajax_touch_stmt->bind_param(
            "i",
            $cart_id
        );

        $ajax_touch_stmt->execute();
        $ajax_touch_stmt->close();
    }


    echo json_encode([
        'success' => true
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Add product to cart
|--------------------------------------------------------------------------
| Called when:
|
| cart.php?product_id=X
|
*/

if (!empty($_GET['product_id'])) {

    $product_id = (int) $_GET['product_id'];


    /*
    |--------------------------------------------------------------------------
    | Check product exists and is active
    |--------------------------------------------------------------------------
    */

    $product_check_sql = "
        SELECT product_id
        FROM products
        WHERE product_id = ?
          AND status = 'active'
        LIMIT 1
    ";

    $product_check_stmt = $conn->prepare($product_check_sql);

    if (!$product_check_stmt) {
        die("Product check failed: " . $conn->error);
    }

    $product_check_stmt->bind_param(
        "i",
        $product_id
    );

    $product_check_stmt->execute();

    $product_check_result =
        $product_check_stmt->get_result();


    if ($product_check_result->num_rows > 0) {


        /*
        |--------------------------------------------------------------------------
        | Check whether product is already in cart
        |--------------------------------------------------------------------------
        */

        $existing_item_sql = "
            SELECT cart_item_id, quantity
            FROM cart_items
            WHERE cart_id = ?
              AND product_id = ?
            LIMIT 1
        ";

        $existing_item_stmt =
            $conn->prepare($existing_item_sql);

        if (!$existing_item_stmt) {

            $product_check_stmt->close();

            die("Unable to check cart item.");
        }

        $existing_item_stmt->bind_param(
            "ii",
            $cart_id,
            $product_id
        );

        $existing_item_stmt->execute();

        $existing_item_result =
            $existing_item_stmt->get_result();


        if ($existing_item =
            $existing_item_result->fetch_assoc()
        ) {

            /*
            |--------------------------------------------------------------------------
            | Product already exists
            | Increase quantity
            |--------------------------------------------------------------------------
            */

            $new_quantity =
                (int) $existing_item['quantity'] + 1;


            $update_item_sql = "
                UPDATE cart_items
                SET quantity = ?
                WHERE cart_item_id = ?
                  AND cart_id = ?
            ";

            $update_item_stmt =
                $conn->prepare($update_item_sql);

            if ($update_item_stmt) {

                $update_item_stmt->bind_param(
                    "iii",
                    $new_quantity,
                    $existing_item['cart_item_id'],
                    $cart_id
                );

                $update_item_stmt->execute();

                $update_item_stmt->close();
            }

        } else {

            /*
            |--------------------------------------------------------------------------
            | Product is not in cart
            | Add new item
            |--------------------------------------------------------------------------
            */

            $insert_item_sql = "
                INSERT INTO cart_items (
                    cart_id,
                    product_id,
                    quantity
                )
                VALUES (?, ?, 1)
            ";

            $insert_item_stmt =
                $conn->prepare($insert_item_sql);

            if ($insert_item_stmt) {

                $insert_item_stmt->bind_param(
                    "ii",
                    $cart_id,
                    $product_id
                );

                $insert_item_stmt->execute();

                $insert_item_stmt->close();
            }
        }


        $existing_item_stmt->close();


        /*
        |--------------------------------------------------------------------------
        | Update cart timestamp
        |--------------------------------------------------------------------------
        */

        $touch_cart_sql = "
            UPDATE carts
            SET updated_at = NOW()
            WHERE cart_id = ?
        ";

        $touch_cart_stmt =
            $conn->prepare($touch_cart_sql);

        if ($touch_cart_stmt) {

            $touch_cart_stmt->bind_param(
                "i",
                $cart_id
            );

            $touch_cart_stmt->execute();

            $touch_cart_stmt->close();
        }
    }


    $product_check_stmt->close();


    /*
    |--------------------------------------------------------------------------
    | Redirect back to clean cart URL
    |--------------------------------------------------------------------------
    */

    header('Location: cart.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Get cart items
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        ci.cart_item_id,
        ci.product_id,
        ci.quantity,
        p.product_name,
        p.generic_name,
        p.sku,
        p.unit_price,
        p.discount_percent,
        p.product_image,
        p.requires_prescription,
        p.status

    FROM cart_items ci

    INNER JOIN products p
        ON p.product_id = ci.product_id

    WHERE ci.cart_id = ?

    ORDER BY ci.cart_item_id DESC
";


$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Cart query failed: " . $conn->error);
}

$stmt->bind_param(
    "i",
    $cart_id
);

$stmt->execute();

$result = $stmt->get_result();


$cart_items = [];

$subtotal = 0;


while ($row = $result->fetch_assoc()) {

    $unit_price =
        (float) $row['unit_price'];

    $discount =
        (float) ($row['discount_percent'] ?? 0);

    $quantity =
        (int) $row['quantity'];


    /*
    |--------------------------------------------------------------------------
    | Calculate discounted price
    |--------------------------------------------------------------------------
    */

    $discounted_price = $unit_price;

    if ($discount > 0) {

        $discounted_price =
            $unit_price
            - ($unit_price * $discount / 100);
    }


    /*
    |--------------------------------------------------------------------------
    | Calculate item subtotal
    |--------------------------------------------------------------------------
    */

    $item_subtotal =
        $discounted_price * $quantity;


    $row['discounted_price'] =
        $discounted_price;

    $row['item_subtotal'] =
        $item_subtotal;


    $cart_items[] = $row;

    $subtotal += $item_subtotal;
}


$stmt->close();


/*
|--------------------------------------------------------------------------
| Shipping
|--------------------------------------------------------------------------
*/

$shipping = 0;

if (!empty($cart_items)) {
    $shipping = 3.00;
}


/*
|--------------------------------------------------------------------------
| Total
|--------------------------------------------------------------------------
*/

$total =
    $subtotal + $shipping;


/*
|--------------------------------------------------------------------------
| Product image
|--------------------------------------------------------------------------
*/

function getProductImage($image)
{
    if (!empty($image)) {

        return 'assets/images/'
            . htmlspecialchars($image);
    }

    return 'assets/images/no-image.png';
}


/*
|--------------------------------------------------------------------------
| Header
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/header.php';

?>


<div class="cart-page-wrapper">

    <div class="cart-container">


        <!-- Page title -->

        <h1 class="page-title">
            Shopping Cart
        </h1>


        <?php if (empty($cart_items)): ?>


            <!-- Empty cart -->

            <div class="empty-cart">

                <div class="empty-cart-icon">
                    🛒
                </div>

                <h2>
                    Your cart is empty
                </h2>

                <p>
                    You haven't added any products
                    to your cart yet.
                </p>

                <a
                    href="shop.php"
                    class="shop-btn"
                >
                    Continue Shopping
                </a>

            </div>


        <?php else: ?>


            <div class="cart-layout">


                <!-- =====================================================
                     CART ITEMS
                ====================================================== -->

                <div class="cart-items">


                    <?php foreach ($cart_items as $item): ?>


                        <div class="cart-item">


                            <!-- Product image -->

                            <div class="product-image">

                                <img
                                    src="<?= getProductImage($item['product_image']) ?>"
                                    alt="<?= htmlspecialchars($item['product_name']) ?>"
                                >

                            </div>


                            <!-- Product details -->

                            <div class="item-details">


                                <h2 class="product-name">

                                    <?= htmlspecialchars(
                                        $item['product_name']
                                    ) ?>

                                </h2>


                                <?php if (!empty($item['generic_name'])): ?>

                                    <p class="generic-name">

                                        <?= htmlspecialchars(
                                            $item['generic_name']
                                        ) ?>

                                    </p>

                                <?php endif; ?>


                                <?php if (!empty($item['sku'])): ?>

                                    <p class="sku">

                                        SKU:
                                        <?= htmlspecialchars(
                                            $item['sku']
                                        ) ?>

                                    </p>

                                <?php endif; ?>


                                <!-- Price -->

                                <div class="price">


                                    <?php if ($item['discount_percent'] > 0): ?>

                                        <span class="old-price">

                                            $<?= number_format(
                                                $item['unit_price'],
                                                2
                                            ) ?>

                                        </span>


                                        <span class="discount">

                                            <?= number_format(
                                                $item['discount_percent'],
                                                0
                                            ) ?>% OFF

                                        </span>

                                    <?php endif; ?>


                                    <strong>

                                        $<?= number_format(
                                            $item['discounted_price'],
                                            2
                                        ) ?>

                                    </strong>


                                    <span class="per-item">
                                        / item
                                    </span>


                                </div>


                                <!-- Prescription -->

                                <?php if (!empty($item['requires_prescription'])): ?>

                                    <div class="prescription-warning">

                                        Prescription required

                                    </div>

                                <?php endif; ?>


                                <!-- Quantity -->

                                <div class="quantity-section">


                                    <button
                                        type="button"
                                        class="quantity-btn"
                                        onclick="changeQuantity(
                                            <?= (int) $item['cart_item_id'] ?>,
                                            <?= max(
                                                1,
                                                $item['quantity'] - 1
                                            ) ?>
                                        )"
                                    >
                                        −
                                    </button>


                                    <span class="quantity">

                                        <?= (int) $item['quantity'] ?>

                                    </span>


                                    <button
                                        type="button"
                                        class="quantity-btn"
                                        onclick="changeQuantity(
                                            <?= (int) $item['cart_item_id'] ?>,
                                            <?= (int) $item['quantity'] + 1 ?>
                                        )"
                                    >
                                        +
                                    </button>


                                </div>


                                <!-- Remove -->

                                <button
                                    type="button"
                                    class="remove-btn"
                                    onclick="removeItem(
                                        <?= (int) $item['cart_item_id'] ?>
                                    )"
                                >
                                    Remove
                                </button>


                            </div>


                            <!-- Item total -->

                            <div class="item-total">

                                $<?= number_format(
                                    $item['item_subtotal'],
                                    2
                                ) ?>

                            </div>


                        </div>


                    <?php endforeach; ?>


                </div>


                <!-- =====================================================
                     ORDER SUMMARY
                ====================================================== -->

                <div class="summary">


                    <h2>
                        Order Summary
                    </h2>


                    <div class="summary-row">

                        <span>
                            Subtotal
                        </span>

                        <span>

                            $<?= number_format(
                                $subtotal,
                                2
                            ) ?>

                        </span>

                    </div>


                    <div class="summary-row">

                        <span>
                            Shipping
                        </span>

                        <span>

                            $<?= number_format(
                                $shipping,
                                2
                            ) ?>

                        </span>

                    </div>


                    <div class="summary-row summary-total">

                        <span>
                            Total
                        </span>

                        <strong>

                            $<?= number_format(
                                $total,
                                2
                            ) ?>

                        </strong>

                    </div>


                    <!-- Checkout -->

                    <a
                        href="checkout.php"
                        class="checkout-btn"
                    >
                        Proceed to Checkout
                    </a>


                    <a
                        href="shop.php"
                        class="continue-btn"
                    >
                        Continue Shopping
                    </a>


                </div>


            </div>


        <?php endif; ?>


    </div>

</div>


<script>


/*
|--------------------------------------------------------------------------
| Change quantity
|--------------------------------------------------------------------------
*/

function changeQuantity(cartItemId, quantity)
{

    if (quantity < 1) {
        quantity = 1;
    }


    const formData =
        new URLSearchParams();


    formData.append(
        'cart_item_id',
        cartItemId
    );


    formData.append(
        'quantity',
        quantity
    );


    fetch(
        'cart.php',
        {
            method: 'POST',

            headers: {
                'Content-Type':
                    'application/x-www-form-urlencoded'
            },

            body:
                formData.toString()
        }
    )


    .then(response => response.json())


    .then(data => {

        if (data.success) {

            window.location.reload();

        } else {

            alert(
                data.message ||
                'Unable to update cart.'
            );
        }

    })


    .catch(error => {

        console.error(error);

        alert(
            'Something went wrong while updating the cart.'
        );

    });

}


/*
|--------------------------------------------------------------------------
| Remove item
|--------------------------------------------------------------------------
*/

function removeItem(cartItemId)
{

    const formData =
        new URLSearchParams();


    formData.append(
        'cart_item_id',
        cartItemId
    );


    formData.append(
        'quantity',
        0
    );


    fetch(
        'cart.php',
        {
            method: 'POST',

            headers: {
                'Content-Type':
                    'application/x-www-form-urlencoded'
            },

            body:
                formData.toString()
        }
    )


    .then(response => response.json())


    .then(data => {

        if (data.success) {

            window.location.reload();

        } else {

            alert(
                data.message ||
                'Unable to remove item.'
            );
        }

    })


    .catch(error => {

        console.error(error);

        alert(
            'Something went wrong while removing the item.'
        );

    });

}


</script>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>