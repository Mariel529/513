<?php
/**
 * Customer Support System for Timeless Tokens Jewelry
 */

// Check if debug constants are already defined, define only if not
if (!defined('WP_DEBUG_LOG')) {
    define('WP_DEBUG_LOG', true);
}
if (!defined('WP_DEBUG_DISPLAY')) {
    define('WP_DEBUG_DISPLAY', false);
}

// ===== 1. Create Support Tickets Database Table =====
function create_support_tickets_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'support_tickets';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        ticket_id INT NOT NULL AUTO_INCREMENT,
        customer_name VARCHAR(100) NOT NULL,
        customer_email VARCHAR(100) NOT NULL,
        subject VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        status VARCHAR(20) DEFAULT 'open',
        PRIMARY KEY (ticket_id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_setup_theme', 'create_support_tickets_table');

// ===== 2. Process Support Form Submission (with debug code merged) =====
function process_support_form() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_support'])) {
        
        // Add debug logging
        error_log('=== SUPPORT FORM SUBMISSION STARTED ===');
        error_log('POST Data: ' . print_r($_POST, true));
        
        // Security verification
        if (!wp_verify_nonce($_POST['support_nonce'], 'submit_support_form')) {
            wp_die('Security check failed');
        }
        
        // Sanitize and validate inputs
        $name = sanitize_text_field($_POST['name'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $subject = sanitize_text_field($_POST['subject'] ?? '');
        $message = sanitize_textarea_field($_POST['message'] ?? '');
        
        $errors = [];
        
        // Validate required fields
        if (empty($name)) {
            $errors[] = 'Name is required.';
        }
        
        if (empty($email) || !is_email($email)) {
            $errors[] = 'Valid email address is required.';
        }
        
        if (empty($subject)) {
            $errors[] = 'Subject is required.';
        }
        
        if (empty($message)) {
            $errors[] = 'Message is required.';
        }
        
        // If no errors, save to database
        if (empty($errors)) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'support_tickets';
            
            $result = $wpdb->insert(
                $table_name,
                [
                    'customer_name' => $name,
                    'customer_email' => $email,
                    'subject' => $subject,
                    'message' => $message,
                    'status' => 'open'
                ],
                ['%s', '%s', '%s', '%s', '%s']
            );
            
            if ($result !== false) {
                // Send confirmation email to customer - simplified version
                $to = $email;
                $subject_email = 'Thank you for contacting Timeless Tokens Jewelry';
                $message_email = "Dear $name,\n\nThank you for contacting us. We have received your message and will respond within 48 hours.\n\nBest regards,\nTimeless Tokens Jewelry Team";
                
                // Debug logging
                error_log('Attempting to send email to: ' . $email);
                
                // Method 1: No headers, let WP Mail SMTP handle it
                $email_sent = wp_mail($to, $subject_email, $message_email);
                
                // Debug logging
                error_log('wp_mail result: ' . ($email_sent ? 'SUCCESS' : 'FAILED'));
                
                if (!$email_sent) {
                    // Check PHPMailer error
                    global $phpmailer;
                    if (isset($phpmailer->ErrorInfo)) {
                        error_log('PHPMailer Error: ' . $phpmailer->ErrorInfo);
                    }
                    
                    // Try alternative method
                    error_log('Trying alternative mail method...');
                    $email_sent = send_support_email_alternative($to, $name, $subject_email, $message_email);
                }
                
                // Send notification to admin (similarly simplified)
                $admin_email = get_option('admin_email');
                $admin_subject = "New Support Ticket: $subject";
                $admin_message = "New support ticket received:\n\nName: $name\nEmail: $email\nSubject: $subject\nMessage: $message\n\nSubmitted at: " . current_time('mysql');
                
                wp_mail($admin_email, $admin_subject, $admin_message);
                
                // Set success message
                $success_msg = 'Thank you for your message. We will respond within 48 hours.';
                if (!$email_sent) {
                    $success_msg .= ' (Note: Confirmation email may not have been sent due to server restrictions.)';
                }
                
                set_transient('support_form_success', $success_msg, 30);
                
                // Redirect to prevent duplicate submissions
                wp_redirect(add_query_arg('success', 'true', wp_get_referer()));
                exit;
            } else {
                set_transient('support_form_error', 'An error occurred while saving your message. Please try again.', 30);
            }
        } else {
            set_transient('support_form_error', implode('<br>', $errors), 30);
        }
    }
}
add_action('init', 'process_support_form');

