<?php

require_once '../../includes/auth.php';
require_once '../../includes/db.php';

requireAdmin();

$isEdit = false;
$staffData = [
    'staff_id'   => '',
    'first_name' => '',
    'last_name'  => '',
    'email'      => '',
    'role'       => '',
    'status'     => 'active'
];

// Check GET parameter for edit mode
$staffId = $_GET['staff_id'] ?? $_GET['id'] ?? null;

if ($staffId !== null && $staffId !== '') {
    $stmt = $conn->prepare("SELECT staff_id, first_name, last_name, email, role, status FROM staff WHERE staff_id = ?");
    
    if ($stmt) {
        $searchId = (string)$staffId;
        $stmt->bind_param("s", $searchId);
        $stmt->execute();
        
        // Use bind_result for compatibility across all PHP/MySQL setups
        $stmt->bind_result($sId, $fName, $lName, $email, $role, $status);
        
        if ($stmt->fetch()) {
            $isEdit = true;
            $staffData = [
                'staff_id'   => $sId,
                'first_name' => $fName,
                'last_name'  => $lName,
                'email'      => $email,
                'role'       => $role,
                'status'     => $status
            ];
        }
        $stmt->close();
    }
}

// Pull any flash message + old input left by handler
$message     = $_SESSION['staff_form_message'] ?? '';
$messageType = $_SESSION['staff_form_message_type'] ?? '';
$old         = $_SESSION['staff_form_old'] ?? [];

unset(
    $_SESSION['staff_form_message'],
    $_SESSION['staff_form_message_type'],
    $_SESSION['staff_form_old']
);

// Merge old inputs if available
$firstNameValue = $old['first_name'] ?? $staffData['first_name'];
$lastNameValue  = $old['last_name']  ?? $staffData['last_name'];
$emailValue     = $old['email']      ?? $staffData['email'];
$roleValue      = $old['role']       ?? $staffData['role'];
$statusValue    = $old['status']     ?? $staffData['status'];

$pageHeading = $isEdit ? "Edit Staff Account" : "Create Staff Account";
$buttonText  = $isEdit ? "Update Staff Account" : "Create Staff Account";
?>
<!DOCTYPE html>
<html
  lang="en"
  class="light-style customizer-hide"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="../admin-assets/assets/"
  data-template="vertical-menu-template-free"
>

<?php require_once __DIR__ . '/includes/head.php'; ?>

  <body>
    <!-- Content -->
    <div class="d-flex justify-content-center align-items-center min-vh-100 px-3">
      <div class="authentication-inner" style="max-width: 550px; width: 100%;">

        <!-- Staff Form Card -->
        <div class="card">
          <div class="card-body">

            <!-- Logo -->
            <div class="app-brand justify-content-center">
                <a href="../admin/index.php" class="app-brand-link gap-2">
                    <span class="app-brand-text fw-bolder mb-2" style="font-size: 28px; color: #696cff;">
                        MediQuick Admin
                    </span>
                </a>
            </div>
            <!-- /Logo -->

            <h4 class="mb-2 mt-2 text-center"><?= $pageHeading ?></h4>
            <p class="mb-4 text-center">
                <?= $isEdit ? 'Modify staff details and permissions.' : 'Add a new admin, pharmacist ' . (getUserRole() === 'superadmin' ? 'or superadmin' : '') . ' to the system.' ?>
            </p>

            <?php if ($message !== ''): ?>
              <div class="alert <?= $messageType === 'success' ? 'alert-success' : 'alert-danger' ?>" role="alert">
                <?= htmlspecialchars($message) ?>
              </div>
            <?php endif; ?>

            <form id="formStaff" class="mb-3" method="POST" action="handlers/staff-handler.php">
              
              <input type="hidden" name="action" value="<?= $isEdit ? 'update_staff' : 'create_staff' ?>" />
              <?php if ($isEdit): ?>
                <input type="hidden" name="staff_id" value="<?= htmlspecialchars($staffData['staff_id']) ?>" />
              <?php endif; ?>

              <div class="row">
                <div class="mb-3 col-12 col-sm-6">
                  <label for="first_name" class="form-label">First Name</label>
                  <input
                    type="text"
                    class="form-control"
                    id="first_name"
                    name="first_name"
                    placeholder="Enter first name"
                    value="<?= htmlspecialchars($firstNameValue) ?>"
                    required
                  />
                </div>

                <div class="mb-3 col-12 col-sm-6">
                  <label for="last_name" class="form-label">Last Name</label>
                  <input
                    type="text"
                    class="form-control"
                    id="last_name"
                    name="last_name"
                    placeholder="Enter last name"
                    value="<?= htmlspecialchars($lastNameValue) ?>"
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
                  value="<?= htmlspecialchars($emailValue) ?>"
                  required
                />
              </div>

              <div class="mb-3 form-password-toggle">
                <label class="form-label" for="password">
                  Password <?= $isEdit ? '<small class="text-muted">(Leave blank to keep unchanged)</small>' : '' ?>
                </label>
                <div class="input-group input-group-merge">
                  <input
                    type="password"
                    id="password"
                    class="form-control"
                    name="password"
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                    minlength="8"
                    <?= $isEdit ? '' : 'required' ?>
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
                    minlength="8"
                    <?= $isEdit ? '' : 'required' ?>
                  />
                  <span class="input-group-text cursor-pointer toggle-password"><i class="bx bx-hide"></i></span>
                </div>
              </div>

              <div class="mb-3">
                <label for="role" class="form-label">Staff Role</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="">Select role</option>
                    <option value="admin" <?= ($roleValue === 'admin') ? 'selected' : '' ?>>Admin</option>
                    <option value="pharmacist" <?= ($roleValue === 'pharmacist') ? 'selected' : '' ?>>Pharmacist</option>
                    <?php if (getUserRole() === 'superadmin'): ?>
                      <option value="superadmin" <?= ($roleValue === 'superadmin') ? 'selected' : '' ?>>Super Admin</option>
                    <?php endif; ?>
                </select>
              </div>

              <?php if ($isEdit): ?>
                <div class="mb-3">
                  <label for="status" class="form-label">Account Status</label>
                  <select class="form-select" id="status" name="status" required>
                    <option value="active" <?= ($statusValue === 'active') ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($statusValue === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                  </select>
                </div>
              <?php endif; ?>

              <button class="btn btn-primary d-grid w-100 mt-4" type="submit"><?= $buttonText ?></button>
            </form>

            <p class="text-center mb-0">
              <a href="manage-staff.php">
                <i class="bx bx-chevron-left scaleX-n1-rtl bx-sm"></i>
                Back to Manage Staff
              </a>
            </p>

          </div>
        </div>
        <!-- /Staff Form Card -->

      </div>
    </div>
    <!-- / Content -->

    <script src="../admin-assets/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../admin-assets/assets/vendor/libs/popper/popper.js"></script>
    <script src="../admin-assets/assets/vendor/js/bootstrap.js"></script>

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