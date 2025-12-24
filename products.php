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
            cursor: pointer;
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
            cursor: pointer;
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
        
        .btn-view-details {
            background-color: #f0f0f0;
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
        
        .btn-view-details:hover {
            background-color: #e0e0e0;
        }
        
        /* Product Actions */
        .product-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .product-actions .btn-cart,
        .product-actions .btn-view-details {
            flex: 1;
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
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
            overflow-y: auto;
            padding: 20px;
        }
        
        .modal-content {
            background-color: var(--white);
            margin: 40px auto;
            padding: 2rem;
            border-radius: 12px;
            max-width: 1000px;
            width: 90%;
            position: relative;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: modalFadeIn 0.3s ease-out;
        }
        
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .close-modal {
            position: absolute;
            top: 1rem;
            right: 1.5rem;
            font-size: 2rem;
            color: var(--light-text);
            cursor: pointer;
            transition: color 0.3s;
            z-index: 10;
            background: none;
            border: none;
            line-height: 1;
        }
        
        .close-modal:hover {
            color: var(--dark-text);
        }
        
        .modal-gallery {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .modal-main-image {
            width: 100%;
            height: 400px;
            object-fit: contain;
            background: #f8f8f8;
            border-radius: 8px;
            padding: 1rem;
        }
        
        .modal-thumbnails {
            display: flex;
            gap: 0.8rem;
            overflow-x: auto;
            padding: 0.5rem 0;
        }
        
        .modal-thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: border-color 0.3s;
        }
        
        .modal-thumbnail:hover,
        .modal-thumbnail.active {
            border-color: var(--primary-gold);
        }
        
        .modal-info {
            padding: 1rem 0;
        }
        
        .modal-title {
            font-size: 2.2rem;
            color: var(--dark-text);
            margin-bottom: 1rem;
            line-height: 1.2;
        }
        
        .modal-price {
            font-size: 2rem;
            color: var(--primary-gold);
            font-weight: bold;
            margin: 1.5rem 0;
        }
        
        .modal-original-price {
            text-decoration: line-through;
            color: var(--light-text);
            font-size: 1.4rem;
            margin-right: 1rem;
        }
        
        .modal-discount {
            background-color: #e74c3c;
            color: white;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 1rem;
            font-weight: bold;
            display: inline-block;
            margin-left: 1rem;
        }
        
        .modal-description {
            font-size: 1.1rem;
            line-height: 1.7;
            color: var(--light-text);
            margin: 1.5rem 0;
        }
        
        .modal-specs {
            margin: 2rem 0;
            padding: 1.5rem;
            background: #f9f9f9;
            border-radius: 8px;
        }
        
        .spec-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--border);
        }
        
        .spec-label {
            font-weight: bold;
            color: var(--dark-text);
        }
        
        .spec-value {
            color: var(--light-text);
        }
        
        .modal-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        
        .btn-quantity {
            display: flex;
            align-items: center;
            border: 1px solid var(--border);
            border-radius: 4px;
            overflow: hidden;
        }
        
        .btn-quantity button {
            background: none;
            border: none;
            padding: 0.8rem 1rem;
            font-size: 1.2rem;
            cursor: pointer;
            color: var(--dark-text);
            transition: background-color 0.3s;
        }
        
        .btn-quantity button:hover {
            background-color: #f0f0f0;
        }
        
        .quantity-input {
            width: 60px;
            text-align: center;
            border: none;
            border-left: 1px solid var(--border);
            border-right: 1px solid var(--border);
            padding: 0.8rem;
            font-size: 1.1rem;
        }
        
        .btn-buy {
            background-color: var(--primary-gold);
            color: var(--dark-text);
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 4px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
            flex: 1;
            min-width: 200px;
        }
        
        .btn-buy:hover {
            background-color: var(--secondary-gold);
        }
        
        /* Responsive Design */
        @media (max-width: 900px) {
            .modal-content-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .modal-main-image {
                height: 300px;
            }
        }
        
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
            
            .modal-content {
                padding: 1.5rem;
                margin: 20px auto;
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
            
            .modal-title {
                font-size: 1.8rem;
            }
            
            .modal-price {
                font-size: 1.6rem;
            }
            
            .modal-actions {
                flex-direction: column;
            }
            
            .btn-buy {
                min-width: 100%;
            }
            
            .product-actions {
                flex-direction: column;
            }
        }
    </style>
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
                    <li><a href="forum.php">Forum</a></li>
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
                        <span class="cart-badge"><?php echo $cart_count; ?></span>
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
                <?php if (isset($product['discount_percent']) && $product['discount_percent'] > 0): ?>
                    <span class="discount-badge">-<?php echo $product['discount_percent']; ?>%</span>
                <?php endif; ?>
                
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                     class="product-image"
                     onclick="openProductModal(<?php echo $product['id']; ?>)"
                     onerror="this.src='https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=300'">
                
                <div class="product-content">
                    <h3 class="product-name" onclick="openProductModal(<?php echo $product['id']; ?>)">
                        <?php echo htmlspecialchars($product['name']); ?>
                    </h3>
                    
                    <div class="product-price">
                        <?php if (isset($product['original_price']) && $product['original_price'] > $product['price']): ?>
                            <span class="original-price">$<?php echo number_format($product['original_price'], 2); ?></span>
                            <strong>$<?php echo number_format($product['price'], 2); ?></strong>
                        <?php else: ?>
                            <strong>$<?php echo number_format($product['price'], 2); ?></strong>
                        <?php endif; ?>
                    </div>
                    
                    <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                    
                    <div class="product-actions">
                        <button type="button" 
                                class="btn-view-details"
                                onclick="openProductModal(<?php echo $product['id']; ?>)">
                            View Details
                        </button>
                        
                        <form method="POST" action="cart.php">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn-cart">Add to Cart</button>
                        </form>
                    </div>
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
    </main>

    <!-- Product Detail Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <button class="close-modal">&times;</button>
            <div class="modal-content-grid" id="modalProductContent">
                <!-- Product details will be loaded here -->
            </div>
        </div>
    </div>
       
    <!-- Footer -->
    <footer class="footer-bottom">
        <div>
            <p>&copy; <?php echo date('Y'); ?> Timeless Tokens Jewelry | Created by Mariel</p>
        </div>
    </footer>

    <script>
    // Store all product data in JavaScript variable
    const allProducts = <?php echo json_encode($products); ?>;
    
    function openProductModal(productId) {
        const product = allProducts.find(p => p.id == productId);
        if (!product) {
            console.error('Product not found:', productId);
            return;
        }
        
        const modal = document.getElementById('productModal');
        const content = document.getElementById('modalProductContent');
        
        // Generate modal HTML
        content.innerHTML = `
            <div class="modal-gallery">
                <img src="${escapeHtml(product.image_url)}" 
                     alt="${escapeHtml(product.name)}" 
                     class="modal-main-image"
                     onerror="this.src='https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=500'">
                <div class="modal-thumbnails">
                    <img src="${escapeHtml(product.image_url)}" 
                         alt="${escapeHtml(product.name)}" 
                         class="modal-thumbnail active"
                         onclick="changeMainImage(this, '${escapeHtml(product.image_url)}')">
                </div>
            </div>
            
            <div class="modal-info">
                <h2 class="modal-title">${escapeHtml(product.name)}</h2>
                
                <div class="modal-price">
                    ${product.original_price && product.original_price > product.price 
                        ? `<span class="modal-original-price">$${product.original_price.toFixed(2)}</span>` 
                        : ''}
                    <strong>$${product.price.toFixed(2)}</strong>
                    ${product.discount_percent && product.discount_percent > 0 
                        ? `<span class="modal-discount">-${product.discount_percent}% OFF</span>` 
                        : ''}
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <span style="color: var(--light-text);">Category: ${escapeHtml(product.category || 'Jewelry')}</span>
                </div>
                
                <div class="modal-description">
                    ${escapeHtml(product.description || 'No description available.')}
                </div>
                
                ${product.details ? `
                    <div class="modal-specs">
                        <h3 style="margin-bottom: 1rem; color: var(--dark-text);">Product Details</h3>
                        <div>${product.details}</div>
                    </div>
                ` : ''}
                
                <div class="modal-specs">
                    <h3 style="margin-bottom: 1rem; color: var(--dark-text);">Specifications</h3>
                    <div class="spec-item">
                        <span class="spec-label">SKU</span>
                        <span class="spec-value">${product.sku || product.id}</span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Availability</span>
                        <span class="spec-value" style="color: ${(product.stock || 0) > 0 ? '#27ae60' : '#e74c3c'};">
                            ${(product.stock || 0) > 0 ? 'In Stock' + (product.stock ? ' (' + product.stock + ' available)' : '') : 'available'}
                        </span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Material</span>
                        <span class="spec-value">${product.material || 'Sterling Silver'}</span>
                    </div>
                    ${product.weight ? `
                    <div class="spec-item">
                        <span class="spec-label">Weight</span>
                        <span class="spec-value">${product.weight}</span>
                    </div>
                    ` : ''}
                    ${product.dimensions ? `
                    <div class="spec-item">
                        <span class="spec-label">Dimensions</span>
                        <span class="spec-value">${product.dimensions}</span>
                    </div>
                    ` : ''}
                </div>
                
                <form method="POST" action="cart.php" class="modal-actions">
                    <input type="hidden" name="product_id" value="${product.id}">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="btn-quantity">
                        <button type="button" onclick="adjustQuantity(-1)">-</button>
                        <input type="number" name="quantity" value="1" min="1" max="10" 
                               class="quantity-input" id="modalQuantity">
                        <button type="button" onclick="adjustQuantity(1)">+</button>
                    </div>
                    
                    <button type="submit" class="btn-buy">
                        Add to Cart
                    </button>
                </form>
            </div>
        `;
        
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        document.body.style.paddingRight = getScrollbarWidth() + 'px';
    }
    
    function changeMainImage(thumbnail, imageUrl) {
        const mainImage = thumbnail.closest('.modal-gallery').querySelector('.modal-main-image');
        mainImage.src = imageUrl;
        
        // Update active thumbnail
        thumbnail.closest('.modal-thumbnails').querySelectorAll('.modal-thumbnail').forEach(img => {
            img.classList.remove('active');
        });
        thumbnail.classList.add('active');
    }
    
    function adjustQuantity(change) {
        const input = document.getElementById('modalQuantity');
        if (!input) return;
        
        let current = parseInt(input.value);
        const max = parseInt(input.max);
        const min = parseInt(input.min);
        
        current += change;
        if (current < min) current = min;
        if (current > max) current = max;
        
        input.value = current;
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function getScrollbarWidth() {
        return window.innerWidth - document.documentElement.clientWidth;
    }
    
    // Close modal
    document.querySelector('.close-modal').addEventListener('click', function() {
        const modal = document.getElementById('productModal');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        document.body.style.paddingRight = '';
    });
    
    // Close when clicking outside modal
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('productModal');
        if (event.target === modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            document.body.style.paddingRight = '';
        }
    });
    
    // Escape key to close modal
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const modal = document.getElementById('productModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            document.body.style.paddingRight = '';
        }
    });
    </script>
</body>
</html>
