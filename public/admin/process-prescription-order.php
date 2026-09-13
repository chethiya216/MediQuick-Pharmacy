<?php
// public/admin/process-prescription-order.php
session_start();

require_once __DIR__ . '/../../includes/auth.php';
requirePharmacist();

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/includes/prescription-functions.php';

$pageTitle = "Process Prescription - MediQuick";

$prescription_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($prescription_id <= 0) {
    die("Invalid Prescription ID.");
}

$prescription = getConfirmedPrescriptionById($conn, $prescription_id);

if (!$prescription) {
    die("Prescription not found or not yet confirmed by customer.");
}

$products = getActiveProducts($conn);

// Flash error reading and clearing
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html
  lang="en"
  class="light-style layout-menu-fixed"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="../assets/"
  data-template="vertical-menu-template-free"
>

<?php require_once __DIR__ . '/includes/head.php'; ?>

<body>
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
      
      <!-- SIDEBAR -->
      <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

      <!-- Layout container -->
      <div class="layout-page">
        
        <!-- HEADER -->
        <?php require_once __DIR__ . '/includes/header.php'; ?>

        <!-- Content wrapper -->
        <div class="content-wrapper">
          <div class="container-xxl flex-grow-1 container-p-y">
            
            <!-- Page Heading -->
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h4 class="fw-bold py-3 mb-0">
                <span class="text-muted fw-light">Prescriptions /</span> Process Order #<?= $prescription_id ?>
              </h4>
              <a href="manage-prescriptions.php" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Go Back
              </a>
            </div>

            <!-- Error Alerts -->
            <?php if (!empty($error)): ?>
              <div class="alert alert-danger alert-dismissible mb-4" role="alert">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            <?php endif; ?>

            <!-- Main Split Layout -->
            <div class="row">
              
              <!-- LEFT PANEL: Customer Details & Uploaded Image -->
              <div class="col-lg-5 mb-4">
                <div class="card h-100">
                  <div class="card-header border-bottom py-3">
                    <h5 class="card-title mb-0 fw-bold text-primary">Prescription Details</h5>
                  </div>
                  <div class="card-body pt-4">
                    <div class="mb-4">
                      <p class="mb-2"><strong>Customer:</strong> <?= htmlspecialchars($prescription['first_name'] . ' ' . $prescription['last_name']) ?></p>
                      <p class="mb-2"><strong>Phone:</strong> <?= htmlspecialchars($prescription['phone']) ?></p>
                      <p class="mb-0"><strong>Email:</strong> <?= htmlspecialchars($prescription['email']) ?></p>
                    </div>

                    <h6 class="fw-bold text-dark mb-3">Uploaded Prescription Document</h6>
                    <div class="text-center p-3 bg-light border rounded">
                      <a href="../<?= htmlspecialchars($prescription['file_path']); ?>" target="_blank">
                        <img 
                          src="../<?= htmlspecialchars($prescription['file_path']); ?>"
                          alt="Prescription Image" 
                          class="img-fluid rounded border" 
                          style="max-height: 400px; object-fit: contain;"
                          onerror="this.onerror=null; this.src='../assets/img/illustrations/page-misc-error-light.png';"
                        >
                      </a>
                    </div>
                  </div>
                </div>
              </div>

              <!-- RIGHT PANEL: Product Selection & Order Creation -->
              <div class="col-lg-7 mb-4">
                <div class="card h-100">
                  <div class="card-header border-bottom py-3">
                    <h5 class="card-title mb-0 fw-bold text-primary">Order & Shipping Details</h5>
                  </div>
                  <div class="card-body pt-4">
                    
                    <!-- Form posts to isolated Handler script -->
                    <form action="handlers/process-order-handler.php" method="POST" id="order_form" onsubmit="return prepareFormSubmission();">
                      <input type="hidden" name="prescription_id" value="<?= $prescription_id ?>">

                      <!-- Product Selector -->
                      <h6 class="fw-bold text-dark mb-3">1. Select Prescribed Products</h6>
                      <div class="mb-3">
                        <select id="product_select" class="form-select">
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

                      <div class="row align-items-end mb-4">
                        <div class="col">
                          <label for="product_qty" class="form-label fw-bold">Quantity</label>
                          <input type="number" id="product_qty" class="form-control" value="1" min="1">
                        </div>
                        <div class="col-auto">
                          <button type="button" class="btn btn-primary px-4" onclick="addProductToTable()">
                            <i class="bx bx-plus me-1"></i> Add Item
                          </button>
                        </div>
                      </div>

                      <!-- Selected Items Table -->
                      <div class="table-responsive text-nowrap border rounded mb-3">
                        <table id="selected_items_table" class="table table-hover mb-0">
                          <thead class="table-light">
                            <tr>
                              <th>Product</th>
                              <th>Price</th>
                              <th>Qty</th>
                              <th>Total</th>
                              <th class="text-center" style="width: 60px;">Action</th>
                            </tr>
                          </thead>
                          <tbody id="items_tbody">
                            <!-- Populated via JavaScript -->
                          </tbody>
                        </table>
                      </div>

                      <!-- Summary breakdown -->
                      <div class="bg-light p-3 rounded mb-4">
                        <div class="d-flex justify-content-between mb-1">
                          <span>Subtotal:</span>
                          <strong>$<span id="summary_subtotal">0.00</span></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                          <span>Tax (8%):</span>
                          <strong>$<span id="summary_tax">0.00</span></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                          <span>Shipping Fee:</span>
                          <strong>$<span id="summary_shipping">5.00</span></strong>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between fs-5 text-dark">
                          <strong>Grand Total:</strong>
                          <strong>$<span id="summary_total">5.00</span></strong>
                        </div>
                      </div>

                      <!-- Shipping Address Form -->
                      <h6 class="fw-bold text-dark mb-3">2. Delivery Address</h6>
                      <div class="row g-3 mb-4">
                        <div class="col-12">
                          <label class="form-label">Address Line 1 *</label>
                          <input type="text" name="shipping_address_line1" class="form-control" placeholder="123 Main St" required>
                        </div>
                        <div class="col-12">
                          <label class="form-label">Address Line 2</label>
                          <input type="text" name="shipping_address_line2" class="form-control" placeholder="Apt, Suite, Unit (optional)">
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">City *</label>
                          <input type="text" name="shipping_city" class="form-control" placeholder="City" required>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">State *</label>
                          <input type="text" name="shipping_state" class="form-control" placeholder="State" required>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">Postal Code *</label>
                          <input type="text" name="shipping_postal_code" class="form-control" placeholder="Postal / ZIP Code" required>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">Country *</label>
                          <input type="text" name="shipping_country" class="form-control" value="Sri Lanka" required>
                        </div>
                      </div>

                      <input type="hidden" name="items_json" id="items_json">
                      <button type="submit" name="place_order" class="btn btn-success w-100 py-2 fw-bold">
                        <i class="bx bx-check-circle me-1"></i> Place Order for Customer
                      </button>
                    </form>

                  </div>
                </div>
              </div>

            </div>
          </div>

          <!-- FOOTER -->
          <?php require_once __DIR__ . '/includes/footer.php'; ?>

          <div class="content-backdrop fade"></div>
        </div>
      </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
  </div>

