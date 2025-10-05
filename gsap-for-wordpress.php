<?php
/**
 * Plugin Name: GSAP for WordPress
 * Plugin URI: https://zahidaramai.com/gsap-for-wordpress
 * Description: Complete GSAP integration with admin interface, file editor, and version control system. Create stunning animations with ease.
 * Version: 1.0.0
 * Author: Zahid Aramai
 * Author URI: https://zahidaramai.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: gsap-for-wordpress
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 *
 * @package GSAP_For_WordPress
 * @version 1.0.0
 * @author Zahid Aramai
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('GSAP_WP_VERSION', '1.0.0');
define('GSAP_WP_PLUGIN_FILE', __FILE__);
define('GSAP_WP_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('GSAP_WP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('GSAP_WP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('GSAP_WP_ASSETS_URL', GSAP_WP_PLUGIN_URL . 'assets/');
define('GSAP_WP_ADMIN_URL', GSAP_WP_PLUGIN_URL . 'admin/');
define('GSAP_WP_INCLUDES_PATH', GSAP_WP_PLUGIN_PATH . 'includes/');
define('GSAP_WP_ADMIN_PATH', GSAP_WP_PLUGIN_PATH . 'admin/');
define('GSAP_WP_TEMPLATES_PATH', GSAP_WP_PLUGIN_PATH . 'templates/');

/**
 * Main GSAP for WordPress class
 *
 * @since 1.0.0
 */
final class GSAP_For_WordPress {

    /**
     * Single instance of the class
     *
     * @var GSAP_For_WordPress
     * @since 1.0.0
     */
    private static $instance = null;

    /**
     * Plugin version
     *
     * @var string
     * @since 1.0.0
     */
    public $version = GSAP_WP_VERSION;

    /**
     * Minimum WordPress version required
     *
     * @var string
     * @since 1.0.0
     */
    public $min_wp_version = '5.0';

    /**
     * Minimum PHP version required
     *
     * @var string
     * @since 1.0.0
     */
    public $min_php_version = '7.4';

    /**
     * Get single instance
     *
     * @return GSAP_For_WordPress
     * @since 1.0.0
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    private function __construct() {
        $this->check_requirements();
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Check system requirements
     *
     * @since 1.0.0
     */
    private function check_requirements() {
        // Check WordPress version
        if (version_compare(get_bloginfo('version'), $this->min_wp_version, '<')) {
            add_action('admin_notices', array($this, 'wp_version_notice'));
            return;
        }

        // Check PHP version
        if (version_compare(PHP_VERSION, $this->min_php_version, '<')) {
            add_action('admin_notices', array($this, 'php_version_notice'));
            return;
        }
    }

    /**
     * Define additional constants
     *
     * @since 1.0.0
     */
    private function define_constants() {
        if (!defined('GSAP_WP_DEBUG')) {
            define('GSAP_WP_DEBUG', WP_DEBUG);
        }
    }

    /**
     * Include required files
     *
     * @since 1.0.0
     */
    private function includes() {
        // Core includes
        require_once GSAP_WP_INCLUDES_PATH . 'class-security.php';
        require_once GSAP_WP_INCLUDES_PATH . 'class-file-manager.php';
        require_once GSAP_WP_INCLUDES_PATH . 'class-version-manager.php';
        require_once GSAP_WP_INCLUDES_PATH . 'class-gsap-loader.php';

        // Admin includes
        if (is_admin()) {
            require_once GSAP_WP_ADMIN_PATH . 'class-admin.php';
            require_once GSAP_WP_ADMIN_PATH . 'class-settings.php';
            require_once GSAP_WP_ADMIN_PATH . 'class-file-editor.php';
            require_once GSAP_WP_ADMIN_PATH . 'class-version-control.php';
        }
    }

    /**
     * Initialize WordPress hooks
     *
     * @since 1.0.0
     */
    private function init_hooks() {
        // Activation and deactivation hooks
        register_activation_hook(GSAP_WP_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(GSAP_WP_PLUGIN_FILE, array($this, 'deactivate'));

        // Core hooks
        add_action('init', array($this, 'init'), 0);
        add_action('plugins_loaded', array($this, 'load_textdomain'));

        // Frontend hooks
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('wp_footer', array($this, 'output_inline_scripts'));

        // Admin hooks
        if (is_admin()) {
            add_action('admin_init', array($this, 'admin_init'));
            add_action('admin_menu', array($this, 'admin_menu'));
            add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        }

        // AJAX hooks
        add_action('wp_ajax_gsap_wp_save_file', array($this, 'ajax_save_file'));
        add_action('wp_ajax_gsap_wp_load_version', array($this, 'ajax_load_version'));
        add_action('wp_ajax_gsap_wp_create_version', array($this, 'ajax_create_version'));

        // Custom hooks for extensions
        do_action('gsap_wp_loaded', $this);
    }

    /**
     * Plugin activation
     *
     * @since 1.0.0
     */
    public function activate() {
        // Create default options
        $this->create_default_options();

        // Create custom files
        $this->create_custom_files();

        // Create database tables
        $this->create_database_tables();

        // Set activation flag
        update_option('gsap_wp_activated', true);
        update_option('gsap_wp_version', GSAP_WP_VERSION);

        // Flush rewrite rules
        flush_rewrite_rules();

        do_action('gsap_wp_activated');
    }

    /**
     * Plugin deactivation
     *
     * @since 1.0.0
     */
    public function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('gsap_wp_cleanup');

        // Flush rewrite rules
        flush_rewrite_rules();

        do_action('gsap_wp_deactivated');
    }

