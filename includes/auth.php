<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| Validate Login Input
|--------------------------------------------------------------------------
*/

function validateLoginInput($email, $password)
{
    if ($email === '') {
        return "Email is required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Please enter a valid email address.";
    }

    if ($password === '') {
        return "Password is required.";
    }

    return null;
}


/*
|--------------------------------------------------------------------------
| Find Customer By Email
|--------------------------------------------------------------------------
*/

function findCustomerByEmail($conn, $email)
{
    $sql = "
        SELECT TOP 1
            customer_id,
            first_name,
            last_name,
            email,
            password_hash,
            status
        FROM CUSTOMER
        WHERE email = ?
    ";

    $stmt = sqlsrv_query(
        $conn,
        $sql,
        [$email]
    );

    if ($stmt === false) {
        return false;
    }

    return sqlsrv_fetch_array(
        $stmt,
        SQLSRV_FETCH_ASSOC
    );
}


/*
|--------------------------------------------------------------------------
| Find Staff By Email
|--------------------------------------------------------------------------
*/

function findStaffByEmail($conn, $email)
{
    $sql = "
        SELECT TOP 1
            staff_id,
            first_name,
            last_name,
            email,
            password_hash,
            role,
            status
        FROM staff
        WHERE email = ?
    ";

    $stmt = sqlsrv_query(
        $conn,
        $sql,
        [$email]
    );

    if ($stmt === false) {
        return false;
    }

    return sqlsrv_fetch_array(
        $stmt,
        SQLSRV_FETCH_ASSOC
    );
}


/*
|--------------------------------------------------------------------------
| Customer Login Session
|--------------------------------------------------------------------------
*/

function loginCustomer($customer)
{
    session_regenerate_id(true);

    $_SESSION['logged_in'] = true;

    $_SESSION['user_type'] = 'customer';

    $_SESSION['role'] = 'customer';

    $_SESSION['customer_id'] =
        $customer['customer_id'];

    $_SESSION['staff_id'] = null;

    $_SESSION['first_name'] =
        $customer['first_name'];

    $_SESSION['last_name'] =
        $customer['last_name'];

    $_SESSION['user_name'] =
        $customer['first_name']
        . ' '
        . $customer['last_name'];

    $_SESSION['email'] =
        $customer['email'];
}


/*
|--------------------------------------------------------------------------
| Staff Login Session
|--------------------------------------------------------------------------
*/

function loginStaff($staff)
{
    session_regenerate_id(true);

    $_SESSION['logged_in'] = true;

    $_SESSION['user_type'] = 'staff';

    $_SESSION['role'] =
        strtolower($staff['role']);

    $_SESSION['staff_id'] =
        $staff['staff_id'];

    $_SESSION['customer_id'] = null;

    $_SESSION['first_name'] =
        $staff['first_name'];

    $_SESSION['last_name'] =
        $staff['last_name'];

    $_SESSION['user_name'] =
        $staff['first_name']
        . ' '
        . $staff['last_name'];

    $_SESSION['email'] =
        $staff['email'];
}


/*
|--------------------------------------------------------------------------
| Check Login
|--------------------------------------------------------------------------
*/

function isLoggedIn()
{
    return isset($_SESSION['logged_in'])
        && $_SESSION['logged_in'] === true;
}


/*
|--------------------------------------------------------------------------
| Require Login
|--------------------------------------------------------------------------
*/

function requireLogin()
{
    if (!isLoggedIn()) {

        header("Location: ../login.php");

        exit;
    }
}


/*
|--------------------------------------------------------------------------
| Require Role
|--------------------------------------------------------------------------
*/

function requireRole(...$roles)
{
    requireLogin();

    $currentRole =
        $_SESSION['role'] ?? null;

    if (
        !in_array(
            $currentRole,
            $roles,
            true
        )
    ) {

        header(
            "Location: ../unauthorized.php"
        );

        exit;
    }
}


/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/

function logoutUser()
{
    $_SESSION = [];

    if (
        ini_get('session.use_cookies')
    ) {

        $params =
            session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}