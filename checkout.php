<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['error'] = "Please log in to proceed to checkout.";
    header('Location: login.php');
    exit;
}


if (empty($_SESSION['cart'])) {
    $_SESSION['error'] = "Your cart is empty.";
    header('Location: cart.php');
    exit;
}

$host = 'localhost';
$db   = '47_99_104_82';
$user = '47_99_104_82';  
$pass = 'bXbwMzyJbk';

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


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $required_fields = ['full_name', 'email', 'phone', 'address_line1', 'city', 'state', 'postal_code', 'country', 'payment_method'];
    
    $errors = [];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required.";
        }
    }
    
    if (empty($errors)) {

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
        

        if (isset($_POST['same_as_shipping']) && $_POST['same_as_shipping'] == 'on') {
            $billing_address = $shipping_address;
        } else {
            $billing_address = [
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
        }
        
        $payment_method = $_POST['payment_method'];
        

        $total_amount = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total_amount += $item['price'] * $item['quantity'];
        }
        
        try {
            $order_number = 'TT' . date('YmdHis') . rand(100, 999);
            
            $stmt = $pdo->prepare("
                INSERT INTO orders 
                (order_number, customer_name, customer_email, customer_phone, 
                 customer_address, total_amount, payment_method, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
            
            $full_address = $shipping_address['address_line1'];
            if (!empty($shipping_address['address_line2'])) {
                $full_address .= ', ' . $shipping_address['address_line2'];
            }
            $full_address .= ', ' . $shipping_address['city'] . ', ' . $shipping_address['state'] . ' ' . $shipping_address['postal_code'] . ', ' . $shipping_address['country'];
            
            $stmt->execute([
                $order_number,
                $shipping_address['full_name'],
                $shipping_address['email'],
                $shipping_address['phone'],
                $full_address,
                $total_amount,
                $payment_method
            ]);
            
            $order_id = $pdo->lastInsertId();

            foreach ($_SESSION['cart'] as $item) {
                $stmt = $pdo->prepare("
                    INSERT INTO order_items 
                    (order_id, product_id, product_name, quantity, unit_price) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $order_id,
                    $item['id'],
                    $item['name'],
                    $item['quantity'],
                    $item['price']
                ]);
            }
            
            $_SESSION['order_details'] = [
                'order_id' => $order_id,
                'order_number' => $order_number,
                'total_amount' => $total_amount,
                'shipping_address' => $shipping_address,
                'billing_address' => $billing_address,
                'payment_method' => $payment_method,
                'items' => $_SESSION['cart']
            ];
            

            $_SESSION['cart'] = [];
            

            header('Location: order_confirmation.php?order_id=' . $order_id);
            exit;
            
        } catch(PDOException $e) {
            $error = "Failed to process order: " . $e->getMessage();
        }
    }
}