<script>
let selectedProducts = [];
const SHIPPING_FEE = 5.00;
const TAX_RATE = 0.08;

function addProductToTable() {
    const select = document.getElementById('product_select');
    const selectedOption = select.options[select.selectedIndex];
    const qtyInput = document.getElementById('product_qty');
    const qty = parseInt(qtyInput.value);

    if (!select.value) {
        alert("Please select a product.");
        return;
    }

    if (isNaN(qty) || qty <= 0) {
        alert("Please enter a valid quantity.");
        return;
    }

    const productId = parseInt(select.value);
    const name = selectedOption.getAttribute('data-name');
    const price = parseFloat(selectedOption.getAttribute('data-price'));
    const stock = parseInt(selectedOption.getAttribute('data-stock'));

    if (qty > stock) {
        alert(`Warning: Selected quantity exceeds current stock (${stock}).`);
    }

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
    let subtotal = 0;

    if (selectedProducts.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">No products selected.</td></tr>';
    } else {
        selectedProducts.forEach(item => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td><strong>${escapeHtml(item.name)}</strong></td>
                <td>$${item.price.toFixed(2)}</td>
                <td>${item.quantity}</td>
                <td>$${itemTotal.toFixed(2)}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeProduct(${item.product_id})">
                        <i class="bx bx-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    const tax = subtotal * TAX_RATE;
    const total = subtotal > 0 ? (subtotal + tax + SHIPPING_FEE) : 0;

    document.getElementById('summary_subtotal').innerText = subtotal.toFixed(2);
    document.getElementById('summary_tax').innerText = tax.toFixed(2);
    document.getElementById('summary_shipping').innerText = subtotal > 0 ? SHIPPING_FEE.toFixed(2) : "0.00";
    document.getElementById('summary_total').innerText = total.toFixed(2);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.innerText = text;
    return div.innerHTML;
}

function prepareFormSubmission() {
    if (selectedProducts.length === 0) {
        alert("Please add at least one product to place the order.");
        return false;
    }
    document.getElementById('items_json').value = JSON.stringify(selectedProducts);
    return true;
}

renderTable();
</script>

</body>
</html>