// ===== Alternative Email Sending Function =====
function send_support_email_alternative($to, $name, $subject, $message) {
    try {
        // Use PHPMailer directly
        require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
        require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
        require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Get WP Mail SMTP settings
        $wpms_options = get_option('wp_mail_smtp', []);
        
        if (!empty($wpms_options['mail']['mailer']) && $wpms_options['mail']['mailer'] === 'smtp') {
            // Use SMTP settings
            $mail->isSMTP();
            $mail->Host = $wpms_options['smtp']['host'] ?? '';
            $mail->Port = $wpms_options['smtp']['port'] ?? 587;
            $mail->SMTPSecure = $wpms_options['smtp']['encryption'] ?? 'tls';
            $mail->SMTPAuth = true;
            $mail->Username = $wpms_options['smtp']['user'] ?? '';
            $mail->Password = $wpms_options['smtp']['pass'] ?? '';
        } else {
            // Use default mail() function
            $mail->isMail();
        }
        
        $mail->setFrom(get_option('admin_email'), 'Timeless Tokens Jewelry');
        $mail->addAddress($to, $name);
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->isHTML(false);
        
        $result = $mail->send();
        error_log('Alternative mail method result: ' . ($result ? 'SUCCESS' : 'FAILED'));
        
        return $result;
        
    } catch (Exception $e) {
        error_log('Alternative mail error: ' . $e->getMessage());
        return false;
    }
}

// ===== 3. Email Testing Function =====
function test_support_email() {
    // Admin access only
    if (!current_user_can('manage_options')) return;
    
    // Check test request
    if (isset($_GET['test_email']) && isset($_GET['email'])) {
        $test_email = sanitize_email($_GET['email']);
        
        if (is_email($test_email)) {
            $subject = 'Test Email from Support System';
            $message = "This is a test email sent at " . date('Y-m-d H:i:s');
            
            // Test wp_mail function
            $result = wp_mail($test_email, $subject, $message);
            
            if ($result) {
                echo '<div class="notice notice-success"><p>✅ Test email sent successfully to ' . $test_email . '</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>❌ Failed to send test email to ' . $test_email . '</p></div>';
                echo '<p>Check debug log at: /wp-content/debug.log</p>';
            }
        }
    }
    
    // Display test form (admin only)
    if (is_admin()) {
        ?>
        <div class="card" style="margin-top: 20px; padding: 20px;">
            <h3>📧 Test Support Email System</h3>
            <form method="get">
                <input type="hidden" name="page" value="support-tickets">
                <input type="email" name="email" placeholder="Enter email to test" required style="width: 300px; padding: 5px;">
                <input type="hidden" name="test_email" value="1">
                <input type="submit" class="button button-primary" value="Send Test Email">
            </form>
            <p><small>This will test the wp_mail() function with your current WP Mail SMTP settings.</small></p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'test_support_email');

// ===== 4. Support Form Shortcode =====
function support_form_shortcode() {
    ob_start();
    
    // Display success or error messages
    if (get_transient('support_form_success')) {
        echo '<div class="alert alert-success">' . get_transient('support_form_success') . '</div>';
        delete_transient('support_form_success');
    }
    
    if (get_transient('support_form_error')) {
        echo '<div class="alert alert-error">' . get_transient('support_form_error') . '</div>';
        delete_transient('support_form_error');
    }
    ?>
    
    <div class="support-form-container">
        <h2>Customer Support</h2>
        <p class="form-intro">Need help with your order or have questions? Contact us below.</p>
        
        <div class="privacy-notice">
            <strong>Privacy Notice:</strong> Your information is protected under the Privacy Act 1988. 
            We only use your personal information to respond to your inquiry.
        </div>
        
        <form method="POST" action="" class="support-form">
            <?php wp_nonce_field('submit_support_form', 'support_nonce'); ?>
            
            <div class="form-group">
                <label for="support_name" class="required">Your Name</label>
                <input type="text" id="support_name" name="name" required 
                       value="<?php echo esc_attr($_POST['name'] ?? ''); ?>"
                       placeholder="Enter your full name">
            </div>
            
            <div class="form-group">
                <label for="support_email" class="required">Email Address</label>
                <input type="email" id="support_email" name="email" required 
                       value="<?php echo esc_attr($_POST['email'] ?? ''); ?>"
                       placeholder="Enter your email address">
            </div>
            
            <div class="form-group">
                <label for="support_subject" class="required">Subject</label>
                <input type="text" id="support_subject" name="subject" required 
                       value="<?php echo esc_attr($_POST['subject'] ?? ''); ?>"
                       placeholder="e.g., Order Inquiry, Product Question">
            </div>
            
            <div class="form-group">
                <label for="support_message" class="required">Message</label>
                <textarea id="support_message" name="message" required 
                          placeholder="Please describe your inquiry in detail..."><?php echo esc_textarea($_POST['message'] ?? ''); ?></textarea>
            </div>
            
            <button type="submit" name="submit_support" class="btn btn-primary">Submit Message</button>
        </form>
    </div>
    
    <style>
        .support-form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .support-form {
            background: #f9f9f9;
            padding: 2rem;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        
        .form-group label.required::after {
            content: " *";
            color: #dc3545;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .form-group textarea {
            height: 150px;
            resize: vertical;
        }
        
        .btn-primary {
            background-color: #d4af37;
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }
        
        .btn-primary:hover {
            background-color: #b8941f;
        }
        
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .privacy-notice {
            background-color: #e7f3fe;
            border: 1px solid #b3d7ff;
            border-radius: 4px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            color: #0066cc;
        }
        
        .form-intro {
            margin-bottom: 1.5rem;
            color: #666;
        }
    </style>
    
    <?php
    return ob_get_clean();
}
add_shortcode('support_form', 'support_form_shortcode');

// ===== 5. Admin Support Tickets Page =====
function support_tickets_admin_page() {
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'support_tickets';
    
    // Handle status updates
    if (isset($_GET['action']) && $_GET['action'] === 'update_status' && isset($_GET['ticket_id'])) {
        $ticket_id = intval($_GET['ticket_id']);
        $new_status = sanitize_text_field($_GET['status'] ?? 'open');
        
        $wpdb->update(
            $table_name,
            ['status' => $new_status],
            ['ticket_id' => $ticket_id],
            ['%s'],
            ['%d']
        );
        
        wp_redirect(remove_query_arg(['action', 'ticket_id', 'status']));
        exit;
    }
    
    // Get all tickets
    $tickets = $wpdb->get_results("SELECT * FROM $table_name ORDER BY submitted_at DESC");
    ?>
    
    <div class="wrap">
        <h1>Support Tickets</h1>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Submitted</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tickets)): ?>
                    <tr>
                        <td colspan="8">No support tickets found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td><?php echo $ticket->ticket_id; ?></td>
                            <td><?php echo esc_html($ticket->customer_name); ?></td>
                            <td><?php echo esc_html($ticket->customer_email); ?></td>
                            <td><?php echo esc_html($ticket->subject); ?></td>
                            <td><?php echo esc_html(wp_trim_words($ticket->message, 10)); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($ticket->submitted_at)); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $ticket->status; ?>">
                                    <?php echo ucfirst($ticket->status); ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?php echo add_query_arg([
                                    'action' => 'update_status',
                                    'ticket_id' => $ticket->ticket_id,
                                    'status' => 'closed'
                                ]); ?>" class="button button-small">Close</a>
                                <a href="<?php echo add_query_arg([
                                    'action' => 'update_status',
                                    'ticket_id' => $ticket->ticket_id,
                                    'status' => 'open'
                                ]); ?>" class="button button-small">Reopen</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php test_support_email(); // Display test form ?>
        
        <style>
            .status-badge {
                padding: 3px 8px;
                border-radius: 3px;
                font-size: 12px;
                font-weight: bold;
            }
            
            .status-open {
                background-color: #ffeb3b;
                color: #333;
            }
            
            .status-closed {
                background-color: #4caf50;
                color: white;
            }
        </style>
    </div>
    <?php
}

