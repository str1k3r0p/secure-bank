/**
 * Banking DVWA Project
 * Source Code Viewer Script
 * 
 * This script handles syntax highlighting and comparison of vulnerability source code.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize code highlighting
    initSyntaxHighlighting();
    
    // Initialize security level tabs
    initSecurityLevelTabs();
    
    // Initialize line highlighting
    initLineHighlighting();
    
    // Initialize diff view if enabled
    initDiffView();
});

/**
 * Initialize syntax highlighting for code blocks
 */
function initSyntaxHighlighting() {
    // Apply syntax highlighting to all code blocks
    document.querySelectorAll('pre code').forEach((block) => {
        hljs.highlightBlock(block);
    });
}

/**
 * Initialize security level tabs for source code
 */
function initSecurityLevelTabs() {
    const securityTabs = document.querySelectorAll('.security-level-tabs .nav-link');
    
    if (securityTabs.length) {
        securityTabs.forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all tabs
                securityTabs.forEach(t => t.classList.remove('active'));
                
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Hide all code containers
                const codeContainers = document.querySelectorAll('.security-level-code');
                codeContainers.forEach(container => container.classList.remove('active'));
                
                // Show the corresponding code container
                const targetId = this.getAttribute('href').substring(1);
                const targetContainer = document.getElementById(targetId);
                if (targetContainer) {
                    targetContainer.classList.add('active');
                }
            });
        });
    }
}

/**
 * Initialize line highlighting for code blocks
 */
function initLineHighlighting() {
    // Add line numbers to code blocks
    document.querySelectorAll('.line-numbers pre code').forEach((block) => {
        // Get the content
        const content = block.innerHTML;
        
        // Split into lines
        const lines = content.split('\n');
        
        // Add line number spans
        let numberedContent = '';
        lines.forEach((line, index) => {
            numberedContent += `<span class="line-number" data-line="${index + 1}"></span>${line}\n`;
        });
        
        // Set the new content
        block.innerHTML = numberedContent;
    });
    
    // Add click handler for line highlighting
    document.querySelectorAll('.line-numbers pre code').forEach((block) => {
        block.addEventListener('click', function(e) {
            // Check if clicked on a line number
            if (e.target.classList.contains('line-number')) {
                // Toggle highlight class
                e.target.classList.toggle('highlight');
            }
        });
    });
}

/**
 * Initialize diff view for comparing security levels
 */
function initDiffView() {
    const diffToggle = document.getElementById('toggle-diff-view');
    
    if (diffToggle) {
        diffToggle.addEventListener('click', function() {
            const diffContainer = document.querySelector('.diff-view-container');
            
            if (diffContainer) {
                // Toggle visibility
                diffContainer.classList.toggle('active');
                
                // Toggle button text
                this.textContent = diffContainer.classList.contains('active') 
                    ? 'Hide Diff View' 
                    : 'Show Diff View';
                
                // Generate diff if container is now active
                if (diffContainer.classList.contains('active')) {
                    generateDiffView();
                }
            }
        });
    }
}

/**
 * Generate diff view comparing security levels
 */
function generateDiffView() {
    const diffContainer = document.querySelector('.diff-view-container .diff-content');
    
    if (!diffContainer) return;
    
    // Get code from different security levels
    const lowCode = document.querySelector('#low-security-code code')?.textContent || '';
    const mediumCode = document.querySelector('#medium-security-code code')?.textContent || '';
    const highCode = document.querySelector('#high-security-code code')?.textContent || '';
    
    // Clear existing content
    diffContainer.innerHTML = '';
    
    // Create low vs medium diff
    if (lowCode && mediumCode) {
        const lowVsMedium = document.createElement('div');
        lowVsMedium.className = 'diff-section';
        lowVsMedium.innerHTML = `
            <h5>Low vs Medium Security</h5>
            <pre class="diff-display"><code class="language-diff">${generateDiff(lowCode, mediumCode)}</code></pre>
        `;
        diffContainer.appendChild(lowVsMedium);
    }
    
    // Create medium vs high diff
    if (mediumCode && highCode) {
        const mediumVsHigh = document.createElement('div');
        mediumVsHigh.className = 'diff-section';
        mediumVsHigh.innerHTML = `
            <h5>Medium vs High Security</h5>
            <pre class="diff-display"><code class="language-diff">${generateDiff(mediumCode, highCode)}</code></pre>
        `;
        diffContainer.appendChild(mediumVsHigh);
    }
    
    // Highlight diff code
    document.querySelectorAll('.diff-display code').forEach((block) => {
        hljs.highlightBlock(block);
    });
}

/**
 * Generate a simple diff between two code snippets
 * 
 * @param {string} oldCode The original code
 * @param {string} newCode The new code
 * @return {string} The diff output
 */
function generateDiff(oldCode, newCode) {
    // Split into lines
    const oldLines = oldCode.split('\n');
    const newLines = newCode.split('\n');
    
    // Simple line-by-line diff
    let diffOutput = '';
    
    // Find maximum length
    const maxLength = Math.max(oldLines.length, newLines.length);
    
    for (let i = 0; i < maxLength; i++) {
        const oldLine = oldLines[i] || '';
        const newLine = newLines[i] || '';
        
        if (oldLine === newLine) {
            // Unchanged line
            diffOutput += ' ' + oldLine + '\n';
        } else {
            // Changed line
            diffOutput += '-' + oldLine + '\n';
            diffOutput += '+' + newLine + '\n';
        }
    }
    
    return diffOutput;
}