    /**
     * Initialize plugin
     *
     * @since 1.0.0
     */
    public function init() {
        // Initialize components
        if (class_exists('GSAP_WP_Security')) {
            GSAP_WP_Security::get_instance();
        }

        if (class_exists('GSAP_WP_GSAP_Loader')) {
            GSAP_WP_GSAP_Loader::get_instance();
        }

        do_action('gsap_wp_init');
    }

    /**
     * Load text domain for translations
     *
     * @since 1.0.0
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'gsap-for-wordpress',
            false,
            dirname(GSAP_WP_PLUGIN_BASENAME) . '/languages/'
        );
    }

    /**
     * Initialize admin
     *
     * @since 1.0.0
     */
    public function admin_init() {
        if (class_exists('GSAP_WP_Admin')) {
            GSAP_WP_Admin::get_instance();
        }
    }

    /**
     * Add admin menu
     *
     * @since 1.0.0
     */
    public function admin_menu() {
        add_menu_page(
            __('GSAP for WordPress', 'gsap-for-wordpress'),
            __('GSAP', 'gsap-for-wordpress'),
            'manage_options',
            'gsap-wordpress',
            array($this, 'admin_page'),
            'dashicons-image-rotate',
            30
        );

        add_submenu_page(
            'gsap-wordpress',
            __('Settings', 'gsap-for-wordpress'),
            __('Settings', 'gsap-for-wordpress'),
            'manage_options',
            'gsap-wordpress',
            array($this, 'admin_page')
        );

        add_submenu_page(
            'gsap-wordpress',
            __('Customize', 'gsap-for-wordpress'),
            __('Customize', 'gsap-for-wordpress'),
            'manage_options',
            'gsap-wordpress-customize',
            array($this, 'customize_page')
        );
    }

    /**
     * Enqueue frontend scripts
     *
     * @since 1.0.0
     */
    public function enqueue_frontend_scripts() {
        if (class_exists('GSAP_WP_GSAP_Loader')) {
            GSAP_WP_GSAP_Loader::get_instance()->enqueue_scripts();
        }
    }

    /**
     * Output inline scripts in footer
     *
     * @since 1.0.0
     */
    public function output_inline_scripts() {
        $custom_js = get_option('gsap_wp_custom_js', '');
        if (!empty($custom_js)) {
            echo '<script type="text/javascript">' . $custom_js . '</script>';
        }
    }

