<?php
session_start();

// 1. Include database connection first
require_once '../includes/db.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $dob = trim($_POST['dob'] ?? '');

    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (
        $firstName === '' ||
        $lastName === '' ||
        $email === '' ||
        $phone === '' ||
        $address === '' ||
        $dob === '' ||
        $password === '' ||
        $confirmPassword === ''
    ) {

        $message = "Please fill in all required fields.";
        $messageType = "danger";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $messageType = "danger";

    } elseif ($password !== $confirmPassword) {

        $message = "Passwords do not match.";
        $messageType = "danger";

    } elseif (strlen($password) < 8) {

        $message = "Password must be at least 8 characters.";
        $messageType = "danger";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Check Existing Customer
        |--------------------------------------------------------------------------
        */

        $sql = "
            SELECT customer_id
            FROM customers
            WHERE email = ?
            LIMIT 1
        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            $message = "Database error: " . $conn->error;
            $messageType = "danger";

        } else {

            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            $customerExists = $stmt->num_rows > 0;
            $stmt->close();

            /*
            |--------------------------------------------------------------------------
            | Check Existing Staff
            |--------------------------------------------------------------------------
            */

            $sql = "
                SELECT staff_id
                FROM staff
                WHERE email = ?
                LIMIT 1
            ";

            $stmt = $conn->prepare($sql);

            if (!$stmt) {

                $message = "Database error: " . $conn->error;
                $messageType = "danger";

            } else {

                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();
                $staffExists = $stmt->num_rows > 0;
                $stmt->close();

                if ($customerExists || $staffExists) {

                    $message = "This email is already registered.";
                    $messageType = "danger";

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Create Customer
                    |--------------------------------------------------------------------------
                    */

                    $customerId = 'CUS-' . strtoupper(bin2hex(random_bytes(4)));
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                    $sql = "
                        INSERT INTO customers
                        (
                            customer_id,
                            first_name,
                            last_name,
                            email,
                            phone,
                            address,
                            date_of_birth,
                            password_hash,
                            status
                        )
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
                    ";

                    $stmt = $conn->prepare($sql);

                    if (!$stmt) {

                        $message = "Database error: " . $conn->error;
                        $messageType = "danger";

                    } else {

                        $stmt->bind_param(
                            "ssssssss",
                            $customerId,
                            $firstName,
                            $lastName,
                            $email,
                            $phone,
                            $address,
                            $dob,
                            $passwordHash
                        );

                        if ($stmt->execute()) {
                            // Redirect works successfully now because no HTML headers/output were sent yet
                            header("Location: login.php");
                            exit;

                        } else {

                            $message = "Registration failed: " . $stmt->error;
                            $messageType = "danger";
                        }

                        $stmt->close();
                    }
                }
            }
        }
    }
}

// 2. Include header.php AFTER processing logic, right before HTML rendering begins
require_once '../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">

<body>

    <!-- Spinner with immediate self-destruct script to prevent hanging -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center" style="z-index: 99999;">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <script>
        // Removes the spinner instantly so external template scripts can't lock it
        (function() {
            const s = document.getElementById('spinner');
            if (s) s.remove();
        })();
    </script>

    <div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center py-5 bg-light">
        <div class="container my-auto">
            <div class="row g-0 shadow-lg rounded overflow-hidden justify-content-center align-items-stretch">
                
                <div class="col-lg-6 bg-white p-4 p-sm-5 d-flex flex-column justify-content-center">
                    
                    <h1 class="mb-2 text-center text-lg-start fw-bold fs-3">Welcome to MediQuick Pharmacy!</h1>
                    <h2 class="mb-4 text-center text-lg-start fw-bold fs-4 text-muted">Create an Account</h2>
                    
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="register.php" method="POST">
                        
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <div class="input-group border rounded bg-light">
                                    <span class="input-group-text bg-transparent border-0 ps-3 text-muted">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text" class="form-control bg-transparent border-0 py-3 pe-3" id="first_name" name="first_name" placeholder="First Name" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group border rounded bg-light">
                                    <span class="input-group-text bg-transparent border-0 ps-3 text-muted">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text" class="form-control bg-transparent border-0 py-3 pe-3" id="last_name" name="last_name" placeholder="Last Name" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="input-group border rounded bg-light">
                                <span class="input-group-text bg-transparent border-0 ps-3 text-muted">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" class="form-control bg-transparent border-0 py-3 pe-3" id="email" name="email" placeholder="Email address" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="input-group border rounded bg-light">
                                <span class="input-group-text bg-transparent border-0 ps-3 text-muted">
                                    <i class="fas fa-phone"></i>
                                </span>
                                <input type="tel" class="form-control bg-transparent border-0 py-3 pe-3" id="phone" name="phone" placeholder="Phone Number" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="input-group border rounded bg-light">
                                <span class="input-group-text bg-transparent border-0 ps-3 pt-3 align-items-start text-muted">
                                    <i class="fas fa-map-marker-alt"></i>
                                </span>
                                <textarea class="form-control bg-transparent border-0 py-2 pe-3" id="address" name="address" placeholder="Address" rows="2" required><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <!-- Date of Birth Field -->
                        <div class="mb-3">
                            <div class="input-group border rounded bg-light">
                                <span class="input-group-text bg-transparent border-0 ps-3 text-muted">
                                    <i class="fas fa-calendar-alt"></i>
                                </span>
                                <input type="date" class="form-control bg-transparent border-0 py-3 pe-3 text-muted" id="dob" name="dob" value="<?php echo htmlspecialchars($_POST['dob'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <!-- Password Field -->
                        <div class="mb-3">
                            <div class="input-group border rounded bg-light">
                                <span class="input-group-text bg-transparent border-0 ps-3 text-muted">
                                    <i class="fas fa-key"></i>
                                </span>
                                <input type="password" class="form-control bg-transparent border-0 py-3" id="register-password" name="password" placeholder="Password (min. 8 characters)" minlength="8" required>
                                
                                <span class="input-group-text bg-transparent border-0 pe-3 text-muted" 
                                    data-toggle="password" 
                                    data-target="register-password" 
                                    style="cursor: pointer;">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>

                        <!-- Confirm Password Field -->
                        <div class="mb-4">
                            <div class="input-group border rounded bg-light">
                                <span class="input-group-text bg-transparent border-0 ps-3 text-muted">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control bg-transparent border-0 py-3" id="confirm-password" name="confirm_password" placeholder="Confirm Password" minlength="8" required>
                                
                                <span class="input-group-text bg-transparent border-0 pe-3 text-muted" 
                                    data-toggle="password" 
                                    data-target="confirm-password" 
                                    style="cursor: pointer;">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <button type="submit" name="register_submit" class="btn btn-primary w-100 py-3 font-weight-bold text-uppercase">Register</button>
                        </div>

                        <div class="text-center small">
                            <span class="text-muted">Already have an account?</span>
                            <a href="login.php" class="text-primary fw-bold text-decoration-none ms-1">Login here</a>
                        </div>

                    </form>
                </div>

                <div class="col-lg-6 d-none d-lg-block position-relative">
                    <img src="assets/img/carousel-1.png" alt="Register Banner" class="w-100 h-100" style="object-fit: cover; position: absolute; top: 0; left: 0;">
                </div>

            </div>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>
</body>

</html>