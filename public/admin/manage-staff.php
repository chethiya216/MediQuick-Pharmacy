<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
requireAdmin();
require_once __DIR__ . '/../../includes/db.php';

$pageTitle = "Manage Staff - MediQuick";

// Fetch all staff members from database
$sql = "SELECT staff_id, first_name, last_name, email, role, status, created_at FROM staff ORDER BY created_at DESC";
$result = $conn->query($sql);
$staffList = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Pull flash messages/old inputs, then clear them
$errors  = $_SESSION['staff_errors'] ?? [];
$success = $_SESSION['staff_success'] ?? '';
$old     = $_SESSION['staff_old'] ?? [];

unset(
    $_SESSION['staff_errors'],
    $_SESSION['staff_success'],
    $_SESSION['staff_old']
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

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->

            <div class="container-xxl flex-grow-1 container-p-y">
              <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">User Management /</span> Staff</h4>
                <!-- Button to trigger Add Staff Modal -->
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                  <i class="bx bx-plus me-1"></i> Add New Staff
                </button>
              </div>

              <!-- Alert Messages -->
              <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible" role="alert">
                  <?php foreach ($errors as $error): ?>
                    <div><?= htmlspecialchars($error) ?></div>
                  <?php endforeach; ?>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              <?php endif; ?>

              <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-dismissible" role="alert">
                  <?= htmlspecialchars($success) ?>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              <?php endif; ?>

              <!-- Staff List Table -->
              <div class="card">
                <h5 class="card-header">All Staff Members</h5>
                <div class="table-responsive text-nowrap">
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                      <?php if (empty($staffList)): ?>
                        <tr>
                          <td colspan="6" class="text-center py-4">No staff members found.</td>
                        </tr>
                      <?php else: ?>
                        <?php foreach ($staffList as $staff): ?>
                          <tr>
                            <td>
                              <strong><?= htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($staff['email']) ?></td>
                            <td>
                              <span class="badge bg-label-info me-1">
                                <?= htmlspecialchars(ucfirst($staff['role'] ?? 'Staff')) ?>
                              </span>
                            </td>
                            <td>
                              <?php if (($staff['status'] ?? 'active') === 'active'): ?>
                                <span class="badge bg-label-success me-1">Active</span>
                              <?php else: ?>
                                <span class="badge bg-label-danger me-1">Inactive</span>
                              <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y', strtotime($staff['created_at'])) ?></td>
                            <td>
                              <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                  <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu">
                                  <!-- Edit action / view logic -->
                                  <a class="dropdown-item" href="edit-staff.php?id=<?= urlencode($staff['staff_id']) ?>">
                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                  </a>

                                  <!-- Toggle Status / Deactivate -->
                                  <form method="POST" action="handlers/manage-staff-handler.php">
                                    <input type="hidden" name="action" value="toggle_status" />
                                    <input type="hidden" name="staff_id" value="<?= htmlspecialchars($staff['staff_id']) ?>" />
                                    <input type="hidden" name="current_status" value="<?= htmlspecialchars($staff['status'] ?? 'active') ?>" />
                                    <button type="submit" class="dropdown-item text-<?= ($staff['status'] ?? 'active') === 'active' ? 'danger' : 'success' ?>">
                                      <i class="bx bx-power-off me-1"></i> 
                                      <?= ($staff['status'] ?? 'active') === 'active' ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                  </form>
                                </div>
                              </div>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>

            </div>
            <!-- / Content -->

            <!-- Modal: Add New Staff -->
            <div class="modal fade" id="addStaffModal" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="modalCenterTitle">Add New Staff Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <form method="POST" action="handlers/manage-staff-handler.php">
                    <input type="hidden" name="action" value="add_staff" />
                    
                    <div class="modal-body">
                      <div class="row">
                        <div class="col mb-3">
                          <label for="first_name_modal" class="form-label">First Name</label>
                          <input type="text" id="first_name_modal" name="first_name" class="form-control" placeholder="John" value="<?= htmlspecialchars($old['first_name'] ?? '') ?>" required />
                        </div>
                        <div class="col mb-3">
                          <label for="last_name_modal" class="form-label">Last Name</label>
                          <input type="text" id="last_name_modal" name="last_name" class="form-control" placeholder="Doe" value="<?= htmlspecialchars($old['last_name'] ?? '') ?>" required />
                        </div>
                      </div>

                      <div class="row">
                        <div class="col mb-3">
                          <label for="email_modal" class="form-label">Email</label>
                          <input type="email" id="email_modal" name="email" class="form-control" placeholder="john.doe@example.com" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required />
                        </div>
                        <div class="col mb-3">
                          <label for="role_modal" class="form-label">Role</label>
                          <select id="role_modal" name="role" class="form-select" required>
                            <option value="staff" selected>Staff</option>
                            <option value="admin">Admin</option>
                          </select>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col mb-3">
                          <label for="password_modal" class="form-label">Password</label>
                          <div class="input-group input-group-merge">
                            <input type="password" id="password_modal" name="password" class="form-control" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" required />
                            <span class="input-group-text cursor-pointer toggle-password" data-target="password_modal">
                              <i class="bx bx-hide pe-none"></i>
                            </span>
                          </div>
                        </div>
                        <div class="col mb-3">
                          <label for="confirm_password_modal" class="form-label">Confirm Password</label>
                          <div class="input-group input-group-merge">
                            <input type="password" id="confirm_password_modal" name="confirm_password" class="form-control" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" required />
                            <span class="input-group-text cursor-pointer toggle-password" data-target="confirm_password_modal">
                              <i class="bx bx-hide pe-none"></i>
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="modal-footer">
                      <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                      <button type="submit" class="btn btn-primary">Create Account</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

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

  </body>
</html>