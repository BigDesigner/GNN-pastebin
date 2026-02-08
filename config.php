<?php
// Application Version
define('APP_VERSION', '1.2');

/**
 * GNNpaste Configuration
 * 
 * Fetches settings from Environment Variables, Apache Server variables, or $_SERVER.
 * This ensures compatibility across different server configurations (FPM, FastCGI, etc.)
 */
function get_config_var($name, $default = null)
{
    // 1. Try getenv()
    $val = getenv($name);
    if ($val !== false)
        return $val;

    // 2. Try $_SERVER (Common for Apache SetEnv)
    if (isset($_SERVER[$name]))
        return $_SERVER[$name];

    // 3. Try $_ENV
    if (isset($_ENV[$name]))
        return $_ENV[$name];

    return $default;
}

// Admin credentials
define('ADMIN_USERNAME', get_config_var('ADMIN_USERNAME'));
define('ADMIN_PASSWORD', get_config_var('ADMIN_PASSWORD'));

// Google reCAPTCHA keys
define('RECAPTCHA_SITE_KEY', get_config_var('RECAPTCHA_SITE_KEY'));
define('RECAPTCHA_SECRET_KEY', get_config_var('RECAPTCHA_SECRET_KEY'));

// Encryption settings
define('ENCRYPTION_KEY', get_config_var('ENCRYPTION_KEY', 'default_key_change_me'));

// AES Initial Vector (IV) - DO NOT CHANGE
define('AES_IV', '1234567891011121');
