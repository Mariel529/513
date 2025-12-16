<?php
/**
 * ICTWEB513 - Admin Products Management
 * Student: [Your Name]
 * Student ID: [Your Student ID]
 * Date: 2024
 */
session_start();

// Check admin login status
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// Load product data
$productsFile = 'data/products.json';
$products = [];

if (file_exists($productsFile)) {
    $productsJson = file_get_contents($productsFile);
    $products = json_decode($productsJson, true) ?: [];
}

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $newProduct = [
                    'id' => uniqid(),
                    'name' => trim($_POST['name']),
                    'description' => trim($_POST['description']),
                    'price' => floatval($_POST['price']),
                    'original_price' => floatval($_POST['original_price']),
                    'discount_percent' => floatval($_POST['discount_percent']),
                    'category' => trim($_POST['category']),
                    'image_url' => trim($_POST['image_url'])
                ];
                $products[] = $newProduct;
                $success = "Product added successfully!";
                break;
                
            case 'update':
                $productId = $_POST['product_id'];
                foreach ($products as &$product) {
                    if ($product['id'] == $productId) {
                        $product['name'] = trim($_POST['name']);
                        $product['description'] = trim($_POST['description']);
                        $product['price'] = floatval($_POST['price']);
                        $product['original_price'] = floatval($_POST['original_price']);
                        $product['discount_percent'] = floatval($_POST['discount_percent']);
                        $product['category'] = trim($_POST['category']);
                        $product['image_url'] = trim($_POST['image_url']);
                        break;
                    }
                }
                $success = "Product updated successfully!";
                break;
                
            case 'delete':
                $productId = $_POST['product_id'];
                $products = array_filter($products, function($product) use ($productId) {
                    return $product['id'] != $productId;
                });
                $products = array_values($products); // Reindex array
                $success = "Product deleted successfully!";
                break;
        }
        
        // Save to JSON file
        file_put_contents($productsFile, json_encode($products, JSON_PRETTY_PRINT));
    }
}

