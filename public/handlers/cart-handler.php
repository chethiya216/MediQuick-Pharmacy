<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/db.php';

// Verify DB Connection
if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Database connection failed.");
}

// 1. Check Customer Auth
if (empty($_SESSION['customer_id'])) {
    header('Location: login.php?redirect=cart.php');
    exit;
}

$customer_id = (int) $_SESSION['customer_id'];
if ($customer_id <= 0) {
    die("Invalid customer account.");
}

/**
 * Get or create cart ID for customer
 */
function getOrCreateCartId(mysqli $conn, int $customer_id): int {
    $cart_sql = "SELECT cart_id FROM carts WHERE customer_id = ? LIMIT 1";
    $cart_stmt = $conn->prepare($cart_sql);
    $cart_stmt->bind_param("i", $customer_id);
    $cart_stmt->execute();
    $cart_result = $cart_stmt->get_result();

    if ($cart_row = $cart_result->fetch_assoc()) {
        $cart_stmt->close();
        return (int) $cart_row['cart_id'];
    }
    $cart_stmt->close();

    // Create cart if none exists
    $create_cart_sql = "INSERT INTO carts (customer_id, created_at, updated_at) VALUES (?, NOW(), NOW())";
    $create_cart_stmt = $conn->prepare($create_cart_sql);
    $create_cart_stmt->bind_param("i", $customer_id);

    if (!$create_cart_stmt->execute()) {
        $create_cart_stmt->close();
        
        // Retry fetch on duplicate entry
        $retry_stmt = $conn->prepare($cart_sql);
        $retry_stmt->bind_param("i", $customer_id);
        $retry_stmt->execute();
        $retry_res = $retry_stmt->get_result();
        $cart_id = ($row = $retry_res->fetch_assoc()) ? (int)$row['cart_id'] : 0;
        $retry_stmt->close();
        return $cart_id;
    }

    $cart_id = $conn->insert_id;
    $create_cart_stmt->close();
    return $cart_id;
}

$cart_id = getOrCreateCartId($conn, $customer_id);
if ($cart_id <= 0) {
    die("Unable to create or find your shopping cart.");
}
$_SESSION['cart_id'] = $cart_id;

// 2. Handle AJAX Request (Update / Remove Item)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_item_id'])) {
    header('Content-Type: application/json');
    $post_cart_item_id = (int) $_POST['cart_item_id'];
    $post_quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 0;

    if ($post_cart_item_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid cart item.']);
        exit;
    }

    // Check ownership
    $chk_stmt = $conn->prepare("SELECT cart_item_id FROM cart_items WHERE cart_item_id = ? AND cart_id = ? LIMIT 1");
    $chk_stmt->bind_param("ii", $post_cart_item_id, $cart_id);
    $chk_stmt->execute();
    if ($chk_stmt->get_result()->num_rows === 0) {
        $chk_stmt->close();
        echo json_encode(['success' => false, 'message' => 'That item is not in your cart.']);
        exit;
    }
    $chk_stmt->close();

    if ($post_quantity <= 0) {
        $del_stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_item_id = ? AND cart_id = ?");
        $del_stmt->bind_param("ii", $post_cart_item_id, $cart_id);
        $del_stmt->execute();
        $del_stmt->close();
    } else {
        $upd_stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ? AND cart_id = ?");
        $upd_stmt->bind_param("iii", $post_quantity, $post_cart_item_id, $cart_id);
        $upd_stmt->execute();
        $upd_stmt->close();
    }

    $touch_stmt = $conn->prepare("UPDATE carts SET updated_at = NOW() WHERE cart_id = ?");
    if ($touch_stmt) {
        $touch_stmt->bind_param("i", $cart_id);
        $touch_stmt->execute();
        $touch_stmt->close();
    }

    echo json_encode(['success' => true]);
    exit;
}

// 3. Handle Add Product to Cart via GET
if (!empty($_GET['product_id'])) {
    $product_id = (int) $_GET['product_id'];

    $p_stmt = $conn->prepare("SELECT product_id FROM products WHERE product_id = ? AND status = 'active' LIMIT 1");
    $p_stmt->bind_param("i", $product_id);
    $p_stmt->execute();

    if ($p_stmt->get_result()->num_rows > 0) {
        $item_stmt = $conn->prepare("SELECT cart_item_id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ? LIMIT 1");
        $item_stmt->bind_param("ii", $cart_id, $product_id);
        $item_stmt->execute();
        $item_res = $item_stmt->get_result();

        if ($existing = $item_res->fetch_assoc()) {
            $new_qty = (int) $existing['quantity'] + 1;
            $u_stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ? AND cart_id = ?");
            $u_stmt->bind_param("iii", $new_qty, $existing['cart_item_id'], $cart_id);
            $u_stmt->execute();
            $u_stmt->close();
        } else {
            $i_stmt = $conn->prepare("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, 1)");
            $i_stmt->bind_param("ii", $cart_id, $product_id);
            $i_stmt->execute();
            $i_stmt->close();
        }
        $item_stmt->close();

        $touch_stmt = $conn->prepare("UPDATE carts SET updated_at = NOW() WHERE cart_id = ?");
        if ($touch_stmt) {
            $touch_stmt->bind_param("i", $cart_id);
            $touch_stmt->execute();
            $touch_stmt->close();
        }
    }
    $p_stmt->close();

    header('Location: cart.php');
    exit;
}

// 4. Fetch Cart Items & Compute Summary Data
$cart_sql = "
    SELECT ci.cart_item_id, ci.product_id, ci.quantity, p.product_name, p.generic_name,
           p.sku, p.unit_price, p.discount_percent, p.product_image, p.requires_prescription, p.status
    FROM cart_items ci
    INNER JOIN products p ON p.product_id = ci.product_id
    WHERE ci.cart_id = ?
    ORDER BY ci.cart_item_id DESC
";

$stmt = $conn->prepare($cart_sql);
$stmt->bind_param("i", $cart_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$subtotal = 0;

while ($row = $result->fetch_assoc()) {
    $unit_price = (float) $row['unit_price'];
    $discount = (float) ($row['discount_percent'] ?? 0);
    $quantity = (int) $row['quantity'];

    $discounted_price = ($discount > 0) ? $unit_price - ($unit_price * $discount / 100) : $unit_price;
    $item_subtotal = $discounted_price * $quantity;

    $row['discounted_price'] = $discounted_price;
    $row['item_subtotal'] = $item_subtotal;

    $cart_items[] = $row;
    $subtotal += $item_subtotal;
}
$stmt->close();

$shipping = !empty($cart_items) ? 3.00 : 0;
$total = $subtotal + $shipping;

/**
 * Image helper function
 */
function getProductImage($image) {
    if (!empty($image)) {
        $image = str_replace('\\', '/', trim($image));
        $image = ltrim($image, '/');
        if (strpos($image, 'uploads/products/') === 0) {
            return htmlspecialchars($image, ENT_QUOTES, 'UTF-8');
        }
    }
    return 'assets/img/product-1.png';
}