<?php

require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../../includes/db.php';

// 1. Initialize empty variables & edit state
$product_id = null;
$product = [];

// 2. Check if an ID was passed via URL (e.g. add-products.php?id=5)
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $product_id = (int)$_GET['id'];

    // 3. Fetch product details from the database using Prepared Statements
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    }
}


$sql_categories = "SELECT * FROM categories";
$categories = $conn->query($sql_categories);

$sql_products = "SELECT * FROM products";
$products = $conn->query($sql_products);

$sql_supplier = "SELECT * FROM suppliers";
$suppliers = $conn->query($sql_supplier);

$sql_prod_batch = "SELECT * FROM product_batches";
$product_batches = $conn->query($sql_prod_batch);


?>

<!DOCTYPE html>
<html
    lang="en"
    class="light-style layout-menu-fixed"
    dir="ltr"
    data-theme="theme-default"
    data-assets-path="../admin-assets/assets/"
    data-template="vertical-menu-template-free"
>

<?php require_once __DIR__ . '/includes/head.php'; ?>

<body>

<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">

        <!-- Sidebar -->
        <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

        <!-- Page Container -->
        <div class="layout-page">

            <!-- Header/Navbar -->
            <?php require_once __DIR__ . '/includes/header.php'; ?>

            <!-- Content Wrapper -->
            <div class="content-wrapper">

                <!-- Main Content -->
                <div class="container-xxl flex-grow-1 container-p-y">

                    <!-- MASTER FORM CONTAINER / BACKGROUND FRAME -->
                    <div class="card p-4 bg-white shadow-sm border rounded">
                        <!-- <form method="POST" action="handlers/add-product-handler.php" enctype="multipart/form-data"> -->
                        <form method="POST" action="../admin/handlers/add-product-handler.php" enctype="multipart/form-data">
                            <?php if ($product_id): ?>
                                <input type="hidden" name="product_id" value="<?= $product_id; ?>">
                            <?php endif; ?>

                            <!-- Breadcrumb -->
                            <div class="border-bottom pb-3 mb-4 fs-5">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="manage-products.php">Products</a></li>
                                        <li class="breadcrumb-item active text-primary">
                                            <?php if (!empty($product_id)): ?>
                                                Edit product
                                            <?php else: ?>
                                                Add product
                                            <?php endif; ?>
                                        </li>
                                    </ol>
                                </nav>
                            </div>

                            <div class="row">
                                <!-- LEFT COLUMN -->
                                <div class="col-12 col-lg-8">

                                    <!-- General information -->
                                    <div class="card mb-4 border">
                                        <div class="card-header">
                                            <h5 class="mb-0">General information</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="product_name" class="form-label">Product name</label>
                                                <input type="text" class="form-control" id="product_name" name="product_name"
                                                    placeholder="e.g. Paracetamol 500mg" value="<?= htmlspecialchars($product['product_name'] ?? ''); ?>" required>
                                            </div>

                                            <div class="mb-3">
                                                <label for="generic_name" class="form-label">Generic name</label>
                                                <input type="text" class="form-control" id="generic_name" name="generic_name" value="<?= htmlspecialchars($product['generic_name'] ?? ''); ?>"
                                                    placeholder="e.g. Acetaminophen">
                                            </div>

                                            <div class="mb-3">
                                                <label for="description" class="form-label">Description</label>
                                                <textarea class="form-control" id="description" name="description" rows="3"
                                                    placeholder="Usage, composition, warnings"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                                            </div>

                                            <div class="row">
                                                <div class="col-6 mb-3">
                                                    <label for="sku" class="form-label">SKU / product code</label>
                                                    <input type="text" class="form-control" id="sku" name="sku" value="<?= htmlspecialchars($product['sku'] ?? ''); ?>"
                                                        placeholder="e.g. PARA-500-10" required>
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label for="barcode" class="form-label">Barcode</label>
                                                    <input type="text" class="form-control" id="barcode" name="barcode" value="<?= htmlspecialchars($product['barcode'] ?? ''); ?>"
                                                        placeholder="e.g. 8901234567890">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pricing & stock -->
                                    <div class="card mb-4 border">
                                        <div class="card-header">
                                            <h5 class="mb-0">Pricing &amp; stock</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-4 mb-3">
                                                    <label for="unit_price" class="form-label">Unit price</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">Rs.</span>
                                                        <input type="number" step="0.01" min="0" class="form-control"
                                                            id="unit_price" name="unit_price" placeholder="0.00" value="<?= htmlspecialchars($product['unit_price'] ?? ''); ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-4 mb-3">
                                                    <label for="discount_percent" class="form-label">Discount %</label>
                                                    <input type="number" step="0.01" min="0" max="100" class="form-control"
                                                        id="discount_percent" value="<?= htmlspecialchars($product['discount_percent'] ?? ''); ?>" placeholder="0" name="discount_percent">
                                                </div>
                                                <div class="col-4 mb-3">
                                                    <label for="reorder_level" class="form-label">Reorder level</label>
                                                    <input type="number" min="0" class="form-control" placeholder="0" value="<?= htmlspecialchars($product['reorder_level'] ?? ''); ?>"
                                                        id="reorder_level" name="reorder_level">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pharmacy details -->
                                    <div class="card mb-4 border">
                                        <div class="card-header">
                                            <h5 class="mb-0">Dosage details</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-6 mb-3">
                                                    <label for="dosage_form" class="form-label">Dosage form</label>
                                                    <select class="form-select" id="dosage_form" name="dosage_form" required>
                                                        <option value="" disabled selected>Select dosage form</option>
                                                        <option value="tablet" <?= ($product['dosage_form'] ?? '') === 'tablet' ? 'selected' : ''; ?>>Tablet</option>
                                                        <option value="syrup" <?= ($product['dosage_form'] ?? '') === 'syrup' ? 'selected' : ''; ?>>Syrup</option>
                                                        <option value="capsule" <?= ($product['dosage_form'] ?? '') === 'capsule' ? 'selected' : ''; ?>>Capsule</option>
                                                        <option value="cream" <?= ($product['dosage_form'] ?? '') === 'cream' ? 'selected' : ''; ?>>Cream</option>
                                                        <option value="injection" <?= ($product['dosage_form'] ?? '') === 'injection' ? 'selected' : ''; ?>>Injection</option>
                                                    </select>
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label for="strength" class="form-label">Strength</label>
                                                    <input type="text" class="form-control" id="strength" name="strength" value="<?= htmlspecialchars($product['strength'] ?? ''); ?>"
                                                        placeholder="e.g. 500mg">
                                                </div>
                                            </div>

                                            <div class="form-check">
                                                <!-- Sends 0 if the checkbox below is left unchecked -->
                                                <input type="hidden" name="requires_prescription" value="0">
                                                
                                                <!-- Sends 1 if checked (overrides the hidden input value above) -->
                                                <input class="form-check-input" 
                                                    type="checkbox" 
                                                    id="requires_prescription"
                                                    name="requires_prescription" 
                                                    value="1" 
                                                    <?= !empty($product['requires_prescription']) && $product['requires_prescription'] == 1 ? 'checked' : ''; ?>>
                                                    
                                                <label class="form-check-label" for="requires_prescription">
                                                    Requires a prescription
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <!-- RIGHT COLUMN -->
                                <div class="col-12 col-lg-4">

                                    <!-- Product image -->
                                    <div class="card mb-4 border flex-fill">
                                        <div class="card-header">
                                            <h5 class="mb-0">Product Image</h5>
                                        </div>
                                        <div class="card-body d-flex flex-column justify-content-center">
                                            <?php 
                                                // Check if an image exists in the database record
                                                $has_image = !empty($product['product_image']);
                                                $image_src = $has_image ? '../' . htmlspecialchars($product['product_image']) : '#';
                                            ?>
                                            <!-- Upload Container / Preview Area -->
                                            <label for="product_image" 
                                                class="d-flex flex-column align-items-center justify-content-center text-center border border-2 border-dashed rounded p-3 position-relative overflow-hidden w-100" 
                                                style="cursor:pointer; min-height:180px;">
                                                
                                                <!-- Hide prompt if an image already exists -->
                                                <div id="upload_prompt" class="d-flex flex-column align-items-center <?= $has_image ? 'd-none' : ''; ?>">
                                                    <i class="bx bx-cloud-upload bx-md mb-2 text-muted"></i>
                                                    <span class="text-muted">Drag &amp; drop / browse</span>
                                                    <small class="text-muted mt-1">PNG/JPG, max 2MB</small>
                                                </div>

                                                <!-- Show image and dynamically load src if an image exists -->
                                                <img id="image_preview" 
                                                    src="<?= $image_src; ?>" 
                                                    alt="Product Preview" 
                                                    class="<?= $has_image ? '' : 'd-none'; ?> position-absolute top-0 start-0 w-100 h-100 rounded" 
                                                    style="object-fit: contain; background-color: #f8f9fa;">

                                                <!-- Hidden Input -->
                                                <input type="file" id="product_image" name="product_image" accept="image/png, image/jpeg" class="d-none">
                                            </label>

                                            <!-- Reset / Remove Button (Hidden initially) -->
                                            <div id="preview_actions" class="d-none justify-content-end mt-2">
                                                <button type="button" id="remove_image" class="btn btn-sm btn-outline-danger">
                                                    <i class="bx bx-trash"></i> Remove Image
                                                </button>
                                            </div>

                                        </div>
                                    </div>

                                    <!-- Organize -->
                                    <div class="card mb-4 border">
                                        <div class="card-header">
                                            <h5 class="mb-0">Organize</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="category_id" class="form-label">Category</label>
                                                <select class="form-select" id="category_id" name="category_id" required>
                                                    <option value="">Select…</option>
                                                    <?php foreach ($categories as $cat): ?>
                                                        <?php 
                                                            $isSelected = isset($product['category_id']) && $product['category_id'] == $cat['category_id'] ? 'selected' : '';
                                                        ?>
                                                        <option value="<?= (int) $cat['category_id']; ?>" <?= $isSelected; ?>>
                                                            <?= htmlspecialchars($cat['category_name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <label class="form-label d-block mb-1">Status</label>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="status" id="status_active" value="<?= htmlspecialchars($product['status'] ?? 'active'); ?>"
                                                    value="active" checked>
                                                <label class="form-check-label" for="status_active">Active</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="status" id="status_draft"
                                                    value="draft">
                                                <label class="form-check-label" for="status_draft">Draft</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>

                            <!-- RIGHT-ALIGNED BUTTONS INSIDE BACKGROUND FRAME -->
                            <div class="d-flex justify-content-end gap-2 pt-3 border-top mt-2">
                                <a href="manage-products.php" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary"><?= $product_id ? 'Update Product' : 'Save Product'; ?></button>
                            </div>

                        </form>
                    </div>
                    <!-- / MASTER FORM CONTAINER -->

                </div>
                <!-- / Main Content -->

                <!-- Footer -->
                <?php require_once __DIR__ . '/includes/footer.php'; ?>

                <div class="content-backdrop fade"></div>
            </div>
            <!-- / Content Wrapper -->

        </div>
        <!-- / Page Container -->

    </div>
    <!-- / Layout Container -->

    <div class="layout-overlay layout-menu-toggle"></div>
</div>
<!-- / Layout Wrapper -->
</body>
</html>