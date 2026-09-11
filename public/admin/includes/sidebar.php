<?php 
require_once '../../includes/auth.php';

// Detect the current file name (e.g., "index.php", "manage-staff.php")
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

  <div class="app-brand demo">
    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
      <i class="bx bx-chevron-left bx-sm align-middle"></i>
    </a>
  </div>

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">
    
    <!-- Dashboard -->
    <li class="menu-item <?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>">
      <a href="index.php" class="menu-link">
        <i class="menu-icon tf-icons bx bx-home-circle"></i>
        <div data-i18n="DashBoard">DashBoard</div>
      </a>
    </li>

    <!-- Users -->
    <?php if($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'superadmin'): ?>
    <?php $usersPages = ['manage-staff.php', 'manage-customers.php']; ?>
    <li class="menu-item <?php echo in_array($currentPage, $usersPages) ? 'active open' : ''; ?>">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-user"></i>
        <div data-i18n="Prescription">Users</div>
      </a>

      <ul class="menu-sub">
        <?php if($_SESSION['role'] == 'superadmin'): ?>
        <li class="menu-item <?php echo ($currentPage == 'manage-staff.php') ? 'active' : ''; ?>">
          <a href="manage-staff.php" class="menu-link">
            <div data-i18n="Manage">Manage Staff</div>
          </a>
        </li>
        <?php endif; ?>

        <?php if($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'superadmin'): ?>
        <li class="menu-item <?php echo ($currentPage == 'manage-customers.php') ? 'active' : ''; ?>">
          <a href="manage-customers.php" class="menu-link">
            <div data-i18n="Verify">Manage Customers</div>
          </a>
        </li>
        <?php endif; ?>
      </ul>
    </li>
    <?php endif; ?> 

    <!-- Prescriptions -->
    <?php if($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'superadmin' || $_SESSION['role'] == 'pharmacist'): ?>
    <?php $prescriptionPages = ['manage-prescriptions.php', 'verify-prescriptions.php']; ?>
    <li class="menu-item <?php echo in_array($currentPage, $prescriptionPages) ? 'active open' : ''; ?>">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-file-find"></i>
        <div data-i18n="Prescription">Prescription</div>
      </a>

      <ul class="menu-sub">
        <li class="menu-item <?php echo ($currentPage == 'manage-prescriptions.php') ? 'active' : ''; ?>">
          <a href="manage-prescriptions.php" class="menu-link">
            <div data-i18n="Manage">Manage Prescription</div>
          </a>
        </li>

        <li class="menu-item <?php echo ($currentPage == 'verify-prescriptions.php') ? 'active' : ''; ?>">
          <a href="verify-prescriptions.php" class="menu-link">
            <div data-i18n="Verify">Verify Prescription</div>
          </a>
        </li>
      </ul>
    </li>
    <?php endif; ?>

    <!-- Products -->
    <?php if($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'superadmin'): ?>
    <?php $productPages = ['manage-products.php', 'add-products.php']; ?>
    <li class="menu-item <?php echo in_array($currentPage, $productPages) ? 'active open' : ''; ?>">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-capsule"></i>
        <div data-i18n="Products">Products</div>
      </a>

      <ul class="menu-sub">
        <li class="menu-item <?php echo ($currentPage == 'manage-products.php') ? 'active' : ''; ?>">
          <a href="manage-products.php" class="menu-link">
            <div data-i18n="Manage">Manage Products</div>
          </a>
        </li>

        <li class="menu-item <?php echo ($currentPage == 'add-products.php') ? 'active' : ''; ?>">
          <a href="add-products.php" class="menu-link">
            <div data-i18n="Add">Add Products</div>
          </a>
        </li>
      </ul>
    </li>

    <!-- Categories -->
    <?php $categoryPages = ['manage-categories.php', 'add-categories.php']; ?>
    <li class="menu-item <?php echo in_array($currentPage, $categoryPages) ? 'active open' : ''; ?>">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-category"></i>
        <div data-i18n="Categories">Categories</div>
      </a>

      <ul class="menu-sub">
        <li class="menu-item <?php echo ($currentPage == 'manage-categories.php') ? 'active' : ''; ?>">
          <a href="manage-categories.php" class="menu-link">
            <div data-i18n="Manage">Manage Categories</div>
          </a>
        </li>

        <li class="menu-item <?php echo ($currentPage == 'add-categories.php') ? 'active' : ''; ?>">
          <a href="add-categories.php" class="menu-link">
            <div data-i18n="Add">Add Categories</div>
          </a>
        </li>
      </ul>
    </li>

    <!-- Suppliers -->
    <?php $supplierPages = ['manage-suppliers.php', 'add-suppliers.php']; ?>
    <li class="menu-item <?php echo in_array($currentPage, $supplierPages) ? 'active open' : ''; ?>">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-store-alt"></i>
        <div data-i18n="Suppliers">Suppliers</div>
      </a>

      <ul class="menu-sub">
        <li class="menu-item <?php echo ($currentPage == 'manage-suppliers.php') ? 'active' : ''; ?>">
          <a href="manage-suppliers.php" class="menu-link">
            <div data-i18n="Manage">Manage Suppliers</div>
          </a>
        </li>

        <li class="menu-item <?php echo ($currentPage == 'add-suppliers.php') ? 'active' : ''; ?>">
          <a href="add-suppliers.php" class="menu-link">
            <div data-i18n="Add">Add Suppliers</div>
          </a>
        </li>
      </ul>
    </li>

    <!-- Product Batches -->
    <?php $batchPages = ['manage-batch.php', 'add-product-batch.php']; ?>
    <li class="menu-item <?php echo in_array($currentPage, $batchPages) ? 'active open' : ''; ?>">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-package"></i>
        <div data-i18n="Product Batches">Product Batches</div>
      </a>

      <ul class="menu-sub">
        <li class="menu-item <?php echo ($currentPage == 'manage-batch.php') ? 'active' : ''; ?>">
          <a href="manage-batch.php" class="menu-link">
            <div data-i18n="Manage Product Batches">Manage Product Batches</div>
          </a>
        </li>

        <li class="menu-item <?php echo ($currentPage == 'add-product-batch.php') ? 'active' : ''; ?>">
          <a href="add-product-batch.php" class="menu-link">
            <div data-i18n="Add Product Batch">Add Product Batch</div>
          </a>
        </li>
      </ul>
    </li>

    <!-- Orders -->
    <?php $orderPages = ['manage-order.php']; ?>
    <li class="menu-item <?php echo in_array($currentPage, $orderPages) ? 'active open' : ''; ?>">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-cart"></i>
        <div data-i18n="Orders">Orders</div>
      </a>

      <ul class="menu-sub">
        <li class="menu-item <?php echo ($currentPage == 'manage-order.php') ? 'active' : ''; ?>">
          <a href="manage-order.php" class="menu-link">
            <div data-i18n="Manage Orders">Manage Orders</div>
          </a>
        </li>
      </ul>
    </li>

    <!-- Payments -->
    <?php $paymentPages = ['show-payments.php']; ?>
    <li class="menu-item <?php echo in_array($currentPage, $paymentPages) ? 'active open' : ''; ?>">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-credit-card"></i>
        <div data-i18n="Payments">Payments</div>
      </a>

      <ul class="menu-sub">
        <li class="menu-item <?php echo ($currentPage == 'show-payments.php') ? 'active' : ''; ?>">
          <a href="show-payments.php" class="menu-link">
            <div data-i18n="Payment Management">Payment Management</div>
          </a>
        </li>
      </ul>
    </li>

    <!-- Customer Messages -->
    <li class="menu-item <?php echo ($currentPage == 'customer-messages.php') ? 'active' : ''; ?>">
      <a href="customer-messages.php" class="menu-link">
        <i class="menu-icon tf-icons bx bx-comment-detail"></i>
        <div data-i18n="Customer Messages">Customer Messages</div>
      </a>
    </li>
    <?php endif; ?>

    <!-- Logout -->
    <li class="menu-item menu-logout">
      <a href="/MediQuick-Pharmacy/public/logout.php" class="menu-link">
        <i class="menu-icon tf-icons bx bx-log-out"></i>
        <div data-i18n="Log out">Log out</div>
      </a>
    </li>

  </ul>

</aside>