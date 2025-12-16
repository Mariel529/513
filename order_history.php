<?php
/**
 * ICTWEB513 - Order History Page
 * Student: [Your Name]
 * Student ID: [Your Student ID]
 * Date: 2024
 */
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Database connection
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

// Get customer email from session (we use email to identify orders since there's no user_id in your database)
$customer_email = $_SESSION['user_email'] ?? '';

if (!$customer_email) {
    echo "<div class='alert alert-error'>Please log in to view your orders.</div>";
    exit;
}

// Check for order ID parameter
$order_id = $_GET['order_id'] ?? null;

// Search and filter parameters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$sort_by = $_GET['sort_by'] ?? 'order_date';
$sort_order = $_GET['sort_order'] ?? 'desc';

if ($order_id) {
    // View single order details
    try {
        // Get order basic information
        $stmt = $pdo->prepare("
            SELECT o.* 
            FROM orders o 
            WHERE o.id = ? AND o.customer_email = ?
        ");
        $stmt->execute([$order_id, $customer_email]);
        $order = $stmt->fetch();
        
        if ($order) {
            // Get order items
            $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $stmt->execute([$order_id]);
            $order_items = $stmt->fetchAll();
        } else {
            $error = "Order not found or you don't have permission to view it.";
        }
    } catch(PDOException $e) {
        $error = "Failed to retrieve order details: " . $e->getMessage();
    }
} else {
    // Get user order list (with filters)
    try {
        $query = "
            SELECT o.*, 
                   COUNT(oi.id) as item_count
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.customer_email = :customer_email
        ";
        
        $params = ['customer_email' => $customer_email];
        
        // Add search condition
        if (!empty($search)) {
            $query .= " AND (o.order_number LIKE :search OR o.customer_address LIKE :search)";
            $params['search'] = "%$search%";
        }
        
        // Add status filter
        if (!empty($status) && $status !== 'all') {
            $query .= " AND o.status = :status";
            $params['status'] = $status;
        }
        
        // Add date filter
        if (!empty($start_date)) {
            $query .= " AND DATE(o.order_date) >= :start_date";
            $params['start_date'] = $start_date;
        }
        
        if (!empty($end_date)) {
            $query .= " AND DATE(o.order_date) <= :end_date";
            $params['end_date'] = $end_date;
        }
        
        // Group and sort
        $query .= " GROUP BY o.id ORDER BY $sort_by $sort_order";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();
        
        // Get items for each order
        foreach ($orders as &$order) {
            $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $stmt->execute([$order['id']]);
            $order['items'] = $stmt->fetchAll();
            $order['item_count'] = count($order['items']);
        }
        
    } catch(PDOException $e) {
        $error = "Failed to retrieve order history: " . $e->getMessage();
        $orders = [];
    }
}

// Handle cancel order request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $cancel_order_id = $_POST['order_id'];
    
    try {
        // Check if order belongs to current user and status allows cancellation
        $stmt = $pdo->prepare("
            SELECT status FROM orders 
            WHERE id = ? AND customer_email = ? AND status IN ('pending', 'confirmed')
        ");
        $stmt->execute([$cancel_order_id, $customer_email]);
        $order = $stmt->fetch();
        
        if ($order) {
            $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
            $stmt->execute([$cancel_order_id]);
            $_SESSION['success'] = "Order has been cancelled successfully.";
            
            header('Location: order_history.php');
            exit;
        } else {
            $error = "Cannot cancel this order. It may already be shipped or delivered.";
        }
    } catch(PDOException $e) {
        $error = "Failed to cancel order: " . $e->getMessage();
    }
}

// Handle reorder request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reorder'])) {
    $reorder_id = $_POST['order_id'];
    
    try {
        // Check if order belongs to current user
        $stmt = $pdo->prepare("SELECT id FROM orders WHERE id = ? AND customer_email = ?");
        $stmt->execute([$reorder_id, $customer_email]);
        
        if ($stmt->fetch()) {
            // Get original order items
            $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $stmt->execute([$reorder_id]);
            $order_items = $stmt->fetchAll();
            
            // Add to cart
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            
            $added_items = 0;
            foreach ($order_items as $item) {
                $product_id = $item['product_id'];
                
                // Check if product still exists
                $stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                $product = $stmt->fetch();
                
                if ($product) {
                    if (isset($_SESSION['cart'][$product_id])) {
                        $_SESSION['cart'][$product_id]['quantity'] += $item['quantity'];
                    } else {
                        $_SESSION['cart'][$product_id] = [
                            'id' => $product_id,
                            'name' => $product['name'],
                            'price' => $product['price'],
                            'quantity' => $item['quantity']
                        ];
                    }
                    $added_items++;
                }
            }
            
            if ($added_items > 0) {
                $_SESSION['success'] = "$added_items items have been added to your cart!";
                header('Location: cart.php');
                exit;
            } else {
                $error = "No items could be added to cart. Products may no longer be available.";
            }
        } else {
            $error = "Order not found or you don't have permission to reorder it.";
        }
    } catch(PDOException $e) {
        $error = "Failed to reorder: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $order_id ? 'Order Details' : 'Order History'; ?> - Timeless Tokens Jewelry</title>
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
        
        .welcome-message {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1.2rem;
            color: var(--light-text);
        }
        
        /* Filter and search area */
        .filter-section {
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .filter-group label {
            font-weight: bold;
            font-size: 0.9rem;
        }
        
        .filter-group input,
        .filter-group select {
            padding: 0.5rem;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 0.9rem;
        }
        
        .filter-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }
        
        .btn {
            padding: 0.6rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: var(--primary-gold);
            color: var(--dark-text);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-gold);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #218838;
        }
        
        /* Order cards */
        .order-card {
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
            border: 1px solid var(--border);
            overflow: hidden;
            transition: transform 0.3s;
        }
        
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .order-header {
            background: #f8f9fa;
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .order-info {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .order-number {
            font-weight: bold;
            color: var(--dark-text);
            font-size: 1.1rem;
        }
        
        .order-number a {
            color: inherit;
            text-decoration: none;
        }
        
        .order-number a:hover {
            color: var(--primary-gold);
        }
        
        .order-date {
            color: var(--light-text);
        }
        
        .order-total {
            font-weight: bold;
            color: var(--primary-gold);
            font-size: 1.1rem;
        }
        
        .order-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #cce7ff; color: #004085; }
        .status-shipped { background: #d1ecf1; color: #0c5460; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        .order-summary {
            padding: 1.5rem;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        
        .order-items-preview {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .item-preview {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        
        .order-actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        /* Order details page styles */
        .order-details-container {
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .details-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
        }
        
        .details-section h3 {
            color: var(--primary-gold);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--border);
        }
        
        .details-section p {
            margin-bottom: 0.5rem;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 2rem 0;
        }
        
        .items-table th {
            background: #f8f9fa;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--dark-text);
            border-bottom: 2px solid var(--border);
        }
        
        .items-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
        }
        
        .items-table tr:hover td {
            background: #f8f9fa;
        }
        
        .item-total {
            text-align: right;
            font-weight: bold;
        }
        
        .order-totals {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 2rem;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .total-row.final {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary-gold);
            border-top: 2px solid var(--border);
            padding-top: 1rem;
            margin-top: 1rem;
        }
        
        /* Empty state and messages */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--light-text);
        }
        
        .empty-state h3 {
            margin-bottom: 1rem;
            color: var(--dark-text);
        }
        
        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
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
        
        /* Back button */
        .back-link {
            display: inline-block;
            margin-bottom: 1rem;
            color: var(--primary-gold);
            text-decoration: none;
            font-weight: bold;
        }
        
        .back-link:hover {
            text-decoration: underline;
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
        
        /* Responsive design */
        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .order-info {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .order-summary {
                grid-template-columns: 1fr;
            }
            
            .filter-grid {
                grid-template-columns: 1fr;
            }
            
            .nav-menu {
                gap: 0.5rem;
                font-size: 0.8rem;
            }
            
            .order-actions {
                flex-direction: row;
                flex-wrap: wrap;
            }
        }
        
        /* Currency styling */
        .currency {
            color: var(--primary-gold);
            font-weight: bold;
        }
    </style>
    <?php require_once 'theme_config.php'; ?>
<?php include 'theme_styles.php'; ?>
</head>
<body>
<!-- Header Navigation -->
        <body class="<?php echo $current_theme === 'dark' ? 'dark-mode' : ''; ?>">
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

    <!-- Main Content -->
    <main class="container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($order_id && isset($order)): ?>
            <!-- Order Details Page -->
            <a href="order_history.php" class="back-link">← Back to Order History</a>
            <h1 class="page-title">Order Details</h1>
            
            <div class="order-details-container">
                <!-- Order header information -->
                <div class="order-header">
                    <div class="order-info">
                        <div class="order-number">Order #<?php echo htmlspecialchars($order['order_number']); ?></div>
                        <div class="order-date">Date: <?php echo date('F j, Y', strtotime($order['order_date'])); ?></div>
                        <div class="order-status status-<?php echo strtolower($order['status']); ?>">
                            <?php echo htmlspecialchars($order['status']); ?>
                        </div>
                    </div>
                    <div class="order-total">Total: <span class="currency">A$</span><?php echo number_format($order['total_amount'], 2); ?></div>
                </div>
                
                <!-- Order details grid -->
                <div class="details-grid">
                    <div class="details-section">
                        <h3>Shipping Information</h3>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?></p>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($order['customer_address']); ?></p>
                    </div>
                    
                    <div class="details-section">
                        <h3>Order Information</h3>
                        <p><strong>Order Number:</strong> <?php echo htmlspecialchars($order['order_number']); ?></p>
                        <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method'] ?? 'Not specified'); ?></p>
                        <p><strong>Order Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
                    </div>
                </div>
                
                <!-- Order items -->
                <h3>Order Items</h3>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $subtotal = 0;
                        foreach ($order_items as $item): 
                            $item_total = $item['unit_price'] * $item['quantity'];
                            $subtotal += $item_total;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td><span class="currency">$</span><?php echo number_format($item['unit_price'], 2); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td class="item-total"><span class="currency">$</span><?php echo number_format($item_total, 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Order totals -->
                <div class="order-totals">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span><span class="currency">$</span><?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="total-row">
                        <span>Shipping:</span>
                        <span>Free</span>
                    </div>
                    <div class="total-row">
                        <span>Tax:</span>
                        <span><span class="currency">$</span>0.00</span>
                    </div>
                    <div class="total-row final">
                        <span>Total:</span>
                        <span><span class="currency">$</span><?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                </div>
                
                <!-- Order actions -->
                <div class="order-actions" style="margin-top: 2rem;">
                    <?php if (in_array($order['status'], ['pending', 'confirmed'])): ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <button type="submit" name="cancel_order" class="btn btn-danger" 
                                    onclick="return confirm('Are you sure you want to cancel this order?')">
                                Cancel Order
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    
                    <a href="javascript:window.print()" class="btn btn-secondary">
                        Print Receipt
                    </a>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Order List Page -->
            <h1 class="page-title">Order History</h1>
            
            <div class="welcome-message">
                Welcome back, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Customer'); ?>!
                You have <?php echo count($orders ?? []); ?> order(s)
            </div>
            
            <!-- Filter and search area -->
            <div class="filter-section">
                <form method="GET" action="order_history.php">
                    <div class="filter-grid">
                        <div class="filter-group">
                            <label for="search">Search Orders</label>
                            <input type="text" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Order number or address...">
                        </div>
                        
                        <div class="filter-group">
                            <label for="status">Order Status</label>
                            <select id="status" name="status">
                                <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Status</option>
                                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $status === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="shipped" <?php echo $status === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo $status === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="start_date">From Date</label>
                            <input type="date" id="start_date" name="start_date" 
                                   value="<?php echo htmlspecialchars($start_date); ?>">
                        </div>
                        
                        <div class="filter-group">
                            <label for="end_date">To Date</label>
                            <input type="date" id="end_date" name="end_date" 
                                   value="<?php echo htmlspecialchars($end_date); ?>">
                        </div>
                        
                        <div class="filter-group">
                            <label for="sort_by">Sort By</label>
                            <select id="sort_by" name="sort_by">
                                <option value="order_date" <?php echo $sort_by === 'order_date' ? 'selected' : ''; ?>>Date</option>
                                <option value="total_amount" <?php echo $sort_by === 'total_amount' ? 'selected' : ''; ?>>Total Amount</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="sort_order">Order</label>
                            <select id="sort_order" name="sort_order">
                                <option value="desc" <?php echo $sort_order === 'desc' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="asc" <?php echo $sort_order === 'asc' ? 'selected' : ''; ?>>Oldest First</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="order_history.php" class="btn btn-secondary">Clear Filters</a>
                    </div>
                </form>
            </div>
            
            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <h3>No Orders Found</h3>
                    <p>You haven't placed any orders yet, or no orders match your search criteria.</p>
                    <a href="products.php" class="btn btn-primary">Start Shopping</a>
                </div>
            <?php else: ?>
                <!-- Order list -->
                <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-info">
                            <div class="order-number">
                                <a href="order_history.php?order_id=<?php echo $order['id']; ?>">
                                    Order #<?php echo htmlspecialchars($order['order_number']); ?>
                                </a>
                            </div>
                            <div class="order-date">
                                <?php echo date('F j, Y', strtotime($order['order_date'])); ?>
                            </div>
                            <div class="order-total">
                                <span class="currency">$</span><?php echo number_format($order['total_amount'], 2); ?>
                            </div>
                        </div>
                        <div class="order-status status-<?php echo strtolower($order['status']); ?>">
                            <?php echo htmlspecialchars($order['status']); ?>
                        </div>
                    </div>
                    
                    <div class="order-summary">
                        <div class="order-items-preview">
                            <?php foreach ($order['items'] as $item): ?>
                            <div class="item-preview">
                                <div>
                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                    <div style="font-size: 0.8rem; color: var(--light-text);">
                                        Qty: <?php echo $item['quantity']; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="order-actions">
                            <a href="order_history.php?order_id=<?php echo $order['id']; ?>" 
                               class="btn btn-primary">
                                View Details
                            </a>
                            
                            <?php if (in_array($order['status'], ['pending', 'confirmed'])): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <button type="submit" name="cancel_order" class="btn btn-danger" 
                                            onclick="return confirm('Are you sure you want to cancel this order?')">
                                        Cancel
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <p style="text-align: center;">&copy; 2025 Timeless Tokens Jewelry | Created by Mariel</p>
        </div>
    </footer>
        <?php include 'theme_toggle.php'; ?>
</body>
</html>