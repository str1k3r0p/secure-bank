<?php
/**
 * Banking DVWA Project
 * Router Class
 * 
 * This class handles routing of requests to the appropriate controllers.
 */

namespace App\Core;

class Router
{
    /**
     * @var array The application routes
     */
    private static $routes = [];
    
    /**
     * @var string The base path of the application
     */
    private static $basePath = '';
    
    /**
     * Initialize the router
     */
    public static function init()
    {
        // Set the routes from the configuration
        self::$routes = APP_ROUTES;
        
        // Set the base path
        $script_name = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        self::$basePath = $script_name === '/' ? '' : $script_name;
    }
    
    /**
     * Process the request
     * 
     * @param string $uri The URI to process
     * @throws NotFoundException If the route is not found
     */
    public function process($uri)
    {
        // Remove the base path from the URI
        $uri = str_replace(self::$basePath, '', $uri);
        
        // Remove query string from URI if present
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        
        // Normalize the URI
        $uri = '/' . trim($uri, '/');
        
        // Check if the URI matches a route
        if (isset(self::$routes[$uri])) {
            // Get the controller and action
            $controller = self::$routes[$uri]['controller'];
            $action = self::$routes[$uri]['action'];
            
            // Create the controller
            $controller_class = '\\App\\Controllers\\' . $controller;
            if (!class_exists($controller_class)) {
                throw new NotFoundException("Controller '{$controller}' not found");
            }
            
            $controller_instance = new $controller_class();
            
            // Check if the action exists
            if (!method_exists($controller_instance, $action)) {
                throw new NotFoundException("Action '{$action}' not found in controller '{$controller}'");
            }
            
            // Call the action
            $controller_instance->$action();
            return;
        }
        
        // Check for parameter routes (e.g. /user/123)
        foreach (self::$routes as $route => $config) {
            // Check if the route contains a parameter placeholder
            if (strpos($route, ':') !== false) {
                // Convert the route pattern to a regular expression
                $pattern = str_replace('/', '\/', $route);
                $pattern = preg_replace('/\:([a-zA-Z0-9_]+)/', '(?P<$1>[^\/]+)', $pattern);
                $pattern = '/^' . $pattern . '$/';
                
                // Check if the URI matches the pattern
                if (preg_match($pattern, $uri, $matches)) {
                    // Get the controller and action
                    $controller = $config['controller'];
                    $action = $config['action'];
                    
                    // Create the controller
                    $controller_class = '\\App\\Controllers\\' . $controller;
                    if (!class_exists($controller_class)) {
                        throw new NotFoundException("Controller '{$controller}' not found");
                    }
                    
                    $controller_instance = new $controller_class();
                    
                    // Check if the action exists
                    if (!method_exists($controller_instance, $action)) {
                        throw new NotFoundException("Action '{$action}' not found in controller '{$controller}'");
                    }
                    
                    // Extract parameters
                    $params = [];
                    foreach ($matches as $key => $value) {
                        if (!is_numeric($key)) {
                            $params[$key] = $value;
                        }
                    }
                    
                    // Call the action with parameters
                    call_user_func_array([$controller_instance, $action], $params);
                    return;
                }
            }
        }
        
        // If no route matches, throw a 404 exception
        throw new NotFoundException("Route '{$uri}' not found");
    }
    
    /**
     * Generate a URL for a route
     * 
     * @param string $route The route name
     * @param array $params Optional parameters
     * @return string The URL
     */
    public static function url($route, $params = [])
    {
        // If the route exists
        if (isset(self::$routes[$route])) {
            $url = APP_URL . $route;
            
            // Add parameters as query string if provided
            if (!empty($params)) {
                $url .= '?' . http_build_query($params);
            }
            
            return $url;
        }
        
        // Check for parameter routes
        foreach (self::$routes as $route_pattern => $config) {
            // If the route contains parameters
            if (strpos($route_pattern, ':') !== false) {
                // Extract parameter names
                preg_match_all('/\:([a-zA-Z0-9_]+)/', $route_pattern, $param_names);
                $param_names = $param_names[1];
                
                // Check if all required parameters are provided
                $all_params_provided = true;
                foreach ($param_names as $name) {
                    if (!isset($params[$name])) {
                        $all_params_provided = false;
                        break;
                    }
                }
                
                // If all parameters are provided
                if ($all_params_provided) {
                    $url = $route_pattern;
                    
                    // Replace parameter placeholders with values
                    foreach ($param_names as $name) {
                        $url = str_replace(":{$name}", $params[$name], $url);
                        unset($params[$name]);
                    }
                    
                    $url = APP_URL . $url;
                    
                    // Add remaining parameters as query string
                    if (!empty($params)) {
                        $url .= '?' . http_build_query($params);
                    }
                    
                    return $url;
                }
            }
        }
        
        // If no route matches, return the home URL
        return APP_URL;
    }
    
    /**
     * Redirect to a URL
     * 
     * @param string $url The URL to redirect to
     * @param int $status The HTTP status code
     */
    public static function redirect($url, $status = 302)
    {
        header("Location: {$url}", true, $status);
        exit;
    }
}