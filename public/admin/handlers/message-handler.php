<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../../../includes/db.php';

// Helper function to handle redirects back to the messages page
function redirectWithMessage($type, $message) {
    $_SESSION[$type . '_msg'] = $message;
    header("Location: ../customer-messages.php");
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    // ------------------------------------------------------------------
    // ACTION 1: Delete Message
    // ------------------------------------------------------------------
    case 'delete':
        $messageId = intval($_GET['id'] ?? 0);

        if ($messageId <= 0) {
            redirectWithMessage('error', 'Invalid message ID specified.');
        }

        $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->bind_param("i", $messageId);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                redirectWithMessage('success', 'Message deleted successfully.');
            } else {
                redirectWithMessage('error', 'Message not found or already deleted.');
            }
        } else {
            redirectWithMessage('error', 'Database error: Could not delete the message.');
        }
        $stmt->close();
        break;

    // ------------------------------------------------------------------
    // ACTION 2: Update Message Status
    // ------------------------------------------------------------------
    case 'update_status':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectWithMessage('error', 'Invalid request method.');
        }

        $messageId = intval($_POST['message_id'] ?? 0);
        $status = strtolower(trim($_POST['status'] ?? ''));
        $allowedStatuses = ['unread', 'read', 'replied', 'archived'];

        if ($messageId <= 0 || !in_array($status, $allowedStatuses, true)) {
            redirectWithMessage('error', 'Invalid status or message ID provided.');
        }

        $stmt = $conn->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $messageId);

        if ($stmt->execute()) {
            redirectWithMessage('success', 'Message status updated successfully.');
        } else {
            redirectWithMessage('error', 'Failed to update message status.');
        }
        $stmt->close();
        break;

    // ------------------------------------------------------------------
    // DEFAULT FALLBACK
    // ------------------------------------------------------------------
    default:
        redirectWithMessage('error', 'Invalid action requested.');
        break;
}