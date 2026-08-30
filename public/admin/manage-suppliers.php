<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
require_once __DIR__ . '/../../includes/db.php';

$pageTitle = "Manage Suppliers - MediQuick";

$limit = 20; 
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) { $page = 1; }
$offset = ($page - 1) * $limit;

// Preserve existing search or filter parameters in pagination links
$queryParams = $_GET;

// 1. Get total number of products
$countSql = "SELECT COUNT(*) AS total FROM suppliers";
$countResult = $conn->query($countSql);
$totalSuppliers = $countResult->fetch_assoc()['total'] ?? 0;

$totalPages = ceil($totalSuppliers / $limit);
if ($totalPages < 1) { $totalPages = 1; }
if ($page > $totalPages) { $page = $totalPages; }

// Search & Filter functionality
$search = trim($_GET['search'] ?? '');
$where  = '';
$params = [];
$types  = '';

if (!empty($search)) {
    $where    = "WHERE supplier_name LIKE ? OR contact_person LIKE ? OR phone LIKE ? OR email LIKE ?";
    $searchTerm = "%{$search}%";
    $params   = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
    $types    = 'ssss';
}

// Fetch Suppliers Query
$sql = "SELECT * FROM suppliers {$where} ORDER BY supplier_id DESC";
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$suppliers = $stmt->get_result();
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

                    <!-- BREADCRUMB & TOP ACTIONS -->
                    <div class="d-flex justify-content-between align-items-center py-3 mb-4">
                        <h4 class="fw-bold m-0">
                            <span class="text-muted fw-light">Suppliers /</span> Manage Suppliers
                        </h4>
                        <a href="add-suppliers.php" class="btn btn-primary">
                            <i class="bx bx-plus me-1"></i> Add Supplier
                        </a>
                    </div>

                    <!-- FLASH SUCCESS ALERT -->
                    <?php if (isset($_SESSION['flash_success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bx bx-check-circle me-1"></i>
                            <?= htmlspecialchars($_SESSION['flash_success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['flash_success']); ?>
                    <?php endif; ?>

                    <!-- FLASH ERROR ALERT -->
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

                    <!-- SUPPLIERS TABLE CARD -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <h5 class="mb-0">All Suppliers</h5>
                            
                            <!-- SEARCH FORM -->
                            <form method="GET" action="" class="d-flex gap-2">
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text" id="basic-addon-search31"><i class="bx bx-search"></i></span>
                                    <input
                                        type="text"
                                        name="search"
                                        class="form-control"
                                        placeholder="Search supplier..."
                                        value="<?= htmlspecialchars($search); ?>"
                                        aria-label="Search..."
                                    />
                                </div>
                                <?php if (!empty($search)): ?>
                                    <a href="manage-suppliers.php" class="btn btn-outline-secondary">Clear</a>
                                <?php endif; ?>
                            </form>
                        </div>

                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#ID</th>
                                        <th>Supplier</th>
                                        <th>Contact Person</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="table-border-bottom-0">
                                    <?php if ($suppliers->num_rows > 0): ?>
                                        <?php while ($row = $suppliers->fetch_assoc()): ?>
                                            <tr>
                                                <td><strong>#<?= $row['supplier_id']; ?></strong></td>
                                                <td><?= htmlspecialchars($row['name'] ?? ''); ?></td>
                                                <td><?= htmlspecialchars($row['contact_person'] ?? 'N/A'); ?></td>
                                                <td><?= htmlspecialchars($row['phone'] ?? 'N/A'); ?></td>
                                                <td><?= htmlspecialchars($row['email'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <?php if (($row['status'] ?? 'active') === 'active'): ?>
                                                        <span class="badge bg-label-success me-1">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-label-secondary me-1">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                            <i class="bx bx-dots-vertical-rounded"></i>
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item" href="add-suppliers.php?supplier_id=<?= $row['supplier_id']; ?>">
                                                                <i class="bx bx-edit-alt me-1 text-primary"></i> Edit
                                                            </a>
                                                            <a 
                                                                class="dropdown-item text-danger" 
                                                                href="../admin/handlers/supplier-handler.php?action=delete&supplier_id=<?= $row['supplier_id']; ?>"
                                                                onclick="return confirm('Are you sure you want to delete this supplier?');"
                                                            >
                                                                <i class="bx bx-trash me-1"></i> Delete
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">No suppliers found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- PAGINATION NAVIGATION -->
                    <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center mt-4">
                            
                            <!-- Previous Button -->
                            <li class="page-item prev <?= ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?= ($page > 1) ? getPageUrl($page - 1, $queryParams) : 'javascript:void(0);'; ?>">
                                    <i class="tf-icon bx bx-chevrons-left"></i>
                                </a>
                            </li>

                            <!-- Page Numbers -->
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= ($i === $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?= getPageUrl($i, $queryParams); ?>">
                                        <?= $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next Button -->
                            <li class="page-item next <?= ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?= ($page < $totalPages) ? getPageUrl($page + 1, $queryParams) : 'javascript:void(0);'; ?>">
                                    <i class="tf-icon bx bx-chevrons-right"></i>
                                </a>
                            </li>

                        </ul>
                    </nav>
                    <?php endif; ?>
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