<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'init_theme.php';
$host = 'localhost';
$db   = '47_99_104_82'; 
$user = '47_99_104_82';
$pass = 'bXbwMzyJbk';
$charset = 'utf8mb4';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}

$positions = [
    ['title' => 'Jewelry Designer', 'desc' => 'Design unique, handcrafted jewelry pieces using CAD and traditional techniques.'],
    ['title' => 'Store Manager', 'desc' => 'Oversee daily operations of our flagship boutique, lead sales team, and ensure customer satisfaction.'],
    ['title' => 'Sales Associate', 'desc' => 'Provide exceptional in-store customer service and drive sales of luxury jewelry collections.'],
    ['title' => 'E-commerce Specialist', 'desc' => 'Manage online store, optimize product listings, and analyze digital sales performance.'],
    ['title' => 'Marketing Coordinator', 'desc' => 'Assist in planning campaigns, managing social media, and coordinating brand events.'],
    ['title' => 'Customer Service Representative', 'desc' => 'Handle inquiries via phone, email, and chat with professionalism and care.']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_application'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($full_name) || empty($email) || empty($position)) {
        $_SESSION['error'] = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Please enter a valid email address.";
    } else {
        $uploaded_files = [];
        $upload_dir = __DIR__ . '/uploads/resumes';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        if (!empty($_FILES['resume']['name'][0])) {
            $count = count($_FILES['resume']['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($_FILES['resume']['error'][$i] === UPLOAD_ERR_OK) {
                    $orig_name = $_FILES['resume']['name'][$i];
                    $tmp_name = $_FILES['resume']['tmp_name'][$i];
                    $size = $_FILES['resume']['size'][$i];
                    $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
                    $allowed = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png'];

                    if (in_array($ext, $allowed) && $size <= 5 * 1024 * 1024) {
                        $safe_name = time() . '_' . uniqid() . '_' . sanitize_filename($orig_name);
                        $path = $upload_dir . '/' . $safe_name;
                        if (move_uploaded_file($tmp_name, $path)) {
                            $uploaded_files[] = $path;
                        }
                    }
                }
            }
        }

        $stmt = $pdo->prepare("
            INSERT INTO job_applications 
            (full_name, email, phone, position_applied, cover_letter, resume_paths) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $full_name,
            $email,
            $phone,
            $position,
            $message,
            !empty($uploaded_files) ? serialize($uploaded_files) : null
        ]);

        $_SESSION['success'] = "Thank you! Your application for “" . htmlspecialchars($position, ENT_QUOTES) . "” has been submitted successfully.";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

function sanitize_filename($filename) {
    return preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Careers - Timeless Tokens Jewelry</title>
    <style>
        :root {
            --primary-gold: #d4af37;
            --dark-text: #2c2c2c;
            --light-bg: #f9f9f9;
            --border: #e0e0e0;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Georgia, serif;
            line-height: 1.6;
            color: var(--dark-text);
            background-color: #fff;
        }
        .header {
            background: linear-gradient(135deg, #1a1a1a 0%, #2c2c2c 100%);
            color: white;
            padding: 1rem 0;
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
            color: var(--primary-gold);
            text-decoration: none;
            font-size: 1.5rem;
            font-weight: bold;
        }
        .nav-menu { list-style: none; display: flex; gap: 1.5rem; }
        .nav-menu a { color: white; text-decoration: none; }
        .nav-menu a:hover { color: var(--primary-gold); }

        .container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 20px;
        }
        .page-title {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2.2rem;
            color: var(--dark-text);
        }
        .page-title:after {
            content: '';
            display: block;
            width: 80px;
            height: 3px;
            background: var(--primary-gold);
            margin: 1rem auto;
        }

        .positions {
            margin-bottom: 3rem;
        }
        .position-item {
            background: white;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.2rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }
        .position-title {
            font-size: 1.4rem;
            font-weight: bold;
            color: var(--dark-text);
            margin-bottom: 0.5rem;
        }
        .position-desc {
            color: #555;
            line-height: 1.6;
        }

        .application-form {
            background: #fafafa;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 2rem;
        }
        .form-group {
            margin-bottom: 1.2rem;
        }
        label {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: bold;
            color: #333;
        }
        input, textarea, select {
            width: 100%;
            padding: 0.7rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-family: Georgia, serif;
            font-size: 1rem;
        }
        textarea {
            height: 120px;
            resize: vertical;
        }
        .btn {
            background-color: var(--primary-gold);
            color: white;
            border: none;
            padding: 0.7rem 2rem;
            font-size: 1.1rem;
            cursor: pointer;
            border-radius: 4px;
            font-weight: bold;
        }
        .btn:hover {
            opacity: 0.9;
        }

        .alert {
            padding: 0.8rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            text-align: center;
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .footer-bottom {
            text-align: center;
            padding: 2rem;
            color: #666;
            border-top: 1px solid #eee;
            margin-top: 3rem;
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

<main class="container">
    <h1 class="page-title">Join Our Team</h1>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>
>
    <section class="positions">
        <h2 style="text-align: center; margin-bottom: 1.5rem; font-size: 1.6rem;">Current Openings</h2>
        <?php foreach ($positions as $pos): ?>
            <div class="position-item">
                <div class="position-title"><?= htmlspecialchars($pos['title']) ?></div>
                <div class="position-desc"><?= htmlspecialchars($pos['desc']) ?></div>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="application-form">
        <h2 style="text-align: center; margin-bottom: 1.5rem; font-size: 1.6rem;">Apply Now</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="full_name">Full Name *</label>
                <input type="text" id="full_name" name="full_name" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone">
            </div>
            <div class="form-group">
                <label for="position">Position Applying For *</label>
                <select id="position" name="position" required>
                    <option value="">Select a position</option>
                    <?php foreach ($positions as $pos): ?>
                        <option value="<?= htmlspecialchars($pos['title']) ?>"><?= htmlspecialchars($pos['title']) ?></option>
                    <?php endforeach; ?>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="message">Cover Letter / Message *</label>
                <textarea id="message" name="message" required placeholder="Tell us why you're a great fit for this role..."></textarea>
            </div>
            <div class="form-group">
                <label for="resume">Upload Resume/CV *</label>
                <input type="file" id="resume" name="resume[]" multiple required accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png">
                <small>Allowed formats: PDF, DOC, DOCX, TXT, JPG, PNG (Max 5MB per file)</small>
            </div>
            <button type="submit" name="submit_application" class="btn">Submit Application</button>
        </form>
    </section>
            <body class="<?php echo $current_theme === 'dark' ? 'dark-mode' : ''; ?>">
</main>

<footer class="footer-bottom">
    <p>&copy;  ©2025 Timeless Tokens Jewelry|Created by Mariel</p>
</footer>
<?php include 'theme_toggle.php'; ?>
</body>

</html>
