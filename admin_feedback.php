<?php
/**
 * ICTWEB513 - Admin Feedback Management
 * Student: [Your Name]
 * Student ID: [Your Student ID]
 * Date: 2024
 */
session_start();

// Check if logged in
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

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $feedback_id = (int)$_POST['feedback_id'];
        $status = $_POST['status'] ?? 'pending';
        $admin_reply = trim($_POST['admin_reply'] ?? '');
        
        try {
            $stmt = $pdo->prepare("
                UPDATE wp_feedback_data 
                SET status = ?, 
                    admin_reply = ?, 
                    replied_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$status, $admin_reply, $feedback_id]);
            
            $_SESSION['success'] = "Feedback status updated successfully!";
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error updating feedback: " . $e->getMessage();
        }
    }
}

// Handle feedback deletion
if (isset($_GET['delete_feedback']) && is_numeric($_GET['delete_feedback'])) {
    $feedback_id = (int)$_GET['delete_feedback'];
    
    try {
        // First get file paths to delete files
        $stmt = $pdo->prepare("SELECT file_names FROM wp_feedback_data WHERE id = ?");
        $stmt->execute([$feedback_id]);
        $feedback = $stmt->fetch();
        
        // Delete files
        if ($feedback && $feedback['file_names']) {
            $file_paths = @unserialize($feedback['file_names']);
            if ($file_paths !== false && is_array($file_paths)) {
                foreach ($file_paths as $path) {
                    if (file_exists($path)) {
                        unlink($path);
                    }
                }
            }
        }
        
        // Delete database record
        $stmt = $pdo->prepare("DELETE FROM wp_feedback_data WHERE id = ?");
        $stmt->execute([$feedback_id]);
        
        $_SESSION['success'] = "Feedback deleted successfully!";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting feedback: " . $e->getMessage();
    }
}

// Get feedback statistics
try {
    $stats_stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(status = 'pending') as pending,
            SUM(status = 'read') as read_count,
            SUM(status = 'replied') as replied,
            SUM(status = 'resolved') as resolved
        FROM wp_feedback_data
    ");
    $stats = $stats_stmt->fetch();
} catch (PDOException $e) {
    $stats = ['total' => 0, 'pending' => 0, 'read_count' => 0, 'replied' => 0, 'resolved' => 0];
}

// Get all feedback
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

