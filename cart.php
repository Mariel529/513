<?php
/**
 * ICTWEB513 - Shopping Cart
 * Student: [Your Name]
 * Student ID: [Your Student ID]
 * Date: 2024
 */

session_start();

// Redirect to login if not logged in (except for viewing cart)
$current_page = basename($_SERVER['PHP_SELF']);
$allowed_pages = ['cart.php', 'products.php', 'index.php', 'login.php'];

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    if (!in_array($current_page, $allowed_pages)) {
        $_SESSION['error'] = "Please log in to access this page.";
        header('Location: login.php');
        exit;
    }
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
    
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Initialize shopping cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Initialize user info if logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && !isset($_SESSION['user_info'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $_SESSION['user_info'] = $stmt->fetch();
    } catch(PDOException $e) {
        // Silent fail, user info will be collected during checkout
    }
}

// Handle cart operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $product_id = intval($_POST['product_id']);
                $quantity = intval($_POST['quantity']);

                // ✅ Read products from products.json
                $productsJson = file_get_contents('data/products.json');
                $products = json_decode($productsJson, true);

                $product = null;
                foreach ($products as $p) {
                    if ($p['id'] == $product_id) {
                        $product = $p;
                        break;
                    }
                }

                if ($product) {
                    if (isset($_SESSION['cart'][$product_id])) {
                        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
                    } else {
                        $_SESSION['cart'][$product_id] = [
                            'id' => $product['id'],
                            'name' => $product['name'],
                            'price' => $product['price'],
                            'image_url' => $product['image_url'],
                            'quantity' => $quantity
                        ];
                    }
                    $_SESSION['success'] = "Product added to cart!";
                } else {
                    $_SESSION['error'] = "Product does not exist!";
                }

                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
                
            case 'update':
                $product_id = intval($_POST['product_id']);
                $quantity = intval($_POST['quantity']);
                
                if ($quantity > 0) {
                    $_SESSION['cart'][$product_id]['quantity'] = $quantity;
                } else {
                    unset($_SESSION['cart'][$product_id]);
                }
                $_SESSION['success'] = "Cart updated!";
                break;
                
            case 'remove':
                $product_id = intval($_POST['product_id']);
                unset($_SESSION['cart'][$product_id]);
                $_SESSION['success'] = "Product removed from cart!";
                break;
                
            case 'clear':
                $_SESSION['cart'] = [];
                $_SESSION['success'] = "Cart cleared!";
                break;
                
            case 'checkout':
                // Redirect to checkout page
                if (!empty($_SESSION['cart'])) {
                    header('Location: checkout.php');
                    exit;
                }
                break;
                
            case 'process_checkout':
                // Process checkout with address and payment
                if (!empty($_SESSION['cart'])) {
                    $shipping_address = [
                        'full_name' => $_POST['full_name'],
                        'email' => $_POST['email'],
                        'phone' => $_POST['phone'],
                        'address_line1' => $_POST['address_line1'],
                        'address_line2' => $_POST['address_line2'] ?? '',
                        'city' => $_POST['city'],
                        'state' => $_POST['state'],
                        'postal_code' => $_POST['postal_code'],
                        'country' => $_POST['country']
                    ];
                    
                    $billing_address = $_POST['same_as_shipping'] ? $shipping_address : [
                        'full_name' => $_POST['billing_full_name'],
                        'email' => $_POST['billing_email'],
                        'phone' => $_POST['billing_phone'],
                        'address_line1' => $_POST['billing_address_line1'],
                        'address_line2' => $_POST['billing_address_line2'] ?? '',
                        'city' => $_POST['billing_city'],
                        'state' => $_POST['billing_state'],
                        'postal_code' => $_POST['billing_postal_code'],
                        'country' => $_POST['billing_country']
                    ];
                    
                    $payment_method = $_POST['payment_method'];
                    
                    // Generate order number
                    $order_number = 'TT' . date('YmdHis') . rand(100, 999);
                    
                    // Calculate total amount
                    $total_amount = 0;
                    foreach ($_SESSION['cart'] as $item) {
                        $total_amount += $item['price'] * $item['quantity'];
                    }
                    
                    // Insert order with address info
                    $stmt = $pdo->prepare("INSERT INTO orders (order_number, user_id, total_amount, shipping_address, billing_address, payment_method) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $order_number,
                        $_SESSION['user_id'] ?? null,
                        $total_amount,
                        json_encode($shipping_address),
                        json_encode($billing_address),
                        $payment_method
                    ]);
                    $order_id = $pdo->lastInsertId();
                    
                    // Insert order items
                    foreach ($_SESSION['cart'] as $item) {
                        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $order_id,
                            $item['id'],
                            $item['name'],
                            $item['quantity'],
                            $item['price']
                        ]);
                    }
                    
                    // Clear cart and redirect to thank you page
                    $_SESSION['order_id'] = $order_number;
                    $_SESSION['order_details'] = [
                        'order_number' => $order_number,
                        'total_amount' => $total_amount,
                        'shipping_address' => $shipping_address,
                        'billing_address' => $billing_address,
                        'payment_method' => $payment_method,
                        'items' => $_SESSION['cart']
                    ];
                    $_SESSION['cart'] = [];
                    
                    header('Location: payment.php?order_id=' . $order_number);
                    exit;
                }
                break;
        }
    }
}

