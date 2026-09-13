<?php
session_start();

// 1. Check Admin/Pharmacist Authentication
if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$prescription_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($prescription_id <= 0) {
    die("Invalid Prescription ID.");
}

// 2. Fetch Prescription details along with Customer details
$sql = "
    SELECT 
        p.prescription_id,
        p.customer_id,
        p.prescription_file,
        p.status,
        p.created_at,
        c.first_name,
        c.last_name,
        c.email,
        c.phone
    FROM prescriptions p
    INNER JOIN customers c ON c.customer_id = p.customer_id
    WHERE p.prescription_id = ?
      AND p.status = 'confirmed' 
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $prescription_id);
$stmt->execute();
$prescription = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$prescription) {
    die("Prescription not found or not yet confirmed by customer.");
}

// 3. Handle Order Placement (POST Request)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $selected_items = json_decode($_POST['items_json'], true);
    
    if (empty($selected_items)) {
        $error = "Please select at least one product before placing the order.";
    } else {
        $conn->begin_transaction();

        try {
            // Calculate Totals
            $subtotal = 0;
            foreach ($selected_items as $item) {
                $subtotal += (float)$item['price'] * (int)$item['quantity'];
            }
            $shipping = 3.00;
            $total = $subtotal + $shipping;

            // Insert into Orders Table
            $order_sql = "
                INSERT INTO orders (customer_id, prescription_id, subtotal, shipping, total_amount, status, created_at)
                VALUES (?, ?, ?, ?, ?, 'processing', NOW())
            ";
            $order_stmt = $conn->prepare($order_sql);
            $order_stmt->bind_param("iiddd", $prescription['customer_id'], $prescription_id, $subtotal, $shipping, $total);
            $order_stmt->execute();
            $order_id = $conn->insert_id;
            $order_stmt->close();

            // Insert Order Items & Update Inventory Stock
            $item_sql = "INSERT INTO order_items (order_id, product_id, price, quantity, total) VALUES (?, ?, ?, ?, ?)";
            $stock_sql = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?";
            
            $item_stmt = $conn->prepare($item_sql);
            $stock_stmt = $conn->prepare($stock_sql);

            foreach ($selected_items as $item) {
                $item_total = (float)$item['price'] * (int)$item['quantity'];
                
                $item_stmt->bind_param("iidid", $order_id, $item['product_id'], $item['price'], $item['quantity'], $item_total);
                $item_stmt->execute();

                $stock_stmt->bind_param("ii", $item['quantity'], $item['product_id']);
                $stock_stmt->execute();
            }

            $item_stmt->close();
            $stock_stmt->close();

            // Update Prescription Status to 'completed'
            $rx_upd = $conn->prepare("UPDATE prescriptions SET status = 'completed' WHERE prescription_id = ?");
            $rx_upd->bind_param("i", $prescription_id);
            $rx_upd->execute();
            $rx_upd->close();

            $conn->commit();
            header("Location: orders.php?success=order_created&order_id=" . $order_id);
            exit;

        } catch (Exception $e) {
            $conn->rollback();
            $error = "Order processing failed: " . $e->getMessage();
        }
    }
}

