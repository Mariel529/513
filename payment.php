<?php
session_start();

// Redirect if no order details
if (!isset($_SESSION['order_details'])) {
    header('Location: index.php');
    exit;
}

$order = $_SESSION['order_details'];

// Simulate payment confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    unset($_SESSION['cart']);           // Clear cart
    header('Location: order_confirmation.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Timeless Tokens Jewelry</title>
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
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Georgia',serif;line-height:1.6;color:var(--dark-text);background:var(--background);}
        .header{background:linear-gradient(135deg, #1a1a1a 0%, #2c2c2c 100%);color:var(--white);padding:1rem 0;}
        .nav-container{max-width:1200px;margin:0 auto;padding:0 20px;display:flex;justify-content:space-between;align-items:center;}
        .logo{font-size:1.8rem;font-weight:bold;color:var(--primary-gold);text-decoration:none;letter-spacing:1px;}
        .container{max-width:800px;margin:0 auto;padding:2rem 20px;min-height:60vh;}
        .payment-container{background:var(--white);border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,.1);padding:3rem;margin-top:2rem;text-align:center;}
        .checkout-steps{display:flex;justify-content:center;margin-bottom:3rem;gap:2rem;}
        .checkout-step{text-align:center;}
        .step-number{width:40px;height:40px;background:#ddd;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto .5rem;font-weight:bold;}
        .checkout-step.completed .step-number{background:#27ae60;color:white;}
        .checkout-step.active .step-number{background:var(--primary-gold);color:var(--dark-text);}
        .spinner{border:4px solid #f3f3f3;border-top:4px solid var(--primary-gold);border-radius:50%;width:60px;height:60px;animation:spin 1s linear infinite;margin:0 auto 2rem;}
        @keyframes spin{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}
        .payment-status{font-size:1.2rem;margin:2rem 0;padding:1rem;border-radius:4px;background:#f8f9fa;}
        .payment-success{color:#27ae60;background:#d4edda;border:1px solid #c3e6cb;}
        .order-details{text-align:left;margin:2rem 0;padding:1.5rem;background:#f8f9fa;border-radius:4px;}
        .order-details h3{color:var(--primary-gold);margin-bottom:1rem;}
        .order-details p{margin-bottom:.5rem;}
        .btn{background:var(--primary-gold);color:var(--dark-text);border:none;padding:1rem 2rem;border-radius:4px;font-size:1.1rem;font-weight:bold;cursor:pointer;text-decoration:none;display:inline-block;margin:1rem;transition:background .3s;}
        .btn:hover{background:var(--secondary-gold);}
        .footer{background:#1a1a1a;color:var(--white);padding:3rem 0 2rem;margin-top:4rem;}
    </style>
    <?php require_once 'theme_config.php'; ?>
<?php include 'theme_styles.php'; ?>
</head>
<body>
<header class="header">
    <nav class="nav-container">
        <a href="index.php" class="logo">
            <img src="photo/2.jpg" alt="Timeless Tokens" style="height:40px;vertical-align:middle;">
            <span style="vertical-align:middle;">Timeless Tokens Jewelry</span>
        </a>
    </nav>
</header>

<main class="container">
    <h1>Payment Processing</h1>

    <div class="checkout-steps">
        <div class="checkout-step completed"><div class="step-number">1</div><div>Cart</div></div>
        <div class="checkout-step completed"><div class="step-number">2</div><div>Address</div></div>
        <div class="checkout-step active"><div class="step-number">3</div><div>Payment</div></div>
        <div class="checkout-step"><div class="step-number">4</div><div>Confirmation</div></div>
    </div>

    <div class="payment-container">
        <div class="order-details">
            <h3>Order Details</h3>
            <p><strong>Order Number:</strong> <?= htmlspecialchars($order['order_number']) ?></p>
            <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
            <p><strong>Total Amount:</strong> AU$<?= number_format($order['total_amount'], 2) ?></p>
        </div>

        <div class="payment-processing">
            <div class="spinner"></div>
            <p>Processing your payment...</p>
            <p class="payment-status" id="payment-status">Please wait while we process your payment</p>
        </div>

        <form method="POST" id="payment-form" style="display:none;">
            <p>Payment approved successfully!</p>
            <button type="submit" name="confirm_payment" class="btn">Complete Order</button>
        </form>
    </div>
            <body class="<?php echo $current_theme === 'dark' ? 'dark-mode' : ''; ?>">
</main>

<footer class="footer">
    <div style="max-width:1200px;margin:0 auto;padding:0 20px;text-align:center;">
        <p>&copy; 2025 Timeless Tokens Jewelry | Created by Mariel</p>
    </div>
</footer>

<script>
// Simulate payment approval
setTimeout(() => {
    document.querySelector('.spinner').style.display = 'none';
    const status = document.getElementById('payment-status');
    status.textContent = 'Payment approved!';
    status.className = 'payment-status payment-success';
    document.getElementById('payment-form').style.display = 'block';
    document.querySelectorAll('.checkout-step')[3].classList.add('completed');
    // Auto-submit after 3 s
    setTimeout(() => document.getElementById('payment-form').submit(), 3000);
}, 3000);
</script>
    <?php include 'theme_toggle.php'; ?>
</body>
</html>