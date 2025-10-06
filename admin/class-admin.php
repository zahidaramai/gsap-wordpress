<?php
/**
 * Admin class for GSAP WordPress plugin
 *
 * @package GSAP_For_WordPress
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GSAP WordPress Admin class
 */
class GSAP_WP_Admin {

    /**
     * Single instance of the class
     *
     * @var GSAP_WP_Admin
     */
    private static $instance = null;

    /**
     * Current tab
     *
     * @var string
     */
    private $current_tab = 'settings';

    /**
     * Available tabs
     *
     * @var array
     */
    private $tabs = array();

    /**
     * Get single instance
     *
     * @return GSAP_WP_Admin
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
        $this->init_tabs();
        $this->init_hooks();
    }

    /**
     * Initialize tabs
     */
    private function init_tabs() {
        $this->tabs = array(
            'settings' => array(
                'title' => __('Settings', 'gsap-for-wordpress'),
                'description' => __('Configure GSAP libraries and performance settings', 'gsap-for-wordpress'),
                'capability' => 'manage_options',
                'callback' => array($this, 'render_settings_tab')
            ),
            'customize' => array(
                'title' => __('Customize', 'gsap-for-wordpress'),
                'description' => __('Edit custom GSAP animations and styles using our professional code editor with version control and syntax highlighting', 'gsap-for-wordpress'),
                'capability' => 'edit_themes',
                'callback' => array($this, 'render_customize_tab')
            )
        );

        // Allow third-party plugins to add tabs
        $this->tabs = apply_filters('gsap_wp_admin_tabs', $this->tabs);
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('admin_init', array($this, 'register_settings'), 5);
        add_action('admin_init', array($this, 'handle_form_submissions'), 10);
        add_action('admin_notices', array($this, 'show_admin_notices'));
        add_action('current_screen', array($this, 'add_help_tabs'));
        add_action('update_option_gsap_wp_settings', array($this, 'after_settings_save'), 10, 3);
    }

    /**
     * Register settings using WordPress Settings API
     *
     * @since 1.0.3
     */
    public function register_settings() {
        register_setting(
            'gsap_wp_settings_group',
            'gsap_wp_settings',
            array(
                'type' => 'array',
                'description' => 'GSAP for WordPress plugin settings',
                'sanitize_callback' => array($this, 'sanitize_settings'),
                'show_in_rest' => false,
                'default' => array(
                    'libraries' => array(),
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
                )
            )
        );
    }

    /**
     * Sanitize settings before saving
     *
     * @param array $input Raw input from form
     * @return array Sanitized settings
     * @since 1.0.3
     */
    public function sanitize_settings($input) {
        $sanitized = array();

        // Sanitize libraries
        $available_libraries = array(
            'gsap_core', 'css_plugin', 'scroll_trigger', 'observer', 'flip', 'text_plugin',
            'drawsvg', 'morphsvg', 'split_text', 'scroll_smoother', 'gsdev_tools',
            'motion_path', 'draggable', 'inertia', 'physics_2d', 'physics_props',
            'easel', 'pixi', 'scramble_text', 'custom_ease', 'custom_bounce',
            'custom_wiggle', 'css_rule', 'scroll_to'
        );

        $sanitized['libraries'] = array();
        foreach ($available_libraries as $library) {
            $sanitized['libraries'][$library] = isset($input['libraries'][$library]) ? (bool) $input['libraries'][$library] : false;
        }

        // Sanitize performance settings
        $sanitized['performance'] = array(
            'minified' => isset($input['performance']['minified']) ? (bool) $input['performance']['minified'] : true,
            'load_in_footer' => isset($input['performance']['load_in_footer']) ? (bool) $input['performance']['load_in_footer'] : true,
            'auto_merge' => isset($input['performance']['auto_merge']) ? (bool) $input['performance']['auto_merge'] : false,
            'use_cdn' => isset($input['performance']['use_cdn']) ? (bool) $input['performance']['use_cdn'] : false,
            'compression' => isset($input['performance']['compression']) ? (bool) $input['performance']['compression'] : true,
            'cache_busting' => isset($input['performance']['cache_busting']) ? (bool) $input['performance']['cache_busting'] : true
        );

        // Sanitize conditional loading
        $sanitized['conditional_loading'] = array(
            'enabled' => isset($input['conditional_loading']['enabled']) ? (bool) $input['conditional_loading']['enabled'] : false,
            'include_pages' => isset($input['conditional_loading']['include_pages'])
                ? array_map('absint', (array) $input['conditional_loading']['include_pages'])
                : array(),
            'exclude_pages' => isset($input['conditional_loading']['exclude_pages'])
                ? array_map('absint', (array) $input['conditional_loading']['exclude_pages'])
                : array(),
            'include_post_types' => isset($input['conditional_loading']['include_post_types'])
                ? array_map('sanitize_key', (array) $input['conditional_loading']['include_post_types'])
                : array(),
            'exclude_post_types' => isset($input['conditional_loading']['exclude_post_types'])
                ? array_map('sanitize_key', (array) $input['conditional_loading']['exclude_post_types'])
                : array()
        );

        return $sanitized;
    }

