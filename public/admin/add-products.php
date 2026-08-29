<?php

require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../../includes/db.php';



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

                            <!-- Breadcrumb -->
                            <div class="border-bottom pb-3 mb-4 fs-5">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="manage-products.php">Products</a></li>
                                        <li class="breadcrumb-item active text-primary">Add product </li>
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
                                                    placeholder="e.g. Paracetamol 500mg" required>
                                            </div>

                                            <div class="mb-3">
                                                <label for="generic_name" class="form-label">Generic name</label>
                                                <input type="text" class="form-control" id="generic_name" name="generic_name"
                                                    placeholder="e.g. Acetaminophen">
                                            </div>

                                            <div class="mb-3">
                                                <label for="description" class="form-label">Description</label>
                                                <textarea class="form-control" id="description" name="description" rows="3"
                                                    placeholder="Usage, composition, warnings"></textarea>
                                            </div>

                                            <div class="row">
                                                <div class="col-6 mb-3">
                                                    <label for="sku" class="form-label">SKU / product code</label>
                                                    <input type="text" class="form-control" id="sku" name="sku"
                                                        placeholder="e.g. PARA-500-10" required>
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label for="barcode" class="form-label">Barcode</label>
                                                    <input type="text" class="form-control" id="barcode" name="barcode"
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
                                                            id="unit_price" name="unit_price" placeholder="0.00" required>
                                                    </div>
                                                </div>
                                                <div class="col-4 mb-3">
                                                    <label for="discount_percent" class="form-label">Discount %</label>
                                                    <input type="number" step="0.01" min="0" max="100" class="form-control"
                                                        id="discount_percent" name="discount_percent" value="0">
                                                </div>
                                                <div class="col-4 mb-3">
                                                    <label for="reorder_level" class="form-label">Reorder level</label>
                                                    <input type="number" min="0" class="form-control"
                                                        id="reorder_level" name="reorder_level" value="0">
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
                                                        <option value="">Select…</option>
                                                        <option value="tablet">Tablet</option>
                                                        <option value="syrup">Syrup</option>
                                                        <option value="capsule">Capsule</option>
                                                        <option value="cream">Cream</option>
                                                        <option value="injection">Injection</option>
                                                    </select>
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label for="strength" class="form-label">Strength</label>
                                                    <input type="text" class="form-control" id="strength" name="strength"
                                                        placeholder="e.g. 500mg">
                                                </div>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="requires_prescription"
                                                    name="requires_prescription" value="1">
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
                                            
                                            <!-- Upload Container / Preview Area -->
                                            <label for="product_image" 
                                                class="d-flex flex-column align-items-center justify-content-center text-center border border-2 border-dashed rounded p-3 position-relative overflow-hidden w-100" 
                                                style="cursor:pointer; min-height:180px;">
                                                
                                                <!-- Initial Upload Content (Icon + Text) -->
                                                <div id="upload_prompt" class="d-flex flex-column align-items-center">
                                                    <i class="bx bx-cloud-upload bx-md mb-2 text-muted"></i>
                                                    <span class="text-muted">Drag &amp; drop / browse</span>
                                                    <small class="text-muted mt-1">PNG/JPG, max 2MB</small>
                                                </div>

                                                <!-- Preview Image (Hidden initially) -->
                                                <img id="image_preview" 
                                                    src="#" 
                                                    alt="Product Preview" 
                                                    class="d-none position-absolute top-0 start-0 w-100 h-100 rounded" 
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
                                                        <option value="<?php echo (int) $cat['category_id']; ?>">
                                                            <?php echo htmlspecialchars($cat['category_name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label for="supplier_id" class="form-label">
                                                    Supplier
                                                    <!-- products table has no supplier_id column yet — see note at top of file -->
                                                </label>
                                                <select class="form-select" id="supplier_id" name="supplier_id" required>
                                                    <option value="">Select…</option>
                                                    <?php foreach ($suppliers as $sup): ?>
                                                        <option value="<?php echo (int) $sup['supplier_id']; ?>">
                                                            <?php echo htmlspecialchars($sup['name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <label class="form-label d-block mb-1">Status</label>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="status" id="status_active"
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
                                <button type="submit" class="btn btn-primary">Save product</button>
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