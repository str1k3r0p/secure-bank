<?php
/**
 * Banking DVWA Project
 * Display Helper Functions
 * 
 * This file contains helper functions for displaying content.
 */

// Prevent direct access to this file
if (!defined('ROOT_PATH')) {
    die('Direct access to this file is not allowed.');
}

/**
 * Format a number as currency
 * 
 * @param float $amount The amount to format
 * @param string $currency Currency code
 * @return string Formatted currency
 */
function format_currency($amount, $currency = DEFAULT_CURRENCY)
{
    switch ($currency) {
        case 'USD':
            $symbol = '$';
            break;
        case 'EUR':
            $symbol = '€';
            break;
        case 'GBP':
            $symbol = '£';
            break;
        default:
            $symbol = $currency;
            break;
    }
    
    return $symbol . number_format($amount, 2);
}

/**
 * Format a date
 * 
 * @param string $date Date string
 * @param string $format Date format
 * @return string Formatted date
 */
function format_date($date, $format = DATE_FORMAT)
{
    if (!$date) {
        return '';
    }
    
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    
    if ($timestamp === false) {
        return $date;
    }
    
    return date($format, $timestamp);
}

/**
 * Format a date and time
 * 
 * @param string $datetime Date and time string
 * @param string $format Date and time format
 * @return string Formatted date and time
 */
function format_datetime($datetime, $format = DATETIME_FORMAT)
{
    return format_date($datetime, $format);
}

/**
 * Format a filesize
 * 
 * @param int $bytes Size in bytes
 * @param int $precision Decimal precision
 * @return string Formatted filesize
 */
