<?php
/**
 * ICTWEB513 - E-Commerce Product Display Page
 * Student: [Your Name]
 * Student ID: [Your Student ID]
 * Date: 2024
 */

// Load product data
session_start();
$productsJson = file_get_contents('data/products.json');
$products = json_decode($productsJson, true);

// Group products by category
$categories = [];
foreach ($products as $product) {
    $category = $product['category'];
    if (!isset($categories[$category])) {
        $categories[$category] = [];
    }
    $categories[$category][] = $product;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jewelry Collections - Timeless Tokens Jewelry</title>
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
            gap: 2rem;
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
            margin: 0;
            padding: 0;
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
        
        /* Cart Icon Styles */
        .cart-icon {
            position: relative;
            display: inline-block;
            transition: transform 0.3s;
        }
        
        .cart-icon:hover {
            transform: scale(1.1);
        }
        
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
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
            margin-bottom: 3rem;
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
        
        .page-title {
            text-align: center;
            margin-bottom: 3rem;
            color: var(--dark-text);
            font-size: 2.5rem;
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
        
        /* Category Sections */
        .category-section {
            margin-bottom: 4rem;
        }
        
        .category-title {
            font-size: 2rem;
            color: var(--dark-text);
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
        }
        
        .category-title:after {
            content: '';
            display: block;
            width: 60px;
            height: 2px;
            background: var(--primary-gold);
            margin: 0.5rem auto;
        }
        
        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2.5rem;
        }
        
        .product-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
        }
        
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .product-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: transform 0.3s;
        }
        
        .product-card:hover .product-image {
            transform: scale(1.05);
        }
        
        .product-content {
            padding: 1.5rem;
        }
        
        .product-name {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--dark-text);
            margin-bottom: 0.5rem;
        }
        
        .product-price {
            font-size: 1.4rem;
            font-weight: bold;
            color: var(--primary-gold);
            margin-bottom: 1rem;
        }
        
        .original-price {
            text-decoration: line-through;
            color: var(--light-text);
            font-size: 1rem;
            margin-right: 0.5rem;
        }
        
        .discount-badge {
            background-color: #e74c3c;
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-weight: bold;
        }
        
        .product-info {
            margin-bottom: 0.8rem;
            font-size: 0.9rem;
            color: var(--light-text);
        }
        
        .product-description {
            margin-bottom: 1.2rem;
            line-height: 1.5;
            color: var(--light-text);
        }
        
        .btn-cart {
            background-color: var(--primary-gold);
            color: var(--dark-text);
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
            font-family: inherit;
        }
        
        .btn-cart:hover {
            background-color: var(--secondary-gold);
        }
        
        /* Features Section */
        .features {
            background-color: #f8f8f8;
            padding: 4rem 0;
            margin: 4rem 0;
            text-align: center;
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
        
        /* Footer */
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
        
        .admin-link {
            display: inline-block;
            background-color: var(--primary-gold);
            color: var(--dark-text);
            padding: 0.7rem 1.5rem;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 2rem;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .admin-link:hover {
            background-color: var(--secondary-gold);
        }
        
        /* Currency Styling */
        .currency {
            color: var(--primary-gold);
            font-weight: bold;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-menu {
                gap: 1rem;
                font-size: 0.8rem;
            }
            
            .hero h1 {
                font-size: 2.2rem;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 2rem;
            }
            
            .page-title {
                font-size: 2rem;
            }
            
            .nav-container {
                flex-direction: column;
                gap: 1rem;
            }
        }
        
        @media (max-width: 480px) {
            .hero {
                padding: 4rem 1rem;
            }
            
            .hero h1 {
                font-size: 1.8rem;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
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
            
            <div style="display: flex; align-items: center; gap: 2rem;">
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
                
                <!-- Cart Icon -->
                <a href="cart.php" class="cart-icon" style="position: relative; display: inline-block;">
                    <!-- Cart Icon SVG -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary-gold);">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    
                    <!-- Cart Quantity Badge -->
                    <?php
                    $cart_count = 0;
                    if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                        foreach ($_SESSION['cart'] as $item) {
                            $cart_count += $item['quantity'];
                        }
                    }
                    if ($cart_count > 0): ?>
                        <span class="cart-badge" style="
                            position: absolute;
                            top: -8px;
                            right: -8px;
                            background-color: #e74c3c;
                            color: white;
                            border-radius: 50%;
                            width: 20px;
                            height: 20px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 12px;
                            font-weight: bold;
                        "><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <h1>Timeless Tokens Jewelry</h1>
        <p>Discover premium lockets, necklaces, and bracelets that tell your story</p>
        <a href="#collections" class="btn-primary">Shop Now</a>
    </section>

    <!-- Main Content -->
    <main class="container" id="collections">
        <h1 class="page-title">Our Personalized Jewelry Collection</h1>
        
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
            <div class="product-card">
                <?php if ($product['discount_percent'] > 0): ?>
                    <span class="discount-badge">-<?php echo $product['discount_percent']; ?>%</span>
                <?php endif; ?>
                
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                     class="product-image"
                     onerror="this.src='https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=300'">
                
                <div class="product-content">
                    <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                    
                    <div class="product-price">
                        <?php if (isset($product['original_price']) && $product['original_price'] > $product['price']): ?>
                            <span class="original-price">$<?php echo number_format($product['original_price'], 2); ?></span>
                            <strong>$<?php echo number_format($product['price'], 2); ?></strong>
                        <?php else: ?>
                            <strong>$<?php echo number_format($product['price'], 2); ?></strong>
                        <?php endif; ?>
                    </div>
                    
                    <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                    
                    <form method="POST" action="cart.php" style="margin-top: 1rem;">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn-cart">Add to Cart</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Features Section -->
        <section class="features">
            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon">✈️</div>
                    <h3 class="feature-title">Free Shipping</h3>
                    <p>Free shipping on all orders over A$100</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🔒</div>
                    <h3 class="feature-title">Secure Payment</h3>
                    <p>100% secure and encrypted payment processing</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🔄</div>
                    <h3 class="feature-title">30-Day Returns</h3>
                    <p>Easy returns within 30 days of purchase</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">⭐</div>
                    <h3 class="feature-title">Premium Quality</h3>
                    <p>High-quality materials and craftsmanship</p>
                </div>
            </div>
        </section>
                <body class="<?php echo $current_theme === 'dark' ? 'dark-mode' : ''; ?>">
    </main>
       
    <!-- Footer -->
    <footer class="footer-bottom">
        <div>
            <p>&copy; <?php echo date('Y'); ?> Timeless Tokens Jewelry | Created by Mariel</p>
        </div>
    </footer>
        <?php include 'theme_toggle.php'; ?>
</body>
</html>