<?php
session_start();

// 1. Include database connection
require_once '../includes/db.php';

// 2. Unset all session variables
$_SESSION = [];

// 3. Clear Remember Me token from database and browser
if (!empty($_COOKIE['remember_me'])) {
    $parts = explode(':', $_COOKIE['remember_me'], 2);

    if (count($parts) === 2) {
        $role = $parts[0];
        $rawToken = $parts[1];
        $hashedToken = hash('sha256', $rawToken);

        $table = ($role === 'staff') ? 'staff' : 'customers';

        // Nullify the token in database
        if (isset($conn) && $conn instanceof mysqli) {
            $stmt = $conn->prepare("UPDATE {$table} SET remember_token = NULL WHERE remember_token = ?");
            $stmt->bind_param("s", $hashedToken);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Unset browser cookie with matching security parameters
    setcookie('remember_me', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

// 4. Destroy session and delete session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

// 5. Redirect to login page
header("Location: login.php");
exit;
?>