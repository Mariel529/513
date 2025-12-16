<?php
/**
 * Theme Toggle Component
 * Contains toggle button and JavaScript functionality
 */
if (!isset($current_theme)) {
    require_once 'theme_config.php';
}
?>
<!-- Theme Toggle Button -->
<div class="theme-toggle-wrapper">
    <button class="theme-toggle" id="themeToggle" aria-label="Toggle theme" title="Switch between light and dark mode">
        <?php echo ($current_theme === 'dark') ? '☀️' : '🌙'; ?>
    </button>
</div>

<style>
/* Theme Toggle Styles */
.theme-toggle-wrapper {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
}

.theme-toggle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: var(--primary-gold, #d4af37);
    color: var(--dark-text, #2c2c2c);
    border: none;
    cursor: pointer;
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.theme-toggle:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
}

.theme-toggle:active {
    transform: scale(0.95);
}

.theme-toggle:before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle, rgba(255,255,255,0.2) 1%, transparent 1%);
    background-size: 10px 10px;
    opacity: 0;
    transition: opacity 0.3s;
}

.theme-toggle:hover:before {
    opacity: 1;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .theme-toggle-wrapper {
        bottom: 15px;
        right: 15px;
    }
    
    .theme-toggle {
        width: 45px;
        height: 45px;
        font-size: 1.3rem;
    }
}

@media (max-width: 480px) {
    .theme-toggle-wrapper {
        bottom: 10px;
        right: 10px;
    }
    
    .theme-toggle {
        width: 40px;
        height: 40px;
        font-size: 1.2rem;
    }
}
</style>

<script>
// Theme switching functionality
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('themeToggle');
    const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
    
    // Get cookie value
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }
    
    // Apply theme to page
    function applyTheme(theme) {
        if (theme === 'dark') {
            document.body.classList.add('dark-mode');
            themeToggle.innerHTML = '☀️';
        } else {
            document.body.classList.remove('dark-mode');
            themeToggle.innerHTML = '🌙';
        }
    }
    
    // Initialize theme on page load
    const savedTheme = getCookie('theme_preference');
    if (savedTheme) {
        applyTheme(savedTheme);
    } else if (prefersDarkScheme.matches) {
        // Use system preference
        applyTheme('dark');
        // Save to cookie
        document.cookie = "theme_preference=dark; path=/; max-age=" + 60*60*24*30;
    }
    
    // Toggle theme on button click
    themeToggle.addEventListener('click', function() {
        const isDarkMode = document.body.classList.contains('dark-mode');
        const newTheme = isDarkMode ? 'light' : 'dark';
        
        // Save to server using AJAX
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'theme_config.php');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    applyTheme(response.theme);
                    
                    // Save to cookie
                    document.cookie = "theme_preference=" + response.theme + "; path=/; max-age=" + 60*60*24*30;
                } catch (e) {
                    // Fallback to local switching
                    applyTheme(newTheme);
                    document.cookie = "theme_preference=" + newTheme + "; path=/; max-age=" + 60*60*24*30;
                }
            }
        };
        
        xhr.onerror = function() {
            // Network error, use local switching
            applyTheme(newTheme);
            document.cookie = "theme_preference=" + newTheme + "; path=/; max-age=" + 60*60*24*30;
        };
        
        xhr.send('toggle_theme=1');
    });
    
    // Listen for system theme changes
    prefersDarkScheme.addEventListener('change', function(e) {
        if (!getCookie('theme_preference')) {
            applyTheme(e.matches ? 'dark' : 'light');
        }
    });
});
</script>