// ===== 6. Add Admin Menu =====
function add_support_tickets_menu() {
    add_menu_page(
        'Support Tickets',
        'Support Tickets',
        'manage_options',
        'support-tickets',
        'support_tickets_admin_page',
        'dashicons-email-alt',
        30
    );
}
add_action('admin_menu', 'add_support_tickets_menu');

// ===== 7. Integrate into Theme =====
function add_support_form_to_theme() {
    // Create support page if it doesn't exist
    $page_exists = get_page_by_title('Customer Support');
    
    if (!$page_exists) {
        $page_data = [
            'post_title'    => 'Customer Support',
            'post_content'  => '[support_form]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'customer-support'
        ];
        
        wp_insert_post($page_data);
    }
}
add_action('after_setup_theme', 'add_support_form_to_theme');

// ===== 8. Helper Function to Check Mail Configuration =====
function check_mail_configuration() {
    if (current_user_can('manage_options') && isset($_GET['check_mail'])) {
        echo '<div class="card" style="padding: 20px;">';
        echo '<h3>📧 Mail Configuration Check</h3>';
        
        // Check WP Mail SMTP settings
        $wpms_options = get_option('wp_mail_smtp', []);
        echo '<p><strong>WP Mail SMTP Status:</strong> ' . 
             (!empty($wpms_options['mail']['mailer']) ? $wpms_options['mail']['mailer'] : 'Not configured') . '</p>';
        
        // Check mail() function
        echo '<p><strong>mail() function:</strong> ' . (function_exists('mail') ? 'Exists' : 'Not available') . '</p>';
        
        // Check sender email
        echo '<p><strong>From Email:</strong> ' . get_option('admin_email') . '</p>';
        
        // Check website URL
        echo '<p><strong>Site URL:</strong> ' . get_site_url() . '</p>';
        
        echo '</div>';
    }
}
add_action('admin_notices', 'check_mail_configuration');