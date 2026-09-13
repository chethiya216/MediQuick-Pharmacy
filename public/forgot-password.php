<?php 
ob_start();   
session_start();
require_once '../includes/head.php'; 
require_once '../includes/db.php';

$message = '';
$messageType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $newPassword = $_POST['new_password'] ?? '';

    if ($email === '' || $newPassword === '') {
        $message = "Please fill in all fields.";
        $messageType = "danger";
    } else {
        // Hash the new password securely
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $userUpdated = false;

        // 1. Check & Update Customers Table
        $stmt = $conn->prepare("SELECT customer_id FROM customers WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $isCustomer = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($isCustomer) {
            $updateStmt = $conn->prepare("UPDATE customers SET password_hash = ? WHERE email = ?");
            $updateStmt->bind_param("ss", $hashedPassword, $email);
            $updateStmt->execute();
            $userUpdated = $updateStmt->affected_rows >= 0; // True if statement executed
            $updateStmt->close();
        } else {
            // 2. Check & Update Staff Table if not found in customers
            $stmt = $conn->prepare("SELECT staff_id FROM staff WHERE email = ? LIMIT 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $isStaff = $stmt->get_result()->num_rows > 0;
            $stmt->close();

            if ($isStaff) {
                $updateStmt = $conn->prepare("UPDATE staff SET password_hash = ? WHERE email = ?");
                $updateStmt->bind_param("ss", $hashedPassword, $email);
                $updateStmt->execute();
                $userUpdated = $updateStmt->affected_rows >= 0;
                $updateStmt->close();
            }
        }

        if ($userUpdated) {
            $message = "Your password has been updated successfully! You can now log in.";
            $messageType = "success";
        } else {
            $message = "Email address not found.";
            $messageType = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reset Password - MediQuick Pharmacy</title>
</head>
<body>

    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->

    <!-- Forgot Password Section Start -->
    <div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center py-5 bg-light">
        <div class="container my-auto">
            <div class="row g-0 shadow-lg rounded overflow-hidden justify-content-center align-items-stretch">
                
                <!-- Reset Form Column -->
                <div class="col-lg-6 bg-white p-4 p-sm-5 d-flex flex-column justify-content-center">
                    
                    <h1 class="mb-2 text-center text-lg-start fw-bold fs-3">MediQuick Pharmacy</h1>
                    <h2 class="mb-3 text-center text-lg-start fw-bold fs-4 text-muted">Reset Password</h2>
                    <p class="text-muted small text-center text-lg-start mb-4">Enter your registered email address and your new password below.</p>
                    
                    <!-- Alert Message Display -->
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="forgot-password.php" method="POST">
                        
                        <!-- Email Field -->
                        <div class="mb-3">
                            <div class="input-group border rounded bg-light">
                                <span class="input-group-text bg-transparent border-0 ps-3 text-muted">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" class="form-control bg-transparent border-0 py-3 pe-3" id="reset-email" name="email" placeholder="Email address" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <!-- New Password Field -->
                        <div class="mb-4">
                            <div class="input-group border rounded bg-light">
                                <span class="input-group-text bg-transparent border-0 ps-3 text-muted">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control bg-transparent border-0 py-3 pe-3" id="new-password" name="new_password" placeholder="New Password" required>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2 mb-4">
                            <button type="submit" name="reset_submit" class="btn btn-primary px-4 py-3 font-weight-bold text-uppercase rounded">Update Password</button>
                        </div>

                        <!-- Back to Login Link -->
                        <div class="text-center text-lg-start small">
                            <a href="login.php" class="text-primary fw-bold text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i> Back to Login
                            </a>
                        </div>

                    </form>
                </div>

                <!-- Right Side Image Column -->
                <div class="col-lg-6 d-none d-lg-block position-relative">
                    <img src="assets/img/MediQuick Pharmacy auth banner.png" alt="Reset Password Banner" class="w-100 h-100" style="object-fit: cover; position: absolute; top: 0; left: 0;">
                </div>

            </div>
        </div>
    </div>
    <!-- Forgot Password Section End -->

    <?php require_once '../includes/footer.php'; ?>
</body>
</html>