<?php
/**
 * Handles single image file uploads safely.
 *
 * Requires includes/db.php to have already been required (it defines
 * UPLOAD_BASE_PATH). This is what makes the destination folder correct
 * regardless of which file/folder calls this function — no more
 * __DIR__-relative guessing.
 *
 * @param array  $file        The $_FILES['input_name'] array.
 * @param string $subDir      Subfolder under the uploads base (e.g., 'products/').
 * @param int    $maxSizeBytes Maximum allowed file size in bytes (default 2MB).
 * @param array  $allowedMime Map of allowed MIME types to file extensions.
 *
 * @return array [ 'success' => bool, 'filepath' => string|null, 'error' => string|null ]
 */
function uploadProductImage(
    array $file,
    string $subDir = 'products/',
    int $maxSizeBytes = 2097152, // 2MB
    array $allowedMime = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp']
): array {

    if (!defined('UPLOAD_BASE_PATH')) {
        return [
            'success' => false,
            'filepath' => null,
            'error' => 'UPLOAD_BASE_PATH is not defined — make sure includes/db.php has been required before calling this function.',
        ];
    }

    // 1. Check if file was provided and uploaded without basic PHP errors
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => true, 'filepath' => null, 'error' => null]; // No file uploaded is valid if optional
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'filepath' => null, 'error' => 'File upload error code: ' . $file['error']];
    }

    // 2. Validate file size
    if ($file['size'] > $maxSizeBytes) {
        $maxMb = $maxSizeBytes / (1024 * 1024);
        return ['success' => false, 'filepath' => null, 'error' => "File size exceeds the maximum limit of {$maxMb}MB."];
    }

    // 3. Validate MIME type using finfo (more secure than relying on $_FILES['type'])
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!array_key_exists($mimeType, $allowedMime)) {
        return ['success' => false, 'filepath' => null, 'error' => 'Invalid file format. Only JPG, PNG, and WEBP images are allowed.'];
    }

    // 4. Ensure target directory exists — always anchored to the fixed
    //    project-wide uploads base, never to this file's own location.
    $subDir = trim($subDir, '/') . '/';
    $absoluteTargetDir = UPLOAD_BASE_PATH . $subDir;

    if (!is_dir($absoluteTargetDir)) {
        if (!mkdir($absoluteTargetDir, 0755, true)) {
            return ['success' => false, 'filepath' => null, 'error' => 'Failed to create upload directory on server.'];
        }
    }

    // 5. Generate unique filename (prevent overwriting)
    $extension = $allowedMime[$mimeType];
    $filename = 'prod_' . bin2hex(random_bytes(8)) . '_' . time() . '.' . $extension;
    $destinationPath = $absoluteTargetDir . $filename;

    // Relative path for storing in the DB / building <img src="">,
    // relative to your web-accessible "public/" root.
    $relativePath = '../public' . $subDir . $filename;

    // 6. Move file to target directory
    if (!move_uploaded_file($file['tmp_name'], $destinationPath)) {
        return ['success' => false, 'filepath' => null, 'error' => 'Failed to save uploaded file to destination.'];
    }

    return [
        'success' => true,
        'filepath' => $relativePath,
        'error' => null
    ];
}  

function uploadInvoiceImage(
    array $file,
    string $subDir = 'invoices/',
    int $maxSizeBytes = 2097152,
    array $allowedMime = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ]
): array {

    if (!defined('UPLOAD_BASE_PATH')) {
        return [
            'success' => false,
            'filepath' => null,
            'error' => 'UPLOAD_BASE_PATH is not defined.'
        ];
    }

    if (
        !isset($file['error']) ||
        $file['error'] === UPLOAD_ERR_NO_FILE
    ) {
        return [
            'success' => true,
            'filepath' => null,
            'error' => null
        ];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'filepath' => null,
            'error' => 'File upload error.'
        ];
    }

    if ($file['size'] > $maxSizeBytes) {
        return [
            'success' => false,
            'filepath' => null,
            'error' => 'Invoice image must be 2MB or less.'
        ];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!array_key_exists($mimeType, $allowedMime)) {
        return [
            'success' => false,
            'filepath' => null,
            'error' => 'Only JPG, PNG and WEBP images are allowed.'
        ];
    }

    $subDir = trim($subDir, '/') . '/';

    $absoluteTargetDir =
        UPLOAD_BASE_PATH . $subDir;

    if (!is_dir($absoluteTargetDir)) {

        if (!mkdir($absoluteTargetDir, 0755, true)) {
            return [
                'success' => false,
                'filepath' => null,
                'error' => 'Could not create invoice upload folder.'
            ];
        }
    }

    $extension = $allowedMime[$mimeType];

    $filename =
        'invoice_' .
        bin2hex(random_bytes(8)) .
        '_' .
        time() .
        '.' .
        $extension;

    $destinationPath =
        $absoluteTargetDir . $filename;

    if (!move_uploaded_file(
        $file['tmp_name'],
        $destinationPath
    )) {
        return [
            'success' => false,
            'filepath' => null,
            'error' => 'Failed to save invoice image.'
        ];
    }

    return [
        'success' => true,
        'filepath' => $subDir . $filename,
        'error' => null
    ];
}   