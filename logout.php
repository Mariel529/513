<?php
/**
 * ICTWEB513 - Logout Page
 * Student: [Your Name]
 * Student ID: [Your Student ID]
 * Date: 2024
 */

session_start();

// Clear all session variables
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session
session_destroy();

// Set success message
session_start();
$_SESSION['success'] = "You have been successfully logged out.";
session_write_close();

// Redirect to home page
header('Location: index.php');
exit;
?>
