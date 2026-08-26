<?php
session_start();
require 'db.php';

$message = '';
$alert_type = 'danger';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        // Password reset link or token logic goes here
        $message = "Password reset instructions have been sent to your email.";
        $alert_type = 'success';
    } else {
        $message = "Email address not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reset Password</title>
</head>
<body>
    <?php include 'partials/spinner.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow border-0 rounded-3 p-4">
                    <h3 class="text-center mb-3">Reset Password</h3>
                    <p class="text-muted text-center mb-4">Enter your email address to receive password reset instructions.</p>

                    <?php if ($message): ?>
                        <div class="alert alert-<?= $alert_type ?>"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>

                    <form action="password-reset.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Email address</label>
                            <input type="email" name="email" class="form-control" placeholder="Enter your registered email" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">Send Reset Link</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="login.php" class="text-primary"><i class="fas fa-arrow-left me-1"></i> Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'partials/scripts.php'; ?>
</body>
</html>