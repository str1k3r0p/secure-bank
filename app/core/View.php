<?php
/**
 * Banking DVWA Project
 * View Class
 * 
 * This class handles rendering of views.
 */

namespace App\Core;

class View
{
    /**
     * @var array Data to pass to the view
     */
    private $data = [];
    
    /**
     * Render a view
     * 
     * @param string $view The view to render
     * @param array $data The data to pass to the view
     * @param string $layout The layout to use
     */
    public function render($view, $data = [], $layout = 'main')
    {
        // Set the data
        $this->data = $data;
        
        // Get the content of the view
        $content = $this->getViewContent($view);
        
        // If a layout is specified, render the view within the layout
        if ($layout) {
            $layout_path = LAYOUT_PATH . '/' . $layout . '.php';
            if (file_exists($layout_path)) {
                // Set the content variable for the layout
                $this->data['content'] = $content;
                
                // Include the layout
                include_once $layout_path;
            } else {
                // If the layout doesn't exist, just output the view content
                echo $content;
            }
        } else {
            // If no layout is specified, just output the view content
            echo $content;
        }
    }
    
    /**
     * Get the content of a view
     * 
     * @param string $view The view to get
     * @return string The view content
     */
    private function getViewContent($view)
    {
        // Extract the data variables into the local scope
        extract($this->data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        $view_path = VIEW_PATH . '/' . $view . '.php';
        if (file_exists($view_path)) {
            include_once $view_path;
        } else {
            echo "Error: View '{$view}' not found.";
        }
        
        // Return the buffered content
        return ob_get_clean();
    }
    
    /**
     * Render a partial view
     * 
     * @param string $partial The partial view to render
     * @param array $data The data to pass to the partial
     */
    public function partial($partial, $data = [])
    {
        // Merge the partial data with the existing data
        $partial_data = array_merge($this->data, $data);
        
        // Extract the data variables into the local scope
        extract($partial_data);
        
        // Include the partial view file
        $partial_path = VIEW_PATH . '/partials/' . $partial . '.php';
        if (file_exists($partial_path)) {
            include_once $partial_path;
        } else {
            echo "Error: Partial '{$partial}' not found.";
        }
    }
    
    /**
     * Escape HTML special characters
     * 
     * @param string $value The value to escape
     * @return string The escaped value
     */
    public function escape($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Format a date
     * 
     * @param string $date The date to format
     * @param string $format The format to use
     * @return string The formatted date
     */
    public function formatDate($date, $format = DATE_FORMAT)
    {
        return format_date($date, $format);
    }
    
    /**
     * Format currency
     * 
     * @param float $amount The amount to format
     * @param string $currency The currency code
     * @return string The formatted currency
     */
    public function formatCurrency($amount, $currency = DEFAULT_CURRENCY)
    {
        return format_currency($amount, $currency);
    }
    
    /**
     * Generate a URL
     * 
     * @param string $route The route
     * @param array $params The parameters
     * @return string The URL
     */
    public function url($route, $params = [])
    {
        return Router::url($route, $params);
    }
    
    /**
     * Get CSRF token field
     * 
     * @return string The CSRF token field HTML
     */
    public function csrfField()
    {
        $token = $this->data[CSRF_TOKEN_NAME] ?? generate_csrf_token();
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . $token . '">';
    }
    
    /**
     * Check if a flash message exists
     * 
     * @return bool True if a flash message exists, false otherwise
     */
    public function hasFlash()
    {
        return isset($this->data['flash_message']) && !empty($this->data['flash_message']);
    }
    
    /**
     * Display a flash message
     */
    public function flash()
    {
        if ($this->hasFlash()) {
            $type = $this->data['flash_message']['type'];
            $message = $this->data['flash_message']['message'];
            
            echo '<div class="alert alert-' . $type . '">' . $message . '</div>';
        }
    }
}