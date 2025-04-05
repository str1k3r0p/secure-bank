/**
 * Banking DVWA Project
 * Main JavaScript
 * 
 * This file contains the main JavaScript functionality for the application.
 */

// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    initTooltips();
    
    // Initialize cyberpunk animations
    initCyberpunkEffects();
    
    // Initialize flash message auto-hide
    initFlashMessages();
    
    // Initialize form validation
    initFormValidation();
});

/**
 * Initialize Bootstrap tooltips
 */
function initTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Initialize cyberpunk visual effects
 */
function initCyberpunkEffects() {
    // Glitch effect for cyberpunk titles
    const titles = document.querySelectorAll('.cyber-title');
    titles.forEach(title => {
        const text = title.getAttribute('data-text');
        if (text) {
            // Add glitch animation randomly
            setInterval(() => {
                if (Math.random() > 0.95) {
                    title.classList.add('glitch');
                    setTimeout(() => {
                        title.classList.remove('glitch');
                    }, 200);
                }
            }, 2000);
        }
    });
    
    // Neon flicker effect for neon text
    const neonElements = document.querySelectorAll('.text-neon');
    neonElements.forEach(element => {
        // Add flicker animation randomly
        setInterval(() => {
            if (Math.random() > 0.97) {
                element.style.opacity = 0.7;
                setTimeout(() => {
                    element.style.opacity = 1;
                }, 100);
            }
        }, 3000);
    });
    
    // Scan line effect for containers
    const scanLines = document.createElement('div');
    scanLines.classList.add('scan-lines');
    document.body.appendChild(scanLines);
}

/**
 * Initialize auto-hide for flash messages
 */
function initFlashMessages() {
    const flashMessages = document.querySelectorAll('.alert:not(.alert-permanent)');
    flashMessages.forEach(message => {
        // Auto-hide after 5 seconds
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => {
                message.style.display = 'none';
            }, 500);
        }, 5000);
        
        // Add close button functionality
        const closeBtn = message.querySelector('.close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                message.style.opacity = '0';
                setTimeout(() => {
                    message.style.display = 'none';
                }, 500);
            });
        }
    });
}

/**
 * Initialize form validation
 */
function initFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
}

/**
 * Show a confirmation dialog
 * 
 * @param {string} message The confirmation message
 * @param {function} callback The function to call if confirmed
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

/**
 * Format currency
 * 
 * @param {number} amount The amount to format
 * @param {string} currency The currency code
 * @returns {string} The formatted currency
 */
function formatCurrency(amount, currency = 'USD') {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency
    }).format(amount);
}

/**
 * Format date
 * 
 * @param {string} dateString The date string
 * @param {string} format The format to use
 * @returns {string} The formatted date
 */
function formatDate(dateString, format = 'short') {
    const date = new Date(dateString);
    
    if (format === 'short') {
        return date.toLocaleDateString();
    } else if (format === 'long') {
        return date.toLocaleDateString(undefined, { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
    } else if (format === 'datetime') {
        return date.toLocaleString();
    }
    
    return date.toLocaleDateString();
}