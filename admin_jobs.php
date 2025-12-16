<?php
/**
 * ICTWEB513 - Admin Jobs Management
 * Student: [Your Name]
 * Student ID: [Your Student ID]
 * Date: 2024
 */
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// Database configuration
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Create job_positions table if it doesn't exist
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS job_positions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
} catch (PDOException $e) {
    // Table exists or creation failed — continue execution
}

// Handle adding a new position
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_position'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (!empty($title) && !empty($description)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO job_positions (title, description) VALUES (?, ?)");
            $stmt->execute([$title, $description]);
            $_SESSION['success'] = "Position added successfully!";
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error adding position: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "Please fill in both title and description.";
    }
}

// Handle deleting a position
if (isset($_GET['delete_position']) && is_numeric($_GET['delete_position'])) {
    $position_id = (int)$_GET['delete_position'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM job_positions WHERE id = ?");
        $stmt->execute([$position_id]);
        $_SESSION['success'] = "Position deleted successfully!";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting position: " . $e->getMessage();
    }
}

// Handle deleting an application
if (isset($_GET['delete_application']) && is_numeric($_GET['delete_application'])) {
    $app_id = (int)$_GET['delete_application'];
    
    try {
        // First, retrieve file paths to delete uploaded files
        $stmt = $pdo->prepare("SELECT resume_paths FROM job_applications WHERE id = ?");
        $stmt->execute([$app_id]);
        $app = $stmt->fetch();
        
        // Delete physical files
        if ($app && $app['resume_paths']) {
            $file_paths = unserialize($app['resume_paths']);
            foreach ($file_paths as $path) {
                if (file_exists($path)) {
                    unlink($path);
                }
            }
        }
        
        // Delete database record
        $stmt = $pdo->prepare("DELETE FROM job_applications WHERE id = ?");
        $stmt->execute([$app_id]);
        
        $_SESSION['success'] = "Application deleted successfully!";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting application: " . $e->getMessage();
    }
}

// Fetch all job positions
try {
    $positions_stmt = $pdo->query("SELECT * FROM job_positions ORDER BY id DESC");
    $positions = $positions_stmt->fetchAll();
} catch (PDOException $e) {
    $positions = [];
    $_SESSION['error'] = "Error fetching positions: " . $e->getMessage();
}

