<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
requireSuperAdmin();
require_once __DIR__ . '/../../includes/db.php';

$pageTitle = "Manage Staff - MediQuick";

// Search & Filter setup
$search = trim($_GET['search'] ?? '');

if (!empty($search)) {
    // Search query with prepared statement
    $sql = "SELECT staff_id, first_name, last_name, email, role, status, created_at 
            FROM staff 
            WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR role LIKE ?
            ORDER BY created_at DESC";
            
    $searchTerm = "%{$search}%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    $staffList = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
} else {
    // Default fetch without search
    $sql = "SELECT staff_id, first_name, last_name, email, role, status, created_at FROM staff ORDER BY created_at DESC";
    $result = $conn->query($sql);
    $staffList = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

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
                <a href="create-staff.php" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Add New Staff Member
                </a>
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
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h5 class="mb-0">All Staff Members</h5>
                    
                    <!-- SEARCH FORM -->
                    <form method="GET" action="" class="d-flex gap-2">
                        <div class="input-group input-group-merge">
                            <span class="input-group-text" id="basic-addon-search31"><i class="bx bx-search"></i></span>
                            <input
                                type="text"
                                name="search"
                                class="form-control"
                                placeholder="Search staff..."
                                value="<?= htmlspecialchars($search); ?>"
                                aria-label="Search staff..."
                            />
                        </div>
                        <?php if (!empty($search)): ?>
                            <a href="manage-staff.php" class="btn btn-outline-secondary">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
                
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
                          <td colspan="6" class="text-center py-4">
                            <?= !empty($search) ? 'No staff members found matching "' . htmlspecialchars($search) . '".' : 'No staff members found.' ?>
                          </td>
                        </tr>
                      <?php else: ?>
                        <?php foreach ($staffList as $staff): ?>
                          <tr>
                            <td>
                              <strong><?= htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($staff['email']) ?></td>
                            <td>
                                <?php
                                    // Define hierarchy badge colors based on role
                                    $roleColors = [
                                        'superadmin' => 'bg-label-danger',    // Highest: Red / Danger
                                        'admin'      => 'bg-label-primary',   // High: Purple / Primary
                                        'pharmacist' => 'bg-label-warning',   // Medium: Orange / Warning
                                        'staff'      => 'bg-label-info'       // Base: Cyan / Info
                                    ];

                                    // Determine the correct class (fallback to bg-label-secondary if role is unknown)
                                    $userRole  = strtolower($staff['role'] ?? 'staff');
                                    $badgeColor = $roleColors[$userRole] ?? 'bg-label-secondary';
                                    ?>

                                    <span class="badge <?= $badgeColor ?> me-1">
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
                                  <a class="dropdown-item" href="create-staff.php?id=<?= htmlspecialchars($staff['staff_id']) ?>">
                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                  </a>

                                  <!-- Toggle Status / Deactivate -->
                                 <form method="POST" action="handlers/staff-handler.php">
                                    <input type="hidden" name="action" value="toggle_status" />
                                    <input type="hidden" name="staff_id" value="<?= htmlspecialchars($staff['staff_id']) ?>" />
                                    
                                    <!-- Send the target status as 'status' -->
                                    <input type="hidden" name="status" value="<?= ($staff['status'] ?? 'active') === 'active' ? 'inactive' : 'active' ?>" />

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