<?php
session_start();


if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['error'] = "Please log in to create a new post.";
    header('Location: login.php');
    exit;
}


$user_email = $_SESSION['user_email'] ?? 'anonymous@example.com';
$user_name  = $_SESSION['user_name']  ?? 'Anonymous';


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


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (empty($title) || empty($content)) {
        $_SESSION['error'] = "Title and content cannot be empty.";
    } elseif (strlen($title) > 255) {
        $_SESSION['error'] = "Title is too long (max 255 characters).";
    } else {
        $stmt = $pdo->prepare("INSERT INTO forum_posts (user_email, user_name, title, content) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_email, $user_name, $title, $content]);

        $_SESSION['success'] = "Your post has been published!";
        header('Location: forum.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>New Post - Discussion Forum</title>
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
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 20px;
        }
        .page-title {
            text-align: center;
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            color: var(--dark-text);
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
        input[type="text"], textarea {
            width: 100%;
            padding: 0.6rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-family: Georgia, serif;
            font-size: 1rem;
        }
        textarea {
            height: 150px;
            resize: vertical;
        }
        .btn {
            background-color: var(--primary-gold);
            color: white;
            border: none;
            padding: 0.6rem 1.5rem;
            font-size: 1rem;
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
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .back-link {
            display: inline-block;
            margin-top: 1rem;
            color: var(--primary-gold);
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
    <?php require_once 'theme_config.php'; ?>
<?php include 'theme_styles.php'; ?>
</head>
<body>
        <body class="<?php echo $current_theme === 'dark' ? 'dark-mode' : ''; ?>">
<header class="header">
    <nav class="nav-container">
        <a href="index.php" class="logo">Timeless Tokens</a>
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
    <h1 class="page-title">Create New Post</h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="title">Post Title</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required maxlength="255">
        </div>

        <div class="form-group">
            <label for="content">Content</label>
            <textarea id="content" name="content" required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn">Publish Post</button>
        <a href="forum.php" class="back-link">← Back to Forum</a>
    </form>
</main>
    <?php include 'theme_toggle.php'; ?>
</body>

</html>
