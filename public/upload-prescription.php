<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once __DIR__ . '/../includes/auth.php';

requireLogin();

if (function_exists('requireCustomer')) {
    requireCustomer();
} elseif (empty($_SESSION['customer_id'])) {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$pageTitle = "Upload Prescription - MediQuick";
$page_css = "upload-prescription.css"; // Used by the header template to load custom CSS
$customerId = (int) $_SESSION['customer_id'];

/*
|--------------------------------------------------------------------------
| CSRF TOKEN
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'];


/*
|--------------------------------------------------------------------------
| FLASH MESSAGES
|--------------------------------------------------------------------------
*/

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;

unset($_SESSION['flash_success'], $_SESSION['flash_error']);


/*
|--------------------------------------------------------------------------
| PRESCRIPTION HISTORY
|--------------------------------------------------------------------------
*/

$historySql = "
    SELECT
        prescription_id,
        file_path,
        status,
        rejection_reason,
        created_at
    FROM prescriptions
    WHERE customer_id = ?
    ORDER BY prescription_id DESC
";

$historyStmt = $conn->prepare($historySql);

if ($historyStmt) {
    $historyStmt->bind_param("i", $customerId);
    $historyStmt->execute();
    $historyResult = $historyStmt->get_result();
} else {
    $historyResult = false;
}

// Include Header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="prescription-page-wrapper">
    <div class="prescription-container">

        <h1 class="page-title">Upload a Prescription</h1>

        <!-- SUCCESS MESSAGE -->
        <?php if ($flashSuccess): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
                <?= htmlspecialchars($flashSuccess); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- ERROR MESSAGE -->
        <?php if ($flashError): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
                <?= htmlspecialchars($flashError); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="prescription-layout">

            <!-- UPLOAD FORM -->
            <div class="prescription-card">
                <h2>New Prescription</h2>

                <form
                    action="handlers/upload-prescription-handler.php"
                    method="POST"
                    enctype="multipart/form-data"
                    id="prescriptionForm"
                >
                    <!-- CSRF -->
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars($csrfToken); ?>"
                    >

                    <!-- DATE -->
                    <div class="mb-3">
                        <label for="issue_date" class="form-label">
                            Date Issued
                        </label>
                        <input
                            type="date"
                            class="form-control"
                            id="issue_date"
                            name="issue_date"
                            max="<?= date('Y-m-d'); ?>"
                        >
                    </div>

                    <!-- PRESCRIPTION FILE -->
                    <div class="mb-3">
                        <label class="form-label">
                            Prescription
                            <span class="text-danger">*</span>
                        </label>

                        <div class="upload-drop" id="dropZone">
                            <i class="bi bi-cloud-arrow-up"></i>
                            <p class="mb-1 fw-semibold" id="dropLabel">
                                Click to choose a prescription or drag it here
                            </p>
                            <p class="text-muted small mb-0">
                                JPG, PNG or PDF - max 5MB
                            </p>
                        </div>

                        <input
                            type="file"
                            class="d-none"
                            id="prescription_file"
                            name="prescription_file"
                            accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf"
                            required
                        >
                    </div>

                    <!-- FILE ERROR -->
                    <div id="fileError" class="text-danger small mb-3"></div>

                    <!-- SUBMIT -->
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                        Submit Prescription
                    </button>
                </form>
            </div>

            <!-- PRESCRIPTION HISTORY -->
            <div class="prescription-card">
                <h5>Your Prescriptions</h5>

                <div class="table-responsive mb-0">
                    <table class="prescription-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Prescription</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php if ($historyResult && $historyResult->num_rows > 0): ?>
                            <?php while ($row = $historyResult->fetch_assoc()): ?>

                                <?php
                                $status = strtolower(trim($row['status'] ?? 'pending'));

                                $badge = match ($status) {
                                    'verified' => 'bg-success',
                                    'rejected' => 'bg-danger',
                                    default    => 'bg-warning text-dark',
                                };

                                $filePath = trim($row['file_path'] ?? '');
                                $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                                $safeFilePath = htmlspecialchars(
                                    ltrim($filePath, '/'),
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>

                                <tr>
                                    <td>
                                        <strong>#<?= (int)$row['prescription_id']; ?></strong>
                                    </td>

                                    <td>
                                        <?php if (!empty($filePath)): ?>
                                            <a href="<?= $safeFilePath; ?>"
                                               target="_blank"
                                               class="prescription-preview-link">

                                                <?php if (in_array($extension, ['jpg', 'jpeg', 'png'])): ?>
                                                    <img
                                                        src="<?= $safeFilePath; ?>"
                                                        alt="Prescription"
                                                        class="prescription-preview">
                                                <?php elseif ($extension === 'pdf'): ?>
                                                    <div class="pdf-preview">PDF</div>
                                                <?php else: ?>
                                                    <div class="pdf-preview">FILE</div>
                                                <?php endif; ?>

                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">No prescription</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <span class="badge <?= $badge; ?>">
                                            <?php
                                            $statusText = match ($status) {
                                                'pending' => 'Verification Pending',
                                                'verified' => 'Prescription Verified',
                                                'rejected' => 'Prescription Rejected',
                                                default => ucfirst($status)
                                            };
                                            ?>
                                            <?= htmlspecialchars($statusText); ?>
                                        </span>

                                        <?php if ($status === 'rejected' && !empty($row['rejection_reason'])): ?>
                                            <div class="small text-muted mt-1">
                                                <?= htmlspecialchars($row['rejection_reason']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-nowrap">
                                        <?= htmlspecialchars($row['created_at'] ?? ''); ?>
                                    </td>

                                    <td>
                                        <?php if ($status === 'verified'): ?>
                                            <a href="confirm-order.php?prescription_id=<?= (int)$row['prescription_id']; ?>"
                                               class="btn btn-success btn-sm">
                                                Confirm Order
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    You haven't uploaded any prescriptions yet.
                                </td>
                            </tr>

                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
(function () {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('prescription_file');
    const dropLabel = document.getElementById('dropLabel');
    const fileError = document.getElementById('fileError');
    const form = document.getElementById('prescriptionForm');

    const ALLOWED_EXT = ['jpg', 'jpeg', 'png', 'pdf'];
    const MAX_SIZE = 5 * 1024 * 1024;

    dropZone.addEventListener('click', function () {
        fileInput.click();
    });

    ['dragover', 'dragenter'].forEach(function (evt) {
        dropZone.addEventListener(evt, function (e) {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });
    });

    ['dragleave', 'drop'].forEach(function (evt) {
        dropZone.addEventListener(evt, function (e) {
            e.preventDefault();
            dropZone.classList.remove('dragover');
        });
    });

    dropZone.addEventListener('drop', function (e) {
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            handleFileChange();
        }
    });

    fileInput.addEventListener('change', handleFileChange);

    function handleFileChange() {
        fileError.textContent = '';
        const file = fileInput.files[0];

        if (!file) {
            dropLabel.textContent = 'Click to choose a prescription or drag it here';
            return;
        }

        const ext = file.name.split('.').pop().toLowerCase();

        if (!ALLOWED_EXT.includes(ext)) {
            fileError.textContent = 'Please choose a JPG, PNG or PDF file.';
            fileInput.value = '';
            dropLabel.textContent = 'Click to choose a prescription or drag it here';
            return;
        }

        if (file.size > MAX_SIZE) {
            fileError.textContent = 'File is too large. Max size is 5MB.';
            fileInput.value = '';
            dropLabel.textContent = 'Click to choose a prescription or drag it here';
            return;
        }

        dropLabel.textContent = file.name;
    }

    form.addEventListener('submit', function (e) {
        if (!fileInput.files.length) {
            e.preventDefault();
            fileError.textContent = 'Please attach a prescription file.';
        }
    });
})();
</script>

<?php
// Include Footer
include_once __DIR__ . '/../includes/footer.php';
?>