    /**
     * Enqueue admin scripts
     *
     * @since 1.0.0
     */
    public function admin_enqueue_scripts($hook) {
        // Only load on plugin pages
        if (strpos($hook, 'gsap-wordpress') === false) {
            return;
        }

        wp_enqueue_style(
            'gsap-wp-admin',
            GSAP_WP_ADMIN_URL . 'css/admin.css',
            array(),
            GSAP_WP_VERSION
        );

        wp_enqueue_script(
            'gsap-wp-admin',
            GSAP_WP_ADMIN_URL . 'js/admin.js',
            array('jquery'),
            GSAP_WP_VERSION,
            true
        );

        // Localize script for AJAX
        wp_localize_script('gsap-wp-admin', 'gsapWpAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('gsap_wp_ajax_nonce'),
            'strings' => array(
                'saving' => __('Saving...', 'gsap-for-wordpress'),
                'saved' => __('Saved!', 'gsap-for-wordpress'),
                'error' => __('Error occurred. Please try again.', 'gsap-for-wordpress'),
                'confirm_reset' => __('Are you sure you want to reset? This action cannot be undone.', 'gsap-for-wordpress')
            )
        ));
    }

    /**
     * Admin settings page
     *
     * @since 1.0.0
     */
    public function admin_page() {
        if (class_exists('GSAP_WP_Settings')) {
            GSAP_WP_Settings::get_instance()->render_page();
        }
    }

    /**
     * Admin customize page
     *
     * @since 1.0.0
     */
    public function customize_page() {
        if (class_exists('GSAP_WP_File_Editor')) {
            GSAP_WP_File_Editor::get_instance()->render_page();
        }
    }

    /**
     * AJAX: Save file
     *
     * @since 1.0.0
     */
    public function ajax_save_file() {
        if (class_exists('GSAP_WP_File_Editor')) {
            GSAP_WP_File_Editor::get_instance()->ajax_save_file();
        }
    }

    /**
     * AJAX: Load version
     *
     * @since 1.0.0
     */
    public function ajax_load_version() {
        if (class_exists('GSAP_WP_Version_Control')) {
            GSAP_WP_Version_Control::get_instance()->ajax_load_version();
        }
    }

    /**
     * AJAX: Create version
     *
     * @since 1.0.0
     */
    public function ajax_create_version() {
        if (class_exists('GSAP_WP_Version_Control')) {
            GSAP_WP_Version_Control::get_instance()->ajax_create_version();
        }
    }

    /**
     * Create default options
     *
     * @since 1.0.0
     */
    private function create_default_options() {
        $default_settings = array(
            'libraries' => array(
                'gsap_core' => true,
                'css_plugin' => true,
                'scroll_trigger' => false,
                'observer' => false,
                'flip' => false,
                'text_plugin' => false,
                'drawsvg' => false,
                'morphsvg' => false,
                'split_text' => false,
                'scroll_smoother' => false,
                'gsdev_tools' => false,
                'motion_path' => false,
                'draggable' => false,
                'inertia' => false,
                'physics_2d' => false,
                'physics_props' => false,
                'easel' => false,
                'pixi' => false,
                'scramble_text' => false,
                'custom_ease' => false,
                'custom_bounce' => false,
                'custom_wiggle' => false,
                'css_rule' => false,
                'scroll_to' => false
            ),
            'performance' => array(
                'minified' => true,
                'load_in_footer' => true,
                'auto_merge' => false,
                'use_cdn' => false,
                'compression' => true,
                'cache_busting' => true
            ),
            'conditional_loading' => array(
                'enabled' => false,
                'include_pages' => array(),
                'exclude_pages' => array(),
                'include_post_types' => array(),
                'exclude_post_types' => array()
            )
        );

        add_option('gsap_wp_settings', $default_settings);
        add_option('gsap_wp_custom_js', '// Your custom GSAP animations here\n');
        add_option('gsap_wp_custom_css', '/* Your custom animation styles here */\n');
    }

    /**
     * Create custom files
     *
     * @since 1.0.0
     */
    private function create_custom_files() {
        $upload_dir = wp_upload_dir();
        $gsap_dir = $upload_dir['basedir'] . '/gsap-wordpress/';

        if (!file_exists($gsap_dir)) {
            wp_mkdir_p($gsap_dir);
        }

        // Create global.js
        $global_js_path = $gsap_dir . 'global.js';
        if (!file_exists($global_js_path)) {
            $default_js = "// GSAP Global Animations\n// Add your custom GSAP animations here\n\n// Example:\n// gsap.to('.my-element', {\n//     duration: 1,\n//     x: 100,\n//     ease: 'power2.out'\n// });";
            file_put_contents($global_js_path, $default_js);
        }

        // Create animation.css
        $animation_css_path = $gsap_dir . 'animation.css';
        if (!file_exists($animation_css_path)) {
            $default_css = "/* GSAP Animation Styles */\n/* Add your custom animation styles here */\n\n.gsap-element {\n    /* Your styles here */\n}";
            file_put_contents($animation_css_path, $default_css);
        }
    }

    /**
     * Create database tables
     *
     * @since 1.0.0
     */
    private function create_database_tables() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'gsap_versions';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            file_path varchar(255) NOT NULL,
            content longtext NOT NULL,
            version_number int(11) NOT NULL,
            user_comment text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            created_by bigint(20) unsigned NOT NULL,
            PRIMARY KEY (id),
            KEY file_path (file_path),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * WordPress version notice
     *
     * @since 1.0.0
     */
    public function wp_version_notice() {
        echo '<div class="notice notice-error"><p>';
        printf(
            __('GSAP for WordPress requires WordPress version %s or higher. You are using version %s.', 'gsap-for-wordpress'),
            $this->min_wp_version,
            get_bloginfo('version')
        );
        echo '</p></div>';
    }

    /**
     * PHP version notice
     *
     * @since 1.0.0
     */
    public function php_version_notice() {
        echo '<div class="notice notice-error"><p>';
        printf(
            __('GSAP for WordPress requires PHP version %s or higher. You are using version %s.', 'gsap-for-wordpress'),
            $this->min_php_version,
            PHP_VERSION
        );
        echo '</p></div>';
    }

    /**
     * Get plugin version
     *
     * @return string
     * @since 1.0.0
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * Prevent cloning
     *
     * @since 1.0.0
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     *
     * @since 1.0.0
     */
    private function __wakeup() {}
}

/**
 * Get the main instance of GSAP_For_WordPress
 *
 * @return GSAP_For_WordPress
 * @since 1.0.0
 */
function gsap_wp() {
    return GSAP_For_WordPress::get_instance();
}

// Initialize the plugin
gsap_wp();