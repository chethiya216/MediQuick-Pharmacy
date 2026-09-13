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
 * Fetch all active products with total stock calculated dynamically from active product_batches.
 */
function getActiveProducts(mysqli $conn): array {
    $sql = "
        SELECT 
            p.product_id, 
            p.product_name, 
            p.generic_name, 
            p.unit_price,
            COALESCE(SUM(pb.quantity_on_hand), 0) AS stock_quantity
        FROM products p
        LEFT JOIN product_batches pb 
            ON p.product_id = pb.product_id 
           AND pb.status = 'active' 
           AND (pb.expiry_date IS NULL OR pb.expiry_date >= CURDATE())
           AND pb.quantity_on_hand > 0
        WHERE p.status = 'active'
        GROUP BY p.product_id, p.product_name, p.generic_name, p.unit_price
        ORDER BY p.product_name ASC
    ";
            
    $result = $conn->query($sql);
    $products = [];
    
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}

/**
 * Execute order creation, order items insertion, and batch inventory updates (FEFO strategy).
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

        // Prepare Order Item Insertion
        $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, unit_price_at_purchase, item_subtotal) VALUES (?, ?, ?, ?, ?)";
        $item_stmt = $conn->prepare($item_sql);

        // Prepare Statements for Batch Stock Deduction (FEFO approach)
        $batch_select_sql = "
            SELECT batch_id, quantity_on_hand 
            FROM product_batches 
            WHERE product_id = ? 
              AND status = 'active' 
              AND (expiry_date IS NULL OR expiry_date >= CURDATE())
              AND quantity_on_hand > 0 
            ORDER BY expiry_date ASC, batch_id ASC
            FOR UPDATE
        ";
        $batch_select_stmt = $conn->prepare($batch_select_sql);

        $batch_update_sql = "
            UPDATE product_batches 
            SET quantity_on_hand = quantity_on_hand - ?,
                status = IF(quantity_on_hand - ? <= 0, 'depleted', status)
            WHERE batch_id = ?
        ";
        $batch_update_stmt = $conn->prepare($batch_update_sql);

        foreach ($items as $item) {
            $qty_needed = (int)$item['quantity'];
            $price = (float)$item['price'];
            $item_total = $price * $qty_needed;
            $product_id = (int)$item['product_id'];
            
            // 1. Insert Order Item
            $item_stmt->bind_param("iiidd", $order_id, $product_id, $qty_needed, $price, $item_total);
            $item_stmt->execute();

            // 2. Fetch Batches (FEFO strategy: Earliest Expiry First)
            $batch_select_stmt->bind_param("i", $product_id);
            $batch_select_stmt->execute();
            $batches = $batch_select_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            // Verify sufficient batch stock exists
            $total_available_batch_stock = array_sum(array_column($batches, 'quantity_on_hand'));
            if ($total_available_batch_stock < $qty_needed) {
                throw new Exception("Insufficient stock in active batches for Product ID: " . $product_id);
            }

            // 3. Deduct stock across active batches
            foreach ($batches as $batch) {
                if ($qty_needed <= 0) {
                    break;
                }

                $batch_id = (int)$batch['batch_id'];
                $available_qty = (int)$batch['quantity_on_hand'];

                $deduct_qty = min($qty_needed, $available_qty);
                $qty_needed -= $deduct_qty;

                // Update Batch Quantity & Status
                $batch_update_stmt->bind_param("iii", $deduct_qty, $deduct_qty, $batch_id);
                $batch_update_stmt->execute();
            }
        }

        $item_stmt->close();
        $batch_select_stmt->close();
        $batch_update_stmt->close();

        $conn->commit();
        return $order_id;

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}