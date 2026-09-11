<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
        header("Location: ../login.php");
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

    if (
        $role !== 'admin' &&
        $role !== 'superadmin'
    ) {
        http_response_code(403);

        die("Access denied. Admin permission required.");
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
        http_response_code(403);

        die("Access denied. Superadmin permission required.");
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
        http_response_code(403);

        die("Access denied. Superadmin, Admin or Pharmacist permission required");
    }
}

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