$cart_total = 0;
$cart_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_total += $item['price'] * $item['quantity'];
    $cart_count += $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Timeless Tokens Jewelry</title>
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
        
        .checkout-step.completed .step-number {
            background: #27ae60;
            color: white;
        }
        
        .checkout-step.active .step-number {
            background: var(--primary-gold);
            color: var(--dark-text);
        }
        
        .checkout-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        
        .address-form {
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
        .form-group select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-gold);
            box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.2);
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .billing-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid var(--border);
        }
        
        .order-summary {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: fit-content;
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
        
        .cart-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }
        
        .error-message {
            color: #dc3545;
            background: #f8d7da;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        
        .success-message {
            color: #155724;
            background: #d4edda;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        
        .footer {
            background-color: #1a1a1a;
            color: var(--white);
            padding: 3rem 0 2rem;
            margin-top: 4rem;
        }
        
        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
        }
        
        .currency {
            color: var(--primary-gold);
            font-weight: bold;
        }
    </style>
    <?php require_once 'theme_config.php'; ?>
<?php include 'theme_styles.php'; ?>
</head>
<body>
    <header class="header">
        <nav class="nav-container">
            <a href="index.php" class="logo">
                <img src="photo/2.jpg" alt="Timeless Tokens" style="height: 40px; vertical-align: middle;">
                <span style="vertical-align: middle;">Timeless Tokens Jewelry</span>
            </a>
        </nav>
    </header>

    <main class="container">
        <h1 class="page-title">Checkout</h1>
        
        <div class="checkout-steps">
            <div class="checkout-step completed">
                <div class="step-number">1</div>
                <div>Cart</div>
            </div>
            <div class="checkout-step active">
                <div class="step-number">2</div>
                <div>Address & Payment</div>
            </div>
            <div class="checkout-step">
                <div class="step-number">3</div>
                <div>Confirmation</div>
            </div>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo $err; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="checkout-container">
            <div class="address-form">
                <h2>Shipping Information</h2>
                
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" required 
                           value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" required 
                           value="<?php echo htmlspecialchars($_SESSION['user_phone'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="address_line1">Address Line 1 *</label>
                    <input type="text" id="address_line1" name="address_line1" required>
                </div>
                
                <div class="form-group">
                    <label for="address_line2">Address Line 2</label>
                    <input type="text" id="address_line2" name="address_line2">
                </div>
                
                <div class="form-group">
                    <label for="city">City *</label>
                    <input type="text" id="city" name="city" required>
                </div>
                
                <div class="form-group">
                    <label for="state">State/Province *</label>
                    <input type="text" id="state" name="state" required>
                </div>
                
                <div class="form-group">
                    <label for="postal_code">Postal Code *</label>
                    <input type="text" id="postal_code" name="postal_code" required>
                </div>
                
                <div class="form-group">
                    <label for="country">Country *</label>
                    <select id="country" name="country" required>
                        <option value="">Select Country</option>
                        <option value="US">United States</option>
                        <option value="CA">Canada</option>
                        <option value="UK">United Kingdom</option>
                        <option value="AU">Australia</option>
                        <option value="JP">Japan</option>
                        <option value="CN">China</option>
                        <option value="HK">Hong Kong</option>
                        <option value="SG">Singapore</option>
                        <option value="NZ">New Zealand</option>
                    </select>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" id="same_as_shipping" name="same_as_shipping" checked>
                    <label for="same_as_shipping">Billing address is the same as shipping address</label>
                </div>
                
                <div id="billing-section" style="display: none;" class="billing-section">
                    <h3>Billing Information</h3>
                    
                    <div class="form-group">
                        <label for="billing_full_name">Full Name *</label>
                        <input type="text" id="billing_full_name" name="billing_full_name">
                    </div>
                    
                    <div class="form-group">
                        <label for="billing_email">Email Address *</label>
                        <input type="email" id="billing_email" name="billing_email">
                    </div>
                    
                    <div class="form-group">
                        <label for="billing_phone">Phone Number *</label>
                        <input type="tel" id="billing_phone" name="billing_phone">
                    </div>
                    
                    <div class="form-group">
                        <label for="billing_address_line1">Address Line 1 *</label>
                        <input type="text" id="billing_address_line1" name="billing_address_line1">
                    </div>
                    
                    <div class="form-group">
                        <label for="billing_address_line2">Address Line 2</label>
                        <input type="text" id="billing_address_line2" name="billing_address_line2">
                    </div>
                    
                    <div class="form-group">
                        <label for="billing_city">City *</label>
                        <input type="text" id="billing_city" name="billing_city">
                    </div>
                    
                    <div class="form-group">
                        <label for="billing_state">State/Province *</label>
                        <input type="text" id="billing_state" name="billing_state">
                    </div>
                    
                    <div class="form-group">
                        <label for="billing_postal_code">Postal Code *</label>
                        <input type="text" id="billing_postal_code" name="billing_postal_code">
                    </div>
                    
                    <div class="form-group">
                        <label for="billing_country">Country *</label>
                        <select id="billing_country" name="billing_country">
                            <option value="">Select Country</option>
                            <option value="US">United States</option>
                            <option value="CA">Canada</option>
                            <option value="UK">United Kingdom</option>
                            <option value="AU">Australia</option>
                            <option value="JP">Japan</option>
                            <option value="CN">China</option>
                            <option value="HK">Hong Kong</option>
                            <option value="SG">Singapore</option>
                            <option value="NZ">New Zealand</option>
                        </select>
                    </div>
                </div>
                
                <h3 style="margin-top: 2rem;">Payment Method</h3>
                <div class="form-group">
                    <select name="payment_method" required>
                        <option value="">Select Payment Method</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="paypal">PayPal</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cash_on_delivery">Cash on Delivery</option>
                    </select>
                </div>
            </div>
            
            <div class="order-summary">
                <h3>Order Summary</h3>
                
                <?php foreach ($_SESSION['cart'] as $item): ?>
                <div class="cart-item">
                    <div>
                        <div><strong><?php echo htmlspecialchars($item['name']); ?></strong></div>
                        <div>Qty: <?php echo $item['quantity']; ?></div>
                    </div>
                    <div><span class="currency">$</span><?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                </div>
                <?php endforeach; ?>
                
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span><span class="currency">$</span><?php echo number_format($cart_total, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping:</span>
                    <span>Free</span>
                </div>
                <div class="summary-row">
                    <span>Tax:</span>
                    <span><span class="currency">$</span>0.00</span>
                </div>
                <div class="summary-row summary-total">
                    <span>Total:</span>
                    <span><span class="currency">$</span><?php echo number_format($cart_total, 2); ?></span>
                </div>
                
                <button type="submit" class="checkout-btn">Place Order</button>
                <a href="cart.php" style="display: block; text-align: center; margin-top: 1rem; color: var(--primary-gold);">← Back to Cart</a>
            </div>
        </form>
                <body class="<?php echo $current_theme === 'dark' ? 'dark-mode' : ''; ?>">
    </main>

    <footer class="footer">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <p style="text-align: center;">&copy; 2025 Timeless Tokens Jewelry | Created by Mariel</p>
        </div>
    </footer>

    <script>
        document.getElementById('same_as_shipping').addEventListener('change', function() {
            const billingSection = document.getElementById('billing-section');
            if (this.checked) {
                billingSection.style.display = 'none';
            } else {
                billingSection.style.display = 'block';
            }
        });
    </script>
        <?php include 'theme_toggle.php'; ?>
</body>
</html>