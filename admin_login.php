<?php
/**
 * ICTWEB513 - Admin Login Page
 * Student: [Your Name]
 * Student ID: [Your Student ID]
 * Date: 2024
 */
session_start();

// Define admin credentials - only in code, not displayed on page
$admin_username = 'admin1'; 
$admin_password = 'admin345';

// Handle admin login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // Validate admin credentials
    if ($username === $admin_username && $password === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: admin_products.php');
        exit;
    } else {
        $error = "Invalid username or password!";
    }
}

// If already logged in, redirect to admin page
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin_products.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Timeless Tokens Jewelry</title>
    <style>
        :root {
            --primary-gold: #d4af37;
            --secondary-gold: #b8941f;
            --dark-text: #2c2c2c;
            --light-text: #666;
            --background: #fefefe;
            --border: #e8e8e8;
            --white: #ffffff;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            line-height: 1.6;
            color: var(--dark-text);
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .header {
            background: linear-gradient(135deg, #1a1a1a 0%, #2c2c2c 100%);
            color: var(--white);
            padding: 1rem 0;
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-gold);
            text-decoration: none;
            letter-spacing: 1px;
        }
        
        .login-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .login-card {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            padding: 3rem;
            width: 100%;
            max-width: 400px;
            border: 1px solid var(--border);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-icon {
            font-size: 3rem;
            color: var(--primary-gold);
            margin-bottom: 1rem;
        }
        
        .login-header h1 {
            font-size: 2rem;
            color: var(--dark-text);
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            color: var(--light-text);
        }
        
        .security-notice {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 0.75rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark-text);
        }
        
        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-gold);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
        }
        
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, var(--primary-gold) 0%, var(--secondary-gold) 100%);
            color: var(--dark-text);
            border: none;
            padding: 0.85rem;
            font-size: 1.1rem;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(212, 175, 55, 0.3);
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            text-align: center;
            border: 1px solid #f5c6cb;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: var(--light-text);
            text-decoration: none;
        }
        
        .back-link:hover {
            color: var(--primary-gold);
        }
        
        .footer {
            background-color: #1a1a1a;
            color: var(--white);
            padding: 2rem 0;
            margin-top: auto;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            text-align: center;
        }
        
        @media (max-width: 480px) {
            .login-card {
                padding: 2rem;
            }
            
            .login-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
    <?php require_once 'theme_config.php'; ?>
<?php include 'theme_styles.php'; ?>
</head>
<body>
    <!-- Header Navigation -->
    <header class="header">
        <nav class="nav-container">
            <a href="index.php" class="logo">
                <img src="photo/2.jpg" alt="Timeless Tokens" style="height: 40px; vertical-align: middle;">
                <span style="vertical-align: middle;">Timeless Tokens Jewelry</span>
            </a>
        </nav>
    </header>

    <!-- Login Form -->
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-icon">🔐</div>
                <h1>Admin Login</h1>
                <p>Access the administration panel</p>
            </div>
            
            <div class="security-notice">
                For authorized personnel only. Unauthorized access is prohibited.
            </div>
            
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required 
                           placeholder="Enter admin username">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Enter admin password">
                </div>
                
                <button type="submit" name="login" class="btn-login">Login</button>
            </form>
            
            <a href="index.php" class="back-link">← Back to Home</a>
        </div>
                <body class="<?php echo $current_theme === 'dark' ? 'dark-mode' : ''; ?>">
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <p>&copy; 2025 Timeless Tokens Jewelry | Created by Mariel</p>
            <p style="font-size: 0.9rem; margin-top: 0.5rem; color: #ccc;">
                This area is restricted to authorized administrators only. 
                All login attempts are logged and monitored for security purposes.
            </p>
        </div>
    </footer>
        <?php include 'theme_toggle.php'; ?>
</body>
</html>