// Get product for editing
$editProduct = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    foreach ($products as $product) {
        if ($product['id'] == $_GET['edit']) {
            $editProduct = $product;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Products - Timeless Tokens Jewelry</title>
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
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 20px;
        }
        
        .page-title {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2.5rem;
            color: var(--dark-text);
        }
        
        .admin-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }
        
        .btn-primary {
            background-color: var(--primary-gold);
            color: var(--dark-text);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-gold);
        }
        
        .btn-secondary {
            background-color: #95a5a6;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #7f8c8d;
        }
        
        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
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
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .product-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .product-content {
            padding: 1.5rem;
        }
        
        .product-name {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: var(--dark-text);
        }
        
        .product-price {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary-gold);
            margin-bottom: 0.5rem;
        }
        
        .product-category {
            color: var(--light-text);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        .product-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .form-section {
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid var(--border);
        }
        
        .form-title {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: var(--dark-text);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark-text);
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 1rem;
            font-family: inherit;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .form-full {
            grid-column: 1 / -1;
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
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
            }
            
            .admin-actions {
                flex-direction: column;
                align-items: flex-start;
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
            <ul class="nav-menu">
                <a href="admin_products.php">Products</a>
                <a href="admin_jobs.php" class="active">Jobs</a>
                <a href="admin_feedback.php" class="active">Feedback</a>
                <a href="admin_subscribers.php" class="active">Subscribers</a>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="container">
        <h1 class="page-title">Product Management</h1>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <div class="admin-actions">
            <h2>Welcome, <?php echo $_SESSION['admin_username']; ?>!</h2>
            <div>
                <a href="index.php" class="btn btn-secondary">View Site</a>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
        
        <!-- Add/Edit Product Form -->
        <section class="form-section">
            <h2 class="form-title"><?php echo $editProduct ? 'Edit Product' : 'Add New Product'; ?></h2>
            <form method="POST" action="">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <input type="text" id="name" name="name" required 
                               value="<?php echo $editProduct ? htmlspecialchars($editProduct['name']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Lockets" <?php echo ($editProduct && $editProduct['category'] == 'Lockets') ? 'selected' : ''; ?>>Lockets</option>
                            <option value="Necklaces" <?php echo ($editProduct && $editProduct['category'] == 'Necklaces') ? 'selected' : ''; ?>>Necklaces</option>
                            <option value="Bracelets" <?php echo ($editProduct && $editProduct['category'] == 'Bracelets') ? 'selected' : ''; ?>>Bracelets</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price ($)</label>
                        <input type="number" id="price" name="price" step="0.01" required 
                               value="<?php echo $editProduct ? $editProduct['price'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="original_price">Original Price ($)</label>
                        <input type="number" id="original_price" name="original_price" step="0.01"
                               value="<?php echo $editProduct ? ($editProduct['original_price'] ?? '') : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="discount_percent">Discount Percent (%)</label>
                        <input type="number" id="discount_percent" name="discount_percent" step="0.01" min="0" max="100"
                               value="<?php echo $editProduct ? ($editProduct['discount_percent'] ?? '') : ''; ?>">
                    </div>
                    
                    <div class="form-group form-full">
                        <label for="image_url">Image URL</label>
                        <input type="url" id="image_url" name="image_url" required 
                               value="<?php echo $editProduct ? htmlspecialchars($editProduct['image_url']) : ''; ?>">
                    </div>
                    
                    <div class="form-group form-full">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" required><?php echo $editProduct ? htmlspecialchars($editProduct['description']) : ''; ?></textarea>
                    </div>
                </div>
                
                <div class="form-group">
                    <?php if ($editProduct): ?>
                        <input type="hidden" name="product_id" value="<?php echo $editProduct['id']; ?>">
                        <button type="submit" name="action" value="update" class="btn btn-primary">Update Product</button>
                        <a href="admin_products.php" class="btn btn-secondary">Cancel</a>
                    <?php else: ?>
                        <button type="submit" name="action" value="add" class="btn btn-primary">Add Product</button>
                    <?php endif; ?>
                </div>
            </form>
        </section>
        
        <!-- Products List -->
        <section>
            <h2 style="margin-bottom: 1.5rem;">Current Products (<?php echo count($products); ?>)</h2>
            
            <?php if (empty($products)): ?>
                <div style="text-align: center; padding: 3rem; color: var(--light-text);">
                    <h3>No Products Found</h3>
                    <p>Add your first product using the form above.</p>
                </div>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="product-image"
                             onerror="this.src='https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=300'">
                        
                        <div class="product-content">
                            <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <div class="product-price">
                                <?php if (isset($product['original_price']) && $product['original_price'] > $product['price']): ?>
                                    <span style="text-decoration: line-through; color: var(--light-text); font-size: 0.9rem;">
                                        $<?php echo number_format($product['original_price'], 0); ?>
                                    </span>
                                    <strong>$<?php echo number_format($product['price'], 0); ?></strong>
                                <?php else: ?>
                                    <strong>$<?php echo number_format($product['price'], 0); ?></strong>
                                <?php endif; ?>
                            </div>
                            <div class="product-category"><?php echo htmlspecialchars($product['category']); ?></div>
                            
                            <div class="product-actions">
                                <a href="?edit=<?php echo $product['id']; ?>" class="btn btn-primary">Edit</a>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" name="action" value="delete" class="btn btn-danger" 
                                            onclick="return confirm('Are you sure you want to delete this product?')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        <body class="<?php echo isset($current_theme) && $current_theme === 'dark' ? 'dark-mode' : ''; ?>">
    </main>

    <!-- Footer -->
     <footer class="footer-bottom">
        <div>
            <p>&copy; © 2025 Timeless Tokens Jewelry | Created by Mariel</p>
        </div>
    </footer>

</body>
</html>