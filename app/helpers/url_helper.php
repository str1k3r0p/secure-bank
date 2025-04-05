<?php
/**
 * Banking DVWA Project
 * URL Helper Functions
 * 
 * This file contains helper functions for URL manipulation.
 */

// Prevent direct access to this file
if (!defined('ROOT_PATH')) {
    die('Direct access to this file is not allowed.');
}

/**
 * Get the base URL of the application
 * 
 * @return string Base URL
 */
function base_url()
{
    return APP_URL;
}

/**
 * Generate a URL for a route
 * 
 * @param string $path The path
 * @param array $params Query parameters
 * @param bool $absolute Use absolute URL
 * @return string Generated URL
 */
function url($path = '', $params = [], $absolute = true)
{
    $path = trim($path, '/');
    $url = $absolute ? APP_URL : '';
    
    if (!empty($path)) {
        $url .= '/' . $path;
    }
    
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    
    return $url;
}

/**
 * Generate a URL for an asset
 * 
 * @param string $path Asset path
 * @param bool $absolute Use absolute URL
 * @return string Asset URL
 */
function asset_url($path = '', $absolute = true)
{
    $path = trim($path, '/');
    $url = $absolute ? APP_URL : '';
    
    return $url . '/public/' . $path;
}

/**
 * Generate a URL for a CSS file
 * 
 * @param string $file CSS file name
 * @return string CSS URL
 */
function css_url($file)
{
    return asset_url('css/' . $file);
}

/**
 * Generate a URL for a JavaScript file
 * 
 * @param string $file JavaScript file name
 * @return string JavaScript URL
 */
function js_url($file)
{
    return asset_url('js/' . $file);
}

/**
 * Generate a URL for an image
 * 
 * @param string $file Image file name
 * @return string Image URL
 */
function image_url($file)
{
    return asset_url('assets/images/' . $file);
}

/**
 * Create an HTML link
 * 
 * @param string $text Link text
 * @param string $url Link URL
 * @param array $attributes Additional link attributes
 * @return string Link HTML
 */
function link_to($text, $url = '', $attributes = [])
{
    // Set default attributes
    $attr = [
        'href' => url($url)
    ];
    
    // Merge with additional attributes
    $attr = array_merge($attr, $attributes);
    
    // Build attributes string
    $attr_str = '';
    foreach ($attr as $key => $value) {
        $attr_str .= " {$key}=\"{$value}\"";
    }
    
    return "<a{$attr_str}>" . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . "</a>";
}

/**
 * Redirect to a URL
 * 
 * @param string $url The URL to redirect to
 * @param int $status HTTP status code
 */
function redirect($url = '', $status = 302)
{
    if (!preg_match('/^https?:\/\//', $url)) {
        $url = url($url);
    }
    
    header('Location: ' . $url, true, $status);
    exit;
}

/**
 * Generate a pagination HTML
 * 
 * @param int $currentPage Current page number
 * @param int $totalPages Total number of pages
 * @param string $url Base URL for pagination
 * @param array $queryParams Additional query parameters
 * @return string Pagination HTML
 */
function pagination($currentPage, $totalPages, $url = '', $queryParams = [])
{
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<nav aria-label="Page navigation"><ul class="pagination">';
    
    // Previous button
    if ($currentPage > 1) {
        $prevParams = array_merge($queryParams, ['page' => $currentPage - 1]);
        $html .= '<li class="page-item"><a class="page-link" href="' . url($url, $prevParams) . '">&laquo; Previous</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">&laquo; Previous</span></li>';
    }
    
    // Page numbers
    $range = 2; // Number of pages on each side of current page
    
    // Start point
    $start = max(1, $currentPage - $range);
    
    // End point
    $end = min($totalPages, $currentPage + $range);
    
    // First page
    if ($start > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . url($url, array_merge($queryParams, ['page' => 1])) . '">1</a></li>';
        
        if ($start > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    // Page links
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $currentPage) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . url($url, array_merge($queryParams, ['page' => $i])) . '">' . $i . '</a></li>';
        }
    }
    
    // Last page
    if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        
        $html .= '<li class="page-item"><a class="page-link" href="' . url($url, array_merge($queryParams, ['page' => $totalPages])) . '">' . $totalPages . '</a></li>';
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $nextParams = array_merge($queryParams, ['page' => $currentPage + 1]);
        $html .= '<li class="page-item"><a class="page-link" href="' . url($url, $nextParams) . '">Next &raquo;</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Next &raquo;</span></li>';
    }
    
    $html .= '</ul></nav>';
    
    return $html;
}

/**
 * Get current URL
 * 
 * @param bool $query Include query string
 * @return string Current URL
 */
function current_url($query = true)
{
    $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    
    if (!$query && strpos($url, '?') !== false) {
        $url = substr($url, 0, strpos($url, '?'));
    }
    
    return $url;
}

/**
 * Generate a query string from an array
 * 
 * @param array $data Query parameters
 * @return string Query string
 */
function query_string($data)
{
    return http_build_query($data);
}

/**
 * Get current path (without base URL)
 * 
 * @return string Current path
 */
function current_path()
{
    $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    $base_path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $base_path = $base_path === '/' ? '' : $base_path;
    
    $path = str_replace($base_path, '', $request_uri);
    
    if (strpos($path, '?') !== false) {
        $path = substr($path, 0, strpos($path, '?'));
    }
    
    return trim($path, '/');
}

/**
 * Check if the current URL matches a given path
 * 
 * @param string $path Path to check
 * @return bool True if the path matches
 */
function is_current($path)
{
    $current = current_path();
    $path = trim($path, '/');
    
    return $current === $path;
}
