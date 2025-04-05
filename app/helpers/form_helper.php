<?php
/**
 * Banking DVWA Project
 * Form Helper Functions
 * 
 * This file contains helper functions for form handling.
 */

// Prevent direct access to this file
if (!defined('ROOT_PATH')) {
    die('Direct access to this file is not allowed.');
}

/**
 * Create a form open tag
 * 
 * @param string $action Form action URL
 * @param string $method Form method (GET, POST)
 * @param array $attributes Additional form attributes
 * @return string Form open tag HTML
 */
function form_open($action = '', $method = 'post', $attributes = [])
{
    // Set default attributes
    $attr = [
        'method' => strtolower($method),
        'action' => empty($action) ? $_SERVER['REQUEST_URI'] : $action
    ];
    
    // Merge with additional attributes
    $attr = array_merge($attr, $attributes);
    
    // Build attributes string
    $attr_str = '';
    foreach ($attr as $key => $value) {
        $attr_str .= " {$key}=\"{$value}\"";
    }
    
    // Add CSRF token for POST forms
    $form = "<form{$attr_str}>\n";
    if (strtolower($method) === 'post') {
        $form .= csrf_field() . "\n";
    }
    
    return $form;
}

/**
 * Create a form close tag
 * 
 * @return string Form close tag HTML
 */
function form_close()
{
    return "</form>\n";
}

/**
 * Create an input field
 * 
 * @param string $name Field name
 * @param string $value Field value
 * @param array $attributes Additional field attributes
 * @return string Input field HTML
 */
function form_input($name, $value = '', $attributes = [])
{
    // Set default attributes
    $attr = [
        'type' => 'text',
        'name' => $name,
        'id' => $name,
        'value' => htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
    ];
    
    // Merge with additional attributes
    $attr = array_merge($attr, $attributes);
    
    // Build attributes string
    $attr_str = '';
    foreach ($attr as $key => $value) {
        $attr_str .= " {$key}=\"{$value}\"";
    }
    
    return "<input{$attr_str}>\n";
}

/**
 * Create a password field
 * 
 * @param string $name Field name
 * @param string $value Field value
 * @param array $attributes Additional field attributes
 * @return string Password field HTML
 */
function form_password($name, $value = '', $attributes = [])
{
    $attributes['type'] = 'password';
    return form_input($name, $value, $attributes);
}

/**
 * Create a hidden field
 * 
 * @param string $name Field name
 * @param string $value Field value
 * @param array $attributes Additional field attributes
 * @return string Hidden field HTML
 */
function form_hidden($name, $value = '', $attributes = [])
{
    $attributes['type'] = 'hidden';
    return form_input($name, $value, $attributes);
}

/**
 * Create a textarea field
 * 
 * @param string $name Field name
 * @param string $value Field value
 * @param array $attributes Additional field attributes
 * @return string Textarea field HTML
 */
function form_textarea($name, $value = '', $attributes = [])
{
    // Set default attributes
    $attr = [
        'name' => $name,
        'id' => $name
    ];
    
    // Merge with additional attributes
    $attr = array_merge($attr, $attributes);
    
    // Build attributes string
    $attr_str = '';
    foreach ($attr as $key => $val) {
        $attr_str .= " {$key}=\"{$val}\"";
    }
    
    return "<textarea{$attr_str}>" . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . "</textarea>\n";
}

/**
 * Create a dropdown/select field
 * 
 * @param string $name Field name
 * @param array $options Options array
 * @param string $selected Selected value
 * @param array $attributes Additional field attributes
 * @return string Select field HTML
 */
function form_dropdown($name, $options = [], $selected = '', $attributes = [])
{
    // Set default attributes
    $attr = [
        'name' => $name,
        'id' => $name
    ];
    
    // Merge with additional attributes
    $attr = array_merge($attr, $attributes);
    
    // Build attributes string
    $attr_str = '';
    foreach ($attr as $key => $value) {
        $attr_str .= " {$key}=\"{$value}\"";
    }
    
    // Start select tag
    $form = "<select{$attr_str}>\n";
    
    // Add options
    foreach ($options as $value => $label) {
        $selected_attr = ($value == $selected) ? ' selected="selected"' : '';
        $form .= "<option value=\"" . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . "\"{$selected_attr}>" . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . "</option>\n";
    }
    
    // Close select tag
    $form .= "</select>\n";
    
    return $form;
}

/**
 * Create a checkbox field
 * 
 * @param string $name Field name
 * @param string $value Field value
 * @param bool $checked Is checkbox checked
 * @param array $attributes Additional field attributes
 * @return string Checkbox field HTML
 */
