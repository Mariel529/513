<?php
/**
 * Theme Configuration File
 * Stores theme preferences and handles theme switching
 */

// Start output buffering to prevent headers already sent error
ob_start();

session_start();

// Initialize theme settings
if (!isset($_SESSION['theme'])) {
    $_SESSION['theme'] = 'light'; // Default to light mode
}

// Handle theme toggle request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_theme'])) {
    $_SESSION['theme'] = ($_SESSION['theme'] === 'dark') ? 'light' : 'dark';
    
    // Set cookie for JavaScript synchronization
    setcookie('theme_preference', $_SESSION['theme'], time() + (86400 * 30), "/");
    
    // Return JSON response for AJAX requests
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['theme' => $_SESSION['theme']]);
        exit;
    }
    
    // Redirect back for regular requests
    $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    header("Location: $referer");
    exit;
}

// Get current theme
$current_theme = $_SESSION['theme'] ?? 'light';
?>