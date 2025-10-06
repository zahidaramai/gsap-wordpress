<?php
/**
 * Security class for GSAP WordPress plugin
 *
 * @package GSAP_For_WordPress
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GSAP WordPress Security class
 */
class GSAP_WP_Security {

    /**
     * Single instance of the class
     *
     * @var GSAP_WP_Security
     */
    private static $instance = null;

    /**
     * Allowed file extensions for editing
     *
     * @var array
     */
    private $allowed_extensions = array('js', 'css');

    /**
     * Maximum file size for editing (in bytes)
     *
     * @var int
     */
    private $max_file_size = 1048576; // 1MB

    /**
     * Get single instance
     *
     * @return GSAP_WP_Security
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('admin_init', array($this, 'check_user_capabilities'));
        add_filter('gsap_wp_validate_file_content', array($this, 'validate_file_content'), 10, 2);
        add_filter('gsap_wp_sanitize_file_path', array($this, 'sanitize_file_path'));
    }

    /**
     * Check if current user has required capabilities
     *
     * @return bool
     */
    public function check_user_capabilities() {
        // Only check on our plugin pages, not all admin pages
        if (!isset($_GET['page']) || $_GET['page'] !== 'gsap-wordpress') {
            return true;
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'gsap-for-wordpress'));
        }
        return true;
    }

    /**
     * Verify nonce for AJAX requests
     *
     * @param string $action The action name
     * @return bool
     */
    public function verify_nonce($action = 'gsap_wp_ajax_nonce') {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], $action)) {
            return false;
        }
        return true;
    }

    /**
     * Validate file path for security
     *
     * @param string $file_path The file path to validate
     * @return bool|WP_Error
     */
    public function validate_file_path($file_path) {
        // Check if file path is empty
        if (empty($file_path)) {
            return new WP_Error('empty_path', __('File path cannot be empty.', 'gsap-for-wordpress'));
        }

        // Sanitize file path
        $file_path = $this->sanitize_file_path($file_path);

        // Check for directory traversal attempts
        if (strpos($file_path, '..') !== false) {
            return new WP_Error('invalid_path', __('Invalid file path detected.', 'gsap-for-wordpress'));
        }

        // Check if file extension is allowed
        $extension = pathinfo($file_path, PATHINFO_EXTENSION);
        if (!in_array(strtolower($extension), $this->allowed_extensions)) {
            return new WP_Error('invalid_extension', __('File extension not allowed.', 'gsap-for-wordpress'));
        }

        // Check if file is within allowed directory
        $upload_dir = wp_upload_dir();
        $allowed_base_path = $upload_dir['basedir'] . '/gsap-wordpress/';
        $real_file_path = realpath(dirname($file_path));
        $real_base_path = realpath($allowed_base_path);

        if ($real_file_path === false || strpos($real_file_path, $real_base_path) !== 0) {
            return new WP_Error('path_outside_allowed', __('File path is outside allowed directory.', 'gsap-for-wordpress'));
        }

        return true;
    }

    /**
     * Validate file content for security issues
     *
     * @param string $content The file content to validate
     * @param string $file_type The file type (js/css)
     * @return bool|WP_Error
     */
    public function validate_file_content($content, $file_type) {
        // Check content size
        if (strlen($content) > $this->max_file_size) {
            return new WP_Error('file_too_large', __('File content is too large.', 'gsap-for-wordpress'));
        }

        // Basic XSS prevention for JavaScript files
        if ($file_type === 'js') {
            // Check for potentially dangerous functions
            $dangerous_patterns = array(
                '/\beval\s*\(/i',
                '/\bFunction\s*\(/i',
                '/\bsetTimeout\s*\(\s*["\'].*["\'].*\)/i',
                '/\bsetInterval\s*\(\s*["\'].*["\'].*\)/i',
                '/\bdocument\.write\s*\(/i',
                '/\binnerHTML\s*=/i',
                '/\bouterHTML\s*=/i',
                '/\binsertAdjacentHTML\s*\(/i',
                '/\bexecCommand\s*\(/i',
                '/\blocation\s*=/i',
                '/window\.location/i',
                '/\bopen\s*\(/i',
                '/XMLHttpRequest/i',
                '/\bfetch\s*\(/i',
                '/\bajax/i'
            );

            foreach ($dangerous_patterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    return new WP_Error('dangerous_content', sprintf(
                        __('Potentially dangerous content detected. Pattern: %s', 'gsap-for-wordpress'),
                        $pattern
                    ));
                }
            }
        }

        // Basic validation for CSS files
        if ($file_type === 'css') {
            // Check for JavaScript in CSS
            if (preg_match('/javascript\s*:/i', $content)) {
                return new WP_Error('js_in_css', __('JavaScript detected in CSS content.', 'gsap-for-wordpress'));
            }

            // Check for expression() which can execute JavaScript in old IE
            if (preg_match('/expression\s*\(/i', $content)) {
                return new WP_Error('expression_in_css', __('CSS expression() detected.', 'gsap-for-wordpress'));
            }

            // Check for @import with data URLs
            if (preg_match('/@import\s+url\s*\(\s*["\']?data:/i', $content)) {
                return new WP_Error('data_import', __('Data URL imports are not allowed.', 'gsap-for-wordpress'));
            }
        }

        return true;
    }

    /**
     * Sanitize file path
     *
     * @param string $file_path The file path to sanitize
     * @return string
     */
    public function sanitize_file_path($file_path) {
        // Remove null bytes
        $file_path = str_replace(chr(0), '', $file_path);

        // Normalize slashes
        $file_path = str_replace('\\', '/', $file_path);

        // Remove multiple consecutive slashes
        $file_path = preg_replace('/\/+/', '/', $file_path);

        // Remove leading/trailing whitespace
        $file_path = trim($file_path);

        return $file_path;
    }

    /**
     * Sanitize file content
     *
     * @param string $content The content to sanitize
     * @param string $file_type The file type
     * @return string
     */
    public function sanitize_file_content($content, $file_type) {
        // Remove null bytes
        $content = str_replace(chr(0), '', $content);

        // Normalize line endings
        $content = str_replace(array("\r\n", "\r"), "\n", $content);

        // Remove BOM if present
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        if ($file_type === 'js') {
            // Additional JavaScript-specific sanitization can be added here
            // For now, we rely on validation to catch dangerous content
        }

        if ($file_type === 'css') {
            // Additional CSS-specific sanitization can be added here
        }

        return $content;
    }

    /**
     * Check if user can edit files
     *
     * @return bool
     */
    public function can_edit_files() {
        // Check if file editing is disabled
        if (defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT) {
            return false;
        }

        // Check user capabilities
        if (!current_user_can('edit_themes') && !current_user_can('manage_options')) {
            return false;
        }

        return true;
    }

    /**
     * Generate secure filename
     *
     * @param string $filename The original filename
     * @return string
     */
    public function generate_secure_filename($filename) {
        $info = pathinfo($filename);
        $name = sanitize_file_name($info['filename']);
        $extension = isset($info['extension']) ? strtolower($info['extension']) : '';

        // Ensure extension is allowed
        if (!in_array($extension, $this->allowed_extensions)) {
            $extension = 'txt'; // Default to txt if not allowed
        }

        return $name . '.' . $extension;
    }

    /**
     * Log security events
     *
     * @param string $event The event type
     * @param string $message The event message
     * @param array $context Additional context
     */
    public function log_security_event($event, $message, $context = array()) {
        if (!GSAP_WP_DEBUG) {
            return;
        }

        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'user_id' => get_current_user_id(),
            'ip_address' => $this->get_client_ip(),
            'event' => $event,
            'message' => $message,
            'context' => $context
        );

        error_log('[GSAP-WP Security] ' . json_encode($log_entry));
    }

    /**
     * Get client IP address
     *
     * @return string
     */
    private function get_client_ip() {
        $ip_keys = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );

        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }

        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    }

    /**
     * Rate limiting for AJAX requests
     *
     * @param string $action The action being performed
     * @param int $limit Number of requests allowed
     * @param int $time_window Time window in seconds
     * @return bool
     */
    public function check_rate_limit($action, $limit = 60, $time_window = 3600) {
        $user_id = get_current_user_id();
        $transient_key = "gsap_wp_rate_limit_{$action}_{$user_id}";

        $requests = get_transient($transient_key);

        if ($requests === false) {
            $requests = 1;
            set_transient($transient_key, $requests, $time_window);
            return true;
        }

        if ($requests >= $limit) {
            $this->log_security_event('rate_limit_exceeded', "Rate limit exceeded for action: {$action}");
            return false;
        }

        $requests++;
        set_transient($transient_key, $requests, $time_window);

        return true;
    }

    /**
     * Prevent cloning
     */
    public function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup() {}
}