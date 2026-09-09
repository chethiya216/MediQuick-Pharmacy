<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
require_once __DIR__ . '/../../includes/db.php';

$pageTitle = "Supplier - MediQuick";

// Initialize default variables
$supplier_id   = null;
$supplierName  = '';
$contactPerson = '';
$phone         = '';
$email         = '';
$address       = '';
$status        = 'active';
$errors        = [];

// 1. Fetch supplier details if editing (Fixed $_GET key to 'supplier_id')
if (isset($_GET['supplier_id']) && is_numeric($_GET['supplier_id'])) {
    $supplier_id = (int)$_GET['supplier_id'];

    $stmt = $conn->prepare("SELECT * FROM suppliers WHERE supplier_id = ?");
    $stmt->bind_param("i", $supplier_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $supplierName  = $row['supplier_name'] ?? '';
        $contactPerson = $row['contact_person'] ?? '';
        $phone         = $row['phone'] ?? '';
        $email         = $row['email'] ?? '';
        $address       = $row['address'] ?? '';
        $status        = $row['status'] ?? 'active';
    }
}

// 2. Restore user input if validation failed in handler
if (isset($_SESSION['old_input'])) {
    $supplierName  = $_SESSION['old_input']['supplier_name'] ?? $supplierName;
    $contactPerson = $_SESSION['old_input']['contact_person'] ?? $contactPerson;
    $phone         = $_SESSION['old_input']['phone'] ?? $phone;
    $email         = $_SESSION['old_input']['email'] ?? $email;
    $address       = $_SESSION['old_input']['address'] ?? $address;
    $status        = $_SESSION['old_input']['status'] ?? $status;
    unset($_SESSION['old_input']);
}
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

        <!-- SIDEBAR -->
        <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

        <div class="layout-page">

            <!-- HEADER -->
            <?php require_once __DIR__ . '/includes/header.php'; ?>

            <!-- CONTENT WRAPPER -->
            <div class="content-wrapper">

                <!-- CONTENT -->
                <div class="container-xxl flex-grow-1 container-p-y">

                    <!-- BREADCRUMB -->
                    <div class="d-flex justify-content-between align-items-center py-3 mb-4">
                        <h4 class="fw-bold m-0">
                            <span class="text-muted fw-light">Suppliers /</span>
                            <?= $supplier_id ? 'Edit Supplier' : 'Add Supplier'; ?>
                        </h4>
                        <a href="manage-suppliers.php" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Back to Suppliers
                        </a>
                    </div>

                    <!-- ERROR ALERTS -->
                    <?php if (isset($_SESSION['form_errors'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                <?php foreach ($_SESSION['form_errors'] as $err): ?>
                                    <li><?= htmlspecialchars($err); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['form_errors']); ?>
                    <?php endif; ?>

                    <!-- FORM CONTAINER -->
                    <div class="row">
                        <div class="col-12 col-md-10 col-lg-8 mx-auto">
                            <div class="card border">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <?= $supplier_id ? 'Edit Supplier Information' : 'Add New Supplier'; ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="../admin/handlers/supplier-handler.php">
                                        
                                        <!-- HIDDEN ID FIELD (CRITICAL FOR UPDATE) -->
                                        <?php if ($supplier_id): ?>
                                            <input type="hidden" name="supplier_id" value="<?= $supplier_id; ?>" />
                                        <?php endif; ?>

                                        <div class="row">
                                            <!-- SUPPLIER NAME -->
                                            <div class="col-md-6 mb-3">
                                                <label for="name" class="form-label">Supplier Name <span class="text-danger">*</span></label>
                                                <input 
                                                    type="text" 
                                                    class="form-control" 
                                                    id="name" 
                                                    name="name" 
                                                    value="<?= htmlspecialchars($supplierName); ?>" 
                                                    placeholder="e.g. Acme Pharmaceuticals" 
                                                    required 
                                                />
                                            </div>

                                            <!-- CONTACT PERSON -->
                                            <div class="col-md-6 mb-3">
                                                <label for="contact_person" class="form-label">Contact Person</label>
                                                <input 
                                                    type="text" 
                                                    class="form-control" 
                                                    id="contact_person" 
                                                    name="contact_person" 
                                                    value="<?= htmlspecialchars($contactPerson); ?>" 
                                                    placeholder="e.g. John Doe" 
                                                />
                                            </div>
                                        </div>

                                        <div class="row">
                                            <!-- PHONE -->
                                            <div class="col-md-6 mb-3">
                                                <label for="phone" class="form-label">Phone Number</label>
                                                <input 
                                                    type="text" 
                                                    class="form-control" 
                                                    id="phone" 
                                                    name="phone" 
                                                    value="<?= htmlspecialchars($phone); ?>" 
                                                    placeholder="e.g. +1 555-0199" 
                                                />
                                            </div>

                                            <!-- EMAIL -->
                                            <div class="col-md-6 mb-3">
                                                <label for="email" class="form-label">Email Address</label>
                                                <input 
                                                    type="email" 
                                                    class="form-control" 
                                                    id="email" 
                                                    name="email" 
                                                    value="<?= htmlspecialchars($email); ?>" 
                                                    placeholder="e.g. supplier@example.com" 
                                                />
                                            </div>
                                        </div>

                                        <!-- ADDRESS -->
                                        <div class="mb-3">
                                            <label for="address" class="form-label">Address</label>
                                            <textarea 
                                                class="form-control" 
                                                id="address" 
                                                name="address" 
                                                rows="3" 
                                                placeholder="Enter full physical address..."
                                            ><?= htmlspecialchars($address); ?></textarea>
                                        </div>

                                        <!-- SUBMIT BUTTONS -->
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <?= $supplier_id ? 'Update Supplier' : 'Save Supplier'; ?>
                                            </button>
                                            <a href="manage-suppliers.php" class="btn btn-outline-secondary">
                                                Cancel
                                            </a>
                                        </div>

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

</body>
</html>