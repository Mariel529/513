<?php
session_start();


if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
   
    header('Location: http://47.99.104.82/week11/login.php?redirect_to=' . urlencode('http://47.99.104.82/forum/'));
    exit;
} else {
    
    header('Location: http://47.99.104.82/forum/');
    exit;
}
?>