function format_filesize($bytes, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Truncate a string to a specified length
 * 
 * @param string $string The string to truncate
 * @param int $length Maximum length
 * @param string $append String to append if truncated
 * @return string Truncated string
 */
function truncate($string, $length = 100, $append = '...')
{
    if (strlen($string) <= $length) {
        return $string;
    }
    
    $string = substr($string, 0, $length);
    
    if (strpos($string, ' ') !== false) {
        $string = substr($string, 0, strrpos($string, ' '));
    }
    
    return $string . $append;
}

/**
 * Highlight a search term in a string
 * 
 * @param string $string The string to search in
 * @param string $term The term to highlight
 * @param string $highlightStart Opening HTML for highlight
 * @param string $highlightEnd Closing HTML for highlight
 * @return string String with highlighted terms
 */
function highlight_search_term($string, $term, $highlightStart = '<span class="highlight">', $highlightEnd = '</span>')
{
    if (empty($term) || empty($string)) {
        return $string;
    }
    
    $term = preg_quote($term, '/');
    
    return preg_replace('/(' . $term . ')/i', $highlightStart . '$1' . $highlightEnd, $string);
}

/**
 * Convert newlines to HTML line breaks
 * 
 * @param string $string The string to convert
 * @return string String with line breaks
 */
function nl2br_safe($string)
{
    return nl2br(htmlspecialchars($string, ENT_QUOTES, 'UTF-8'));
}

/**
 * Get a gravatar URL for an email address
 * 
 * @param string $email Email address
 * @param int $size Image size in pixels
 * @param string $default Default image type
 * @param string $rating Maximum rating
 * @return string Gravatar URL
 */
function get_gravatar($email, $size = 80, $default = 'mp', $rating = 'g')
{
    $hash = md5(strtolower(trim($email)));
    return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d={$default}&r={$rating}";
}

/**
 * Display a status badge
 * 
 * @param string $status Status text
 * @param string $type Badge type (success, warning, danger, info, etc.)
 * @return string Badge HTML
 */
function status_badge($status, $type = null)
{
    if ($type === null) {
        switch (strtolower($status)) {
            case 'active':
            case 'completed':
            case 'approved':
            case 'success':
                $type = 'success';
                break;
            case 'pending':
            case 'processing':
            case 'wait':
                $type = 'warning';
                break;
            case 'inactive':
            case 'failed':
            case 'declined':
            case 'error':
                $type = 'danger';
                break;
            default:
                $type = 'info';
                break;
        }
    }
    
    return '<span class="badge badge-' . $type . '">' . htmlspecialchars($status, ENT_QUOTES, 'UTF-8') . '</span>';
}

/**
 * Generate a random color hex code
 * 
 * @param bool $darkOnly Only generate dark colors
 * @return string Hex color code
 */
function random_color($darkOnly = false)
{
    if ($darkOnly) {
        $r = mt_rand(0, 100);
        $g = mt_rand(0, 100);
        $b = mt_rand(0, 100);
    } else {
        $r = mt_rand(0, 255);
        $g = mt_rand(0, 255);
        $b = mt_rand(0, 255);
    }
    
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}

/**
 * Display a progress bar
 * 
 * @param int $value Current value
 * @param int $max Maximum value
 * @param string $type Bar type (success, warning, danger, info, etc.)
 * @param bool $showLabel Show percentage label
 * @return string Progress bar HTML
 */
function progress_bar($value, $max = 100, $type = 'primary', $showLabel = true)
{
    $percent = ($value / $max) * 100;
    $percent = round($percent, 2);
    
    $html = '<div class="progress">';
    $html .= '<div class="progress-bar bg-' . $type . '" role="progressbar" style="width: ' . $percent . '%"';
    $html .= ' aria-valuenow="' . $percent . '" aria-valuemin="0" aria-valuemax="100">';
    
    if ($showLabel) {
        $html .= $percent . '%';
    }
    
    $html .= '</div></div>';
    
    return $html;
}

/**
 * Format a phone number
 * 
 * @param string $phone Phone number
 * @param string $format Format pattern
 * @return string Formatted phone number
 */
function format_phone($phone, $format = '(xxx) xxx-xxxx')
{
    // Remove all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Format the phone number
    $count = 0;
    $formatted = preg_replace_callback('/x/i', function($matches) use($phone, &$count) {
        return isset($phone[$count]) ? $phone[$count++] : '';
    }, $format);
    
    return $formatted;
}

/**
 * Convert a time to "time ago" format
 * 
 * @param string $datetime Date and time
 * @param bool $full Show full date for older times
 * @return string Time ago
 */
function time_ago($datetime, $full = false)
{
    $now = new \DateTime;
    $ago = new \DateTime($datetime);
    $diff = $now->diff($ago);
    
    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;
    
    $string = [
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    ];
    
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }
    
    if (!$full) {
        $string = array_slice($string, 0, 1);
    }
    
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

/**
 * Generate a color based on a string (e.g. for user avatars)
 * 
 * @param string $string Input string
 * @return string Hex color code
 */
function string_to_color($string)
{
    $hash = md5($string);
    
    // Use first 6 chars for color
    $hex = substr($hash, 0, 6);
    
    return '#' . $hex;
}

/**
 * Get the first letter(s) of each word in a string
 * 
 * @param string $string Input string
 * @param int $length Number of letters to return
 * @return string Initials
 */
function get_initials($string, $length = 2)
{
    $words = explode(' ', $string);
    $initials = '';
    
    foreach ($words as $word) {
        if (!empty($word)) {
            $initials .= strtoupper(substr($word, 0, 1));
            
            if (strlen($initials) >= $length) {
                break;
            }
        }
    }
    
    return $initials;
}

/**
 * Convert a string to title case
 * 
 * @param string $string Input string
 * @return string Title case string
 */
function title_case($string)
{
    // List of small words that should not be capitalized
    $small_words = ['a', 'an', 'and', 'as', 'at', 'but', 'by', 'for', 'if', 'in', 'nor', 'of', 'on', 'or', 'the', 'to', 'via', 'vs'];
    
    // Split into words
    $words = explode(' ', strtolower($string));
    
    // Capitalize first and last word, and all other words that are not small
    foreach ($words as $key => $word) {
        // Always capitalize first and last word
        if ($key === 0 || $key === count($words) - 1) {
            $words[$key] = ucfirst($word);
        } 
        // Don't capitalize small words unless it's the first or last word
        elseif (!in_array($word, $small_words)) {
            $words[$key] = ucfirst($word);
        }
    }
    
    return implode(' ', $words);
}