try {
    $query = "SELECT * FROM wp_feedback_data WHERE 1=1";
    $params = [];
    
    if ($filter === 'pending') {
        $query .= " AND (status = 'pending' OR status IS NULL OR status = '')";
    } elseif ($filter === 'read') {
        $query .= " AND status = 'read'";
    } elseif ($filter === 'replied') {
        $query .= " AND status = 'replied'";
    } elseif ($filter === 'resolved') {
        $query .= " AND status = 'resolved'";
    }
    
    if (!empty($search)) {
        $query .= " AND (user_name LIKE ? OR user_email LIKE ? OR user_message LIKE ?)";
        $search_term = "%$search%";
        $params = array_merge($params, [$search_term, $search_term, $search_term]);
    }
    
    $query .= " ORDER BY submission_date DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $feedbacks = $stmt->fetchAll();
} catch (PDOException $e) {
    $feedbacks = [];
    $_SESSION['error'] = "Error fetching feedback: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Feedback Management - Timeless Tokens Jewelry</title>
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
            --warning: #ffc107;
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
        
        .btn-success {
            background-color: var(--success);
            color: white;
        }
        
        .btn-warning {
            background-color: var(--warning);
            color: var(--dark-text);
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: var(--white);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: var(--light-text);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .stat-total .stat-number { color: var(--dark-text); }
        .stat-pending .stat-number { color: var(--warning); }
        .stat-read .stat-number { color: var(--info); }
        .stat-replied .stat-number { color: var(--primary-gold); }
        .stat-resolved .stat-number { color: var(--success); }
        
        .filters {
            background: var(--white);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
        }
        
        .filter-btn {
            padding: 0.5rem 1rem;
            border: 1px solid var(--border);
            background: transparent;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .filter-btn.active {
            background-color: var(--primary-gold);
            color: var(--dark-text);
            border-color: var(--primary-gold);
        }
        
        .filter-btn:hover:not(.active) {
            background-color: #f8f9fa;
        }
        
        .search-box {
            flex: 1;
            min-width: 250px;
        }
        
        .search-box input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-family: inherit;
        }
        
        .table-container {
            background: var(--white);
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow-x: auto;
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
        
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: bold;
        }
        
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-read { background-color: #d1ecf1; color: #0c5460; }
        .status-replied { background-color: #d4edda; color: #155724; }
        .status-resolved { background-color: #c3e6cb; color: #155724; }
        
        .actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-small {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: var(--white);
            border-radius: 8px;
            padding: 2rem;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }
        
        .modal-title {
            font-size: 1.5rem;
            color: var(--dark-text);
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--light-text);
        }
        
        .feedback-details {
            margin-bottom: 2rem;
        }
        
        .detail-item {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }
        
        .detail-label {
            font-weight: bold;
            color: var(--dark-text);
            margin-bottom: 0.5rem;
        }
        
        .detail-value {
            color: var(--light-text);
            white-space: pre-wrap;
        }
        
        .attachments {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .attachment-link {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background-color: #f8f9fa;
            border: 1px solid var(--border);
            border-radius: 4px;
            color: var(--info);
            text-decoration: none;
            font-size: 0.875rem;
        }
        
        .attachment-link:hover {
            background-color: #e9ecef;
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
        
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-family: inherit;
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--light-text);
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
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            table {
                font-size: 0.875rem;
            }
            
            th, td {
                padding: 0.75rem 0.5rem;
            }
        }
        
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <body class="<?php echo isset($current_theme) && $current_theme === 'dark' ? 'dark-mode' : ''; ?>">
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
                <a href="admin_feedback.php" class="active">Feedback</a>
                <a href="admin_subscribers.php" class="active">Subscribers</a>
                <li><a href="logout.php">Logout</a></li>
            </div>
        </nav>
    </header>

    <div class="container">
        <!-- Admin Header -->
        <div class="admin-header">
            <h1>📩 Feedback Management</h1>
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

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card stat-total">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Feedback</div>
            </div>
            <div class="stat-card stat-pending">
                <div class="stat-number"><?php echo $stats['pending']; ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card stat-read">
                <div class="stat-number"><?php echo $stats['read_count']; ?></div>
                <div class="stat-label">Read</div>
            </div>
            <div class="stat-card stat-replied">
                <div class="stat-number"><?php echo $stats['replied']; ?></div>
                <div class="stat-label">Replied</div>
            </div>
            <div class="stat-card stat-resolved">
                <div class="stat-number"><?php echo $stats['resolved']; ?></div>
                <div class="stat-label">Resolved</div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="filters">
            <a href="?filter=all" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">
                All (<?php echo $stats['total']; ?>)
            </a>
            <a href="?filter=pending" class="filter-btn <?php echo $filter === 'pending' ? 'active' : ''; ?>">
                Pending (<?php echo $stats['pending']; ?>)
            </a>
            <a href="?filter=read" class="filter-btn <?php echo $filter === 'read' ? 'active' : ''; ?>">
                Read (<?php echo $stats['read_count']; ?>)
            </a>
            <a href="?filter=replied" class="filter-btn <?php echo $filter === 'replied' ? 'active' : ''; ?>">
                Replied (<?php echo $stats['replied']; ?>)
            </a>
            <a href="?filter=resolved" class="filter-btn <?php echo $filter === 'resolved' ? 'active' : ''; ?>">
                Resolved (<?php echo $stats['resolved']; ?>)
            </a>
            
            <form method="GET" class="search-box">
                <input type="text" name="search" placeholder="Search by name, email, or message..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <?php if ($filter !== 'all'): ?>
                    <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                <?php endif; ?>
            </form>
        </div>

        <!-- Feedback Table -->
        <div class="table-container">
            <?php if (empty($feedbacks)): ?>
                <div class="empty-state">
                    <p>No feedback found.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Message Preview</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($feedbacks as $feedback): ?>
                            <?php 
                            // Parse message
                            $message_parts = explode("\n\n", $feedback['user_message'], 2);
                            $subject_line = $message_parts[0] ?? '';
                            $message_body = $message_parts[1] ?? $feedback['user_message'];
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($feedback['id']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($feedback['user_name']); ?></strong>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($feedback['user_email']); ?>
                                </td>
                                <td>
                                    <?php 
                                    if (strpos($subject_line, 'Subject:') === 0) {
                                        echo htmlspecialchars(substr($subject_line, 9));
                                    } else {
                                        echo htmlspecialchars(substr($feedback['user_message'], 0, 50)) . '...';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars(substr($message_body, 0, 80)); ?>
                                    <?php if (strlen($message_body) > 80): ?>...<?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $status = $feedback['status'] ?? 'pending';
                                    $status_text = ucfirst($status);
                                    ?>
                                    <span class="status-badge status-<?php echo $status; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    if (isset($feedback['submission_date'])) {
                                        echo date('M d, Y H:i', strtotime($feedback['submission_date']));
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                                <td class="actions">
                                    <button class="btn btn-info btn-small" 
                                            onclick="openModal(<?php echo $feedback['id']; ?>)">
                                        View
                                    </button>
                                    <a href="?delete_feedback=<?php echo $feedback['id']; ?>" 
                                       class="btn btn-danger btn-small"
                                       onclick="return confirm('Are you sure you want to delete this feedback?')">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Feedback Detail Modal -->
    <div id="feedbackModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Feedback Details</h2>
                <button class="close-modal" onclick="closeModal()">×</button>
            </div>
            
            <div class="feedback-details" id="feedbackDetails">
                <!-- Details will be loaded via JavaScript -->
            </div>
            
            <form method="POST" id="updateForm">
                <input type="hidden" name="feedback_id" id="feedbackId">
                
                <div class="form-group">
                    <label for="status">Update Status</label>
                    <select name="status" id="status" required>
                        <option value="pending">Pending</option>
                        <option value="read">Read</option>
                        <option value="replied">Replied</option>
                        <option value="resolved">Resolved</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="admin_reply">Admin Reply (Optional)</label>
                    <textarea name="admin_reply" id="admin_reply" 
                              placeholder="Enter your reply to the customer..."></textarea>
                    <small>This will be stored in the database. You may want to also email the customer.</small>
                </div>
                
                <div class="actions">
                    <button type="submit" name="update_status" class="btn btn-primary">Update</button>
                    <button type="button" class="btn" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2025 Timeless Tokens Jewelry Admin Panel | Created by Mariel</p>
        <p style="font-size: 0.875rem; margin-top: 0.5rem; color: #888;">
            Total Feedback: <?php echo $stats['total']; ?> | 
            Pending: <?php echo $stats['pending']; ?>
        </p>
    </footer>

    <script>
        function openModal(feedbackId) {
            // Show loading state
            document.getElementById('feedbackDetails').innerHTML = '<p>Loading...</p>';
            
            // Show modal
            document.getElementById('feedbackModal').style.display = 'flex';
            
            // Set form feedback_id
            document.getElementById('feedbackId').value = feedbackId;
            
            // Fetch details via AJAX
            fetch(`get_feedback_details.php?id=${feedbackId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const feedback = data.feedback;
                        let attachmentsHtml = '';
                        
                        // Handle attachments
                        if (feedback.file_names) {
                            try {
                                const filePaths = JSON.parse(feedback.file_names);
                                if (Array.isArray(filePaths) && filePaths.length > 0) {
                                    attachmentsHtml = '<div class="attachments">';
                                    filePaths.forEach(path => {
                                        const fileName = path.split('/').pop();
                                        attachmentsHtml += `
                                            <a href="${path}" target="_blank" download class="attachment-link">
                                                📎 ${fileName}
                                            </a>
                                        `;
                                    });
                                    attachmentsHtml += '</div>';
                                }
                            } catch (e) {
                                attachmentsHtml = '<p>Error loading attachments</p>';
                            }
                        }
                        
                        // Set status select box
                        document.getElementById('status').value = feedback.status || 'pending';
                        document.getElementById('admin_reply').value = feedback.admin_reply || '';
                        
                        // Display details
                        const detailsHtml = `
                            <div class="detail-item">
                                <div class="detail-label">Name</div>
                                <div class="detail-value">${escapeHtml(feedback.user_name)}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Email</div>
                                <div class="detail-value">${escapeHtml(feedback.user_email)}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Submitted</div>
                                <div class="detail-value">${new Date(feedback.submission_date).toLocaleString()}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Message</div>
                                <div class="detail-value">${escapeHtml(feedback.user_message)}</div>
                            </div>
                            ${feedback.admin_reply ? `
                                <div class="detail-item">
                                    <div class="detail-label">Admin Reply</div>
                                    <div class="detail-value">${escapeHtml(feedback.admin_reply)}</div>
                                    <small>Replied at: ${feedback.replied_at ? new Date(feedback.replied_at).toLocaleString() : 'N/A'}</small>
                                </div>
                            ` : ''}
                            <div class="detail-item">
                                <div class="detail-label">Attachments</div>
                                <div class="detail-value">
                                    ${attachmentsHtml || 'No attachments'}
                                </div>
                            </div>
                        `;
                        
                        document.getElementById('feedbackDetails').innerHTML = detailsHtml;
                    } else {
                        document.getElementById('feedbackDetails').innerHTML = 
                            '<p class="alert alert-error">Error loading feedback details.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('feedbackDetails').innerHTML = 
                        '<p class="alert alert-error">Failed to load feedback details.</p>';
                });
        }
        
        function closeModal() {
            document.getElementById('feedbackModal').style.display = 'none';
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('feedbackModal');
            if (event.target === modal) {
                closeModal();
            }
        };
        
        // Real-time search in search box
        document.querySelector('input[name="search"]').addEventListener('input', function(e) {
            // Delay submission to avoid frequent requests
            clearTimeout(this.searchTimer);
            this.searchTimer = setTimeout(() => {
                this.form.submit();
            }, 500);
        });
    </script>
</body>
</html>