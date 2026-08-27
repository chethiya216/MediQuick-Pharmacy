<?php

/*
|--------------------------------------------------------------------------
| Security / Utility Functions
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| Escape HTML
|--------------------------------------------------------------------------
*/

function e($value)
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}


/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

function redirect($url)
{
    header("Location: " . $url);
    exit;
}


/*
|--------------------------------------------------------------------------
| Check POST Request
|--------------------------------------------------------------------------
*/

function isPost()
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}


/*
|--------------------------------------------------------------------------
| Generate User ID
|--------------------------------------------------------------------------
*/

function generateUserId($prefix)
{
    return strtoupper($prefix) . '-' .
        strtoupper(bin2hex(random_bytes(4)));
}


/*
|--------------------------------------------------------------------------
| Flash Message
|--------------------------------------------------------------------------
*/

function setFlash($message, $type = 'success')
{
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}


/*
|--------------------------------------------------------------------------
| Get Flash Message
|--------------------------------------------------------------------------
*/

function getFlash()
{
    if (!isset($_SESSION['flash_message'])) {
        return null;
    }

    $message = $_SESSION['flash_message'];
    $type = $_SESSION['flash_type'] ?? 'success';

    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);

    return [
        'message' => $message,
        'type' => $type
    ];
}