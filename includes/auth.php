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

        die("Access denied.");
    }
}