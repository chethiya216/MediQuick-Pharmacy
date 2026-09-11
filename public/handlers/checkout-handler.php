<?php


session_start();
require_once __DIR__ . '/../../includes/db.php';

// 1. Security & Request Validation
validate_checkout_request($conn);

$customer_id = (int) $_SESSION['customer_id'];

// 2. Capture and Sanitize User Input
$checkout_data = [
    'phone'          => trim($_POST['phone'] ?? ''),
    'address'        => trim($_POST['address'] ?? ''),
    'payment_method' => trim($_POST['payment_method'] ?? 'card'),
    'card_number'    => trim($_POST['card_number'] ?? ''),
    'card_expiry'    => trim($_POST['card_expiry'] ?? ''),
    'card_cvv'       => trim($_POST['card_cvv'] ?? '')
];

validate_user_inputs($checkout_data);

// 3. Fetch Cart and Cart Items
$cart_info = get_customer_cart($conn, $customer_id);
$cart_items = get_cart_items($conn, $cart_info['cart_id']);

if (empty($cart_items)) {
    redirect_with_error('../cart.php', "Your cart is empty.");
}

// 4. Calculate Totals
$totals = calculate_order_totals($cart_items);

// 5. Process Database Transaction (Order + Payment + Cleanup)
execute_checkout_transaction(
    $conn, 
    $customer_id, 
    $cart_info['cart_id'], 
    $checkout_data, 
    $cart_items, 
    $totals
);


/*
|--------------------------------------------------------------------------
| HELPER FUNCTIONS (Modular & Human-Readable)
|--------------------------------------------------------------------------
*/

function validate_checkout_request($conn) {
    if (!isset($conn) || !($conn instanceof mysqli)) {
        redirect_with_error('../checkout.php', "Database connection failed.");
    }

    if (empty($_SESSION['customer_id'])) {
        header("Location: ../login.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: ../checkout.php");
        exit;
    }
}

function validate_user_inputs($data) {
    if (empty($data['phone']) || empty($data['address'])) {
        redirect_with_error('../checkout.php', "Phone number and delivery address are required.");
    }

    if ($data['payment_method'] === 'card') {
        $clean_card = str_replace(' ', '', $data['card_number']);

        if ($clean_card !== '4242424242424242' && strlen($clean_card) !== 16) {
            redirect_with_error('../checkout.php', "Invalid  card.");
        }

        if (empty($data['card_expiry']) || empty($data['card_cvv'])) {
            redirect_with_error('../checkout.php', "Please fill in all card payment details.");
        }
    }
}

function get_customer_cart($conn, $customer_id) {
    $stmt = $conn->prepare("SELECT cart_id FROM carts WHERE customer_id = ? LIMIT 1");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$result) {
        header("Location: ../cart.php");
        exit;
    }

    return $result;
}

function get_cart_items($conn, $cart_id) {
    $sql = "
        SELECT 
            ci.product_id, 
            ci.quantity, 
            p.unit_price, 
            p.discount_percent 
        FROM cart_items ci
        INNER JOIN products p ON ci.product_id = p.product_id
        WHERE ci.cart_id = ?
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $unit_price = (float) $row['unit_price'];
        $discount   = (float) $row['discount_percent'];
        
        $price = $discount > 0 ? $unit_price - ($unit_price * $discount / 100) : $unit_price;
        
        $row['final_price']   = $price;
        $row['item_subtotal'] = $price * (int)$row['quantity'];
        
        $items[] = $row;
    }
    $stmt->close();

    return $items;
}

function calculate_order_totals($items) {
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += $item['item_subtotal'];
    }

    $shipping_fee = 3.00;
    $tax_amount   = 0.00;
    $total_amount = $subtotal + $shipping_fee + $tax_amount;

    return [
        'subtotal'     => $subtotal,
        'shipping_fee' => $shipping_fee,
        'tax_amount'   => $tax_amount,
        'total_amount' => $total_amount
    ];
}

function execute_checkout_transaction($conn, $customer_id, $cart_id, $data, $items, $totals) {
    $conn->begin_transaction();

    try {
        // 1. Update customer profile info
        $update_cust = $conn->prepare("UPDATE customers SET phone = ?, address = ? WHERE customer_id = ?");
        $update_cust->bind_param("ssi", $data['phone'], $data['address'], $customer_id);
        $update_cust->execute();
        $update_cust->close();

        // 2. Insert main order record
        $order_status = 'pending';
        $insert_order = $conn->prepare("
            INSERT INTO orders (customer_id, order_date, status, subtotal, tax_amount, shipping_fee, total_amount)
            VALUES (?, NOW(), ?, ?, ?, ?, ?)
        ");
        $insert_order->bind_param(
            "isdddd", 
            $customer_id, 
            $order_status, 
            $totals['subtotal'], 
            $totals['tax_amount'], 
            $totals['shipping_fee'], 
            $totals['total_amount']
        );
        $insert_order->execute();
        $order_id = $conn->insert_id;
        $insert_order->close();

        // 3. Insert individual order items
        foreach ($items as $item) {
            $insert_item = $conn->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, unit_price_at_purchase, item_subtotal)
                VALUES (?, ?, ?, ?, ?)
            ");
            $insert_item->bind_param(
                "iiidd", 
                $order_id, 
                $item['product_id'], 
                $item['quantity'], 
                $item['final_price'], 
                $item['item_subtotal']
            );
            $insert_item->execute();
            $insert_item->close();
        }

        // 4. Record payment details
        $payment_status = ($data['payment_method'] === 'cash') ? 'pending' : 'completed';
        $txn_ref        = 'TXN-' . date('Ymd') . '-' . rand(1000, 9999);
        $paid_at        = ($payment_status === 'completed') ? date('Y-m-d H:i:s') : NULL;

        $insert_pay = $conn->prepare("
            INSERT INTO payments (order_id, payment_method, amount, transaction_reference, payment_status, paid_at)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $insert_pay->bind_param(
            "isdsss", 
            $order_id, 
            $data['payment_method'], 
            $totals['total_amount'], 
            $txn_ref, 
            $payment_status, 
            $paid_at
        );
        $insert_pay->execute();
        $insert_pay->close();

        // 5. Empty the customer's shopping cart
        $clear_cart = $conn->prepare("DELETE FROM cart_items WHERE cart_id = ?");
        $clear_cart->bind_param("i", $cart_id);
        $clear_cart->execute();
        $clear_cart->close();

        $conn->commit();

        $_SESSION['order_success'] = true;
        $_SESSION['last_order_id'] = $order_id;

        header("Location: ../checkout.php");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        redirect_with_error('../checkout.php', "Checkout failed: " . $e->getMessage());
    }
}

function redirect_with_error($url, $message) {
    $_SESSION['checkout_error'] = $message;
    header("Location: " . $url);
    exit;
}