    /**
     * Get current tab
     *
     * @return string
     */
    public function get_current_tab() {
        if (isset($_GET['tab']) && array_key_exists($_GET['tab'], $this->tabs)) {
            $this->current_tab = sanitize_key($_GET['tab']);
        }
        return $this->current_tab;
    }

    /**
     * Render admin page header
     */
    public function render_header() {
        $current_tab = $this->get_current_tab();
        ?>
        <div class="wrap gsap-wp-admin">
            <h1 class="wp-heading-inline">
                <?php echo esc_html(get_admin_page_title()); ?>
                <span class="title-count"><?php echo esc_html(GSAP_WP_VERSION); ?></span>
            </h1>

            <hr class="wp-header-end">

            <!-- Display admin notices -->
            <?php settings_errors('gsap_wp_settings'); ?>

            <!-- Friendly instruction box with pastel background -->
            <div class="gsap-wp-instruction-box">
                <div class="gsap-wp-instruction-content">
                    <span class="dashicons dashicons-lightbulb"></span>
                    <p><?php _e('Welcome to GSAP for WordPress! Use the <strong>Settings</strong> tab to activate animation libraries, and the <strong>Customize</strong> tab to edit your custom animations and styles with our built-in code editor featuring syntax highlighting and version control.', 'gsap-for-wordpress'); ?></p>
                </div>
            </div>

            <?php if (isset($this->tabs[$current_tab]['description'])): ?>
                <p class="description"><?php echo esc_html($this->tabs[$current_tab]['description']); ?></p>
            <?php endif; ?>

            <?php $this->render_tab_navigation(); ?>

            <div class="gsap-wp-tab-content">
        <?php
    }

    /**
     * Render admin page footer
     */
    public function render_footer() {
        ?>
            </div> <!-- .gsap-wp-tab-content -->

            <div class="gsap-wp-footer">
                <p>
                    <?php
                    printf(
                        __('GSAP for WordPress v%s by %s', 'gsap-for-wordpress'),
                        GSAP_WP_VERSION,
                        '<a href="https://zahidaramai.com" target="_blank">Zahid Aramai</a>'
                    );
                    ?>
                </p>
            </div>
        </div> <!-- .wrap -->
        <?php
    }

    /**
     * Render tab navigation
     */
    private function render_tab_navigation() {
        $current_tab = $this->get_current_tab();
        ?>
        <nav class="nav-tab-wrapper wp-clearfix">
            <?php foreach ($this->tabs as $tab_key => $tab_data): ?>
                <?php if (current_user_can($tab_data['capability'])): ?>
                    <a href="<?php echo esc_url($this->get_tab_url($tab_key)); ?>"
                       class="nav-tab <?php echo $current_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
                        <?php echo esc_html($tab_data['title']); ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
        <?php
    }

    /**
     * Get tab URL
     *
     * @param string $tab_key
     * @return string
     */
    private function get_tab_url($tab_key) {
        $base_url = admin_url('admin.php?page=gsap-wordpress');

        if ($tab_key !== 'settings') {
            $base_url = add_query_arg('tab', $tab_key, $base_url);
        }

        return $base_url;
    }

    /**
     * Render settings tab
     */
    public function render_settings_tab() {
        if (class_exists('GSAP_WP_Settings')) {
            GSAP_WP_Settings::get_instance()->render_content();
        }
    }