// Fetch all applications — handle potential column name variations
try {
    // Inspect table structure to determine correct timestamp column
    $columns_stmt = $pdo->query("SHOW COLUMNS FROM job_applications");
    $columns = $columns_stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $order_column = 'id'; // Default fallback
    
    if (in_array('applied_at', $columns)) {
        $order_column = 'applied_at';
    } elseif (in_array('created_at', $columns)) {
        $order_column = 'created_at';
    } elseif (in_array('submission_date', $columns)) {
        $order_column = 'submission_date';
    }
    
    $applications_stmt = $pdo->query("SELECT * FROM job_applications ORDER BY $order_column DESC");
    $applications = $applications_stmt->fetchAll();
} catch (PDOException $e) {
    $applications = [];
    $_SESSION['error'] = "Error fetching applications: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Jobs Management - Timeless Tokens Jewelry</title>
    <style>
        :root {
            --primary-gold: #d4af37;
            --secondary-gold: #b8941f;
            --dark-text: #2c2c2c;
            --light-text: #666;
            --background: #fefefe;
            --border: #e8e8e8;
            --white: #ffffff;
            --success: #28a745;
            --danger: #dc3545;
            --info: #17a2b8;
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
            background-color: #f5f7fa;
        }
        
        .header {
            background: linear-gradient(135deg, #1a1a1a 0%, #2c2c2c 100%);
            color: var(--white);
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-gold);
            text-decoration: none;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .admin-nav {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }
        
        .admin-nav a {
            color: var(--white);
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .admin-nav a:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .admin-nav a.active {
            background-color: var(--primary-gold);
            color: var(--dark-text);
        }
        
        .admin-user {
            color: var(--primary-gold);
            font-weight: bold;
        }
        
        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 20px;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border);
        }
        
        .admin-header h1 {
            font-size: 2rem;
            color: var(--dark-text);
        }
        
        .btn {
            padding: 0.5rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background-color: var(--primary-gold);
            color: var(--dark-text);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-gold);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .btn-danger {
            background-color: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }
        
        .btn-info {
            background-color: var(--info);
            color: white;
        }
        
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 4px;
            font-weight: bold;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .section {
            background: var(--white);
            border-radius: 8px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .section-title {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: var(--dark-text);
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
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
            font-family: inherit;
            font-size: 1rem;
        }
        
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .table-container {
            overflow-x: auto;
            margin-top: 1rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: var(--dark-text);
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .application-files {
            margin-top: 0.5rem;
        }
        
        .application-files a {
            color: var(--info);
            text-decoration: none;
            margin-right: 1rem;
        }
        
        .application-files a:hover {
            text-decoration: underline;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--light-text);
        }
        
        .actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .footer {
            text-align: center;
            padding: 2rem;
            color: var(--light-text);
            margin-top: 3rem;
            border-top: 1px solid var(--border);
        }
        
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 1rem;
            }
            
            .admin-nav {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            table {
                font-size: 0.875rem;
            }
            
            th, td {
                padding: 0.75rem 0.5rem;
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
            <div class="admin-nav">
                <a href="admin_products.php">Products</a>
                <a href="admin_jobs.php" class="active">Jobs</a>
                <a href="admin_feedback.php">Feedback</a>
                <a href="admin_subscribers.php">Subscribers</a>
                <a href="logout.php">Logout</a>
            </div>
        </nav>
    </header>

    <div class="container">
        <!-- Admin Header -->
        <div class="admin-header">
            <h1>📋 Jobs Management</h1>
        </div>

        <!-- Alerts -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Section 1: Add New Position -->
        <section class="section">
            <h2 class="section-title">➕ Add New Job Position</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="title">Position Title *</label>
                    <input type="text" id="title" name="title" required 
                           placeholder="e.g., Senior Jewelry Designer">
                </div>
                
                <div class="form-group">
                    <label for="description">Position Description *</label>
                    <textarea id="description" name="description" required 
                              placeholder="Describe the position, responsibilities, and requirements..."></textarea>
                </div>
                
                <button type="submit" name="add_position" class="btn btn-primary">
                    Add Position
                </button>
            </form>
        </section>

        <!-- Section 2: Current Positions -->
        <section class="section">
            <h2 class="section-title">📋 Current Job Positions (<?php echo count($positions); ?>)</h2>
            
            <?php if (empty($positions)): ?>
                <div class="empty-state">
                    <p>No job positions found. Add your first position above.</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Position Title</th>
                                <th>Description</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($positions as $position): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($position['id']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($position['title']); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars(substr($position['description'], 0, 150)); ?>
                                        <?php if (strlen($position['description']) > 150): ?>...<?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if (isset($position['created_at'])) {
                                            echo date('M d, Y', strtotime($position['created_at']));
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                    <td class="actions">
                                        <a href="?delete_position=<?php echo $position['id']; ?>" 
                                           class="btn btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this position?')">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <!-- Section 3: Job Applications -->
        <section class="section">
            <h2 class="section-title">📥 Job Applications (<?php echo count($applications); ?>)</h2>
            
            <?php if (empty($applications)): ?>
                <div class="empty-state">
                    <p>No job applications received yet.</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Applicant</th>
                                <th>Position Applied</th>
                                <th>Contact Info</th>
                                <th>Cover Letter</th>
                                <th>Resumes</th>
                                <th>Applied</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($app['id']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($app['full_name']); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($app['position_applied']); ?>
                                    </td>
                                    <td>
                                        📧 <?php echo htmlspecialchars($app['email']); ?><br>
                                        📞 <?php echo htmlspecialchars($app['phone'] ?: 'N/A'); ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $cover_letter = $app['cover_letter'] ?? '';
                                        echo htmlspecialchars(substr($cover_letter, 0, 100)); 
                                        if (strlen($cover_letter) > 100): ?>...<?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($app['resume_paths'])): ?>
                                            <?php 
                                            $files = @unserialize($app['resume_paths']);
                                            if ($files !== false && is_array($files)) {
                                                foreach ($files as $file): 
                                                    $filename = basename($file);
                                            ?>
                                                <div class="application-files">
                                                    <a href="<?php echo htmlspecialchars($file); ?>" target="_blank" download>
                                                        📄 <?php echo htmlspecialchars($filename); ?>
                                                    </a>
                                                </div>
                                            <?php 
                                                endforeach; 
                                            } else {
                                                echo '<em>File paths error</em>';
                                            }
                                            ?>
                                        <?php else: ?>
                                            <em>No files uploaded</em>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        // Try multiple possible timestamp columns
                                        $date_fields = ['applied_at', 'created_at', 'submission_date', 'applied_date'];
                                        $date_displayed = false;
                                        
                                        foreach ($date_fields as $field) {
                                            if (isset($app[$field]) && !empty($app[$field])) {
                                                echo date('M d, Y', strtotime($app[$field]));
                                                $date_displayed = true;
                                                break;
                                            }
                                        }
                                        
                                        if (!$date_displayed) {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                    <td class="actions">
                                        <a href="?delete_application=<?php echo $app['id']; ?>" 
                                           class="btn btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this application? This action cannot be undone.')">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2025 Timeless Tokens Jewelry Admin Panel | Created by Mariel</p>
        <p style="font-size: 0.875rem; margin-top: 0.5rem; color: #888;">
            Total Positions: <?php echo count($positions); ?> | 
            Total Applications: <?php echo count($applications); ?>
        </p>
    </footer>
</body>
</html>