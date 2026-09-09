<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
require_once __DIR__ . '/../../includes/db.php';

$pageTitle = "Edit Customer - MediQuick";

// 1. Get and validate customer_id from URL
$customer_id = isset($_GET['customer_id']) && is_numeric($_GET['customer_id']) ? (int)$_GET['customer_id'] : 0;

if ($customer_id <= 0) {
    $_SESSION['form_errors'] = ["Invalid customer ID."];
    header("Location: manage-customers.php");
    exit();
}

// 2. Fetch existing customer details
$stmt = $conn->prepare("SELECT `customer_id`, `first_name`, `last_name`, `email`, `phone`, `address`, `date_of_birth`, `status` FROM `customers` WHERE `customer_id` = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['form_errors'] = ["Customer not found."];
    header("Location: manage-customers.php");
    exit();
}

$customer = $result->fetch_assoc();

// Format date_of_birth for HTML5 date input (YYYY-MM-DD)
if (!empty($customer['date_of_birth']) && $customer['date_of_birth'] !== '0000-00-00') {
    $customer['date_of_birth'] = date('Y-m-d', strtotime($customer['date_of_birth']));
} else {
    $customer['date_of_birth'] = '';
}


?>

<!DOCTYPE html>
<html
    lang="en"
    class="light-style layout-menu-fixed"
    dir="ltr"
    data-theme="theme-default"
    data-assets-path="../admin-assets/assets/"
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

                    <!-- BREADCRUMB & ACTIONS -->
                    <div class="d-flex justify-content-between align-items-center py-3 mb-4">
                        <h4 class="fw-bold m-0">
                            <span class="text-muted fw-light">Customers /</span> Edit Customer #<?= $customer['customer_id']; ?>
                        </h4>
                        <a href="manage-customers.php" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Back to Customers
                        </a>
                    </div>

                    <!-- FLASH ERROR ALERT -->
                    <?php if (isset($_SESSION['form_errors'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0 ps-3">
                                <?php foreach ($_SESSION['form_errors'] as $err): ?>
                                    <li><?= htmlspecialchars($err); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['form_errors']); ?>
                    <?php endif; ?>

                    <!-- EDIT FORM CARD -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Customer Information</h5>
                            <small class="text-muted float-end">Update account details</small>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="../admin/handlers/customer-handler.php">
                                <div class="row g-3">

                                    <input type="hidden" name="customer_id" value="<?= htmlspecialchars($customer['customer_id'] ?? ''); ?>">

                                    <!-- First Name -->
                                    <div class="col-md-6">
                                        <label class="form-label" for="first_name">First Name <span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            id="first_name"
                                            name="first_name"
                                            class="form-control"
                                            value="<?= htmlspecialchars($customer['first_name'] ?? ''); ?>"
                                            required
                                        />
                                    </div>

                                    <!-- Last Name -->
                                    <div class="col-md-6">
                                        <label class="form-label" for="last_name">Last Name <span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            id="last_name"
                                            name="last_name"
                                            class="form-control"
                                            value="<?= htmlspecialchars($customer['last_name'] ?? ''); ?>"
                                            required
                                        />
                                    </div>

                                    <!-- Email -->
                                    <div class="col-md-6">
                                        <label class="form-label" for="email">Email Address <span class="text-danger">*</span></label>
                                        <input
                                            type="email"
                                            id="email"
                                            name="email"
                                            class="form-control"
                                            value="<?= htmlspecialchars($customer['email'] ?? ''); ?>"
                                            required
                                        />
                                    </div>

                                    <!-- Phone -->
                                    <div class="col-md-6">
                                        <label class="form-label" for="phone">Phone Number</label>
                                        <input
                                            type="text"
                                            id="phone"
                                            name="phone"
                                            class="form-control"
                                            value="<?= htmlspecialchars($customer['phone'] ?? ''); ?>"
                                        />
                                    </div>

                                    <!-- Date of Birth -->
                                    <div class="col-md-6">
                                        <label class="form-label" for="date_of_birth">Date of Birth</label>
                                        
                                        <input
                                            type="date"
                                            id="date_of_birth"
                                            name="date_of_birth"
                                            class="form-control"
                                            value="<?= htmlspecialchars($customer['date_of_birth'] ?? ''); ?>"
                                        />
                                    </div>

                                    <!-- Status -->
                                    <div class="col-md-6">
                                        <label class="form-label" for="status">Account Status</label>
                                        <select id="status" name="status" class="form-select">
                                            <option value="active" <?= ($customer['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?= ($customer['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>

                                    <!-- Address -->
                                    <div class="col-12">
                                        <label class="form-label" for="address">Address</label>
                                        <textarea
                                            id="address"
                                            name="address"
                                            class="form-control"
                                            rows="3"
                                        ><?= htmlspecialchars($customer['address'] ?? ''); ?></textarea>
                                    </div>

                                    <!-- New Password (Optional) -->
                                    <div class="col-12">
                                        <hr class="my-3" />
                                        <label class="form-label" for="password">Reset Password</label>
                                        <input
                                            type="password"
                                            id="password"
                                            name="password"
                                            class="form-control"
                                            placeholder="Leave blank if you don't want to change the password"
                                        />
                                        <div class="form-text">Only fill this field if you need to manually set a new password for the customer.</div>
                                    </div>

                                </div>

                                <!-- Form Actions -->
                                <div class="mt-4 text-end">
                                    <a href="manage-customers.php" class="btn btn-outline-secondary me-2">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-save me-1"></i> Save Changes
                                    </button>
                                </div>
                            </form>
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