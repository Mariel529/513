<?php
/**
 * Login Page - Timeless Tokens Jewelry
 * Dual login system: Subscriber Login and Admin Login
 */
session_start();

// Define admin credentials
$admin_username = 'admin1'; 
$admin_password = 'admin345';

// Check if user is already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: index.php');
    exit;
}

// Check if admin is already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin_products.php');
    exit;
}

$host = 'localhost';
$db   = '47_99_104_82';
$user = '47_99_104_82';  
$pass = 'bXbwMzyJbk';      

// Establish PDO Connection
try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    exit('Database connection failed: ' . $e->getMessage());
}

/* ---------- Handle Login ---------- */
$errors = [];
$email = '';
$phone = '';
$admin_username_input = '';
$admin_password_input = '';
$login_type = 'subscriber'; // Default login type

// Determine login type
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_type = $_POST['login_type'] ?? 'subscriber';
    
    /* ---------- Subscriber Login ---------- */
    if ($login_type === 'subscriber' && isset($_POST['subscriber_login'])) {
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        // Basic validation
        if ($email === '') {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format.';
        }

        if ($phone === '') {
            $errors[] = 'Phone number is required.';
        }

        // Query database if no errors
        if (empty($errors)) {
            try {
                $sql = "SELECT first_name, last_name, email, phone
                        FROM wp_fc_subscribers
                        WHERE email = :email
                          AND phone = :phone
                        LIMIT 1";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':email' => $email,
                    ':phone' => $phone
                ]);
                $subscriber = $stmt->fetch();
            } catch (PDOException $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }

            if ($subscriber) {
                // Login successful -> set session and redirect
                $_SESSION['logged_in']  = true;
                $_SESSION['user_email'] = $subscriber['email'];
                $_SESSION['user_name']  = $subscriber['first_name'] . ' ' . $subscriber['last_name'];
                $_SESSION['user_phone'] = $subscriber['phone'];

                // Redirect to home page
                header('Location: index.php');
                exit;
            } else {
                $errors[] = 'Invalid email or phone number. Please check your credentials.';
            }
        }
    }
    
    /* ---------- Admin Login ---------- */
    if ($login_type === 'admin' && isset($_POST['admin_login'])) {
        $admin_username_input = trim($_POST['admin_username'] ?? '');
        $admin_password_input = trim($_POST['admin_password'] ?? '');
        
        // Validate admin credentials using variables
        if ($admin_username_input === $admin_username && $admin_password_input === $admin_password) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $admin_username_input;
            header('Location: admin_products.php');
            exit;
        } else {
            $errors[] = "Invalid admin username or password!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Timeless Tokens Jewelry</title>
    <style>
        /* Design system based on WordPress website */
        :root {
            --primary-gold: #d4af37;
            --secondary-gold: #b8941f;
            --dark-text: #2c2c2c;
            --light-text: #666;
            --background: #fefefe;
            --border: #e8e8e8;
            --white: #ffffff;
            --admin-blue: #2c5282;
            --admin-light-blue: #4299e1;
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
            background-color: var(--background);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Header Styles */
               .header {
            background: linear-gradient(135deg, #1a1a1a 0%, #2c2c2c 100%);
            color: var(--white);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
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
        
        .nav-menu {
            display: flex;
            list-style: none;
            gap: 1.5rem;
            flex-wrap: wrap;
        }
        
        .nav-menu a {
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            font-size: 0.9rem;
            padding: 0.3rem 0.5rem;
            border-radius: 4px;
        }
        
        .nav-menu a:hover {
            color: var(--primary-gold);
            background: rgba(255, 255, 255, 0.1);
        }
        
        .nav-menu a.active {
            color: var(--primary-gold);
            background: rgba(212, 175, 55, 0.2);
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=1200');
            background-size: cover;
            background-position: center;
            color: var(--white);
            text-align: center;
            padding: 4rem 2rem;
            margin-bottom: 2rem;
        }
        
        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary-gold);
        }
        
        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Main Content */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 3rem;
            flex: 1;
        }
        
        .page-title {
            text-align: center;
            margin-bottom: 2rem;
            color: var(--dark-text);
            font-size: 2.2rem;
            position: relative;
        }
        
        .page-title:after {
            content: '';
            display: block;
            width: 80px;
            height: 3px;
            background: var(--primary-gold);
            margin: 1rem auto;
        }
        
        /* Login Tabs */
        .login-tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
            border-bottom: 2px solid var(--border);
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .tab-btn {
            padding: 1rem 2rem;
            background: none;
            border: none;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            color: var(--light-text);
            transition: all 0.3s ease;
            position: relative;
            border-bottom: 3px solid transparent;
        }
        
        .tab-btn:hover {
            color: var(--dark-text);
        }
        
        .tab-btn.active {
            color: var(--dark-text);
            border-bottom: 3px solid var(--primary-gold);
        }
        
        .tab-btn.admin-tab.active {
            border-bottom: 3px solid var(--admin-blue);
        }
        
        /* Login Sections */
        .login-sections {
            max-width: 500px;
            margin: 0 auto;
        }
        
        .login-section {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            border: 1px solid var(--border);
            padding: 2.5rem;
            margin-bottom: 2rem;
            display: none;
        }
        
        .login-section.active {
            display: block;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header h2 {
            font-size: 1.8rem;
            color: var(--dark-text);
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            color: var(--light-text);
            font-size: 1.1rem;
        }
        
        .login-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .subscriber-icon {
            color: var(--primary-gold);
        }
        
        .admin-icon {
            color: var(--admin-blue);
        }
        
        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark-text);
            font-size: 1rem;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.3s, box-shadow 0.3s;
            background: #fafafa;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-gold);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.15);
            background: var(--white);
        }
        
        .admin-form input:focus {
            border-color: var(--admin-blue);
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.15);
        }
        
        .btn {
            width: 100%;
            padding: 14px 25px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            font-family: inherit;
            margin-top: 1rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-gold) 0%, var(--secondary-gold) 100%);
            color: var(--dark-text);
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--secondary-gold) 0%, var(--primary-gold) 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);
        }
        
        .btn-admin {
            background: linear-gradient(135deg, var(--admin-blue) 0%, var(--admin-light-blue) 100%);
            color: var(--white);
        }
        
        .btn-admin:hover {
            background: linear-gradient(135deg, var(--admin-light-blue) 0%, var(--admin-blue) 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(66, 153, 225, 0.3);
        }
        
        /* Status Messages */
        .status-message {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 6px;
            text-align: center;
            font-weight: 500;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Login Footer */
        .login-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
            color: var(--light-text);
            font-size: 0.95rem;
        }
        
        .login-footer a {
            color: var(--primary-gold);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
            color: var(--secondary-gold);
        }
        
        .admin-footer a {
            color: var(--admin-blue);
        }
        
        .back-link {
            display: inline-block;
            margin-top: 1rem;
            color: var(--dark-text);
            font-weight: 500;
        }
        
        .info-tip {
            margin-top: 1.5rem;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 6px;
            font-size: 0.9rem;
            color: var(--light-text);
            text-align: center;
            border: 1px solid var(--border);
            line-height: 1.5;
        }
        
        .admin-tip {
            background: #e6f7ff;
            border: 1px solid #bae7ff;
        }
        
        /* Footer */
        .footer-bottom {
            text-align: center;
            padding: 2rem;
            background-color: #1a1a1a;
            color: var(--white);
            margin-top: auto;
        }
        
        .footer-bottom p {
            margin-bottom: 0.5rem;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-menu {
                gap: 1rem;
                flex-wrap: wrap;
            }
            
            .hero h1 {
                font-size: 2rem;
            }
            
            .page-title {
                font-size: 1.8rem;
            }
            
            .login-section {
                padding: 1.5rem;
                margin: 0 1rem;
            }
            
            .tab-btn {
                padding: 0.75rem 1.5rem;
            }
        }
        
        @media (max-width: 480px) {
            .nav-container {
                flex-direction: column;
                gap: 1rem;
            }
            
            .nav-menu {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
                width: 100%;
            }
            
            .nav-menu li {
                width: 100%;
            }
            
            .nav-menu a {
                display: block;
                padding: 10px;
                border-bottom: 1px solid #333;
            }
            
            .hero {
                padding: 3rem 1rem;
            }
            
            .hero h1 {
                font-size: 1.8rem;
            }
            
            .login-section {
                padding: 1.5rem;
            }
            
            .login-tabs {
                flex-direction: column;
            }
            
            .tab-btn {
                width: 100%;
                text-align: center;
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
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="order_history.php">Order History</a></li>
                <li><a href="careers.php">Apply for Job</a></li>
                <li><a href="forum.php">Forum</a></li>
                <li><a href="http://47.99.104.82/feedback/">Feedback</a></li>
                <li><a href="login.php" class="active">Login</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <h1>Login to Your Account</h1>
        <p>Access your Timeless Tokens Jewelry account or admin panel</p>
    </section>

    <!-- Main Content -->
    <main class="container">
        <h1 class="page-title">Dual Login Portal</h1>
        
        <!-- Login Tabs -->
        <div class="login-tabs">
            <button type="button" class="tab-btn <?php echo $login_type === 'subscriber' ? 'active' : ''; ?>" 
                    data-tab="subscriber-login">
                🔐 Subscriber Login
            </button>
            <button type="button" class="tab-btn admin-tab <?php echo $login_type === 'admin' ? 'active' : ''; ?>" 
                    data-tab="admin-login">
                ⚙️ Admin Login
            </button>
        </div>
        
        <!-- Login Sections -->
        <div class="login-sections">
            <!-- Subscriber Login Section -->
            <div class="login-section <?php echo $login_type === 'subscriber' ? 'active' : ''; ?>" id="subscriber-login">
                <div class="login-header">
                    <div class="login-icon subscriber-icon">👤</div>
                    <h2>Subscriber Login</h2>
                    <p>Sign in to access your Timeless Tokens account</p>
                </div>
                
                <?php if (!empty($errors) && $login_type === 'subscriber'): ?>
                    <div class="status-message error">
                        <?php foreach ($errors as $error): ?>
                            <p>❌ <?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="" id="subscriber-form">
                    <input type="hidden" name="login_type" value="subscriber">
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required
                               value="<?php echo htmlspecialchars($email); ?>"
                               placeholder="Enter your registered email address">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="text" id="phone" name="phone" required
                               value="<?php echo htmlspecialchars($phone); ?>"
                               placeholder="Enter your registered phone number">
                    </div>
                    
                    <button type="submit" name="subscriber_login" class="btn btn-primary">Subscriber Sign In</button>
                </form>
                
                <div class="login-footer">
                    <p>Not a subscriber yet? <a href="contact.php">Contact us</a> to join our exclusive community.</p>
                    <p><a href="index.php" class="back-link">← Return to Homepage</a></p>
                </div>
                
                <div class="info-tip">
                    ℹ️ Please use the email and phone number you provided during subscription. 
                    If you have any issues, please <a href="contact.php">contact our support team</a>.
                </div>
            </div>
            
            <!-- Admin Login Section -->
            <div class="login-section <?php echo $login_type === 'admin' ? 'active' : ''; ?>" id="admin-login">
                <div class="login-header">
                    <div class="login-icon admin-icon">⚙️</div>
                    <h2>Admin Login</h2>
                    <p>Access the administration panel</p>
                </div>
                
                <?php if (!empty($errors) && $login_type === 'admin'): ?>
                    <div class="status-message error">
                        <?php foreach ($errors as $error): ?>
                            <p>❌ <?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="" id="admin-form" class="admin-form">
                    <input type="hidden" name="login_type" value="admin">
                    <div class="form-group">
                        <label for="admin_username">Username</label>
                        <input type="text" id="admin_username" name="admin_username" required
                               value="<?php echo htmlspecialchars($admin_username_input); ?>"
                               placeholder="Enter admin username">
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_password">Password</label>
                        <input type="password" id="admin_password" name="admin_password" required
                               placeholder="Enter admin password">
                    </div>
                    
                    <button type="submit" name="admin_login" class="btn btn-admin">Admin Login</button>
                </form>
                
                <div class="login-footer admin-footer">
                    <p>For authorized personnel only. Unauthorized access is prohibited.</p>
                    <p><a href="admin_login.php" target="_blank">Direct Admin Login Page</a> | 
                       <a href="index.php" class="back-link">← Return to Homepage</a></p>
                </div>
                
                <div class="info-tip admin-tip">
                    ⚠️ This area is restricted to authorized administrators only. 
                    All login attempts are logged and monitored for security purposes.
                </div>
            </div>
        </div>
                <body class="<?php echo $current_theme === 'dark' ? 'dark-mode' : ''; ?>">
    </main>
    
    <!-- Footer -->
    <footer class="footer-bottom">
        <div>
            <p>&copy; <?php echo date('Y'); ?> Timeless Tokens Jewelry | Created by Mariel</p>
            <p style="font-size: 0.9rem; color: #aaa; margin-top: 0.5rem;">Crafting memories into wearable art</p>
        </div>
    </footer>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab switching functionality
            const tabButtons = document.querySelectorAll('.tab-btn');
            const loginSections = document.querySelectorAll('.login-section');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const targetTab = this.getAttribute('data-tab');
                    
                    // Update active tab button
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Update active login section
                    loginSections.forEach(section => {
                        section.classList.remove('active');
                        if (section.id === targetTab) {
                            section.classList.add('active');
                        }
                    });
                    
                    // Focus on first input of active form
                    setTimeout(() => {
                        const activeForm = document.querySelector(`#${targetTab} form`);
                        if (activeForm) {
                            const firstInput = activeForm.querySelector('input[type="text"], input[type="email"]');
                            if (firstInput) {
                                firstInput.focus();
                            }
                        }
                    }, 100);
                });
            });
            
            // Form validations
            const subscriberForm = document.getElementById('subscriber-form');
            const adminForm = document.getElementById('admin-form');
            
            if (subscriberForm) {
                subscriberForm.addEventListener('submit', function(e) {
                    const email = document.getElementById('email').value.trim();
                    const phone = document.getElementById('phone').value.trim();
                    
                    if (!email || !phone) {
                        e.preventDefault();
                        alert('Please fill in all required fields for subscriber login.');
                        return false;
                    }
                    
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(email)) {
                        e.preventDefault();
                        alert('Please enter a valid email address (e.g., name@example.com).');
                        return false;
                    }
                    
                    return true;
                });
            }
            
            if (adminForm) {
                adminForm.addEventListener('submit', function(e) {
                    const username = document.getElementById('admin_username').value.trim();
                    const password = document.getElementById('admin_password').value.trim();
                    
                    if (!username || !password) {
                        e.preventDefault();
                        alert('Please enter both username and password for admin login.');
                        return false;
                    }
                    
                    return true;
                });
            }
            
            // Auto-focus based on active tab
            const activeTab = document.querySelector('.login-section.active');
            if (activeTab) {
                setTimeout(function() {
                    const firstInput = activeTab.querySelector('input[type="text"], input[type="email"]');
                    if (firstInput) {
                        firstInput.focus();
                    }
                }, 300);
            }
            
            // Preserve tab state on form submission with errors
            const urlParams = new URLSearchParams(window.location.search);
            const loginType = urlParams.get('login_type');
            if (loginType === 'admin') {
                const adminTab = document.querySelector('.tab-btn[data-tab="admin-login"]');
                if (adminTab) {
                    adminTab.click();
                }
            }
        });
    </script>
        <?php include 'theme_toggle.php'; ?>
</body>
</html>
