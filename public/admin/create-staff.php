<?php

require_once '../../includes/auth.php';

requireAdmin();

// Pull any flash message + old input left by the handler, then clear it
// so it doesn't persist across a page refresh.
$message = $_SESSION['staff_form_message'] ?? '';
$messageType = $_SESSION['staff_form_message_type'] ?? '';
$old = $_SESSION['staff_form_old'] ?? [];

unset(
    $_SESSION['staff_form_message'],
    $_SESSION['staff_form_message_type'],
    $_SESSION['staff_form_old']
);

?>
<!DOCTYPE html>

<!-- =========================================================
* Styled using the Sneat - Bootstrap 5 HTML Admin Template
* "Register Basic" auth card layout
==============================================================
-->
<html
  lang="en"
  class="light-style customizer-hide"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="../admin-assets/assets/"
  data-template="vertical-menu-template-free"
>
  <head>
    <style>
.authentication-inner {
    max-width: 700px !important;
    width: 100% !important;
}
</style>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>Create Staff Account</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../admin-assets/assets/img/favicon/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />

    <!-- Icons -->
    <link rel="stylesheet" href="../admin-assets/assets/vendor/fonts/boxicons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="../admin-assets/assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="../admin-assets/assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="../admin-assets/assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="../admin-assets/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="../admin-assets/assets/vendor/css/pages/page-auth.css" />

    <!-- Helpers -->
    <script src="../admin-assets/assets/vendor/js/helpers.js"></script>
    <script src="../admin-assets/assets/js/config.js"></script>
  </head>

  <body>
    <!-- Content -->
    <div class="container-xxl">
      <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">

          <!-- Create Staff Card -->
          <div class="card">
            <div class="card-body">

              <!-- Logo -->
<div class="app-brand justify-content-center">
    <a href="../admin/index.php" class="app-brand-link gap-2">
        <span
            class="app-brand-text fw-bolder"
            style="font-size: 28px; color: #696cff;"
        >
            MediQuick Admin
        </span>
    </a>
</div>
<!-- /Logo -->

              <h4 class="mb-2 text-center">Create Staff Account</h4>
              <p class="mb-4 text-center">Add a new admin, pharmacist, or superadmin to the system.</p>

              <?php if ($message !== ''): ?>
                <div class="alert <?= $messageType === 'success' ? 'alert-success' : 'alert-danger' ?>" role="alert">
                  <?= htmlspecialchars($message) ?>
                </div>
              <?php endif; ?>

              <form id="formCreateStaff" class="mb-3" method="POST" action="create-staff-handler.php">

                <div class="row">
                  <div class="mb-3 col-6">
                    <label for="first_name" class="form-label">First Name</label>
                    <input
                      type="text"
                      class="form-control"
                      id="first_name"
                      name="first_name"
                      placeholder="Enter first name"
                      value="<?= htmlspecialchars($old['first_name'] ?? '') ?>"
                      required
                    />
                  </div>

                  <div class="mb-3 col-6">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input
                      type="text"
                      class="form-control"
                      id="last_name"
                      name="last_name"
                      placeholder="Enter last name"
                      value="<?= htmlspecialchars($old['last_name'] ?? '') ?>"
                      required
                    />
                  </div>
                </div>

                <div class="mb-3">
                  <label for="email" class="form-label">Email</label>
                  <input
                    type="email"
                    class="form-control"
                    id="email"
                    name="email"
                    placeholder="Enter email address"
                    value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                    required
                  />
                </div>

                <div class="mb-3 form-password-toggle">
                  <label class="form-label" for="password">Password</label>
                  <div class="input-group input-group-merge">
                    <input
                      type="password"
                      id="password"
                      class="form-control"
                      name="password"
                      placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                      aria-describedby="password"
                      minlength="8"
                      required
                    />
                    <span class="input-group-text cursor-pointer toggle-password"><i class="bx bx-hide"></i></span>
                  </div>
                </div>

                <div class="mb-3 form-password-toggle">
                  <label class="form-label" for="confirm_password">Confirm Password</label>
                  <div class="input-group input-group-merge">
                    <input
                      type="password"
                      id="confirm_password"
                      class="form-control"
                      name="confirm_password"
                      placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                      aria-describedby="confirm_password"
                      minlength="8"
                      required
                    />
                    <span class="input-group-text cursor-pointer toggle-password"><i class="bx bx-hide"></i></span>
                  </div>
                </div>

                <div class="mb-3">
    <label for="role" class="form-label">Staff Role</label>
    <select class="form-select" id="role" name="role" required>
        <option value="">Select role</option>

        <option
            value="admin"
            <?= (($old['role'] ?? '') === 'admin') ? 'selected' : '' ?>
        >
            Admin
        </option>

        <option
            value="pharmacist"
            <?= (($old['role'] ?? '') === 'pharmacist') ? 'selected' : '' ?>
        >
            Pharmacist
        </option>
    </select>
</div>
                <button class="btn btn-primary d-grid w-100" type="submit">Create Staff Account</button>
              </form>

              <p class="text-center">
                <a href="../admin/index.php">
                  <i class="bx bx-chevron-left scaleX-n1-rtl bx-sm"></i>
                  Back to Dashboard
                </a>
              </p>

              <p class="text-center">
                <a href="../logout.php">Logout</a>
              </p>

            </div>
          </div>
          <!-- /Create Staff Card -->

        </div>
      </div>
    </div>
    <!-- / Content -->

    <!-- Core JS -->
    <script src="../admin-assets/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../admin-assets/assets/vendor/libs/popper/popper.js"></script>
    <script src="../admin-assets/assets/vendor/js/bootstrap.js"></script>
    <script src="../admin-assets/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../admin-assets/assets/vendor/js/menu.js"></script>

    <!-- Main JS -->
    <script src="../admin-assets/assets/js/main.js"></script>

    <!-- Page JS: show/hide password toggle -->
    <script>
      document.querySelectorAll('.toggle-password').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
          const input = this.closest('.input-group').querySelector('input');
          const icon = this.querySelector('i');
          if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bx-hide');
            icon.classList.add('bx-show');
          } else {
            input.type = 'password';
            icon.classList.remove('bx-show');
            icon.classList.add('bx-hide');
          }
        });
      });
    </script>

  </body>
</html>
