/**
 * Banking DVWA Project
 * Security Display Script
 * 
 * This script handles the display of security levels and code comparisons.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize security level selector
    initSecurityLevelSelector();
    
    // Initialize vulnerability tabs if they exist
    initVulnerabilityTabs();
    
    // Initialize code comparison if it exists
    initCodeComparison();
});

/**
 * Initialize security level selector
 */
function initSecurityLevelSelector() {
    const securitySelector = document.querySelector('.security-level-select');
    
    if (securitySelector) {
        // Add change event listener
        securitySelector.addEventListener('change', function() {
            // Add "changed" class to highlight the change
            this.parentElement.classList.add('security-level-changed');
            
            // Auto-submit the form after a short delay
            setTimeout(() => {
                this.closest('form').submit();
            }, 500);
        });
        
        // Add visual indicators for security levels
        updateSecurityLevelStyles();
    }
}

/**
 * Update security level styles based on selected level
 */
function updateSecurityLevelStyles() {
    const securitySelector = document.querySelector('.security-level-select');
    
    if (securitySelector) {
        const currentLevel = securitySelector.value;
        securitySelector.className = securitySelector.className.replace(/security-level-(low|medium|high)/, '').trim();
        securitySelector.classList.add(`security-level-${currentLevel}`);
        
        // Update the form container class
        const formContainer = securitySelector.closest('.form-group');
        if (formContainer) {
            formContainer.className = formContainer.className.replace(/security-level-(low|medium|high)/, '').trim();
            formContainer.classList.add(`security-level-${currentLevel}`);
        }
    }
}

/**
 * Initialize vulnerability tabs
 */
function initVulnerabilityTabs() {
    const tabLinks = document.querySelectorAll('.vulnerability-tabs .nav-link');
    
    if (tabLinks.length) {
        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all tabs
                tabLinks.forEach(tab => tab.classList.remove('active'));
                
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Hide all tab content
                const tabContents = document.querySelectorAll('.vulnerability-tab-content');
                tabContents.forEach(content => content.classList.remove('active'));
                
                // Show the corresponding tab content
                const targetId = this.getAttribute('href').substring(1);
                const targetContent = document.getElementById(targetId);
                if (targetContent) {
                    targetContent.classList.add('active');
                }
            });
        });
    }
}

/**
 * Initialize code comparison display
 */
function initCodeComparison() {
    const codeTabs = document.querySelectorAll('.code-comparison-tabs .nav-link');
    
    if (codeTabs.length) {
        codeTabs.forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all tabs
                codeTabs.forEach(t => t.classList.remove('active'));
                
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Hide all code blocks
                const codeBlocks = document.querySelectorAll('.code-comparison-content');
                codeBlocks.forEach(block => block.classList.remove('active'));
                
                // Show the corresponding code block
                const targetId = this.getAttribute('href').substring(1);
                const targetBlock = document.getElementById(targetId);
                if (targetBlock) {
                    targetBlock.classList.add('active');
                }
            });
        });
    }
}

/**
 * Add a visual glitch effect to an element
 * 
 * @param {HTMLElement} element The element to add the effect to
 */
function addGlitchEffect(element) {
    if (!element) return;
    
    // Add glitch class
    element.classList.add('cyber-glitch');
    
    // Remove after animation completes
    setTimeout(() => {
        element.classList.remove('cyber-glitch');
    }, 1000);
}