<?php
/**
 * Admin Subscribers Management
 * Timeless Tokens Jewelry
 */
session_start();

// 检查管理员登录状态
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// 数据库配置
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
    $connection_status = "✅ Database connection successful";
} catch (PDOException $e) {
    $connection_status = "❌ Database connection failed: " . $e->getMessage();
    $pdo = null;
}

// 处理新增订阅者
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_subscriber']) && $pdo) {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $city = $_POST['city'] ?? '';
    $address_line_1 = $_POST['address_line_1'] ?? '';
    $state = $_POST['state'] ?? '';
    $source = $_POST['source'] ?? 'Website';
    $country = 'Australia';
    
    // 处理出生日期
    $birth_day = $_POST['birth_day'] ?? '';
    $birth_month = $_POST['birth_month'] ?? '';
    $birth_year = $_POST['birth_year'] ?? '';
    $date_of_birth = '';
    
    if (!empty($birth_day) && !empty($birth_month) && !empty($birth_year)) {
        $date_of_birth = $birth_year . '-' . $birth_month . '-' . $birth_day;
    }
    
    $errors = array();
    
    // 验证邮箱
    if (empty(trim($email)) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email address is required.";
    }
    
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO wp_fc_subscribers (
                        first_name, 
                        last_name, 
                        email, 
                        phone,
                        city,
                        date_of_birth,
                        address_line_1,
                        state,
                        country,
                        source,
                        ip, 
                        status, 
                        contact_type,
                        created_at,
                        updated_at
                    ) VALUES (
                        :first_name, 
                        :last_name, 
                        :email, 
                        :phone,
                        :city,
                        :date_of_birth,
                        :address_line_1,
                        :state,
                        :country,
                        :source,
                        :ip, 
                        'subscribed', 
                        'lead',
                        NOW(),
                        NOW()
                    )";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'phone' => $phone,
                'city' => $city,
                'date_of_birth' => $date_of_birth,
                'address_line_1' => $address_line_1,
                'state' => $state,
                'country' => $country,
                'source' => $source,
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);
            
            $_SESSION['success'] = "Subscriber added successfully!";
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
            
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $_SESSION['error'] = "This email is already subscribed.";
            } else {
                $_SESSION['error'] = "Database error: " . $e->getMessage();
            }
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}

// 处理编辑订阅者
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_subscriber']) && $pdo) {
    $id = (int)$_POST['id'];
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $city = $_POST['city'] ?? '';
    $address_line_1 = $_POST['address_line_1'] ?? '';
    $state = $_POST['state'] ?? '';
    $status = $_POST['status'] ?? 'subscribed';
    $source = $_POST['source'] ?? 'Website';
    
    // 处理出生日期
    $birth_day = $_POST['birth_day'] ?? '';
    $birth_month = $_POST['birth_month'] ?? '';
    $birth_year = $_POST['birth_year'] ?? '';
    $date_of_birth = '';
    
    if (!empty($birth_day) && !empty($birth_month) && !empty($birth_year)) {
        $date_of_birth = $birth_year . '-' . $birth_month . '-' . $birth_day;
    }
    
    $errors = array();
    
    if (empty($errors)) {
        try {
            $sql = "UPDATE wp_fc_subscribers SET
                        first_name = :first_name,
                        last_name = :last_name,
                        email = :email,
                        phone = :phone,
                        city = :city,
                        date_of_birth = :date_of_birth,
                        address_line_1 = :address_line_1,
                        state = :state,
                        status = :status,
                        source = :source,
                        updated_at = NOW()
                    WHERE id = :id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'phone' => $phone,
                'city' => $city,
                'date_of_birth' => $date_of_birth,
                'address_line_1' => $address_line_1,
                'state' => $state,
                'status' => $status,
                'source' => $source,
                'id' => $id
            ]);
            
            $_SESSION['success'] = "Subscriber updated successfully!";
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
            
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error updating subscriber: " . $e->getMessage();
        }
    }
}

// 处理删除订阅者
if (isset($_GET['delete_id']) && $pdo) {
    $delete_id = (int)$_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM wp_fc_subscribers WHERE id = ?");
        $stmt->execute([$delete_id]);
        $_SESSION['success'] = "Subscriber deleted successfully!";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting subscriber: " . $e->getMessage();
    }
}

