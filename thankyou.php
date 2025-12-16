<?php
/**
 * ICTWEB513 - Thank You Page
 * Student: [Your Name]
 * Student ID: [Your Student ID]
 * Date: 2024
 */
session_start();


if (!isset($_SESSION['order_id'])) {
    header('Location: products.php');
    exit;
}

$order_id = $_SESSION['order_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You - Timeless Tokens Jewelry</title>
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
            background-color: var(--background);
        }
        
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
            gap: 2rem;
        }
        
        .nav-menu a {
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            font-size: 1rem;
        }
        
        .nav-menu a:hover {
            color: var(--primary-gold);
        }
        
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
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px 3rem;
        }
        
        .thankyou-card {
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            border: 1px solid var(--border);
            padding: 3rem;
            text-align: center;
        }
        
        .success-icon {
            font-size: 4rem;
            color: #27ae60;
            margin-bottom: 1rem;
        }
        
        .order-number {
            background: var(--primary-gold);
            color: var(--dark-text);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin: 1rem 0;
        }
        
        .btn-primary {
            background-color: var(--primary-gold);
            color: var(--dark-text);
            padding: 0.8rem 2rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
            margin-top: 1rem;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-gold);
        }
        
        .footer {
            background-color: #1a1a1a;
            color: var(--white);
            padding: 3rem 0 2rem;
            margin-top: 4rem;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .footer-section h3 {
            color: var(--primary-gold);
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid #333;
            max-width: 1200px;
            margin: 0 auto;
            padding-left: 20px;
            padding-right: 20px;
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
                <li><a href="http://47.99.104.82/forum/">Forum</a></li>
                <li><a href="http://47.99.104.82/feedback/">feedback</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <h1>Order Confirmation</h1>
        <p>Thank you for your purchase!</p>
    </section>

    <!-- Main Content -->
    <main class="container">
        <div class="thankyou-card">
            <div class="success-icon">✅</div>
            <h2>Thank You for Your Order!</h2>
            <p>Your order has been successfully placed and is being processed.</p>
            
            <div class="order-number">
                Order #<?php echo $order_id; ?>
            </div>
            
            <p>You will receive a confirmation email shortly with your order details.</p>
            <p>If you have any questions about your order, please contact our customer service team.</p>
            
            <div style="margin-top: 2rem;">
                <a href="products.php" class="btn-primary">Continue Shopping</a>
                <a href="index.php" class="btn-primary" style="background: #95a5a6; margin-left: 1rem;">Back to Home</a>
            </div>
        </div>
                <body class="<?php echo $current_theme === 'dark' ? 'dark-mode' : ''; ?>">
    </main>

    <!-- Footer -->
    <footer class="footer-bottom">
        <div>
            <p>&copy; 2025 MariaTech Solutions | Created by Mariel</p>
        </div>
    </footer>
        <?php include 'theme_toggle.php'; ?>
</body>
</html>
<?php
unset($_SESSION['order_id']);
?>