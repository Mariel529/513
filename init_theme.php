<?php
/**
 * Theme Initialization File
 * Include this at the VERY TOP of every page
 */

// Start output buffering
if (!ob_get_level()) {
    ob_start();
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include theme configuration
require_once 'theme_config.php';
?>