// 处理导出 Excel
if (isset($_GET['export']) && $pdo) {
    $export_sql = "SELECT 
                       id,
                       first_name, 
                       last_name, 
                       email, 
                       phone,
                       city,
                       address_line_1,
                       date_of_birth,
                       state,
                       country,
                       status,
                       source,
                       created_at
                   FROM wp_fc_subscribers 
                   ORDER BY created_at DESC";
    
    $export_stmt = $pdo->prepare($export_sql);
    $export_stmt->execute();
    $export_data = $export_stmt->fetchAll();
    
    // 设置 Excel 输出头
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="subscribers_' . date('Y-m-d') . '.xls"');
    header('Cache-Control: max-age=0');
    
    $output = fopen('php://output', 'w');
    
    // 添加标题行
    fputcsv($output, [
        'ID', 'First Name', 'Last Name', 'Email', 'Phone', 
        'City', 'Address', 'Date of Birth', 'State', 
        'Country', 'Status', 'Source', 'Created At'
    ]);
    
    // 添加数据行
    foreach ($export_data as $row) {
        fputcsv($output, [
            $row['id'],
            $row['first_name'],
            $row['last_name'],
            $row['email'],
            $row['phone'],
            $row['city'],
            $row['address_line_1'],
            $row['date_of_birth'],
            $row['state'],
            $row['country'],
            $row['status'],
            $row['source'],
            $row['created_at']
        ]);
    }
    
    fclose($output);
    exit;
}

// 查询订阅者详情（用于编辑）
$edit_subscriber = null;
if (isset($_GET['edit_id']) && $pdo) {
    $edit_id = (int)$_GET['edit_id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM wp_fc_subscribers WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_subscriber = $stmt->fetch();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error loading subscriber: " . $e->getMessage();
    }
}

// 查询所有订阅者（带搜索功能）
$search = trim($_GET['search'] ?? '');
$filters = [];

// 构建查询语句
$sql = "SELECT 
            id,
            first_name, 
            last_name, 
            email, 
            phone,
            city,
            address_line_1,
            date_of_birth,
            state,
            country,
            status,
            source,
            created_at
        FROM wp_fc_subscribers 
        WHERE 1=1";
        
if (!empty($search)) {
    $sql .= " AND (first_name LIKE :search OR last_name LIKE :search OR email LIKE :search OR phone LIKE :search)";
    $filters['search'] = "%$search%";
}

