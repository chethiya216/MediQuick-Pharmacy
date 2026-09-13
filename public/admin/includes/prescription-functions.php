<?php
// public/admin/includes/prescription-functions.php

/**
 * Fetch prescription and customer details by prescription ID.
 */
function getConfirmedPrescriptionById(mysqli $conn, int $prescription_id): ?array {
    $sql = "
        SELECT 
            p.prescription_id,
            p.file_path,
            p.customer_id,
            p.status,
            p.customer_status,
            p.created_at,
            c.first_name,
            c.last_name,
            c.email,
            c.phone
        FROM prescriptions p
        INNER JOIN customers c ON c.customer_id = p.customer_id
        WHERE p.prescription_id = ?
          AND p.status = 'verified'
          AND p.customer_status = 'confirmed'
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $prescription_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $result ?: null;
}

/**
 * Fetch all active products for selection.
 */
function getActiveProducts(mysqli $conn): array {
    $sql = "SELECT product_id, product_name, generic_name, unit_price, stock_quantity 
            FROM products 
            WHERE status = 'active' 
            ORDER BY product_name ASC";
            
    $result = $conn->query($sql);
    $products = [];
    
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}

/**
 * Execute order creation, order items insertion, and inventory updates.
 */
function createPrescriptionOrder(mysqli $conn, int $customerId, int $prescriptionId, array $items, array $shippingAddress): int {
    $conn->begin_transaction();

    try {
        // Calculate Totals
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += (float)$item['price'] * (int)$item['quantity'];
        }
        $tax_amount = round($subtotal * 0.08, 2);
        $shipping_fee = 5.00;
        $total_amount = $subtotal + $tax_amount + $shipping_fee;

        // Insert into Orders Table
        $order_sql = "
            INSERT INTO orders 
            (customer_id, prescription_id, subtotal, tax_amount, shipping_fee, total_amount, status, 
             shipping_address_line1, shipping_address_line2, shipping_city, shipping_state, shipping_postal_code, shipping_country)
            VALUES (?, ?, ?, ?, ?, ?, 'confirmed', ?, ?, ?, ?, ?, ?)
        ";
        
        $order_stmt = $conn->prepare($order_sql);
        $order_stmt->bind_param(
            "iiddddssssss", 
            $customerId, 
            $prescriptionId, 
            $subtotal, 
            $tax_amount, 
            $shipping_fee, 
            $total_amount,
            $shippingAddress['line1'],
            $shippingAddress['line2'],
            $shippingAddress['city'],
            $shippingAddress['state'],
            $shippingAddress['postal_code'],
            $shippingAddress['country']
        );
        
        $order_stmt->execute();
        $order_id = $conn->insert_id;
        $order_stmt->close();

        // Insert Order Items & Update Inventory Stock
        $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, unit_price_at_purchase, item_subtotal) VALUES (?, ?, ?, ?, ?)";
        $stock_sql = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?";
        
        $item_stmt = $conn->prepare($item_sql);
        $stock_stmt = $conn->prepare($stock_sql);

        foreach ($items as $item) {
            $qty = (int)$item['quantity'];
            $price = (float)$item['price'];
            $item_total = $price * $qty;
            $product_id = (int)$item['product_id'];
            
            $item_stmt->bind_param("iiidd", $order_id, $product_id, $qty, $price, $item_total);
            $item_stmt->execute();

            $stock_stmt->bind_param("ii", $qty, $product_id);
            $stock_stmt->execute();
        }

        $item_stmt->close();
        $stock_stmt->close();

        $conn->commit();
        return $order_id;

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}