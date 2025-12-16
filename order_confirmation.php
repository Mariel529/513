<?php
session_start();

if (!isset($_GET['order_id']) && !isset($_SESSION['order_details'])) {
    header('Location: index.php');
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

$order_id = $_GET['order_id'] ?? ($_SESSION['order_details']['order_id'] ?? 0);

try {
    $stmt = $pdo->prepare("
        SELECT o.*, 
               GROUP_CONCAT(CONCAT(oi.product_name, ' (Qty: ', oi.quantity, ')') SEPARATOR ', ') as items_summary
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.id = ?
        GROUP BY o.id
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        if (isset($_SESSION['order_details'])) {
            $order = [
                'id' => $_SESSION['order_details']['order_id'],
                'order_number' => $_SESSION['order_details']['order_number'],
                'total_amount' => $_SESSION['order_details']['total_amount'],
                'customer_name' => $_SESSION['order_details']['shipping_address']['full_name'],
                'customer_email' => $_SESSION['order_details']['shipping_address']['email'],
                'customer_address' => $_SESSION['order_details']['shipping_address']['address_line1'] . ', ' . 
                                      $_SESSION['order_details']['shipping_address']['city'] . ', ' . 
                                      $_SESSION['order_details']['shipping_address']['state'] . ' ' . 
                                      $_SESSION['order_details']['shipping_address']['postal_code'] . ', ' . 
                                      $_SESSION['order_details']['shipping_address']['country'],
                'payment_method' => $_SESSION['order_details']['payment_method'],
                'order_date' => date('Y-m-d H:i:s'),
                'status' => 'pending',
                'items_summary' => ''
            ];
        } else {
            header('Location: index.php');
            exit;
        }
    }
    
    $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll();
    
} catch(PDOException $e) {
    die("Error fetching order details: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Timeless Tokens Jewelry</title>
    <style>
        :root {
            --primary-gold: #d4af37;
            --secondary-gold: #b8941f;
            --dark-text: #2c2c2c;
            --light-text: #666;
            --background: #fefefe;
            --border: #e8e8e8;
            --white: #ffffff;
            --success-green: #27ae60;
            --success-bg: #d4f8e8;
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
        
        .success-message {
            background-color: var(--success-bg);
            color: var(--success-green);
            padding: 2rem;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 2rem;
            border-left: 4px solid var(--success-green);
        }
        
        .success-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .order-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-gold);
            margin-bottom: 0.5rem;
        }
        
        .confirmation-text {
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }
        
        .order-details-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .order-info-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .card-title {
            font-size: 1.2rem;
            color: var(--primary-gold);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--border);
        }
        
        .info-group {
            margin-bottom: 1rem;
        }
        
        .info-label {
            font-weight: bold;
            color: var(--light-text);
            display: block;
            margin-bottom: 0.3rem;
        }
        
        .info-value {
            color: var(--dark-text);
        }
        
        .order-items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }
        
        .order-items-table th {
            background-color: #f8f8f8;
            padding: 1rem;
            text-align: left;
            border-bottom: 2px solid var(--border);
            color: var(--dark-text);
        }
        
        .order-items-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
        }
        
        .order-total {
            text-align: right;
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--primary-gold);
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid var(--border);
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .btn-primary {
            background-color: var(--primary-gold);
            color: var(--dark-text);
            border: none;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-gold);
        }
        
        .btn-secondary {
            background-color: transparent;
            color: var(--primary-gold);
            border: 2px solid var(--primary-gold);
        }
        
        .btn-secondary:hover {
            background-color: var(--primary-gold);
            color: var(--dark-text);
        }
        
        .footer {
            background-color: #1a1a1a;
            color: var(--white);
            padding: 3rem 0 2rem;
            margin-top: 4rem;
        }
        
        .currency {
            color: var(--primary-gold);
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .order-details-container {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                text-align: center;
            }
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
            margin-left: 0.5rem;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-confirmed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-shipped {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .status-delivered {
            background-color: #d1ecf1;
            color: #0c5460;
        }
    </style>
    <?php require_once 'theme_config.php'; ?>
<?php include 'theme_styles.php'; ?>
</head>
<body>
            <body class="<?php echo $current_theme === 'dark' ? 'dark-mode' : ''; ?>">
    <header class="header">
        <nav class="nav-container">
            <a href="index.php" class="logo">
                <img src="photo/2.jpg" alt="Timeless Tokens" style="height: 40px; vertical-align: middle;">
                <span style="vertical-align: middle;">Timeless Tokens Jewelry</span>
            </a>
        </nav>
    </header>

    <main class="container">
        <h1 class="page-title">Order Confirmation</h1>
        
        <div class="success-message">
            <div class="success-icon">✓</div>
            <div class="order-number">Order #<?php echo htmlspecialchars($order['order_number']); ?></div>
            <div class="confirmation-text">
                Thank you for your purchase! Your order has been successfully placed.
            </div>
            <p>A confirmation email has been sent to <?php echo htmlspecialchars($order['customer_email']); ?></p>
        </div>
        
        <div class="order-details-container">
            <div class="order-info-card">
                <h3 class="card-title">Order Information</h3>
                <div class="info-group">
                    <span class="info-label">Order Number:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['order_number']); ?></span>
                </div>
                <div class="info-group">
                    <span class="info-label">Order Date:</span>
                    <span class="info-value"><?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></span>
                </div>
                <div class="info-group">
                    <span class="info-label">Order Status:</span>
                    <span class="info-value">
                        <?php echo ucfirst($order['status']); ?>
                        <span class="status-badge status-<?php echo $order['status']; ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </span>
                </div>
                <div class="info-group">
                    <span class="info-label">Payment Method:</span>
                    <span class="info-value"><?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></span>
                </div>
            </div>
            
            <div class="order-info-card">
                <h3 class="card-title">Customer Information</h3>
                <div class="info-group">
                    <span class="info-label">Name:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                </div>
                <div class="info-group">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['customer_email']); ?></span>
                </div>
                <div class="info-group">
                    <span class="info-label">Phone:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['customer_phone'] ?? 'N/A'); ?></span>
                </div>
                <div class="info-group">
                    <span class="info-label">Shipping Address:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['customer_address']); ?></span>
                </div>
            </div>
        </div>
        
        <div class="order-info-card">
            <h3 class="card-title">Order Items</h3>
            <table class="order-items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $subtotal = 0;
                    if (!empty($order_items)): 
                        foreach ($order_items as $item): 
                            $item_total = $item['unit_price'] * $item['quantity'];
                            $subtotal += $item_total;
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><span class="currency">$</span><?php echo number_format($item['unit_price'], 2); ?></td>
                        <td><span class="currency">$</span><?php echo number_format($item_total, 2); ?></td>
                    </tr>
                    <?php 
                        endforeach; 
                    else: 
                    
                        if (isset($_SESSION['order_details']['items'])): 
                            foreach ($_SESSION['order_details']['items'] as $item): 
                                $item_total = $item['price'] * $item['quantity'];
                                $subtotal += $item_total;
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><span class="currency">$</span><?php echo number_format($item['price'], 2); ?></td>
                        <td><span class="currency">$</span><?php echo number_format($item_total, 2); ?></td>
                    </tr>
                    <?php 
                            endforeach; 
                        endif;
                    endif; 
                    ?>
                </tbody>
            </table>
            
            <div class="order-total">
                <span>Total Amount: </span>
                <span class="currency">$</span><?php echo number_format($subtotal ?: $order['total_amount'], 2); ?>
            </div>
        </div>
        
        <div class="order-info-card">
            <h3 class="card-title">What's Next?</h3>
            <div style="line-height: 1.8;">
                <p><strong>1. Order Processing:</strong> Your order is now being processed. You will receive an email confirmation shortly.</p>
                <p><strong>2. Shipping:</strong> Once your order is shipped, you will receive a tracking number via email.</p>
                <p><strong>3. Delivery:</strong> Your order will be delivered within 3-5 business days.</p>
                <p><strong>4. Questions?</strong> If you have any questions about your order, please contact our customer service at support@timelesstokens.com</p>
            </div>
        </div>
        
        <div class="action-buttons">
            <a href="index.php" class="btn btn-primary">Continue Shopping</a>
            <a href="order_history.php" class="btn btn-secondary">View All Orders</a>
        </div>
    </main>

    <footer class="footer">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <p style="text-align: center;">&copy; 2025 Timeless Tokens Jewelry | Created by Mariel</p>
            <p style="text-align: center; margin-top: 0.5rem; font-size: 0.9rem; color: #aaa;">
                Need help? Contact us at support@timelesstokens.com or call +61 2 1234 5678
            </p>
        </div>
    </footer>

    <script>
        function printOrder() {
            window.print();
        }

        document.addEventListener('DOMContentLoaded', function() {
            console.log('Order confirmation page loaded');
        });
    </script>
        <?php include 'theme_toggle.php'; ?>
</body>
</html>
<?php
if (isset($_SESSION['order_details'])) {
    unset($_SESSION['order_details']);
}
?>