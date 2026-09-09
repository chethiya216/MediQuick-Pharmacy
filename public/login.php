<?php 

session_start();
require_once '../includes/header.php'; 
require_once '../includes/db.php';

$message = '';
$messageType = '';

// ---------  Helper Function to Set Remember Me Cookie

function handleRememberMe($conn, $role, $userId) {
    if (!empty($_POST['rememberme'])) {
        $token = bin2hex(random_bytes(32)); // 64-char random string
        $hashedToken = hash('sha256', $token);

        $table = ($role === 'staff') ? 'staff' : 'customers';
        $idColumn = ($role === 'staff') ? 'staff_id' : 'customer_id';

        // Store hashed token in DB
        $stmt = $conn->prepare("UPDATE {$table} SET remember_token = ? WHERE {$idColumn} = ?");
        $stmt->bind_param("ss", $hashedToken, $userId);
        $stmt->execute();
        $stmt->close();

        // Store role + plain token in cookie
        $cookieValue = $role . ':' . $token;
        setcookie('remember_me', $cookieValue, [
            'expires'  => time() + (86400 * 30), // 30 Days
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
}


// --------  Auto-Login Check via Cookie (If no active session)

if (!isset($_SESSION['user_id']) && !empty($_COOKIE['remember_me'])) {
    $parts = explode(':', $_COOKIE['remember_me'], 2);

    if (count($parts) === 2) {
        $role = $parts[0];
        $rawToken = $parts[1];
        $hashedToken = hash('sha256', $rawToken);

        $table = ($role === 'staff') ? 'staff' : 'customers';
        $idColumn = ($role === 'staff') ? 'staff_id' : 'customer_id';

        $stmt = $conn->prepare("SELECT * FROM {$table} WHERE remember_token = ? AND status = 'active' LIMIT 1");
        $stmt->bind_param("s", $hashedToken);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user[$idColumn];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = ($role === 'staff') ? $user['role'] : 'customer';

            $redirectUrl = ($role === 'staff') ? "../public/admin/index.php" : "../public/index.html";
            header("Location: " . $redirectUrl);
            exit;
        } else {
            // Invalid token — clear broken cookie
            setcookie('remember_me', '', time() - 3600, '/');
        }
    }
}



//  -----------  POST Login Handler

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '' ) {
        $message = "Please enter email and password.";
        $messageType = "danger";

    } else {

        // 1. Check Customers Table
        $sql = "
            SELECT customer_id, first_name, last_name, email, password_hash, status
            FROM customers
            WHERE email = ?
            LIMIT 1
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user) {
            if ($user['status'] !== 'active') {
                $message = "Your account is not active.";
                $messageType = "danger";
            } elseif (password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['customer_id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = 'customer';

                // Save Remember Me Cookie
                handleRememberMe($conn, 'customer', $user['customer_id']);

                header("Location: ../public/index.html");
                exit;

            } else {
                $message = "Invalid email or password.";
                $messageType = "danger";
            }

        } else {

            // 2. Check Staff Table
            $sql = "
                SELECT staff_id, first_name, last_name, email, password_hash, role, status
                FROM staff
                WHERE email = ?
                LIMIT 1
            ";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $staff = $result->fetch_assoc();
            $stmt->close();

            if (!$staff) {
                $message = "Invalid email or password.";
                $messageType = "danger";
            } elseif ($staff['status'] !== 'active') {
                $message = "Your account is not active.";
                $messageType = "danger";
            } elseif (password_verify($password, $staff['password_hash'])) {

                session_regenerate_id(true);
                $_SESSION['user_id'] = $staff['staff_id'];
                $_SESSION['first_name'] = $staff['first_name'];
                $_SESSION['last_name'] = $staff['last_name'];
                $_SESSION['email'] = $staff['email'];
                $_SESSION['role'] = $staff['role'];

                // Save Remember Me Cookie
                handleRememberMe($conn, 'staff', $staff['staff_id']);

                header("Location: ../public/admin/index.php");
                exit;

            } else {
                $message = "Invalid email or password.";
                $messageType = "danger";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<body>

    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->

    <!-- Login Section Start -->
    <div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center py-5 bg-light">
        <div class="container my-auto">
            <div class="row g-0 shadow-lg rounded overflow-hidden justify-content-center align-items-stretch">
                
                <!-- Login Form Column -->
                <div class="col-lg-6 bg-white p-4 p-sm-5 d-flex flex-column justify-content-center">
                    
                    <h1 class="mb-2 text-center text-lg-start fw-bold fs-3">Welcome to MediQuick Pharmacy!</h1>
                    <h2 class="mb-4 text-center text-lg-start fw-bold fs-4 text-muted">Login</h2>
                    
                    <!-- Alert Message Display -->
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="login.php" method="POST">
                        
                        <!-- Email Field -->
                        <div class="mb-3">
                            <div class="input-group border rounded bg-light">
                                <span class="input-group-text bg-transparent border-0 ps-3 text-muted">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" class="form-control bg-transparent border-0 py-3 pe-3" id="login-email" name="email" placeholder="Email address" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <!-- Password Field -->
                        <div class="mb-3">
                            <div class="input-group border rounded bg-light">
                                <span class="input-group-text bg-transparent border-0 ps-3 text-muted">
                                    <i class="fas fa-key"></i>
                                </span>
                                <input type="password" class="form-control bg-transparent border-0 py-3" id="login-password" name="password" placeholder="Password" required>
                                <span class="input-group-text bg-transparent border-0 pe-3 text-muted" 
                                    data-toggle="password" 
                                    data-target="login-password" 
                                    style="cursor: pointer;">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>

                        <!-- Remember Me & Submit -->
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="form-check m-0">
                                <input class="form-check-input" type="checkbox" id="rememberme" name="rememberme">
                                <label class="form-check-label text-muted small" for="rememberme">Remember me</label>
                            </div>
                            <button type="submit" name="login_submit" class="btn btn-primary px-4 py-2 font-weight-bold text-uppercase">Login</button>
                        </div>

                        <!-- Links -->
                        <div class="d-flex justify-content-between small">
                            <a href="register.php" class="text-primary fw-bold text-decoration-none">Register now</a>
                            <a href="#" class="text-muted text-decoration-none">Forgot password?</a>
                        </div>

                    </form>
                </div>

                <!-- Right Side Image Column -->
                <div class="col-lg-6 d-none d-lg-block position-relative">
                    <img src="assets/img/carousel-1.png" alt="Login Banner" class="w-100 h-100" style="object-fit: cover; position: absolute; top: 0; left: 0;">
                </div>

            </div>
        </div>
    </div>
    <!-- Login Section End -->

    <?php require_once '../includes/footer.php'; ?>
</body>

</html>