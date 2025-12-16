<?php
/**
 * Home Page - Timeless Tokens Jewelry
 * Shows personalized welcome message after login
 */
session_start();

// Check if user is logged in
$logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$admin_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Get user info from session
$user_name = $_SESSION['user_name'] ?? 'Guest';
$user_email = $_SESSION['user_email'] ?? '';
$user_phone = $_SESSION['user_phone'] ?? '';

// Get admin info
$admin_username = $_SESSION['admin_username'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timeless Tokens Jewelry - Home</title>
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
        
        /* Welcome Banner Styles */
        .welcome-banner {
            background: linear-gradient(135deg, #1a1a1a 0%, #2c2c2c 100%);
            color: white;
            padding: 2rem;
            margin-bottom: 2rem;
            border-radius: 8px;
            position: relative;
            overflow: hidden;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .welcome-banner:before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, var(--primary-gold) 0%, transparent 70%);
            opacity: 0.1;
        }
        
        .welcome-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .welcome-header h1 {
            font-size: 2rem;
            color: var(--primary-gold);
            margin: 0;
        }
        
        .welcome-header.admin h1 {
            color: var(--admin-light-blue);
        }
        
        .user-avatar {
            width: 60px;
            height: 60px;
            background: var(--primary-gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--dark-text);
            font-weight: bold;
            border: 3px solid var(--secondary-gold);
        }
        
        .user-avatar.admin {
            background: var(--admin-blue);
            color: white;
            border-color: var(--admin-light-blue);
        }
        
        .user-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .info-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 1rem;
            border-radius: 6px;
            border-left: 4px solid var(--primary-gold);
        }
        
        .info-card.admin {
            border-left: 4px solid var(--admin-blue);
        }
        
        .info-card h3 {
            margin: 0 0 0.3rem 0;
            font-size: 0.9rem;
            color: #ccc;
        }
        
        .info-card p {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 500;
        }
        
        .quick-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }
        
        .action-btn {
            padding: 0.5rem 1.5rem;
            background: var(--primary-gold);
            color: var(--dark-text);
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            font-size: 0.9rem;
        }
        
        .action-btn:hover {
            background: var(--secondary-gold);
            transform: translateY(-2px);
        }
        
        .action-btn.admin {
            background: var(--admin-blue);
            color: white;
        }
        
        .action-btn.admin:hover {
            background: var(--admin-light-blue);
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=1200');
            background-size: cover;
            background-position: center;
            color: var(--white);
            text-align: center;
            padding: 6rem 2rem;
        }
        
        .hero h1 {
            font-size: 3rem;
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
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-gold);
        }
        
        /* Main Content */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .collections-section {
            padding: 4rem 0;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 3rem;
            color: var(--dark-text);
            font-size: 2.5rem;
            position: relative;
        }
        
        .section-title:after {
            content: '';
            display: block;
            width: 80px;
            height: 3px;
            background: var(--primary-gold);
            margin: 1rem auto;
        }
        
        .collections-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }
        
        .collection-card {
            background: var(--white);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .collection-card:hover {
            transform: translateY(-5px);
        }
        
        .collection-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        
        .collection-content {
            padding: 1.5rem;
            text-align: center;
        }
        
        .collection-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--dark-text);
        }
        
        .collection-description {
            color: var(--light-text);
            line-height: 1.6;
        }
        
        /* Features Section */
        .features {
            background-color: #f8f8f8;
            padding: 4rem 0;
            margin: 4rem 0;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .feature-item {
            text-align: center;
            padding: 2rem;
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary-gold);
            margin-bottom: 1rem;
        }
        
        .feature-title {
            font-size: 1.3rem;
            margin-bottom: 1rem;
            color: var(--dark-text);
        }
        
        .feature-description {
            color: var(--light-text);
            line-height: 1.6;
        }
        
        /* Testimonials Section */
        .testimonials {
            padding: 4rem 0;
        }
        
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .testimonial-card {
            background: var(--white);
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-left: 4px solid var(--primary-gold);
        }
        
        .testimonial-text {
            font-style: italic;
            margin-bottom: 1.5rem;
            color: var(--light-text);
            line-height: 1.6;
        }
        
        .testimonial-author {
            font-weight: bold;
            color: var(--dark-text);
        }
        
        .testimonial-location {
            color: var(--light-text);
            font-size: 0.9rem;
        }
        
        /* Footer */
        .footer-bottom {
            text-align: center;
            padding: 2rem;
            background-color: #1a1a1a;
            color: var(--white);
            margin-top: 4rem;
        }
        
        .footer-bottom p {
            margin-bottom: 0.5rem;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-menu {
                gap: 1rem;
            }
            
            .hero h1 {
                font-size: 2.2rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .collections-grid,
            .testimonials-grid {
                grid-template-columns: 1fr;
            }
            
            .welcome-header {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }
            
            .welcome-header h1 {
                font-size: 1.8rem;
            }
            
            .user-info-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-actions {
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .nav-container {
                flex-direction: column;
                gap: 1rem;
            }
            
            .hero {
                padding: 4rem 1rem;
            }
            
            .hero h1 {
                font-size: 1.8rem;
            }
            
            .welcome-banner {
                padding: 1.5rem;
                margin: 1rem;
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
                <li><a href="http://47.99.104.82/forum/">Forum</a></li>
                <li><a href="http://47.99.104.82/feedback/">feedback</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <?php if ($logged_in || $admin_logged_in): ?>
    <!-- Welcome Banner for Logged-in Users -->
    <div class="container">
        <div class="welcome-banner <?php echo $admin_logged_in ? 'admin-banner' : ''; ?>">
            <div class="welcome-header <?php echo $admin_logged_in ? 'admin' : ''; ?>">
                <h1>
                    <?php if ($admin_logged_in): ?>
                        ⚙️ Welcome, Admin <?php echo htmlspecialchars($admin_username); ?>!
                    <?php else: ?>
                        👋 Welcome back, <?php echo htmlspecialchars($user_name); ?>!
                    <?php endif; ?>
                </h1>
                <div class="user-avatar <?php echo $admin_logged_in ? 'admin' : ''; ?>">
                    <?php echo $admin_logged_in ? 'A' : substr($user_name, 0, 1); ?>
                </div>
            </div>
            
            <?php if ($admin_logged_in): ?>
                <p>You have access to the admin dashboard. Manage products, orders, and more.</p>
            <?php else: ?>
                <p>Thank you for being a valued member of Timeless Tokens Jewelry!</p>
            <?php endif; ?>
            
            <div class="user-info-grid">
                <?php if ($admin_logged_in): ?>
                    <div class="info-card admin">
                        <h3>Admin Role</h3>
                        <p>Administrator</p>
                    </div>
                    <div class="info-card admin">
                        <h3>Access Level</h3>
                        <p>Full Access</p>
                    </div>
                    <div class="info-card admin">
                        <h3>Session Active</h3>
                        <p><?php echo date('Y-m-d H:i:s'); ?></p>
                    </div>
                <?php else: ?>
                    <div class="info-card">
                        <h3>Email</h3>
                        <p><?php echo htmlspecialchars($user_email); ?></p>
                    </div>
                    <div class="info-card">
                        <h3>Phone</h3>
                        <p><?php echo htmlspecialchars($user_phone); ?></p>
                    </div>
                    <div class="info-card">
                        <h3>Member Since</h3>
                        <p><?php echo date('Y-m-d'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="quick-actions">
                <?php if ($admin_logged_in): ?>
                    <a href="admin_products.php" class="action-btn admin">
                        📦 Manage Products
                    </a>
                    <a href="admin_orders.php" class="action-btn admin">
                        📋 View Orders
                    </a>
                <?php else: ?>
                    <a href="order_history.php" class="action-btn">
                        📜 View Order History
                    </a>
                    <a href="products.php" class="action-btn">
                        🛍️ Shop Now
                    </a>
                    <a href="cart.php" class="action-btn">
                        🛒 View Cart
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Hero Section -->
    <section class="hero">
        <h1>Timeless Tokens Jewelry</h1>
        <p>Discover premium lockets, necklaces, and bracelets that tell your story</p>
        <a href="products.php" class="btn-primary">Shop Now</a>
    </section>

    <!-- Main Content -->
    <main class="container">
        <!-- Collections Section -->
        <section class="collections-section">
            <h2 class="section-title">Our Collections</h2>
            <div class="collections-grid">
                <div class="collection-card">
                    <img src="https://images.unsplash.com/photo-1602173574767-37ac01994b2a?w=400" alt="Elegant Lockets" class="collection-image">
                    <div class="collection-content">
                        <h3 class="collection-title">Elegant Lockets</h3>
                        <p class="collection-description">Preserve your precious memories with our handcrafted lockets, perfect for keeping loved ones close to your heart.</p>
                    </div>
                </div>
                <div class="collection-card">
                    <img src="https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=400" alt="Luxury Necklaces" class="collection-image">
                    <div class="collection-content">
                        <h3 class="collection-title">Luxury Necklaces</h3>
                        <p class="collection-description">Make a statement with our exquisite necklace collection, featuring timeless designs for every occasion.</p>
                    </div>
                </div>
                <div class="collection-card">
                    <img src="photo/Charming Bracelets.jpg" alt="Charming Bracelets" class="collection-image">
                    <div class="collection-content">
                        <h3 class="collection-title">Charming Bracelets</h3>
                        <p class="collection-description">Adorn your wrists with our beautiful bracelet selection, combining elegance with meaningful symbolism.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features">
            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon">💎</div>
                    <h3 class="feature-title">Premium Quality</h3>
                    <p class="feature-description">Each piece is crafted with the finest materials and attention to detail</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🚚</div>
                    <h3 class="feature-title">Free Shipping</h3>
                    <p class="feature-description">Enjoy complimentary shipping on all orders over $50</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">💬</div>
                    <h3 class="feature-title">24/7 Support</h3>
                    <p class="feature-description">Our customer service team is always here to help you</p>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="testimonials">
            <h2 class="section-title">What Our Customers Say</h2>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <p class="testimonial-text">"The locket I purchased is absolutely stunning! The craftsmanship is exceptional and it holds my favorite photo perfectly. I wear it every day!"</p>
                    <div class="testimonial-author">Sarah M.</div>
                    <div class="testimonial-location">New York</div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-text">"Amazing quality and fast shipping! My necklace arrived beautifully packaged and exceeded all my expectations. Will definitely shop here again!"</p>
                    <div class="testimonial-author">James L.</div>
                    <div class="testimonial-location">California</div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-text">"The bracelet I bought for my wife's anniversary was perfect. She hasn't taken it off since! Wonderful customer service too."</p>
                    <div class="testimonial-author">Michael T.</div>
                    <div class="testimonial-location">Texas</div>
                </div>
            </div>
        </section>
        <body class="<?php echo $current_theme === 'dark' ? 'dark-mode' : ''; ?>">
    </main>

    <!-- Footer -->
    <footer class="footer-bottom">
        <div>
            <p>&copy; <?php echo date('Y'); ?> Timeless Tokens Jewelry | Created by Mariel</p>
            <p style="font-size: 0.9rem; color: #aaa; margin-top: 0.5rem;">Crafting memories into wearable art</p>
        </div>
    </footer>
    <?php include 'theme_toggle.php'; ?>
</body>
</html>