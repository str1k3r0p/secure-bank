<?php
/**
 * Banking DVWA Project
 * Base Controller Class
 * 
 * This class serves as the base for all controllers in the application.
 */

namespace App\Core;

class Controller
{
    /**
     * @var View The view object
     */
    protected $view;
    
    /**
     * @var array The request data
     */
    protected $request;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        // Initialize the view
        $this->view = new View();
        
        // Combine GET and POST data into request
        $this->request = array_merge($_GET, $_POST);
        
        // Check if CSRF protection is required
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verify CSRF token
            if (!$this->verifyCsrfToken()) {
                // Log the CSRF attempt
                Logger::warning('CSRF attempt detected', [
                    'ip' => get_client_ip(),
                    'uri' => $_SERVER['REQUEST_URI'],
                    'referer' => $_SERVER['HTTP_REFERER'] ?? 'Unknown'
                ]);
                
                // Redirect to error page
                $this->redirect('/error', [
                    'message' => 'Invalid security token. Please try again.'
                ]);
            }
        }
    }
    
    /**
     * Verify CSRF token
     * 
     * @return bool True if the token is valid, false otherwise
     */
    protected function verifyCsrfToken()
    {
        // Skip CSRF verification for specific routes if needed
        $skip_csrf_routes = [
            '/vulnerabilities/csrf-test'
        ];
        
        if (in_array($_SERVER['REQUEST_URI'], $skip_csrf_routes)) {
            return true;
        }
        
        // Verify token
        $token = $_POST[CSRF_TOKEN_NAME] ?? null;
        return verify_csrf_token($token);
    }
    
    /**
     * Render a view
     * 
     * @param string $view The view to render
     * @param array $data The data to pass to the view
     * @param string $layout The layout to use
     */
    protected function render($view, $data = [], $layout = 'main')
    {
        // Add CSRF token to data
        $data[CSRF_TOKEN_NAME] = generate_csrf_token();
        
        // Add flash message to data if exists
        $flash_message = get_flash_message();
        if ($flash_message) {
            $data['flash_message'] = $flash_message;
        }
        
        // Render the view
        $this->view->render($view, $data, $layout);
    }
    
    /**
     * Redirect to a URL
     * 
     * @param string $url The URL to redirect to
     * @param array $params Optional parameters
     * @param int $status The HTTP status code
     */
    protected function redirect($url, $params = [], $status = 302)
    {
        // Add parameters as query string if provided
        if (!empty($params)) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
        }
        
        // If the URL doesn't start with http:// or https://, prepend APP_URL
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = APP_URL . $url;
        }
        
        Router::redirect($url, $status);
    }
    
    /**
     * Set a flash message
     * 
     * @param string $type The message type (success, error, info, warning)
     * @param string $message The message to display
     */
    protected function setFlash($type, $message)
    {
        set_flash_message($type, $message);
    }
    
    /**
     * Check if the user is logged in
     * 
     * @return bool True if logged in, false otherwise
     */
    protected function isLoggedIn()
    {
        return is_logged_in();
    }
    
    /**
     * Check if the user has a specific role
     * 
     * @param string $role The role to check
     * @return bool True if the user has the role, false otherwise
     */
    protected function hasRole($role)
    {
        return has_role($role);
    }
    
    /**
     * Require authentication
     * 
     * @param string $redirect The URL to redirect to if not logged in
     */
    protected function requireAuth($redirect = '/login')
    {
        if (!$this->isLoggedIn()) {
            $this->setFlash('error', 'You must be logged in to access this page.');
            $this->redirect($redirect, ['return' => $_SERVER['REQUEST_URI']]);
        }
    }
    
    /**
     * Require a specific role
     * 
     * @param string $role The required role
     * @param string $redirect The URL to redirect to if not authorized
     */
    protected function requireRole($role, $redirect = '/login')
    {
        if (!$this->hasRole($role)) {
            $this->setFlash('error', 'You do not have permission to access this page.');
            $this->redirect($redirect);
        }
    }
    
    /**
     * Get a model instance
     * 
     * @param string $model The model name
     * @return Model The model instance
     */
    protected function model($model)
    {
        $model_class = '\\App\\Models\\' . $model;
        return new $model_class();
    }
    
    /**
     * Get request input
     * 
     * @param string $key The input key
     * @param mixed $default The default value if the key doesn't exist
     * @return mixed The input value
     */
    protected function input($key, $default = null)
    {
        return $this->request[$key] ?? $default;
    }
    
    /**
     * Get all request input
     * 
     * @return array The request input
     */
    protected function all()
    {
        return $this->request;
    }
    
    /**
     * Validate request data
     * 
     * @param array $rules The validation rules
     * @return array The validation errors
     */
    protected function validate($rules)
    {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $this->input($field);
            
            // Check if the field exists and if it's required
            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[$field] = 'The ' . $field . ' field is required.';
                continue;
            }
            
            // If the field is empty but not required, skip other validations
            if (empty($value) && strpos($rule, 'required') === false) {
                continue;
            }
            
            // Check individual rules
            $rule_parts = explode('|', $rule);
            foreach ($rule_parts as $part) {
                if ($part === 'required') {
                    // Already checked
                    continue;
                } elseif ($part === 'email') {
                    if (!is_valid_email($value)) {
                        $errors[$field] = 'The ' . $field . ' must be a valid email address.';
                    }
                } elseif (strpos($part, 'min:') === 0) {
                    $min = substr($part, 4);
                    if (strlen($value) < $min) {
                        $errors[$field] = 'The ' . $field . ' must be at least ' . $min . ' characters.';
                    }
                } elseif (strpos($part, 'max:') === 0) {
                    $max = substr($part, 4);
                    if (strlen($value) > $max) {
                        $errors[$field] = 'The ' . $field . ' may not be greater than ' . $max . ' characters.';
                    }
                } elseif ($part === 'numeric') {
                    if (!is_numeric($value)) {
                        $errors[$field] = 'The ' . $field . ' must be a number.';
                    }
                } elseif ($part === 'alpha') {
                    if (!ctype_alpha($value)) {
                        $errors[$field] = 'The ' . $field . ' may only contain letters.';
                    }
                } elseif ($part === 'alpha_num') {
                    if (!ctype_alnum($value)) {
                        $errors[$field] = 'The ' . $field . ' may only contain letters and numbers.';
                    }
                } elseif (strpos($part, 'same:') === 0) {
                    $other_field = substr($part, 5);
                    $other_value = $this->input($other_field);
                    if ($value !== $other_value) {
                        $errors[$field] = 'The ' . $field . ' and ' . $other_field . ' must match.';
                    }
                }
            }
        }
        
        return $errors;
    }
}