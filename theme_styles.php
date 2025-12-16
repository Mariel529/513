<?php
/**
 * Theme Styles Definition
 * Contains CSS variables and theme-specific styles
 */
if (!isset($current_theme)) {
    require_once 'theme_config.php';
}
?>
<style>
/* CSS Variable Definitions */
:root {
    /* Light Mode Variables */
    --primary-gold: #d4af37;
    --secondary-gold: #b8941f;
    --dark-text: #2c2c2c;
    --light-text: #666;
    --background: #fefefe;
    --border: #e8e8e8;
    --white: #ffffff;
    --admin-blue: #2c5282;
    --admin-light-blue: #4299e1;
    --card-bg: #ffffff;
    --feature-bg: #f8f8f8;
    --footer-bg: #1a1a1a;
    --shadow-color: rgba(0,0,0,0.1);
    --hero-overlay: rgba(0,0,0,0.7);
}

/* Dark Mode Variables */
.dark-mode {
    --dark-text: #e0e0e0;
    --light-text: #b0b0b0;
    --background: #121212;
    --border: #333333;
    --white: #1e1e1e;
    --card-bg: #1e1e1e;
    --feature-bg: #1a1a1a;
    --footer-bg: #0a0a0a;
    --shadow-color: rgba(0,0,0,0.3);
    --hero-overlay: rgba(0,0,0,0.8);
}

/* Apply to elements */
body {
    font-family: 'Georgia', 'Times New Roman', serif;
    line-height: 1.6;
    color: var(--dark-text);
    background-color: var(--background);
    transition: background-color 0.3s, color 0.3s;
}

/* Header */
.header {
    background: linear-gradient(135deg, #1a1a1a 0%, #2c2c2c 100%);
    color: var(--white);
    padding: 1rem 0;
    position: sticky;
    top: 0;
    z-index: 1000;
    transition: background 0.3s;
}

.dark-mode .header {
    background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
}

/* Hero Section */
.hero {
    background: linear-gradient(var(--hero-overlay), var(--hero-overlay)), 
                url('https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=1200');
    background-size: cover;
    background-position: center;
    color: var(--white);
    text-align: center;
    padding: 6rem 2rem;
    transition: background 0.3s;
}

/* Cards */
.collection-card,
.testimonial-card {
    background: var(--card-bg);
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 5px 15px var(--shadow-color);
    transition: transform 0.3s, background 0.3s, box-shadow 0.3s;
}

/* Welcome Banner */
.welcome-banner {
    background: linear-gradient(135deg, #1a1a1a 0%, #2c2c2c 100%);
    color: white;
    padding: 2rem;
    margin-bottom: 2rem;
    border-radius: 8px;
    position: relative;
    overflow: hidden;
    max-width: 1200px;
    margin-left: auto;
    margin-right: auto;
    transition: background 0.3s;
}

.dark-mode .welcome-banner {
    background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
}

/* Info Cards */
.info-card {
    background: rgba(255, 255, 255, 0.1);
    padding: 1rem;
    border-radius: 6px;
    border-left: 4px solid var(--primary-gold);
    transition: all 0.3s;
}

.dark-mode .info-card {
    background: rgba(255, 255, 255, 0.05);
}

/* Features Section */
.features {
    background-color: var(--feature-bg);
    padding: 4rem 0;
    margin: 4rem 0;
    transition: background-color 0.3s;
}

/* Footer */
.footer-bottom {
    text-align: center;
    padding: 2rem;
    background-color: var(--footer-bg);
    color: var(--white);
    margin-top: 4rem;
    transition: background-color 0.3s;
}

/* Buttons */
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

.dark-mode .btn-primary {
    background-color: var(--secondary-gold);
}

.dark-mode .btn-primary:hover {
    background-color: var(--primary-gold);
}

/* Action Buttons */
.action-btn {
    padding: 0.5rem 1.5rem;
    background: var(--primary-gold);
    color: var(--dark-text);
    border: none;
    border-radius: 4px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
    font-size: 0.9rem;
}

.dark-mode .action-btn {
    background: var(--secondary-gold);
}

.dark-mode .action-btn:hover {
    background: var(--primary-gold);
}

/* Text color adjustments */
.dark-mode .collection-title,
.dark-mode .feature-title,
.dark-mode .testimonial-author,
.dark-mode .section-title {
    color: var(--dark-text);
}

.dark-mode .collection-description,
.dark-mode .feature-description,
.dark-mode .testimonial-text,
.dark-mode .testimonial-location {
    color: var(--light-text);
}
</style>