<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



if (!defined('BASE_URL')) {
    define('BASE_URL', '/GitHub/MediQuick-Pharmacy/public');
}


/*
|--------------------------------------------------------------------------
| Access Denied Redirect Helper
|--------------------------------------------------------------------------
*/

function denyAccess(string $message = "Access denied. You do not have permission to view this resource.", string $redirectPath = "/access-denied.php"): void
{
    $_SESSION['auth_error'] = $message;
    http_response_code(403);
    header("Location: " . BASE_URL . $redirectPath);
    exit;
}


/*
|--------------------------------------------------------------------------
| Check if user is logged in
|--------------------------------------------------------------------------
*/

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}


/*
|--------------------------------------------------------------------------
| Require Login
|--------------------------------------------------------------------------
*/

function requireLogin(): void
{
    if (!isLoggedIn()) {
        $_SESSION['auth_error'] = "Please log in to access this page.";
        header("Location: " . BASE_URL . "/login.php");
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| Get Current User Role
|--------------------------------------------------------------------------
*/

function getUserRole(): ?string
{
    return $_SESSION['role'] ?? null;
}


/*
|--------------------------------------------------------------------------
| Require Admin
|--------------------------------------------------------------------------
*/

function requireAdmin(): void
{
    requireLogin();

    $role = getUserRole();

    if ($role !== 'admin' && $role !== 'superadmin') {
        denyAccess("Access denied. Admin or Superadmin permission is required.", "/admin/access-denied.php");
    }
}


/*
|--------------------------------------------------------------------------
| Require Superadmin
|--------------------------------------------------------------------------
*/

function requireSuperAdmin(): void
{
    requireLogin();

    if (getUserRole() !== 'superadmin') {
        denyAccess("Access denied. Superadmin permission is required.", "/admin/access-denied.php");
    }
}


/*
|--------------------------------------------------------------------------
| Require Pharmacist
|--------------------------------------------------------------------------
*/

function requirePharmacist(): void
{
    requireLogin();

    $role = getUserRole();

    if (
        $role !== 'pharmacist' &&
        $role !== 'admin' &&
        $role !== 'superadmin'
    ) {
        denyAccess("Access denied. Superadmin, Admin, or Pharmacist permission is required.", "/admin/access-denied.php");
    }
}


/*
|--------------------------------------------------------------------------
| Require Customer
|--------------------------------------------------------------------------
| Customer-only pages (cart, prescriptions, account) call this. It first
| makes sure someone is logged in at all (requireLogin), then makes sure
| they're specifically logged in as a customer — staff/admin/pharmacist
| accounts are logged in, but aren't customers, so they get a clear
| "access denied" instead of being bounced back to the login page.
*/

function requireCustomer(): void
{
    requireLogin();

    if (getUserRole() !== 'customer' || empty($_SESSION['customer_id'])) {
        denyAccess("Access denied. This page is only available to customer accounts.");
    }
}


/*
|--------------------------------------------------------------------------
| Get User By ID
|--------------------------------------------------------------------------
*/

function getUserById($userId) {
    global $conn;

    if (!$userId || !$conn) {
        return null;
    }

    // 1. Check staff table first
    $sql = "SELECT staff_id, first_name, last_name, email, role, status, hire_date 
            FROM staff 
            WHERE staff_id = ? 
            LIMIT 1";
            
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user) {
            return $user;
        }
    }

    // 2. If not in staff, check customers table
    $sql = "SELECT customer_id, first_name, last_name, email 
            FROM customers 
            WHERE customer_id = ? 
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user) {
            return $user;
        }
    }

    return null;
}