// 4. Load Active Products for Selector Dropdown
$products_res = $conn->query("SELECT product_id, product_name, generic_name, unit_price, stock_quantity FROM products WHERE status = 'active' ORDER BY product_name ASC");
$products = [];
while ($row = $products_res->fetch_assoc()) {
    $products[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Process Prescription Order #<?= $prescription_id ?></title>
    <style>
        .container { max-width: 1100px; margin: 20px auto; font-family: Arial, sans-serif; }
        .flex-wrapper { display: flex; gap: 20px; }
        .box { background: #fff; border: 1px solid #ddd; padding: 20px; border-radius: 8px; flex: 1; }
        .rx-image { width: 100%; max-height: 450px; object-fit: contain; border: 1px solid #eee; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f4f4f4; }
        .form-group { margin-bottom: 15px; }
        .form-control { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .btn { padding: 10px 18px; border: none; cursor: pointer; border-radius: 4px; font-weight: bold; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; width: 100%; margin-top: 15px; }
        .btn-danger { background: #dc3545; color: white; padding: 4px 8px; font-size: 12px; }
        .error { color: red; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Process Prescription Order #<?= $prescription_id ?></h2>
    
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="flex-wrapper">
        <!-- LEFT: Customer Details & Uploaded Image -->
        <div class="box">
            <h3>Prescription Details</h3>
            <p><strong>Customer:</strong> <?= htmlspecialchars($prescription['first_name'] . ' ' . $prescription['last_name']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($prescription['phone']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($prescription['email']) ?></p>

            <h4>Uploaded Prescription Document</h4>
            <a href="../<?= htmlspecialchars($prescription['prescription_file']) ?>" target="_blank">
                <img src="../<?= htmlspecialchars($prescription['prescription_file']) ?>" alt="Prescription Image" class="rx-image">
            </a>
        </div>

        <!-- RIGHT: Product Selection & Order Creation -->
        <div class="box">
            <h3>Select Prescribed Products</h3>

            <div class="form-group">
                <label for="product_select">Search & Select Product</label>
                <select id="product_select" class="form-control">
                    <option value="">-- Choose Product --</option>
                    <?php foreach ($products as $p): ?>
                        <option 
                            value="<?= $p['product_id'] ?>" 
                            data-name="<?= htmlspecialchars($p['product_name']) ?>" 
                            data-price="<?= $p['unit_price'] ?>"
                            data-stock="<?= $p['stock_quantity'] ?>"
                        >
                            <?= htmlspecialchars($p['product_name']) ?> (Stock: <?= $p['stock_quantity'] ?>) - $<?= number_format($p['unit_price'], 2) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group flex-wrapper">
                <div style="flex: 1;">
                    <label for="product_qty">Quantity</label>
                    <input type="number" id="product_qty" class="form-control" value="1" min="1">
                </div>
                <div style="align-self: flex-end;">
                    <button type="button" class="btn btn-primary" onclick="addProductToTable()">Add to List</button>
                </div>
            </div>

            <!-- SELECTED PRODUCTS TABLE -->
            <h4>Selected Prescription Items</h4>
            <table id="selected_items_table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="items_tbody">
                    <!-- Populated via Javascript -->
                </tbody>
            </table>

            <div style="margin-top: 15px; text-align: right;">
                <strong>Grand Total: $<span id="grand_total">0.00</span></strong>
            </div>

            <!-- Form submission to place order -->
            <form method="POST" id="order_form" onsubmit="return prepareFormSubmission();">
                <input type="hidden" name="items_json" id="items_json">
                <button type="submit" name="place_order" class="btn btn-success">Place Order for Customer</button>
            </form>
        </div>
    </div>
</div>

<script>
let selectedProducts = [];

function addProductToTable() {
    const select = document.getElementById('product_select');
    const selectedOption = select.options[select.selectedIndex];
    const qtyInput = document.getElementById('product_qty');
    const qty = parseInt(qtyInput.value);

    if (!select.value) {
        alert("Please select a product.");
        return;
    }

    const productId = parseInt(select.value);
    const name = selectedOption.getAttribute('data-name');
    const price = parseFloat(selectedOption.getAttribute('data-price'));
    const stock = parseInt(selectedOption.getAttribute('data-stock'));

    if (qty > stock) {
        alert(`Warning: Selected quantity exceeds current stock (${stock}).`);
    }

    // Check if item already added
    const existingIndex = selectedProducts.findIndex(item => item.product_id === productId);
    if (existingIndex > -1) {
        selectedProducts[existingIndex].quantity += qty;
    } else {
        selectedProducts.push({
            product_id: productId,
            name: name,
            price: price,
            quantity: qty
        });
    }

    renderTable();
    select.value = "";
    qtyInput.value = 1;
}

function removeProduct(productId) {
    selectedProducts = selectedProducts.filter(item => item.product_id !== productId);
    renderTable();
}

function renderTable() {
    const tbody = document.getElementById('items_tbody');
    tbody.innerHTML = '';
    let grandTotal = 0;

    if (selectedProducts.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No products selected yet.</td></tr>';
    } else {
        selectedProducts.forEach(item => {
            const itemTotal = item.price * item.quantity;
            grandTotal += itemTotal;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${item.name}</td>
                <td>$${item.price.toFixed(2)}</td>
                <td>${item.quantity}</td>
                <td>$${itemTotal.toFixed(2)}</td>
                <td>
                    <button type="button" class="btn btn-danger" onclick="removeProduct(${item.product_id})">Remove</button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    document.getElementById('grand_total').innerText = grandTotal.toFixed(2);
}

function prepareFormSubmission() {
    if (selectedProducts.length === 0) {
        alert("Please add at least one product to place the order.");
        return false;
    }
    document.getElementById('items_json').value = JSON.stringify(selectedProducts);
    return true;
}

// Initial render
renderTable();
</script>

</body>
</html>