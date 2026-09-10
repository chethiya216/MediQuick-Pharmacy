<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
require_once __DIR__ . '/../../includes/db.php';

$pageTitle = "Customer Messages - MediQuick";

$customerMessagesQuery = "SELECT * FROM `customer_messages` JOIN customers ON customer_messages.customer_id = customers.customer_id ORDER BY `id` DESC";

$stmt = $conn->prepare($customerMessagesQuery);
$stmt->execute();
$messages = $stmt->get_result();

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
                <div class="container-xxl flex-grow-1 container-p-y">

                    <!-- Customer Messages Card -->
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title m-0 me-2">
                                <i class="menu-icon tf-icons bx bx-message-square-detail me-1 text-primary"></i>
                                Customer Messages
                            </h5>
                            <div class="dropdown">
                                <button class="btn p-0" type="button" id="messagesOptions" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="messagesOptions">
                                    <a class="dropdown-item" href="javascript:void(0);">Mark all as read</a>
                                    <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="table-border-bottom-0">
                                    <!-- Unread Message Row -->
                                    <tr class="table-active">
                                        <td>
                                            <div class="d-flex justify-content-start align-items-center customer-name">
                                                <div class="avatar-wrapper">
                                                    <div class="avatar me-2">
                                                        <span class="avatar-initial rounded-circle bg-label-primary">JD</span>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold text-heading">John Doe</span>
                                                    <small class="text-muted">john.doe@example.com</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-heading">Order Delay Inquiry</span>
                                            <div class="text-muted small text-truncate" style="max-width: 200px;">
                                                Hello, I placed an order three days ago...
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-label-danger me-1">Unread</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">Today, 10:45 AM</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-inline-block text-nowrap">
                                                <button class="btn btn-sm btn-icon" data-bs-toggle="modal" data-bs-target="#viewMessageModal" title="View">
                                                    <i class="bx bx-show text-primary"></i>
                                                </button>
                                                <button class="btn btn-sm btn-icon" title="Reply">
                                                    <i class="bx bx-reply text-success"></i>
                                                </button>
                                                <button class="btn btn-sm btn-icon" title="Delete">
                                                    <i class="bx bx-trash text-danger"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Read Message Row -->
                                    <tr>
                                        <td>
                                            <div class="d-flex justify-content-start align-items-center customer-name">
                                                <div class="avatar-wrapper">
                                                    <div class="avatar me-2">
                                                        <span class="avatar-initial rounded-circle bg-label-info">AS</span>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold text-heading">Alice Smith</span>
                                                    <small class="text-muted">alice.s@example.com</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-heading">Product Refund Request</span>
                                            <div class="text-muted small text-truncate" style="max-width: 200px;">
                                                I would like to request a refund for item...
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-label-secondary me-1">Read</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">Yesterday, 4:15 PM</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-inline-block text-nowrap">
                                                <button class="btn btn-sm btn-icon" data-bs-toggle="modal" data-bs-target="#viewMessageModal" title="View">
                                                    <i class="bx bx-show text-primary"></i>
                                                </button>
                                                <button class="btn btn-sm btn-icon" title="Reply">
                                                    <i class="bx bx-reply text-success"></i>
                                                </button>
                                                <button class="btn btn-sm btn-icon" title="Delete">
                                                    <i class="bx bx-trash text-danger"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Sneat Styled Modal -->
                    <div class="modal fade" id="viewMessageModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalCenterTitle">
                                        <i class="bx bx-envelope me-1 text-primary"></i> Message Details
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar me-2">
                                                <span class="avatar-initial rounded-circle bg-label-primary">JD</span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">John Doe</h6>
                                                <small class="text-muted">john.doe@example.com</small>
                                            </div>
                                        </div>
                                        <span class="badge bg-label-secondary">Today, 10:45 AM</span>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Subject:</label>
                                        <p class="form-control-plaintext pt-0">Order Delay Inquiry</p>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Message:</label>
                                        <div class="p-3 bg-lighter rounded border">
                                            <p class="mb-0">Hello, I placed an order three days ago (Order #1042) and haven't received a tracking update yet. Could you please check the status?</p>
                                        </div>
                                    </div>

                                    <form>
                                        <div class="mb-3">
                                            <label for="replyMessage" class="form-label fw-semibold">Quick Reply</label>
                                            <textarea class="form-control" id="replyMessage" rows="3" placeholder="Write your response..."></textarea>
                                        </div>
                                        <div class="modal-footer px-0 pb-0">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-paper-plane me-1"></i> Send Reply
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- / Container -->

                <!-- FOOTER -->
                <?php require_once __DIR__ . '/includes/footer.php'; ?>

                <div class="content-backdrop fade"></div>
            </div>
            <!-- / Content Wrapper -->

        </div>
        <!-- / Layout Page -->

    </div>
    <!-- / Layout Container -->

    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>
</div>
<!-- / Layout Wrapper -->

<!-- SCRIPTS -->
<?php require_once __DIR__ . '/includes/scripts.php'; ?>

</body>
</html>