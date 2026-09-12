<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check for custom session error message or set default
$errorMessage = "You don't have permission to access this page or resource.";

if (isset($_SESSION['auth_error'])) {
    $errorMessage = $_SESSION['auth_error'];
    unset($_SESSION['auth_error']); // Clear after showing once
} elseif (isset($_GET['error'])) {
    $errorMessage = htmlspecialchars($_GET['error']);
}
?>
<!DOCTYPE html>

<!-- =========================================================
* Sneat - Bootstrap 5 HTML Admin Template - Pro | v1.0.0
==============================================================

* Product Page: https://themeselection.com/products/sneat-bootstrap-html-admin-template/
* Created by: ThemeSelection
* License: You must have a valid license purchased in order to legally use the theme for your project.
* Copyright ThemeSelection (https://themeselection.com)

=========================================================
 -->
<!-- beautify ignore:start -->
<html
  lang="en"
  class="light-style"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="../assets/"
  data-template="vertical-menu-template-free"
>
  <head>
    <?php require_once __DIR__ . '/includes/head.php'; ?>
    <link rel="stylesheet" href="../assets/vendor/css/pages/page-misc.css" />
  </head>

  <body>
    <!-- Content -->

    <!-- Not Authorized / Access Denied -->
    <div class="container-xxl container-p-y">
      <div class="misc-wrapper text-center d-flex flex-column align-items-center justify-content-center">
        <h2 class="mb-2 mx-2">Access Denied! 🚫</h2>
        <p class="mb-4 mx-2">You are not authorized to view this page.</p>

        <!-- Dynamic Auth Error Alert -->
        <div class="row justify-content-center w-100 mb-4">
          <div class="col-md-6 col-12">
            <div class="alert alert-danger d-flex align-items-center justify-content-center mb-0" role="alert">
              <i class="bx bx-error-circle me-2 fs-4"></i>
              <div class="text-start">
                <?= htmlspecialchars($errorMessage); ?>
              </div>
            </div>
          </div>
        </div>

        <div class="mb-4">
          <a href="index.php" class="btn btn-primary me-2">Back to Dashboard</a>
          <a href="../login.php" class="btn btn-outline-secondary">Switch Account</a>
        </div>

        <div class="mt-4">
          <img
            src="../admin-assets/assets/img/illustrations/girl-doing-yoga-light.png"
            alt="Access Denied"
            width="500"
            class="img-fluid"
            data-app-dark-img="illustrations/girl-doing-yoga-dark.png"
            data-app-light-img="illustrations/girl-doing-yoga-light.png"
          />
        </div>
      </div>
    </div>
    <!-- / Not Authorized -->

    <!-- Core JS -->
    <script src="../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../assets/vendor/libs/popper/popper.js"></script>
    <script src="../assets/vendor/js/bootstrap.js"></script>
    <script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

    <script src="../assets/vendor/js/menu.js"></script>

    <!-- Main JS -->
    <script src="../assets/js/main.js"></script>

    <!-- GitHub Buttons JS -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
  </body>
</html>