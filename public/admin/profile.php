<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
requireAdmin();
requirePharmacist();
require_once __DIR__ . '/../../includes/db.php';

$pageTitle = "My Profile - MediQuick";

// Get logged-in user details from session
$userId = $_SESSION['user_id'] ?? $_SESSION['staff_id'] ?? null;
$user   = getUserById($userId);

if (!$user) {
    $_SESSION['form_errors'] = ["User not found."];
    header("Location: ../logout.php");
    exit();
}

// Pull flash messages/old inputs, then clear them
$errors  = $_SESSION['profile_errors'] ?? [];
$success = $_SESSION['profile_success'] ?? '';
$old     = $_SESSION['profile_old'] ?? [];

unset(
    $_SESSION['profile_errors'],
    $_SESSION['profile_success'],
    $_SESSION['profile_old']
);

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
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        
       <!-- SIDEBAR -->
        <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

        <!-- Layout container -->
        <div class="layout-page">
          
        <!-- HEADER -->
        <?php require_once __DIR__ . '/includes/header.php'; ?>

          <!-- / Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->

            <div class="container-xxl flex-grow-1 container-p-y">
              <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Account Settings /</span> Account</h4>

              <div class="row">
                <div class="col-md-12">

                  <!-- Alert Messages -->
                  <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger" role="alert">
                      <?php foreach ($errors as $error): ?>
                        <div><?= htmlspecialchars($error) ?></div>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>

                  <?php if (!empty($success)): ?>
                    <div class="alert alert-success" role="alert">
                      <?= htmlspecialchars($success) ?>
                    </div>
                  <?php endif; ?>

                  <div class="card mb-4">
                    <h5 class="card-header">Profile Details</h5>
                    <hr class="my-0" />
                    <div class="card-body">
                      
                      <!-- Submit form to profile handler -->
                      <form id="formAccountSettings" method="POST" action="handlers/profile-handler.php">
                        <div class="row">

                          <div class="mb-3 col-md-6">
                            <label for="first_name" class="form-label">First Name</label>
                            <input
                              class="form-control"
                              type="text"
                              id="first_name"
                              name="first_name"
                              value="<?= htmlspecialchars($old['first_name'] ?? $user['first_name'] ?? ''); ?>"
                              required
                            />
                          </div>

                          <div class="mb-3 col-md-6">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input
                              class="form-control"
                              type="text"
                              name="last_name"
                              id="last_name"
                              value="<?= htmlspecialchars($old['last_name'] ?? $user['last_name'] ?? ''); ?>" 
                              required
                            />
                          </div>

                          <div class="mb-3 col-md-6">
                            <label for="email" class="form-label">E-mail</label>
                            <input
                              class="form-control"
                              type="email"
                              id="email"
                              name="email"
                              value="<?= htmlspecialchars($old['email'] ?? $user['email'] ?? ''); ?>"
                              required
                            />
                          </div>
                       
                          <hr class="my-4">

                          <!-- Checkbox Toggle -->
                          <div class="col-12 mb-3">
                            <div class="form-check">
                              <label class="form-check-label fw-semibold" for="change_password_toggle">
                                Change Password
                              </label>
                              <input 
                                class="form-check-input" 
                                type="checkbox" 
                                id="change_password_toggle" 
                                name="change_password_toggle" 
                                value="1" 
                              />
                              
                            </div>
                          </div>

                          <!-- Password Fields Container (Hidden by default) -->
                          <div id="passwordFieldsSection" class="row col-12" style="display: none;">
                            
                            <!-- Current Password Field -->
                            <div class="mb-3 col-md-4">
                              <label for="current_password" class="form-label">Current Password</label>
                              <div class="form-password-toggle">
                                <div class="input-group input-group-merge">
                                  <input
                                    type="password"
                                    class="form-control"
                                    id="current_password"
                                    name="current_password"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    maxlength="255" 
                                  />
                                  <span class="input-group-text cursor-pointer toggle-password" data-target="current_password">
                                    <i class="bx bx-hide"></i>
                                  </span>
                                </div>
                              </div>
                            </div>

                            <!-- New Password Field -->
                            <div class="mb-3 col-md-4">
                              <label for="password" class="form-label">New Password</label>
                              <div class="form-password-toggle">
                                <div class="input-group input-group-merge">
                                  <input
                                    type="password"
                                    class="form-control"
                                    id="new_password"
                                    name="new_password"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    maxlength="255" 
                                  />
                                  <span class="input-group-text cursor-pointer toggle-password" data-target="password">
                                    <i class="bx bx-hide"></i>
                                  </span>
                                </div>
                              </div>
                              <small class="text-muted d-block mt-1">Must be at least 8 characters.</small>
                            </div>

                            <!-- Confirm Password Field -->
                            <div class="mb-3 col-md-4">
                              <label for="confirm_password" class="form-label">Confirm Password</label>
                              <div class="form-password-toggle">
                                <div class="input-group input-group-merge">
                                  <input
                                    type="password"
                                    class="form-control"
                                    id="confirm_password"
                                    name="confirm_password"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    maxlength="255" 
                                  />
                                  <span class="input-group-text cursor-pointer toggle-password" data-target="confirm_password">
                                    <i class="bx bx-hide"></i>
                                  </span>
                                </div>
                              </div>
                            </div>

                        <div class="mt-2 mb-4">
                          <button type="submit" class="btn btn-primary me-2">Save changes</button>
                          <button type="reset" class="btn btn-outline-secondary">Cancel</button>
                        </div>
                      </form>

                    </div>
                    <!-- /Account -->
                  </div>

                  <div class="card">
                    <h5 class="card-header">Delete Account</h5>
                    <div class="card-body">
                      <div class="mb-3 col-12 mb-0">
                        <div class="alert alert-warning">
                          <h6 class="alert-heading fw-bold mb-1">Are you sure you want to delete your account?</h6>
                          <p class="mb-0">Once you delete your account, there is no going back. Please be certain.</p>
                        </div>
                      </div>
                      <form id="formAccountDeactivation" method="POST" action="handlers/profile-handler.php">
                          <!-- Action flag -->
                          <input type="hidden" name="action" value="deactivate_account" />

                          <div class="form-check mb-3">
                              <input
                                  class="form-check-input"
                                  type="checkbox"
                                  name="accountActivation"
                                  id="accountActivation"
                                  required
                              />
                              <label class="form-check-label" for="accountActivation">
                                  I confirm my account deactivation
                              </label>
                          </div>
                          <button type="submit" class="btn btn-danger deactivate-account">Deactivate Account</button>
                      </form>
                    </div>
                  </div>

                </div>
              </div>
            </div>
            <!-- / Content -->

            <!-- Footer -->
            <?php require_once __DIR__ . '/includes/footer.php'; ?>

            <div class="content-backdrop fade"></div>
          </div>
          <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
      </div>

      <!-- Overlay -->
      <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Page JS: Password Visibility Toggles -->
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const toggleCheckbox = document.getElementById('change_password_toggle');
        const passwordSection = document.getElementById('passwordFieldsSection');
        const passwordInputs = passwordSection.querySelectorAll('input');

        toggleCheckbox.addEventListener('change', function () {
          if (this.checked) {
            passwordSection.style.display = 'flex';
            passwordInputs.forEach(input => input.setAttribute('required', 'required'));
          } else {
            passwordSection.style.display = 'none';
            passwordInputs.forEach(input => {
              input.removeAttribute('required');
              input.value = '';
            });
          }
        });
      });

    </script>
  </body>
</html>