    /**
     * Render customize tab
     */
    public function render_customize_tab() {
        if (class_exists('GSAP_WP_File_Editor')) {
            GSAP_WP_File_Editor::get_instance()->render_content();
        }
    }

    /**
     * Handle form submissions
     *
     * Note: Settings form is now handled automatically by WordPress Settings API
     * via options.php and our sanitize_settings() callback.
     * This method is kept for future custom form handlers (like file editor).
     */
    public function handle_form_submissions() {
        // Settings API handles our main settings form automatically
        // Custom handlers for other forms can be added here
    }

    /**
     * Post-processing after settings save
     * Called via 'update_option_gsap_wp_settings' hook
     */
    public function after_settings_save($old_value, $new_value, $option_name) {
        // Clear any cached data
        $this->clear_cache();

        // Delete transients to force fresh settings reload
        delete_transient('gsap_wp_loaded_settings');
        delete_transient('gsap_wp_library_cache');

        // Log the settings change
        if (class_exists('GSAP_WP_Security')) {
            $library_count = isset($new_value['libraries']) ? count(array_filter($new_value['libraries'])) : 0;
            GSAP_WP_Security::get_instance()->log_security_event(
                'settings_updated',
                'GSAP settings were updated',
                array(
                    'user_id' => get_current_user_id(),
                    'libraries_count' => $library_count,
                    'performance' => isset($new_value['performance']) ? $new_value['performance'] : array()
                )
            );
        }

        // Trigger action for other plugins
        do_action('gsap_wp_settings_updated', $new_value, $old_value);
    }