function form_checkbox($name, $value = '1', $checked = false, $attributes = [])
{
    // Set default attributes
    $attr = [
        'type' => 'checkbox',
        'name' => $name,
        'id' => $name,
        'value' => htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
    ];
    
    // Add checked attribute if needed
    if ($checked) {
        $attr['checked'] = 'checked';
    }
    
    // Merge with additional attributes
    $attr = array_merge($attr, $attributes);
    
    // Build attributes string
    $attr_str = '';
    foreach ($attr as $key => $value) {
        $attr_str .= " {$key}=\"{$value}\"";
    }
    
    return "<input{$attr_str}>\n";
}

/**
 * Create a radio button field
 * 
 * @param string $name Field name
 * @param string $value Field value
 * @param bool $checked Is radio button checked
 * @param array $attributes Additional field attributes
 * @return string Radio button field HTML
 */
function form_radio($name, $value = '', $checked = false, $attributes = [])
{
    // Set default attributes
    $attr = [
        'type' => 'radio',
        'name' => $name,
        'value' => htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
    ];
    
    // Add checked attribute if needed
    if ($checked) {
        $attr['checked'] = 'checked';
    }
    
    // Merge with additional attributes
    $attr = array_merge($attr, $attributes);
    
    // Build attributes string
    $attr_str = '';
    foreach ($attr as $key => $value) {
        $attr_str .= " {$key}=\"{$value}\"";
    }
    
    return "<input{$attr_str}>\n";
}

/**
 * Create a submit button
 * 
 * @param string $text Button text
 * @param array $attributes Additional button attributes
 * @return string Submit button HTML
 */
function form_submit($text = 'Submit', $attributes = [])
{
    // Set default attributes
    $attr = [
        'type' => 'submit',
        'value' => htmlspecialchars($text, ENT_QUOTES, 'UTF-8')
    ];
    
    // Merge with additional attributes
    $attr = array_merge($attr, $attributes);
    
    // Build attributes string
    $attr_str = '';
    foreach ($attr as $key => $value) {
        $attr_str .= " {$key}=\"{$value}\"";
    }
    
    return "<input{$attr_str}>\n";
}

/**
 * Create a button
 * 
 * @param string $text Button text
 * @param array $attributes Additional button attributes
 * @return string Button HTML
 */
function form_button($text = 'Button', $attributes = [])
{
    // Set default attributes
    $attr = [
        'type' => 'button'
    ];
    
    // Merge with additional attributes
    $attr = array_merge($attr, $attributes);
    
    // Build attributes string
    $attr_str = '';
    foreach ($attr as $key => $value) {
        $attr_str .= " {$key}=\"{$value}\"";
    }
    
    return "<button{$attr_str}>" . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . "</button>\n";
}

/**
 * Create a label
 * 
 * @param string $text Label text
 * @param string $for Field ID
 * @param array $attributes Additional label attributes
 * @return string Label HTML
 */
function form_label($text, $for = '', $attributes = [])
{
    // Set default attributes
    $attr = [];
    
    // Add for attribute if provided
    if (!empty($for)) {
        $attr['for'] = $for;
    }
    
    // Merge with additional attributes
    $attr = array_merge($attr, $attributes);
    
    // Build attributes string
    $attr_str = '';
    foreach ($attr as $key => $value) {
        $attr_str .= " {$key}=\"{$value}\"";
    }
    
    return "<label{$attr_str}>" . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . "</label>\n";
}

/**
 * Create a CSRF token field
 * 
 * @return string CSRF token field HTML
 */
function csrf_field()
{
    $token = generate_csrf_token();
    return form_hidden(CSRF_TOKEN_NAME, $token);
}

/**
 * Get old input value (e.g. after validation failure)
 * 
 * @param string $key Input field name
 * @param mixed $default Default value
 * @return mixed Input value
 */
function old($key, $default = '')
{
    if (isset($_SESSION['old_input']) && array_key_exists($key, $_SESSION['old_input'])) {
        return $_SESSION['old_input'][$key];
    }
    
    return $default;
}

/**
 * Display validation error for a field
 * 
 * @param string $field Field name
 * @param array $errors Errors array
 * @return string Error HTML or empty string
 */
function validation_error($field, $errors = [])
{
    if (empty($errors)) {
        $errors = $_SESSION['errors'] ?? [];
    }
    
    if (isset($errors[$field])) {
        return '<div class="invalid-feedback d-block">' . htmlspecialchars($errors[$field], ENT_QUOTES, 'UTF-8') . '</div>';
    }
    
    return '';
}