// Calculate cart total
$cart_total = 0;
$cart_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_total += $item['price'] * $item['quantity'];
    $cart_count += $item['quantity'];
}

// Determine current page for navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Timeless Tokens Jewelry</title>
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
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 20px;
            min-height: 60vh;
        }
        
        .page-title {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2.5rem;
            color: var(--dark-text);
        }
        
        .cart-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .cart-items {
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1.5rem;
        }
        
        .cart-summary {
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1.5rem;
            height: fit-content;
        }
        
        .cart-item {
            display: grid;
            grid-template-columns: 100px 1fr auto auto auto;
            gap: 1rem;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border);
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .cart-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .cart-item-name {
            font-weight: bold;
            color: var(--dark-text);
        }
        
        .cart-item-price {
            color: var(--primary-gold);
            font-weight: bold;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .quantity-btn {
            background: var(--primary-gold);
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            font-weight: bold;
            color: var(--dark-text);
        }
        
        .quantity-btn:hover {
            background: var(--secondary-gold);
        }
        
        .quantity-input {
            width: 50px;
            text-align: center;
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 0.3rem;
        }
        
        .remove-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .remove-btn:hover {
            background: #c0392b;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border);
        }
        
        .summary-total {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary-gold);
        }
        
        .checkout-btn {
            background-color: var(--primary-gold);
            color: var(--dark-text);
            border: none;
            padding: 1rem 2rem;
            border-radius: 4px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            margin-top: 1rem;
            transition: background-color 0.3s;
        }
        
        .checkout-btn:hover {
            background-color: var(--secondary-gold);
        }
        
        .checkout-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        
        .empty-cart {
            text-align: center;
            padding: 3rem;
            color: var(--light-text);
        }
        
        .continue-shopping {
            display: inline-block;
            background: var(--primary-gold);
            color: var(--dark-text);
            padding: 0.8rem 1.5rem;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin-top: 1rem;
            transition: background-color 0.3s;
        }
        
        .continue-shopping:hover {
            background: var(--secondary-gold);
        }
        
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            text-align: center;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .cart-count {
            background: var(--primary-gold);
            color: var(--dark-text);
            border-radius: 50%;
            padding: 0.2rem 0.5rem;
            font-size: 0.8rem;
            margin-left: 0.3rem;
            font-weight: bold;
        }
        
        .clear-cart {
            background: #95a5a6;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 1rem;
        }
        
        .clear-cart:hover {
            background: #7f8c8d;
        }
        
        .user-welcome {
            color: var(--primary-gold);
            font-weight: bold;
            margin-left: 1rem;
        }
        
        .footer {
            background-color: #1a1a1a;
            color: var(--white);
            padding: 3rem 0 2rem;
            margin-top: 4rem;
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

        .checkout-steps {
            display: flex;
            justify-content: center;
            margin-bottom: 3rem;
            gap: 2rem;
        }
        
        .checkout-step {
            text-align: center;
            position: relative;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            background: #ddd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            font-weight: bold;
        }
        
        .checkout-step.active .step-number {
            background: var(--primary-gold);
            color: var(--dark-text);
        }
        
        .checkout-step.completed .step-number {
            background: #27ae60;
            color: white;
        }
        
        .address-form {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-gold);
            box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.2);
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .billing-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid var(--border);
        }
        
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }
        
        .payment-method {
            border: 2px solid var(--border);
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .payment-method:hover {
            border-color: var(--primary-gold);
        }
        
        .payment-method.selected {
            border-color: var(--primary-gold);
            background: rgba(212, 175, 55, 0.1);
        }
        
        .payment-method input {
            display: none;
        }
        
        .order-review {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .review-section {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid var(--border);
        }
        
        .review-section h3 {
            color: var(--primary-gold);
            margin-bottom: 1rem;
        }
        
        .review-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .payment-processing {
            text-align: center;
            padding: 3rem;
        }
        
        .payment-status {
            font-size: 1.2rem;
            margin: 2rem 0;
            padding: 1rem;
            border-radius: 4px;
            background: #f8f9fa;
        }
        
        .payment-success {
            color: #27ae60;
            background: #d4edda;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary-gold);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .order-confirmation {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 auto;
        }
        
        .confirmation-icon {
            font-size: 4rem;
            color: #27ae60;
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .cart-container {
                grid-template-columns: 1fr;
            }
            
            .cart-item {
                grid-template-columns: 80px 1fr;
                gap: 0.5rem;
            }
            
            .cart-item-price, .quantity-controls, .remove-btn {
                grid-column: 1 / -1;
                justify-self: start;
            }
            
            .nav-menu {
                gap: 0.5rem;
            }
            
            .nav-menu a {
                font-size: 0.8rem;
                padding: 0.2rem 0.3rem;
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
                <li><a href="http://47.99.104.82/feedback/">feedback</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="container">
        <h1 class="page-title">Shopping Cart</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($_SESSION['cart'])): ?>
            <div class="empty-cart">
                <h2>Your cart is empty</h2>
                <p>Browse our collections to find something you love!</p>
                <a href="products.php" class="continue-shopping">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-container">
                <div class="cart-items">
                    <?php if (count($_SESSION['cart']) > 0): ?>
                        <form method="POST">
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="clear-cart" onclick="return confirm('Are you sure you want to clear your cart?')">Clear Cart</button>
                        </form>
                    <?php endif; ?>
                    
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                    <div class="cart-item">
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                             class="cart-item-image"
                             onerror="this.src='https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=150'">
                        
                        <div class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                        
                        <div class="cart-item-price">$<?php echo number_format($item['price'], 0); ?></div>
                        
                        <form method="POST" class="quantity-controls">
                            <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                            <input type="hidden" name="action" value="update">
                            <button type="submit" name="quantity" value="<?php echo $item['quantity'] - 1; ?>" class="quantity-btn">-</button>
                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" class="quantity-input" min="1" readonly>
                            <button type="submit" name="quantity" value="<?php echo $item['quantity'] + 1; ?>" class="quantity-btn">+</button>
                        </form>
                        
                        <form method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                            <input type="hidden" name="action" value="remove">
                            <button type="submit" class="remove-btn" onclick="return confirm('Are you sure you want to remove this item?')">Remove</button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-summary">
                    <h3>Order Summary</h3>
                    <div class="summary-row">
                        <span>Items (<?php echo $cart_count; ?>):</span>
                        <span>$<?php echo number_format($cart_total, 0); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping:</span>
                        <span>Free</span>
                    </div>
                    <div class="summary-row">
                        <span>Tax:</span>
                        <span>$0</span>
                    </div>
                    <div class="summary-row summary-total">
                        <span>Total:</span>
                        <span>$<?php echo number_format($cart_total, 0); ?></span>
                    </div>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="checkout">
                        <button type="submit" class="checkout-btn">Proceed to Checkout</button>
                    </form>
                    
                    <a href="products.php" class="continue-shopping" style="display: block; text-align: center; margin-top: 1rem;">Continue Shopping</a>
                </div>
            </div>
                    <body class="<?php echo $current_theme === 'dark' ? 'dark-mode' : ''; ?>">
        <?php endif; ?>
    </main>
<footer class="footer-bottom">
        <div>
            <p>&copy; <?php echo date('Y'); ?> Timeless Tokens Jewelry | Created by Mariel</p>
        </div>
    </footer>
        <?php include 'theme_toggle.php'; ?>
</body>
</html>