$sql .= " ORDER BY created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($filters);
    $subscribers = $stmt->fetchAll();
    $total_subscribers = count($subscribers);
} catch (PDOException $e) {
    $subscribers = [];
    $total_subscribers = 0;
    $subscribers_error = "Failed to load subscribers: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Subscribers Management - Timeless Tokens Jewelry</title>
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
        
        .btn-info:hover {
            background-color: #138496;
        }
        
        .btn-success {
            background-color: var(--success);
            color: white;
        }
        
        .btn-success:hover {
            background-color: #218838;
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
        
        .search-container {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            align-items: center;
        }
        
        .search-container input {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .search-container button {
            padding: 0.75rem 1.5rem;
        }
        
        .actions-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
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
        
        .actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--light-text);
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: var(--white);
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-gold);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: var(--light-text);
            font-size: 0.9rem;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }
        
        .modal-header h3 {
            color: var(--dark-text);
            font-size: 1.5em;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--dark-text);
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #bdc3c7;
            border-radius: 4px;
            font-size: 14px;
            font-family: inherit;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-gold);
            box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.2);
        }
        
        .date-selectors {
            display: flex;
            gap: 10px;
        }
        
        .date-selectors select {
            flex: 1;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid var(--border);
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
            
            .search-container {
                flex-direction: column;
            }
            
            .actions-bar {
                flex-direction: column;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            table {
                font-size: 0.875rem;
            }
            
            th, td {
                padding: 0.75rem 0.5rem;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .date-selectors {
                flex-direction: column;
            }
        }
    </style>
    <?php require_once 'theme_config.php'; ?>
<?php include 'theme_styles.php'; ?>
</head>
<body>
    <!-- Header Navigation -->
    <<header class="header">
        <nav class="nav-container">
            <a href="index.php" class="logo">
                <img src="photo/2.jpg" alt="Timeless Tokens" style="height: 40px; vertical-align: middle;">
                <span style="vertical-align: middle;">Timeless Tokens Jewelry</span>
            </a>
            <div class="admin-nav">
                <a href="admin_products.php">Products</a>
                <a href="admin_jobs.php">Jobs</a>
                <a href="admin_feedback.php">Feedback</a>
                <a href="admin_subscribers.php" class="active">Subscribers</a>
                <a href="logout.php">Logout</a>
            </div>
        </nav>
    </header>

    <div class="container">
        <!-- Admin Header -->
        <div class="admin-header">
            <h1>📋 Subscribers Management</h1>
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

        <!-- Stats -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_subscribers; ?></div>
                <div class="stat-label">Total Subscribers</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($subscribers, function($s) { return $s['status'] === 'subscribed'; })); ?></div>
                <div class="stat-label">Active Subscribers</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($subscribers, function($s) { return !empty($s['phone']); })); ?></div>
                <div class="stat-label">With Phone Number</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo date('Y-m-d'); ?></div>
                <div class="stat-label">Last Updated</div>
            </div>
        </div>

        <!-- Actions Bar -->
        <div class="actions-bar">
            <button class="btn btn-success" onclick="openAddModal()">＋ Add Subscriber</button>
            <a href="?export=1" class="btn btn-info">📊 Export to Excel</a>
        </div>

        <!-- Search -->
        <section class="section">
            <div class="search-container">
                <form method="GET" style="display: flex; flex: 1; gap: 1rem;">
                    <input type="text" name="search" placeholder="Search by name, email, or phone..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary">🔍 Search</button>
                    <?php if (!empty($search)): ?>
                        <a href="?" class="btn btn-secondary">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
        </section>

        <!-- Subscribers List -->
        <section class="section">
            <h2 class="section-title">📋 Subscribers List (<?php echo $total_subscribers; ?>)</h2>
            
            <?php if (empty($subscribers)): ?>
                <div class="empty-state">
                    <p>No subscribers found.</p>
                    <?php if (!empty($search)): ?>
                        <p>Try a different search term.</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Location</th>
                                <th>Address</th>
                                <th>DOB</th>
                                <th>Status</th>
                                <th>Source</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subscribers as $subscriber): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($subscriber['id']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($subscriber['first_name'] . ' ' . $subscriber['last_name']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($subscriber['email']); ?></td>
                                    <td><?php echo htmlspecialchars($subscriber['phone'] ?: 'N/A'); ?></td>
                                    <td>
                                        <?php
                                        $location_parts = [];
                                        if (!empty($subscriber['city'])) $location_parts[] = $subscriber['city'];
                                        if (!empty($subscriber['state'])) $location_parts[] = $subscriber['state'];
                                        if (!empty($subscriber['country'])) $location_parts[] = $subscriber['country'];
                                        echo htmlspecialchars(implode(', ', array_slice($location_parts, 0, 2)));
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($subscriber['address_line_1'] ?: 'N/A'); ?></td>
                                    <td>
                                        <?php 
                                        if (!empty($subscriber['date_of_birth'])) {
                                            echo date('Y-m-d', strtotime($subscriber['date_of_birth']));
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <span style="
                                            padding: 2px 6px;
                                            border-radius: 3px;
                                            font-size: 0.8em;
                                            background-color: <?php echo ($subscriber['status'] == 'subscribed') ? '#d4edda' : '#fff3cd'; ?>;
                                            color: <?php echo ($subscriber['status'] == 'subscribed') ? '#155724' : '#856404'; ?>;
                                            border: 1px solid <?php echo ($subscriber['status'] == 'subscribed') ? '#c3e6cb' : '#ffeaa7'; ?>;
                                        ">
                                            <?php echo htmlspecialchars($subscriber['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($subscriber['source'] ?: 'Website'); ?></td>
                                    <td>
                                        <?php 
                                        if (!empty($subscriber['created_at'])) {
                                            echo date('Y-m-d', strtotime($subscriber['created_at']));
                                        }
                                        ?>
                                    </td>
                                    <td class="actions">
                                        <a href="?edit_id=<?php echo $subscriber['id']; ?>" 
                                           class="btn btn-primary"
                                           onclick="openEditModal(<?php echo $subscriber['id']; ?>)">Edit</a>
                                        <a href="?delete_id=<?php echo $subscriber['id']; ?>" 
                                           class="btn btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this subscriber? This action cannot be undone.')">
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
            Total Subscribers: <?php echo $total_subscribers; ?> | 
            Active: <?php echo count(array_filter($subscribers, function($s) { return $s['status'] === 'subscribed'; })); ?>
        </p>
    </footer>

    <!-- Add Subscriber Modal -->
    <div class="modal" id="addModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Subscriber</h3>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="add_subscriber" value="1">
                
                <div class="form-group">
                    <label for="add_first_name">First Name *</label>
                    <input type="text" id="add_first_name" name="first_name" required>
                </div>
                
                <div class="form-group">
                    <label for="add_last_name">Last Name *</label>
                    <input type="text" id="add_last_name" name="last_name" required>
                </div>
                
                <div class="form-group">
                    <label for="add_email">Email *</label>
                    <input type="email" id="add_email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="add_phone">Phone</label>
                    <input type="text" id="add_phone" name="phone">
                </div>
                
                <div class="form-group">
                    <label>Date of Birth</label>
                    <div class="date-selectors">
                        <select name="birth_day">
                            <option value="">Day</option>
                            <?php for ($i = 1; $i <= 31; $i++): ?>
                                <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>">
                                    <?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <select name="birth_month">
                            <option value="">Month</option>
                            <option value="01">Jan</option>
                            <option value="02">Feb</option>
                            <option value="03">Mar</option>
                            <option value="04">Apr</option>
                            <option value="05">May</option>
                            <option value="06">Jun</option>
                            <option value="07">Jul</option>
                            <option value="08">Aug</option>
                            <option value="09">Sep</option>
                            <option value="10">Oct</option>
                            <option value="11">Nov</option>
                            <option value="12">Dec</option>
                        </select>
                        <select name="birth_year">
                            <option value="">Year</option>
                            <?php for ($i = date('Y'); $i >= date('Y') - 100; $i--): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="add_address">Address</label>
                    <input type="text" id="add_address" name="address_line_1">
                </div>
                
                <div class="form-group">
                    <label for="add_city">City</label>
                    <input type="text" id="add_city" name="city">
                </div>
                
                <div class="form-group">
                    <label for="add_state">State</label>
                    <input type="text" id="add_state" name="state">
                </div>
                
                <div class="form-group">
                    <label for="add_source">Source</label>
                    <select id="add_source" name="source">
                        <option value="Website">Website</option>
                        <option value="Social Media">Social Media</option>
                        <option value="Event">Event</option>
                        <option value="Referral">Referral</option>
                        <option value="Email Campaign">Email Campaign</option>
                        <option value="Store Visit">Store Visit</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeAddModal()">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Subscriber</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Subscriber Modal -->
    <?php if ($edit_subscriber): ?>
    <div class="modal" id="editModal" style="display: flex;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Subscriber #<?php echo $edit_subscriber['id']; ?></h3>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="edit_subscriber" value="1">
                <input type="hidden" name="id" value="<?php echo $edit_subscriber['id']; ?>">
                
                <div class="form-group">
                    <label for="edit_first_name">First Name</label>
                    <input type="text" id="edit_first_name" name="first_name" 
                           value="<?php echo htmlspecialchars($edit_subscriber['first_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_last_name">Last Name</label>
                    <input type="text" id="edit_last_name" name="last_name" 
                           value="<?php echo htmlspecialchars($edit_subscriber['last_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_email">Email</label>
                    <input type="email" id="edit_email" name="email" 
                           value="<?php echo htmlspecialchars($edit_subscriber['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_phone">Phone</label>
                    <input type="text" id="edit_phone" name="phone" 
                           value="<?php echo htmlspecialchars($edit_subscriber['phone']); ?>">
                </div>
                
                <div class="form-group">
                    <label>Date of Birth</label>
                    <div class="date-selectors">
                        <?php
                        $dob = $edit_subscriber['date_of_birth'] ?? '';
                        $birth_day = $birth_month = $birth_year = '';
                        if (!empty($dob)) {
                            $parts = explode('-', $dob);
                            if (count($parts) >= 3) {
                                $birth_year = $parts[0];
                                $birth_month = $parts[1];
                                $birth_day = $parts[2];
                            }
                        }
                        ?>
                        <select name="birth_day">
                            <option value="">Day</option>
                            <?php for ($i = 1; $i <= 31; $i++): 
                                $day = str_pad($i, 2, '0', STR_PAD_LEFT);
                                $selected = ($birth_day == $day) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $day; ?>" <?php echo $selected; ?>>
                                    <?php echo $day; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <select name="birth_month">
                            <option value="">Month</option>
                            <?php 
                            $months = [
                                '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
                                '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Aug',
                                '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec'
                            ];
                            foreach ($months as $num => $name):
                                $selected = ($birth_month == $num) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $num; ?>" <?php echo $selected; ?>>
                                    <?php echo $name; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <select name="birth_year">
                            <option value="">Year</option>
                            <?php for ($i = date('Y'); $i >= date('Y') - 100; $i--): 
                                $selected = ($birth_year == $i) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $i; ?>" <?php echo $selected; ?>>
                                    <?php echo $i; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_address">Address</label>
                    <input type="text" id="edit_address" name="address_line_1" 
                           value="<?php echo htmlspecialchars($edit_subscriber['address_line_1'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="edit_city">City</label>
                    <input type="text" id="edit_city" name="city" 
                           value="<?php echo htmlspecialchars($edit_subscriber['city'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="edit_state">State</label>
                    <input type="text" id="edit_state" name="state" 
                           value="<?php echo htmlspecialchars($edit_subscriber['state'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="edit_status">Status</label>
                    <select id="edit_status" name="status">
                        <option value="subscribed" <?php echo ($edit_subscriber['status'] == 'subscribed') ? 'selected' : ''; ?>>Subscribed</option>
                        <option value="pending" <?php echo ($edit_subscriber['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit_source">Source</label>
                    <select id="edit_source" name="source">
                        <option value="Website" <?php echo ($edit_subscriber['source'] == 'Website') ? 'selected' : ''; ?>>Website</option>
                        <option value="Social Media" <?php echo ($edit_subscriber['source'] == 'Social Media') ? 'selected' : ''; ?>>Social Media</option>
                        <option value="Event" <?php echo ($edit_subscriber['source'] == 'Event') ? 'selected' : ''; ?>>Event</option>
                        <option value="Referral" <?php echo ($edit_subscriber['source'] == 'Referral') ? 'selected' : ''; ?>>Referral</option>
                        <option value="Email Campaign" <?php echo ($edit_subscriber['source'] == 'Email Campaign') ? 'selected' : ''; ?>>Email Campaign</option>
                        <option value="Store Visit" <?php echo ($edit_subscriber['source'] == 'Store Visit') ? 'selected' : ''; ?>>Store Visit</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                    <a href="?" class="btn btn-danger">Cancel Edit</a>
                    <button type="submit" class="btn btn-success">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
            <body class="<?php echo $current_theme === 'dark' ? 'dark-mode' : ''; ?>">
    <?php endif; ?>

    <script>
        // Modal Functions
        function openAddModal() {
            document.getElementById('addModal').style.display = 'flex';
        }
        
        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
        }
        
        function openEditModal(id) {
            // Already handled by PHP
            return true;
        }
        
        function closeEditModal() {
            window.location.href = window.location.pathname;
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addModal');
            if (event.target === addModal) {
                closeAddModal();
            }
        }
        
        // Auto close modals on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAddModal();
                closeEditModal();
            }
        });
        
        // Confirm delete
        function confirmDelete(name) {
            return confirm('Are you sure you want to delete "' + name + '"? This action cannot be undone.');
        }
    </script>
        <?php include 'theme_toggle.php'; ?>
</body>
</html>