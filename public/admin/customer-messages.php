<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
require_once __DIR__ . '/../../includes/db.php';

$pageTitle = "Customer Messages - MediQuick";

// Select specific fields to prevent duplicate name collisions from joining tables
$customerMessagesQuery = "SELECT 
    cm.id AS message_id,
    cm.subject,
    cm.message,
    cm.received_at,
    cm.status AS message_status,
    c.first_name AS customer_name,
    c.email AS customer_email
FROM contact_messages cm 
JOIN customers c ON cm.customer_id = c.customer_id 
ORDER BY cm.id DESC";

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
                                    <?php if ($messages->num_rows > 0): ?>
                                        <?php foreach ($messages as $message): 
                                            $msgId = (int)$message['message_id'];
                                            $custName = htmlspecialchars($message['customer_name'] ?? 'Unknown');
                                            $custEmail = htmlspecialchars($message['customer_email'] ?? 'N/A');
                                            $subject = htmlspecialchars($message['subject'] ?? 'No Subject');
                                            $body = htmlspecialchars($message['message'] ?? '');
                                            $status = strtolower($message['message_status'] ?? 'unread');
                                            $formattedDate = date('M d, Y h:i A', strtotime($message['received_at'] ?? 'N/A'));
                                            
                                            // Avatar Initials
                                            $words = explode(' ', trim($custName));
                                            $initials = strtoupper(substr($words[0] ?? '', 0, 1) . substr($words[1] ?? '', 0, 1));
                                            
                                            // Status Badge Class
                                            $badgeClass = ($status === 'unread') ? 'bg-label-danger' : 'bg-label-secondary';
                                            $rowClass = ($status === 'unread') ? 'table-active' : '';
                                        ?>
                                        <tr class="<?php echo $rowClass; ?>">
                                            <td>
                                                <div class="d-flex justify-content-start align-items-center customer-name">
                                                    <div class="avatar-wrapper">
                                                        <div class="avatar me-2">
                                                            <span class="avatar-initial rounded-circle bg-label-primary"><?php echo $initials; ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-semibold text-heading"><?php echo $custName; ?></span>
                                                        <small class="text-muted"><?php echo $custEmail; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-semibold text-heading"><?php echo $subject; ?></span>
                                                <div class="text-muted small text-truncate" style="max-width: 200px;">
                                                    <?php echo $body; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $badgeClass; ?> me-1"><?php echo ucfirst($status); ?></span>
                                            </td>
                                            <td>
                                                <span class="text-muted"><?php echo $formattedDate; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-inline-block text-nowrap">
                                                    <button class="btn btn-sm btn-icon" data-bs-toggle="modal" data-bs-target="#viewMessageModal<?php echo $msgId; ?>" title="View">
                                                        <i class="bx bx-show text-primary"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-icon" title="Delete">
                                                        <i class="bx bx-trash text-danger"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Dynamic Modal per Message -->
                                        <div class="modal fade" id="viewMessageModal<?php echo $msgId; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            <i class="bx bx-envelope me-1 text-primary"></i> Message Details
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar me-2">
                                                                    <span class="avatar-initial rounded-circle bg-label-primary"><?php echo $initials; ?></span>
                                                                </div>
                                                                <div>
                                                                    <h6 class="mb-0"><?php echo $custName; ?></h6>
                                                                    <small class="text-muted"><?php echo $custEmail; ?></small>
                                                                </div>
                                                            </div>
                                                            <span class="badge bg-label-secondary"><?php echo $formattedDate; ?></span>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Subject:</label>
                                                            <p class="form-control-plaintext pt-0"><?php echo $subject; ?></p>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Message:</label>
                                                            <div class="p-3 bg-lighter rounded border">
                                                                <p class="mb-0 text-wrap"><?php echo nl2br($body); ?></p>
                                                            </div>
                                                        </div>

                                                        <!-- <form method="POST" action="reply_message.php">
                                                            <input type="hidden" name="message_id" value="<?php echo $msgId; ?>">
                                                            <div class="mb-3">
                                                                <label for="replyMessage<?php echo $msgId; ?>" class="form-label fw-semibold">Quick Reply</label>
                                                                <textarea class="form-control" name="reply_text" id="replyMessage<?php echo $msgId; ?>" rows="3" placeholder="Write your response..."></textarea>
                                                            </div>
                                                            <div class="modal-footer px-0 pb-0">
                                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                                                <button type="submit" class="btn btn-primary">
                                                                    <i class="bx bx-paper-plane me-1"></i> Send Reply
                                                                </button>
                                                            </div>
                                                        </form> -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                No customer messages found.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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
</body>
</html>