    /**
     * Show admin notices
     */
    public function show_admin_notices() {
        // Only show on plugin pages
        if (!isset($_GET['page']) || $_GET['page'] !== 'gsap-wordpress') {
            return;
        }

        // Display settings errors/success messages from Settings API
        settings_errors('gsap_wp_settings');

        // Show enhanced settings saved message
        if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true') {
            $settings = get_option('gsap_wp_settings', array());
            $library_count = isset($settings['libraries']) ? count(array_filter($settings['libraries'])) : 0;
            ?>
            <div class="notice notice-success is-dismissible gsap-wp-success-notice">
                <p>
                    <span class="dashicons dashicons-yes-alt"></span>
                    <strong><?php _e('Settings saved successfully!', 'gsap-for-wordpress'); ?></strong>
                    <?php
                    if ($library_count > 0) {
                        printf(
                            _n(
                                '%d GSAP library is now active on your website.',
                                '%d GSAP libraries are now active on your website.',
                                $library_count,
                                'gsap-for-wordpress'
                            ),
                            $library_count
                        );
                    } else {
                        _e('No GSAP libraries are currently active. Enable libraries below to start using GSAP.', 'gsap-for-wordpress');
                    }
                    ?>
                </p>
            </div>
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Auto-dismiss after 7 seconds
                setTimeout(function() {
                    $('.gsap-wp-success-notice').fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 7000);
            });
            </script>
            <?php
        }

        // Show activation notice
        if (get_option('gsap_wp_activated', false)) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <?php _e('GSAP for WordPress has been activated successfully!', 'gsap-for-wordpress'); ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=gsap-wordpress')); ?>">
                        <?php _e('Configure settings', 'gsap-for-wordpress'); ?>
                    </a>
                </p>
            </div>
            <?php
            delete_option('gsap_wp_activated');
        }

        // Show file editing warning if needed
        if (defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT && $this->get_current_tab() === 'customize') {
            ?>
            <div class="notice notice-warning">
                <p>
                    <?php _e('File editing is disabled in your WordPress configuration. The customize tab will have limited functionality.', 'gsap-for-wordpress'); ?>
                </p>
            </div>
            <?php
        }

        // Show premium plugin notice
        $settings = get_option('gsap_wp_settings', array());
        $premium_libraries = array('drawsvg', 'morphsvg', 'split_text', 'scroll_smoother', 'gsdev_tools');
        $has_premium_enabled = false;

        foreach ($premium_libraries as $library) {
            if (isset($settings['libraries'][$library]) && $settings['libraries'][$library]) {
                $has_premium_enabled = true;
                break;
            }
        }

        if ($has_premium_enabled) {
            ?>
            <div class="notice notice-info">
                <p>
                    <?php
                    printf(
                        __('You have premium GSAP plugins enabled. Make sure you have a valid %s for commercial use.', 'gsap-for-wordpress'),
                        '<a href="https://greensock.com/licensing/" target="_blank">' . __('GSAP license', 'gsap-for-wordpress') . '</a>'
                    );
                    ?>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Add help tabs
     */
    public function add_help_tabs($screen) {
        // Only add help tabs on plugin pages
        if (strpos($screen->id, 'gsap-wordpress') === false) {
            return;
        }

        $screen->add_help_tab(array(
            'id' => 'gsap_wp_overview',
            'title' => __('Overview', 'gsap-for-wordpress'),
            'content' => $this->get_help_content('overview')
        ));

        $screen->add_help_tab(array(
            'id' => 'gsap_wp_libraries',
            'title' => __('GSAP Libraries', 'gsap-for-wordpress'),
            'content' => $this->get_help_content('libraries')
        ));

        $screen->add_help_tab(array(
            'id' => 'gsap_wp_performance',
            'title' => __('Performance', 'gsap-for-wordpress'),
            'content' => $this->get_help_content('performance')
        ));

        $screen->add_help_tab(array(
            'id' => 'gsap_wp_customization',
            'title' => __('Customization', 'gsap-for-wordpress'),
            'content' => $this->get_help_content('customization')
        ));

        // Set help sidebar
        $screen->set_help_sidebar($this->get_help_sidebar());
    }

    /**
     * Get help content for different sections
     *
     * @param string $section
     * @return string
     */
    private function get_help_content($section) {
        switch ($section) {
            case 'overview':
                return '<p>' . __('GSAP for WordPress provides a comprehensive integration of the GreenSock Animation Platform into your WordPress website. Use the Settings tab to configure which GSAP libraries to load, and the Customize tab to write custom animations.', 'gsap-for-wordpress') . '</p>';

            case 'libraries':
                return '<p>' . __('Choose which GSAP libraries to load on your website. Only enable the libraries you actually use to optimize performance. Premium libraries require a valid GSAP license for commercial use.', 'gsap-for-wordpress') . '</p>';

            case 'performance':
                return '<p>' . __('Configure performance settings to optimize how GSAP files are loaded. Enable minified files and footer loading for better performance. Use conditional loading to only load GSAP on specific pages.', 'gsap-for-wordpress') . '</p>';

            case 'customization':
                return '<p>' . __('Use the Customize tab to write custom GSAP animations and styles. The built-in editor provides syntax highlighting and version control. Your changes are automatically saved and can be restored from previous versions.', 'gsap-for-wordpress') . '</p>';

            default:
                return '';
        }
    }

    /**
     * Get help sidebar
     *
     * @return string
     */
    private function get_help_sidebar() {
        $content = '<p><strong>' . __('For more information:', 'gsap-for-wordpress') . '</strong></p>';
        $content .= '<p><a href="https://greensock.com/docs/" target="_blank">' . __('GSAP Documentation', 'gsap-for-wordpress') . '</a></p>';
        $content .= '<p><a href="https://zahidaramai.com" target="_blank">' . __('Plugin Support', 'gsap-for-wordpress') . '</a></p>';
        $content .= '<p><a href="https://greensock.com/licensing/" target="_blank">' . __('GSAP Licensing', 'gsap-for-wordpress') . '</a></p>';

        return $content;
    }

    /**
     * Clear plugin cache
     */
    private function clear_cache() {
        // Clear WordPress object cache
        wp_cache_flush();

        // Clear any plugin-specific transients
        delete_transient('gsap_wp_library_cache');
        delete_transient('gsap_wp_dependency_cache');

        // Trigger action for cache clearing
        do_action('gsap_wp_clear_cache');
    }

    /**
     * Get admin page URL
     *
     * @param string $tab
     * @return string
     */
    public function get_admin_url($tab = '') {
        $url = admin_url('admin.php?page=gsap-wordpress');

        if (!empty($tab) && $tab !== 'settings') {
            $url = add_query_arg('tab', $tab, $url);
        }

        return $url;
    }

    /**
     * Check if current page is plugin admin page
     *
     * @return bool
     */
    public function is_plugin_page() {
        return isset($_GET['page']) && $_GET['page'] === 'gsap-wordpress';
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