<?php
/**
 * Contact Page - Subscriber List
 * Timeless Tokens Jewelry
 */

// Start output buffering to prevent headers already sent error
ob_start();

// Include theme initialization at the VERY TOP
require_once 'init_theme.php';
// Database Configuration
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
    
    $connection_status = "✅ Database connection successful";
    
} catch (PDOException $e) {
    $connection_status = "❌ Database connection failed: " . $e->getMessage();
    $pdo = null;
}

// Query All Subscribers
if ($pdo) {
    try {
        // Select fields for display - adjusted according to table structure
        $subscribers_sql = "SELECT 
                               id,
                               first_name, 
                               last_name, 
                               email, 
                               phone,
                               city,
                               status,
                               created_at,
                               date_of_birth,
                               source,
                               state,
                               country,
                               address_line_1
                           FROM wp_fc_subscribers 
                           WHERE status IN ('subscribed', 'pending')
                           ORDER BY created_at DESC";
        
        $subscribers_stmt = $pdo->prepare($subscribers_sql);
        $subscribers_stmt->execute();
        $subscribers = $subscribers_stmt->fetchAll();
        $total_subscribers = count($subscribers);
        
    } catch (PDOException $e) {
        $subscribers = array();
        $total_subscribers = 0;
        $subscribers_error = "Failed to load subscribers: " . $e->getMessage();
    }
} else {
    $subscribers = array();
    $total_subscribers = 0;
    $subscribers_error = "Cannot load subscribers: No database connection.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Timeless Tokens Jewelry</title>
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
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-gold);
            text-decoration: none;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 10px;
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
            padding: 0 20px 3rem;
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
        
        /* Subscriber Management Section */
        .management-section {
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            border: 1px solid var(--border);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .section-header h2 {
            font-size: 1.8rem;
            color: var(--dark-text);
            margin-bottom: 0.5rem;
        }
        
        .section-header p {
            color: var(--light-text);
            font-size: 1.1rem;
        }
        
        .actions {
            display: flex;
            gap: 15px;
            margin-bottom: 2rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            font-family: inherit;
        }
        
        .btn-primary {
            background: var(--primary-gold);
            color: var(--dark-text);
        }
        
        .btn-primary:hover {
            background: var(--secondary-gold);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
            transform: translateY(-2px);
        }
        
        .btn-external {
            background: #3498db;
            color: white;
            font-size: 1.1rem;
            padding: 15px 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-external:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }
        
        .status-message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
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
        
        .database-status {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #bee5eb;
            font-weight: 500;
        }
        
        .subscribers-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            font-size: 0.95rem;
        }
        
        .subscribers-table th {
            background: #34495e;
            color: white;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #2c3e50;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .subscribers-table td {
            padding: 12px 12px;
            border-bottom: 1px solid #ecf0f1;
            color: var(--dark-text);
        }
        
        .subscribers-table tr:hover {
            background: #f8f9fa;
        }
        
        .subscribers-table tr:last-child td {
            border-bottom: none;
        }
        
        .total-subscribers {
            text-align: center;
            margin-top: 25px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            font-weight: 500;
            color: var(--dark-text);
            font-size: 1.1rem;
            border: 1px solid var(--border);
        }
        
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--primary-gold), transparent);
            margin: 30px 0;
        }
        
        /* Registration Info Box */
        .registration-info {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
            border-left: 4px solid #3498db;
        }
        
        .registration-info h3 {
            color: #3498db;
            margin-bottom: 10px;
            font-size: 1.3rem;
        }
        
        .registration-info p {
            margin: 8px 0;
            color: #495057;
            line-height: 1.5;
        }
        
        .registration-info .highlight {
            color: #d4af37;
            font-weight: bold;
        }
        
        .registration-info .external-link {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
        }
        
        .registration-info .external-link:hover {
            text-decoration: underline;
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
            background-color: #1a1a1a;
            color: var(--white);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .actions {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 280px;
                max-width: 100%;
            }
            
            .btn-external {
                padding: 12px 20px;
                font-size: 1rem;
            }
            
            .subscribers-table {
                font-size: 0.9em;
            }
            
            .subscribers-table th,
            .subscribers-table td {
                padding: 8px 10px;
            }
            
            .hero h1 {
                font-size: 2rem;
            }
            
            .page-title {
                font-size: 1.8rem;
            }
            
            .registration-info {
                padding: 15px;
                margin: 20px 0;
            }
        }
        
        @media (max-width: 480px) {
            .nav-container {
                flex-direction: column;
                gap: 1rem;
            }
            
            .hero {
                padding: 3rem 1rem;
            }
            
            .hero h1 {
                font-size: 1.8rem;
            }
            
            .management-section {
                padding: 1.5rem;
            }
            
            .subscribers-table {
                font-size: 0.8em;
                display: block;
                overflow-x: auto;
            }
            
            .btn-external {
                width: 100%;
            }
        }
        
        /* Loading Animation */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: white;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
        <h1>Contact Us</h1>
        <p>Get in touch with Timeless Tokens Jewelry</p>
    </section>

    <!-- Main Content -->
    <main class="container">
        <h1 class="page-title">Subscriber Management System</h1>
        
        <div class="management-section">
            <div class="section-header">
                <h2>Subscriber List</h2>
                <div class="actions">
                    <button class="btn btn-external" onclick="openExternalRegistration()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16" style="flex-shrink: 0;">
                            <path d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5z"/>
                            <path d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0v-5z"/>
                        </svg>
                        Add New Subscriber
                    </button>
                </div>
                
                <div class="registration-info">
                    <h3>External Registration Process</h3>
                    <p>Click the "Add New Subscriber" button to open our official contact form.</p>
                    <p>The registration will open in a new window. After completing the form and submitting it, close the window and return to this page.</p>
                    <p><span class="highlight">Note:</span> The subscriber list will automatically refresh when you return to this page.</p>
                    <p>External registration link: 
                        <a href="http://47.99.104.82/contact-us/" class="external-link" target="_blank">
                            http://47.99.104.82/contact-us/
                        </a>
                    </p>
                </div>
            </div>
            
            <div class="database-status">
                <?php echo $connection_status; ?>
            </div>
            
            <div class="divider"></div>
            
            <?php if (!empty($subscribers)): ?>
                <table class="subscribers-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>NAME</th>
                            <th>EMAIL</th>
                            <th>PHONE</th>
                            <th>LOCATION</th>
                            <th>ADDRESS LINE 1</th>
                            <th>DATE OF BIRTH</th>
                            <th>SOURCE</th>
                            <th>STATUS</th>
                            <th>CREATED</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subscribers as $subscriber): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($subscriber['id'] ?? ''); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars(($subscriber['first_name'] ?? '') . ' ' . ($subscriber['last_name'] ?? '')); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($subscriber['email'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($subscriber['phone'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php
                                    $location_parts = [];
                                    if (!empty($subscriber['city'])) $location_parts[] = $subscriber['city'];
                                    if (!empty($subscriber['state'])) $location_parts[] = $subscriber['state'];
                                    if (!empty($subscriber['country'])) $location_parts[] = $subscriber['country'];
                                    echo htmlspecialchars(implode(', ', array_slice($location_parts, 0, 2)));
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($subscriber['address_line_1'] ?? 'N/A'); ?></td>
                                <td><?php echo !empty($subscriber['date_of_birth']) ? date('Y-m-d', strtotime($subscriber['date_of_birth'])) : 'N/A'; ?></td>
                                <td>
                                    <?php 
                                    if (!empty($subscriber['source'])) {
                                        echo '<span style="
                                            padding: 3px 8px;
                                            border-radius: 3px;
                                            font-size: 0.9em;
                                            font-weight: bold;
                                            background-color: #e3f2fd;
                                            color: #0d47a1;
                                            border: 1px solid #bbdefb;
                                        ">' . htmlspecialchars($subscriber['source']) . '</span>';
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span style="
                                        padding: 3px 8px;
                                        border-radius: 3px;
                                        font-size: 0.8em;
                                        font-weight: bold;
                                        background-color: <?php echo ($subscriber['status'] == 'subscribed') ? '#d4edda' : '#fff3cd'; ?>;
                                        color: <?php echo ($subscriber['status'] == 'subscribed') ? '#155724' : '#856404'; ?>;
                                        border: 1px solid <?php echo ($subscriber['status'] == 'subscribed') ? '#c3e6cb' : '#ffeaa7'; ?>;
                                    ">
                                        <?php echo htmlspecialchars($subscriber['status'] ?? ''); ?>
                                    </span>
                                </td>
                                <td><?php 
                                    if (!empty($subscriber['created_at'])) {
                                        echo date('Y-m-d', strtotime($subscriber['created_at']));
                                    }
                                ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="status-message error">
                    No subscribers found in the database.
                    <?php if (isset($subscribers_error)): ?>
                        <br><small>Error: <?php echo $subscribers_error; ?></small>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="total-subscribers">
                Total subscribers: <?php echo $total_subscribers; ?>
            </div>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="footer-bottom">
        <div>
            <p>&copy; © 2025 Timeless Tokens Jewelry | Created by Mariel</p>
        </div>
    </footer>
    
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
        <p>Processing registration...</p>
        <p>Please complete the form in the new window</p>
    </div>
    
    <script>
        // Function to open external registration page
        function openExternalRegistration() {
            // Show loading overlay
            document.getElementById('loadingOverlay').style.display = 'flex';
            
            // Calculate centered position for the popup window
            const width = 1000;
            const height = 800;
            const left = (window.screen.width - width) / 2;
            const top = (window.screen.height - height) / 2;
            
            // Open external registration page in a centered popup window
            const registrationWindow = window.open(
                'http://47.99.104.82/contact-us/',
                'TimelessTokensRegistration',
                `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes,toolbar=no,location=yes,status=yes`
            );
            
            if (registrationWindow) {
                // Focus on the new window
                registrationWindow.focus();
                
                // Store window reference
                window.registrationWindow = registrationWindow;
                
                // Hide loading overlay after window opens
                setTimeout(() => {
                    document.getElementById('loadingOverlay').style.display = 'none';
                }, 1000);
                
                // Check if window is closed periodically
                const checkInterval = setInterval(function() {
                    if (registrationWindow.closed) {
                        clearInterval(checkInterval);
                        
                        // Show processing message
                        showSuccessMessage('Registration window closed. Refreshing subscriber list...');
                        
                        // Refresh page after 3 seconds to show updated list
                        setTimeout(function() {
                            window.location.reload();
                        }, 3000);
                    }
                }, 2000);
                
                // Also check for inactivity (if window loses focus for too long)
                let windowActive = true;
                registrationWindow.addEventListener('focus', () => {
                    windowActive = true;
                });
                
                registrationWindow.addEventListener('blur', () => {
                    windowActive = false;
                });
                
            } else {
                // If popup is blocked, hide loading overlay and open in new tab
                document.getElementById('loadingOverlay').style.display = 'none';
                
                const userConfirmed = confirm(
                    'Popup blocked by your browser.\n\n' +
                    'To register a new subscriber, we need to open our contact form.\n\n' +
                    'Click OK to open in a new tab, or Cancel to stay on this page.'
                );
                
                if (userConfirmed) {
                    window.open('http://47.99.104.82/contact-us/', '_blank');
                    
                    // Show message about manual refresh
                    showSuccessMessage(
                        'Registration opened in new tab. ' +
                        'Please complete the form and return to this page. ' +
                        'Click the refresh button or press F5 to update the list.'
                    );
                }
            }
        }
        
        // Function to show success message
        function showSuccessMessage(message) {
            // Remove any existing success messages
            const existingMessages = document.querySelectorAll('.status-message.success');
            existingMessages.forEach(msg => msg.remove());
            
            // Create new success message
            const successMessage = document.createElement('div');
            successMessage.className = 'status-message success';
            successMessage.innerHTML = `✅ ${message}`;
            
            // Insert before the database status
            const dbStatus = document.querySelector('.database-status');
            if (dbStatus) {
                dbStatus.parentNode.insertBefore(successMessage, dbStatus);
            }
            
            // Scroll to the message
            successMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        
        // Listen for messages from external registration page
        window.addEventListener('message', function(event) {
            // For security, verify the origin if possible
            // if (event.origin !== "http://47.99.104.82") return;
            
            // Check if message indicates registration completion
            if (event.data && event.data.type === 'registration_completed') {
                console.log('Registration completed message received from external page');
                
                // Close the popup window if it's open
                if (window.registrationWindow && !window.registrationWindow.closed) {
                    window.registrationWindow.close();
                }
                
                // Show success message
                showSuccessMessage('Registration completed successfully! Refreshing subscriber list...');
                
                // Refresh page to show updated list
                setTimeout(function() {
                    window.location.reload();
                }, 2000);
            }
        });
        
        // Add refresh button functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Add refresh button to the actions section
            const actionsDiv = document.querySelector('.actions');
            if (actionsDiv) {
                const refreshButton = document.createElement('button');
                refreshButton.className = 'btn btn-secondary';
                refreshButton.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" style="vertical-align: middle; margin-right: 8px;" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
                        <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
                    </svg>
                    Refresh List
                `;
                refreshButton.onclick = function() {
                    window.location.reload();
                };
                actionsDiv.appendChild(refreshButton);
            }
            
            // Show message if there was a recent registration
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('registration') === 'success') {
                showSuccessMessage('Registration completed! Subscriber list updated.');
                
                // Remove the parameter from URL without refreshing
                const newUrl = window.location.pathname + window.location.search.replace(/[?&]registration=success/, '');
                window.history.replaceState({}, document.title, newUrl);
            }
        });
        
        // Auto-refresh every 30 seconds to keep list updated
        setInterval(function() {
            console.log('Auto-refreshing subscriber list...');
            // Just refresh without showing message
            window.location.reload();
        }, 30000); // 30 seconds
    </script>
    <?php include 'theme_toggle.php'; ?>
</body>
</html>
<?php ob_end_flush(); ?>