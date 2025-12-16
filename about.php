<?php
// Include theme initialization at the VERY TOP
require_once 'init_theme.php';

// Check if user is logged in
$logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$admin_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Get user info from session
$user_name = $_SESSION['user_name'] ?? 'Guest';
$user_email = $_SESSION['user_email'] ?? '';
$user_phone = $_SESSION['user_phone'] ?? '';

// Get admin info
$admin_username = $_SESSION['admin_username'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Timeless Tokens Jewelry</title>
    
    <!-- Include theme styles -->
    <?php include 'theme_styles.php'; ?>
    
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
        .about-hero {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=1200');
            background-size: cover;
            background-position: center;
            color: var(--white);
            text-align: center;
            padding: 6rem 2rem;
            margin-bottom: 3rem;
        }
        
        .about-hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--primary-gold);
        }
        
        .about-hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Main Content */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .page-title {
            text-align: center;
            margin-bottom: 3rem;
            color: var(--dark-text);
            font-size: 2.5rem;
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
        
        /* About Sections */
        .about-section {
            margin-bottom: 4rem;
            padding: 2rem 0;
        }
        
        .section-title {
            font-size: 2rem;
            color: var(--dark-text);
            margin-bottom: 1.5rem;
            text-align: center;
            position: relative;
        }
        
        .section-title:after {
            content: '';
            display: block;
            width: 60px;
            height: 2px;
            background: var(--primary-gold);
            margin: 0.5rem auto;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
        }
        
        .about-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .about-content {
            font-size: 1.1rem;
            line-height: 1.8;
        }
        
        .about-content p {
            margin-bottom: 1.5rem;
        }
        
        /* Mission & Values */
        .mission-section {
            background-color: #f8f8f8;
            padding: 4rem 0;
            margin: 4rem 0;
        }
        
        .mission-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .mission-card {
            background: var(--white);
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .mission-icon {
            font-size: 3rem;
            color: var(--primary-gold);
            margin-bottom: 1rem;
        }
        
        .mission-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--dark-text);
        }
        
        /* Crafting Process */
        .process-section {
            margin: 4rem 0;
        }
        
        .process-steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .process-step {
            text-align: center;
            padding: 2rem;
        }
        
        .step-number {
            background-color: var(--primary-gold);
            color: var(--white);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.2rem;
            font-weight: bold;
        }
        
        .step-title {
            font-size: 1.3rem;
            margin-bottom: 1rem;
            color: var(--dark-text);
        }
        
        /* Founder Section */
        .founder-section {
            background: linear-gradient(135deg, #1a1a1a 0%, #2c2c2c 100%);
            color: var(--white);
            padding: 4rem 0;
            margin: 4rem 0;
        }
        
        .founder-content {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }
        
        .founder-image {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 2rem;
            border: 4px solid var(--primary-gold);
        }
        
        .founder-name {
            font-size: 2rem;
            color: var(--primary-gold);
            margin-bottom: 0.5rem;
        }
        
        .founder-title {
            font-size: 1.2rem;
            color: var(--white);
            margin-bottom: 2rem;
            opacity: 0.8;
        }
        
        /* Contact Info */
        .contact-info {
            background-color: #f8f8f8;
            padding: 3rem;
            border-radius: 8px;
            margin: 2rem 0;
        }
        
        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }
        
        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .contact-icon {
            font-size: 1.5rem;
            color: var(--primary-gold);
            margin-top: 0.2rem;
        }
        
        .contact-details h4 {
            margin-bottom: 0.5rem;
            color: var(--dark-text);
        }

        .map-section {
            margin: 4rem 0;
            padding: 2rem 0;
        }
        
        .map-container {
            width: 100%;
            margin: 0 auto;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            position: relative;
        }
        
        .map-frame {
            width: 100%;
            height: 600px; 
            border: none;
            display: block;
        }
        
        .map-overlay {
            margin-top: 20px;
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            border-left: 4px solid var(--primary-gold);
        }
        
        .map-overlay h3 {
            color: var(--primary-gold);
            margin-bottom: 0.5rem;
            font-size: 1.3rem;
        }
        
        .map-overlay p {
            color: var(--light-text);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        
        .map-details {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
        }
        
        .map-details-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .map-details-icon {
            color: var(--primary-gold);
            font-size: 1rem;
        }

        .store-info-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-top: 2rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-top: 5px solid var(--primary-gold);
        }
        
        .store-rating {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 1rem;
        }
        
        .rating-stars {
            color: #ffc107;
            font-size: 1.2rem;
        }
        
        .rating-score {
            font-weight: bold;
            color: var(--dark-text);
            background: #f8f9fa;
            padding: 2px 8px;
            border-radius: 4px;
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
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        
        .footer-links a {
            color: var(--white);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: var(--primary-gold);
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .content-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .map-frame {
                height: 500px;
            }
        }
        
        @media (max-width: 768px) {
            .nav-menu {
                gap: 1rem;
            }
            
            .about-hero h1 {
                font-size: 2.2rem;
            }
            
            .page-title {
                font-size: 2rem;
            }
            
            .map-frame {
                height: 400px;
            }
            
            .footer-links {
                flex-direction: column;
                gap: 1rem;
            }
        }
        
        @media (max-width: 480px) {
            .nav-container {
                flex-direction: column;
                gap: 1rem;
            }
            
            .about-hero {
                padding: 4rem 1rem;
            }
            
            .about-hero h1 {
                font-size: 1.8rem;
            }
            
            .mission-grid,
            .process-steps {
                grid-template-columns: 1fr;
            }
            
            .map-frame {
                height: 300px;
            }
            
            .store-info-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body class="<?php echo $current_theme === 'dark' ? 'dark-mode' : ''; ?>">
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
    <section class="about-hero">
        <h1>About Timeless Tokens</h1>
        <p>Crafting Memories into Wearable Art Since 2024</p>
    </section>

    <!-- Main Content -->
    <main class="container">
        <!-- Our Story Section -->
        <section class="about-section">
            <h2 class="section-title">Our Story</h2>
            <div class="content-grid">
                <div>
                    <img src="photo/Jewelery_Workshop_1.jpg" alt="Jewelry Workshop" class="about-image">
                </div>
                <div class="about-content">
                    <p>Founded with a passion for preserving precious moments, Timeless Tokens Jewelry began as a small workshop dedicated to creating meaningful, personalized pieces. We believe that jewelry should tell a story – your story.</p>
                    <p>Every piece we create is infused with care and attention to detail, ensuring that your memories are preserved in a beautiful, lasting form. From custom engravings to personalized designs, each creation carries the emotional significance that makes it truly unique.</p>
                    <p>What started as a humble passion project has grown into a beloved brand trusted by thousands of customers worldwide. Our commitment to quality craftsmanship and personalized service remains at the heart of everything we do.</p>
                </div>
            </div>
        </section>

        <!-- Mission & Values Section -->
        <section class="mission-section">
            <div class="container">
                <h2 class="section-title">Our Mission & Values</h2>
                <div class="mission-grid">
                    <div class="mission-card">
                        <div class="mission-icon">🎯</div>
                        <h3 class="mission-title">Our Vision</h3>
                        <p>To become the leading provider of personalized jewelry that celebrates life's most precious moments, creating heirlooms that last for generations.</p>
                    </div>
                    <div class="mission-card">
                        <div class="mission-icon">💎</div>
                        <h3 class="mission-title">Our Values</h3>
                        <p>Quality craftsmanship, personalized service, and creating pieces that hold emotional significance. We believe in integrity, creativity, and the power of meaningful connections.</p>
                    </div>
                    <div class="mission-card">
                        <div class="mission-icon">🤝</div>
                        <h3 class="mission-title">Our Promise</h3>
                        <p>Each piece is made with the utmost care and attention to detail, ensuring it becomes a cherished heirloom that tells your unique story for years to come.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Crafting Process Section -->
        <section class="process-section">
            <h2 class="section-title">Our Crafting Process</h2>
            <div class="process-steps">
                <div class="process-step">
                    <div class="step-number">1</div>
                    <h3 class="step-title">Consultation</h3>
                    <p>We discuss your vision and the story you want to tell through your jewelry, understanding your unique needs and preferences.</p>
                </div>
                <div class="process-step">
                    <div class="step-number">2</div>
                    <h3 class="step-title">Design</h3>
                    <p>Our designers create a custom mockup for your approval, ensuring every detail matches your vision perfectly.</p>
                </div>
                <div class="process-step">
                    <div class="step-number">3</div>
                    <h3 class="step-title">Crafting</h3>
                    <p>Skilled artisans bring your design to life with precision and care, using only the finest materials and techniques.</p>
                </div>
                <div class="process-step">
                    <div class="step-number">4</div>
                    <h3 class="step-title">Delivery</h3>
                    <p>Your finished piece is carefully packaged and delivered to you, ready to become a part of your story.</p>
                </div>
            </div>
        </section>

        <!-- Founder Section -->
        <section class="founder-section">
            <div class="founder-content">
                <img src="https://images.unsplash.com/photo-1494790108755-2616b612b786?w=200" alt="Mariel Miao" class="founder-image">
                <h2 class="founder-name">Mariel Miao</h2>
                <p class="founder-title">Founder & Lead Designer</p>
                <p>"I started Timeless Tokens with a simple belief: that jewelry should be more than just decoration. It should carry meaning, tell stories, and preserve the moments that matter most. Every piece we create is a testament to the beautiful stories our customers entrust us with."</p>
            </div>
        </section>
        <section class="map-section">
            <h2 class="section-title">Visit Our Store in Queen Victoria Building</h2>
            
            <div class="map-container">
                <iframe 
                    class="map-frame" 
                    src="https://map.baidu.com/poi/Fossil/@16832442.68933116,-3987633.0,19z?uid=071009f3b4bec5aea3a93430&ugc_type=3&ugc_ver=1&device_ratio=2&compat=1&en_uid=071009f3b4bec5aea3a93430&pcevaname=pc4.1&querytype=detailConInfo&da_src=shareurl" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Timeless Tokens at Fossil Store in Queen Victoria Building, Sydney">
                </iframe>
            </div>
            
            <div class="store-info-card">
                <div class="store-rating">
                    <div class="rating-stars">★★★★☆</div>
                    <span class="rating-score">3.8 / 5.0</span>
                    <span style="color: var(--light-text); font-size: 0.9rem;">(Based on customer reviews)</span>
                </div>
                
                <h3 style="color: var(--primary-gold); margin-bottom: 1rem; font-size: 1.4rem;">
                    Timeless Tokens at Fossil Store
                </h3>
                
                <div class="map-details">
                    <div class="map-details-item">
                        <span class="map-details-icon">🏬</span>
                        <span>
                            <strong>Queen Victoria Building</strong><br>
                            Shop L-0081 Lower Ground Floor<br>
                            456 George Street, Sydney NSW 2000
                        </span>
                    </div>
                    <div class="map-details-item">
                        <span class="map-details-icon">📞</span>
                        <span>+61 3 8287 8778</span>
                    </div>
                    <div class="map-details-item">
                        <span class="map-details-icon">✉️</span>
                        <span>queenvic@timelesstokens.com.au</span>
                    </div>
                    <div class="map-details-item">
                        <span class="map-details-icon">🕒</span>
                        <span>
                            <strong>Opening Hours:</strong><br>
                            Monday - Saturday: 9:00 AM - 6:00 PM<br>
                            Sunday: 10:00 AM - 5:00 PM
                        </span>
                    </div>
                </div>
                
                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
                    <p style="color: var(--light-text); font-size: 0.95rem; line-height: 1.6;">
                        <strong>About this location:</strong> Our exclusive collection is available at the Fossil store in the historic Queen Victoria Building. This iconic shopping destination in Sydney's CBD is the perfect place to discover our timeless jewelry pieces.
                    </p>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="about-section">
            <h2 class="section-title">Contact Us</h2>
            <p style="text-align: center; font-size: 1.2rem; margin-bottom: 2rem;">We'd love to hear from you! Get in touch with any questions or to start creating your custom piece.</p>
            
            <div class="contact-info">
                <div class="contact-grid">
                    <div class="contact-item">
                        <div class="contact-icon">📧</div>
                        <div class="contact-details">
                            <h4>Email</h4>
                            <p>hello@timelesstokens.com.au</p>
                            <p><small>We respond within 24 hours</small></p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">📞</div>
                        <div class="contact-details">
                            <h4>Phone</h4>
                            <p>+61 3 8287 8778</p>
                            <p><small>Mon-Sat, 9AM-6PM AEST</small></p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">📍</div>
                        <div class="contact-details">
                            <h4>Store Location</h4>
                            <p>Queen Victoria Building<br>
                               Shop L-0081 Lower Ground Floor<br>
                               456 George Street, Sydney NSW 2000</p>
                            <p><small><a href="https://map.baidu.com/poi/Fossil/@16832442.68933116,-3987633.0,19z?uid=071009f3b4bec5aea3a93430&ugc_type=3&ugc_ver=1&device_ratio=2&compat=1&en_uid=071009f3b4bec5aea3a93430&pcevaname=pc4.1&querytype=detailConInfo&da_src=shareurl" target="_blank">View detailed map on Baidu →</a></small></p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">🕒</div>
                        <div class="contact-details">
                            <h4>Store Hours (AEST)</h4>
                            <p>Monday – Saturday: 9:00 AM – 6:00 PM<br>
                               Sunday: 10:00 AM – 5:00 PM<br>
                               Public Holidays: 10:00 AM – 4:00 PM</p>
                        </div>
                    </div>
                </div>
            </div>
            <div style="text-align: center; margin-top: 2rem;">
                <a href="https://map.baidu.com/poi/Fossil/@16832442.68933116,-3987633.0,19z?uid=071009f3b4bec5aea3a93430&ugc_type=3&ugc_ver=1&device_ratio=2&compat=1&en_uid=071009f3b4bec5aea3a93430&pcevaname=pc4.1&querytype=detailConInfo&da_src=shareurl" 
                   target="_blank" 
                   style="display: inline-block; background: var(--primary-gold); color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: bold; transition: background 0.3s;">
                   📍 Open in Baidu Map for Directions
                </a>
                <p style="margin-top: 10px; color: var(--light-text); font-size: 0.9rem;">
                    Get real-time directions, see store photos, and read customer reviews
                </p>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer-bottom">
        <div>
            <p>&copy;  © 2025 Timeless Tokens Jewelry|Created by Mariel</p>
            <p style="margin-top: 0.5rem; font-size: 0.9rem; color: #aaa;">
                <a href="https://map.baidu.com/poi/Fossil/@16832442.68933116,-3987633.0,19z?uid=071009f3b4bec5aea3a93430&ugc_type=3&ugc_ver=1&device_ratio=2&compat=1&en_uid=071009f3b4bec5aea3a93430&pcevaname=pc4.1&querytype=detailConInfo&da_src=shareurl" 
                   target="_blank" 
                   style="color: #aaa; text-decoration: none;">
                   Find us at Fossil Store, Queen Victoria Building on Baidu Map
                </a>
            </p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const baiduLinks = document.querySelectorAll('a[href*="map.baidu.com"]');
            baiduLinks.forEach(link => {
                link.addEventListener('click', function() {
                    console.log('Opening Baidu Map for Fossil Store location in QVB');
                });
            });
            
            const mapIframe = document.querySelector('.map-frame');
            if (mapIframe) {
                mapIframe.addEventListener('load', function() {
                    console.log('Baidu Map for Fossil Store loaded successfully');
                });
                
                mapIframe.addEventListener('error', function() {
                    console.log('Baidu Map failed to load');
                    const mapContainer = document.querySelector('.map-container');
                    if (mapContainer) {
                        mapContainer.innerHTML = `
                            <div style="width:100%;height:600px;background:#f5f5f5;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:2rem;text-align:center;border-radius:12px;">
                                <div style="font-size:3rem;color:#d4af37;margin-bottom:1rem;">📍</div>
                                <h3 style="color:#333;margin-bottom:1rem;">Timeless Tokens at Fossil Store</h3>
                                <p style="color:#666;margin-bottom:1rem;">Queen Victoria Building, Sydney</p>
                                <p style="color:#666;margin-bottom:2rem;">Shop L-0081, 456 George Street, Sydney NSW 2000</p>
                                <a href="https://map.baidu.com/poi/Fossil/@16832442.68933116,-3987633.0,19z?uid=071009f3b4bec5aea3a93430&ugc_type=3&ugc_ver=1&device_ratio=2&compat=1&en_uid=071009f3b4bec5aea3a93430&pcevaname=pc4.1&querytype=detailConInfo&da_src=shareurl" 
                                   target="_blank" 
                                   style="background:#d4af37;color:white;padding:10px 20px;border-radius:6px;text-decoration:none;">
                                   Open in Baidu Map
                                </a>
                            </div>
                        `;
                    }
                });
            }
        });
    </script>
    <?php include 'theme_toggle.php'; ?>
</body>
</html>
<?php
// Flush output buffer
if (ob_get_level() > 0) {
    ob_end_flush();
}
?>