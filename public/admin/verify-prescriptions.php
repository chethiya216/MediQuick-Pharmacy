<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../includes/auth.php';
requireLogin();
require_once __DIR__ . '/../../includes/db.php';

$pageTitle = "Verify Prescription - MediQuick";

// 1. Validate incoming prescription ID
$prescriptionId = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;

if (!$prescriptionId) {
    header("Location: manage-prescriptions.php");
    exit;
}

// 2. Fetch prescription record joined with customer details
$sql = "
    SELECT 
        pr.*,
        c.first_name,
        c.last_name,
        c.email,
        s.staff_id,
        s.first_name AS staff_first_name
    FROM prescriptions pr
    LEFT JOIN staff s ON pr.staff_id = s.staff_id
    LEFT JOIN customers c ON c.customer_id = pr.customer_id
    WHERE pr.prescription_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $prescriptionId);
$stmt->execute();
$prescription = $stmt->get_result()->fetch_assoc();

if (!$prescription) {
    header("Location: manage-prescriptions.php");
    exit;
}

// 3. Handle status updates (POST action)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action           = $_POST['action'] ?? '';
    $rejectionReason  = trim($_POST['rejection_reason'] ?? '');

    $errors = [];

    if ($action === 'verified') {
        $status = 'verified';
        $rejectionReason = null; // Clear reason if approving
    } elseif ($action === 'rejected') {
        $status = 'rejected';
        if (empty($rejectionReason)) {
            $errors[] = "Please provide a reason for rejecting this prescription.";
        }
    } else {
        $errors[] = "Invalid verification action.";
    }

    if (empty($errors)) {
        // Clean SQL statement with duplicate status column removed
        $updateSql = "
            UPDATE prescriptions SET
                rejection_reason = ?,
                status = ?,
                verified_date = NOW(),
                updated_at = NOW()
            WHERE prescription_id = ?
        ";

        $updateStmt = $conn->prepare($updateSql);
        
        // Types: s = string (rejection_reason), s = string (status), i = integer (staff_id), i = integer (prescription_id)
        $updateStmt->bind_param(
            "ssi",
            $rejectionReason,
            $status,
            $prescriptionId,
        );

        if ($updateStmt->execute()) {
            $_SESSION['flash_success'] = "Prescription #{$prescriptionId} status updated to '{$status}'.";
            header("Location: manage-prescriptions.php");
            exit;
        } else {
            $errors[] = "Failed to update record: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html
    lang="en"
    class="light-style layout-menu-fixed"
    dir="ltr"
    data-theme="theme-default"
    data-assets-path="../admin-assets/assets/"
    data-template="vertical-menu-template-free"
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

                    <!-- BREADCRUMB -->
                    <div class="d-flex justify-content-between align-items-center py-3 mb-4">
                        <h4 class="fw-bold m-0">
                            <span class="text-muted fw-light">Prescriptions /</span> Verify Rx #<?= htmlspecialchars($prescription['prescription_id']); ?>
                        </h4>
                        <a href="manage-prescriptions.php" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Back to List
                        </a>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <ul class="mb-0">
                                <?php foreach ($errors as $err): ?>
                                    <li><?= htmlspecialchars($err); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <!-- LEFT COLUMN: PRESCRIPTION FILE PREVIEW -->
                        <div class="col-12 col-lg-7 mb-4">
                            <div class="card h-100 border">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Prescription Image</h5>
                                    <div>
                                        <button type="button" id="zoomIn" class="btn btn-sm btn-outline-primary me-1">
                                            <i class="bx bx-zoom-in"></i>
                                        </button>
                                        <button type="button" id="zoomOut" class="btn btn-sm btn-outline-primary me-1">
                                            <i class="bx bx-zoom-out"></i>
                                        </button>
                                        <button type="button" id="rotateImg" class="btn btn-sm btn-outline-secondary">
                                            <i class="bx bx-refresh"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body d-flex justify-content-center align-items-center bg-light overflow-auto p-3" style="min-height: 480px; max-height: 650px;">
                                    <?php 
                                        $rawPath = $prescription['file_path'] ?? '';
                                        $cleanPath = ltrim(str_replace('../', '', $rawPath), '/');

                                        $pathInsidePublic = dirname(__DIR__) . '/' . $cleanPath;
                                        $pathOutsidePublic = dirname(__DIR__, 2) . '/' . $cleanPath;

                                        $serverPath = null;
                                        $webPath = null;

                                        if (!empty($rawPath)) {
                                            if (file_exists($pathInsidePublic)) {
                                                $serverPath = $pathInsidePublic;
                                                $webPath = '../' . $cleanPath;
                                            } elseif (file_exists($pathOutsidePublic)) {
                                                $serverPath = $pathOutsidePublic;
                                                $webPath = '../../' . $cleanPath;
                                            }
                                        }

                                        $extension = strtolower(pathinfo($cleanPath, PATHINFO_EXTENSION));
                                    ?>

                                    <?php if ($serverPath && file_exists($serverPath)): ?>
                                        
                                        <?php if ($extension === 'pdf'): ?>
                                            <!-- PDF Viewer -->
                                            <embed src="<?= htmlspecialchars($webPath); ?>" 
                                                type="application/pdf" 
                                                width="100%" 
                                                height="550px" 
                                                class="rounded shadow-sm" />
                                        <?php else: ?>
                                            <!-- Image Viewer -->
                                            <img id="rxPreview" 
                                                src="<?= htmlspecialchars($webPath); ?>" 
                                                alt="Prescription Document" 
                                                class="img-fluid rounded shadow-sm"
                                                style="transition: transform 0.2s ease; transform-origin: center center; max-height: 550px;">
                                        <?php endif; ?>

                                    <?php else: ?>
                                        <div class="text-center text-muted">
                                            <i class="bx bx-file-blank bx-lg mb-2"></i>
                                            <p class="mb-1">Prescription document missing from server.</p>
                                            <small class="text-danger d-block">Checked location: <?= htmlspecialchars($pathInsidePublic); ?></small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT COLUMN: VERIFICATION FORM & DETAILS -->
                        <div class="col-12 col-lg-5 mb-4">
                            <form method="POST" action="">
                                
                                <!-- CUSTOMER INFO -->
                                <div class="card mb-4 border">
                                    <div class="card-header">
                                        <h5 class="mb-0">Customer Details</h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars(($prescription['first_name'] ?? 'Guest') . ' ' . ($prescription['last_name'] ?? '')); ?></p>
                                        <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($prescription['email'] ?? 'N/A'); ?></p>
                                        <p class="mb-1"><strong>Phone:</strong> <?= htmlspecialchars($prescription['phone'] ?? 'N/A'); ?></p>
                                        <p class="mb-0"><strong>Uploaded At:</strong> <?= htmlspecialchars($prescription['upload_date'] ?? $prescription['created_at']); ?></p>
                                    </div>
                                </div>

                                <!-- VERIFICATION DECISION -->
                                <div class="card mb-4 border">
                                    <div class="card-header">
                                        <h5 class="mb-0">Verification Decision</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="rejection_reason" class="form-label text-danger">Rejection Reason (If rejecting)</label>
                                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" 
                                                      placeholder="Specify why this prescription cannot be verified (e.g. Missing signature, unreadable image, expired file)"><?= htmlspecialchars($prescription['rejection_reason'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- ACTION BUTTONS -->
                                <div class="card border">
                                    <div class="card-body d-flex gap-2">
                                        <button type="submit" name="action" value="verified" class="btn btn-success flex-fill">
                                            <i class="bx bx-check-circle me-1"></i> Approve
                                        </button>
                                        <button type="submit" name="action" value="rejected" class="btn btn-danger flex-fill">
                                            <i class="bx bx-x-circle me-1"></i> Reject
                                        </button>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>

                </div>

                <!-- FOOTER -->
                <?php require_once __DIR__ . '/includes/footer.php'; ?>
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
</div>

<!-- IMAGE CONTROLS SCRIPT -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const img = document.getElementById('rxPreview');
    if (!img) return;

    let scale = 1;
    let rotation = 0;

    document.getElementById('zoomIn').addEventListener('click', function () {
        scale += 0.2;
        applyTransform();
    });

    document.getElementById('zoomOut').addEventListener('click', function () {
        if (scale > 0.4) {
            scale -= 0.2;
            applyTransform();
        }
    });

    document.getElementById('rotateImg').addEventListener('click', function () {
        rotation = (rotation + 90) % 360;
        applyTransform();
    });

    function applyTransform() {
        img.style.transform = `scale(${scale}) rotate(${rotation}deg)`;
    }
});
</script>

</body>
</html>