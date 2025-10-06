<?php
/**
 * Settings class for GSAP WordPress plugin
 *
 * @package GSAP_For_WordPress
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GSAP WordPress Settings class
 */
class GSAP_WP_Settings {

    /**
     * Single instance of the class
     *
     * @var GSAP_WP_Settings
     */
    private static $instance = null;

    /**
     * Settings data
     *
     * @var array
     */
    private $settings = array();

    /**
     * GSAP library definitions
     *
     * @var array
     */
    private $library_definitions = array();

    /**
     * Get single instance
     *
     * @return GSAP_WP_Settings
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
        $this->init_library_definitions();
        $this->load_settings();
    }

    /**
     * Load current settings
     */
    private function load_settings() {
        // Always get fresh data from database
        $this->settings = get_option('gsap_wp_settings', array());

        // Ensure proper structure
        if (!isset($this->settings['libraries'])) {
            $this->settings['libraries'] = array();
        }
        if (!isset($this->settings['performance'])) {
            $this->settings['performance'] = array();
        }
        if (!isset($this->settings['conditional_loading'])) {
            $this->settings['conditional_loading'] = array();
        }
    }

    /**
     * Initialize GSAP library definitions
     */
    private function init_library_definitions() {
        $this->library_definitions = array(
            'core' => array(
                'title' => __('Core Libraries', 'gsap-for-wordpress'),
                'description' => __('Essential GSAP libraries required for basic animations', 'gsap-for-wordpress'),
                'libraries' => array(
                    'gsap_core' => array(
                        'name' => 'GSAP Core',
                        'file' => 'gsap.min.js',
                        'description' => __('The main GSAP animation engine (required)', 'gsap-for-wordpress'),
                        'required' => true,
                        'free' => true,
                        'size' => '47KB'
                    ),
                    'css_plugin' => array(
                        'name' => 'CSS Plugin',
                        'file' => 'CSSPlugin.min.js',
                        'description' => __('Enables CSS property animations (recommended)', 'gsap-for-wordpress'),
                        'required' => false,
                        'free' => true,
                        'size' => '12KB'
                    )
                )
            ),
            'free' => array(
                'title' => __('Free Plugins', 'gsap-for-wordpress'),
                'description' => __('Free GSAP plugins that extend core functionality', 'gsap-for-wordpress'),
                'libraries' => array(
                    'scroll_trigger' => array(
                        'name' => 'ScrollTrigger',
                        'file' => 'ScrollTrigger.min.js',
                        'description' => __('Create scroll-driven animations and effects', 'gsap-for-wordpress'),
                        'free' => true,
                        'size' => '29KB'
                    ),
                    'observer' => array(
                        'name' => 'Observer',
                        'file' => 'Observer.min.js',
                        'description' => __('Performance-optimized event handling', 'gsap-for-wordpress'),
                        'free' => true,
                        'size' => '8KB'
                    ),
                    'flip' => array(
                        'name' => 'Flip',
                        'file' => 'Flip.min.js',
                        'description' => __('State-based animations with automatic transitions', 'gsap-for-wordpress'),
                        'free' => true,
                        'size' => '15KB'
                    ),
                    'text_plugin' => array(
                        'name' => 'TextPlugin',
                        'file' => 'TextPlugin.min.js',
                        'description' => __('Animate text content with typewriter effects', 'gsap-for-wordpress'),
                        'free' => true,
                        'size' => '3KB'
                    ),
                    'scroll_to' => array(
                        'name' => 'ScrollToPlugin',
                        'file' => 'ScrollToPlugin.min.js',
                        'description' => __('Smooth scrolling to specific elements', 'gsap-for-wordpress'),
                        'free' => true,
                        'size' => '4KB'
                    )
                )
            ),
            'premium' => array(
                'title' => __('Premium Plugins', 'gsap-for-wordpress'),
                'description' => __('Premium GSAP plugins (requires Club GreenSock license)', 'gsap-for-wordpress'),
                'libraries' => array(
                    'drawsvg' => array(
                        'name' => 'DrawSVG',
                        'file' => 'DrawSVGPlugin.min.js',
                        'description' => __('Animate SVG strokes to create drawing effects', 'gsap-for-wordpress'),
                        'free' => false,
                        'size' => '5KB'
                    ),
                    'morphsvg' => array(
                        'name' => 'MorphSVG',
                        'file' => 'MorphSVGPlugin.min.js',
                        'description' => __('Morph any SVG shape into any other shape', 'gsap-for-wordpress'),
                        'free' => false,
                        'size' => '12KB'
                    ),
                    'split_text' => array(
                        'name' => 'SplitText',
                        'file' => 'SplitText.min.js',
                        'description' => __('Split text into individual characters, words, or lines', 'gsap-for-wordpress'),
                        'free' => false,
                        'size' => '8KB'
                    ),
                    'scroll_smoother' => array(
                        'name' => 'ScrollSmoother',
                        'file' => 'ScrollSmoother.min.js',
                        'description' => __('Ultra-smooth scrolling with momentum and effects', 'gsap-for-wordpress'),
                        'free' => false,
                        'size' => '16KB',
                        'dependencies' => array('scroll_trigger')
                    ),
                    'gsdev_tools' => array(
                        'name' => 'GSDevTools',
                        'file' => 'GSDevTools.min.js',
                        'description' => __('Visual timeline scrubbing and debugging tools', 'gsap-for-wordpress'),
                        'free' => false,
                        'size' => '22KB'
                    ),
                    'motion_path' => array(
                        'name' => 'MotionPath',
                        'file' => 'MotionPathPlugin.min.js',
                        'description' => __('Animate elements along custom paths', 'gsap-for-wordpress'),
                        'free' => false,
                        'size' => '8KB'
                    ),
                    'draggable' => array(
                        'name' => 'Draggable',
                        'file' => 'Draggable.min.js',
                        'description' => __('Create drag-and-drop interactions', 'gsap-for-wordpress'),
                        'free' => false,
                        'size' => '35KB'
                    ),
                    'inertia' => array(
                        'name' => 'InertiaPlugin',
                        'file' => 'InertiaPlugin.min.js',
                        'description' => __('Physics-based momentum and inertia effects', 'gsap-for-wordpress'),
                        'free' => false,
                        'size' => '7KB',
                        'dependencies' => array('draggable')
                    ),
                    'custom_ease' => array(
                        'name' => 'CustomEase',
                        'file' => 'CustomEase.min.js',
                        'description' => __('Create custom easing curves', 'gsap-for-wordpress'),
                        'free' => false,
                        'size' => '5KB'
                    ),
                    'custom_bounce' => array(
                        'name' => 'CustomBounce',
                        'file' => 'CustomBounce.min.js',
                        'description' => __('Configurable bounce easing effects', 'gsap-for-wordpress'),
                        'free' => false,
                        'size' => '4KB'
                    ),
                    'custom_wiggle' => array(
                        'name' => 'CustomWiggle',
                        'file' => 'CustomWiggle.min.js',
                        'description' => __('Random wiggle and wave animations', 'gsap-for-wordpress'),
                        'free' => false,
                        'size' => '4KB'
                    ),
                    'scramble_text' => array(
                        'name' => 'ScrambleText',
                        'file' => 'ScrambleTextPlugin.min.js',
                        'description' => __('Scrambled text reveal animations', 'gsap-for-wordpress'),
                        'free' => false,
                        'size' => '4KB'
                    ),
                    'physics_2d' => array(
                        'name' => 'Physics2D',
                        'file' => 'Physics2DPlugin.min.js',
                        'description' => __('2D physics simulations', 'gsap-for-wordpress'),
                        'free' => false,
                        'size' => '3KB'
                    ),
                    'physics_props' => array(
                        'name' => 'PhysicsProps',
                        'file' => 'PhysicsPropsPlugin.min.js',
                        'description' => __('Physics-based property animations', 'gsap-for-wordpress'),
                        'free' => false,
                        'size' => '3KB'
                    ),
                    'css_rule' => array(
                        'name' => 'CSSRule',
                        'file' => 'CSSRulePlugin.min.js',
                        'description' => __('Animate CSS rules and pseudo-elements', 'gsap-for-wordpress'),
                        'free' => false,
                        'size' => '2KB'
                    ),
                    'easel' => array(
                        'name' => 'EaselJS',
                        'file' => 'EaselPlugin.min.js',
                        'description' => __('Integration with EaselJS canvas library', 'gsap-for-wordpress'),
                        'free' => false,
                        'size' => '4KB'
                    ),
                    'pixi' => array(
                        'name' => 'PixiJS',
                        'file' => 'PixiPlugin.min.js',
                        'description' => __('Integration with PixiJS WebGL library', 'gsap-for-wordpress'),
                        'free' => false,
                        'size' => '6KB'
                    )
                )
            )
        );

        // Allow filtering of library definitions
        $this->library_definitions = apply_filters('gsap_wp_library_definitions', $this->library_definitions);
    }

    /**
     * Render settings page
     */
    public function render_page() {
        if (class_exists('GSAP_WP_Admin')) {
            $admin = GSAP_WP_Admin::get_instance();
            $admin->render_header();
            $this->render_content();
            $admin->render_footer();
        }
    }

    /**
     * Render settings content
     */
    public function render_content() {
        // Debug output (visible in page source only)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            echo '<!-- GSAP Settings Debug: ' . print_r($this->settings, true) . ' -->';
        }
        ?>
        <div class="gsap-wp-settings">
            <form method="post" action="options.php" class="gsap-wp-settings-form">
                <?php settings_fields('gsap_wp_settings_group'); ?>
                <input type="hidden" name="option_page" value="gsap_wp_settings_group" />
                <input type="hidden" name="action" value="update" />

                <?php $this->render_library_settings(); ?>
                <?php $this->render_performance_settings(); ?>
                <?php $this->render_conditional_loading_settings(); ?>

                <div class="gsap-wp-form-actions">
                    <?php submit_button(__('Save Settings', 'gsap-for-wordpress'), 'primary', 'submit_settings', false); ?>
                    <button type="button" class="button button-secondary" id="gsap-wp-reset-settings">
                        <?php _e('Reset to Defaults', 'gsap-for-wordpress'); ?>
                    </button>
                </div>
            </form>

            <?php $this->render_info_panel(); ?>
        </div>
        <?php
    }

    /**
     * Render library settings section
     */
    private function render_library_settings() {
        ?>
        <div class="gsap-wp-settings-section">
            <h2><?php _e('GSAP Libraries', 'gsap-for-wordpress'); ?></h2>
            <p class="description">
                <?php _e('Select which GSAP libraries to load on your website. Only enable the libraries you actually use to optimize performance.', 'gsap-for-wordpress'); ?>
            </p>

            <?php foreach ($this->library_definitions as $category_key => $category): ?>
                <div class="gsap-wp-library-category">
                    <h3><?php echo esc_html($category['title']); ?></h3>
                    <?php if (!empty($category['description'])): ?>
                        <p class="description"><?php echo esc_html($category['description']); ?></p>
                    <?php endif; ?>

                    <div class="gsap-wp-library-grid">
                        <?php foreach ($category['libraries'] as $library_key => $library): ?>
                            <?php $this->render_library_option($library_key, $library); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * Render individual library option
     *
     * @param string $library_key
     * @param array $library
     */
    private function render_library_option($library_key, $library) {
        // Check if library is enabled in settings with proper fallback
        $is_enabled = false;

        if (isset($this->settings['libraries'][$library_key])) {
            $is_enabled = (bool) $this->settings['libraries'][$library_key];
        }

        $is_required = isset($library['required']) ? $library['required'] : false;
        $is_premium = !$library['free'];
        $has_dependencies = isset($library['dependencies']) && !empty($library['dependencies']);

        // Required libraries are always enabled
        if ($is_required) {
            $is_enabled = true;
        }

        // Debug logging in WP_DEBUG mode
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf(
                'GSAP Library %s: %s (required: %s)',
                $library_key,
                $is_enabled ? 'ENABLED' : 'disabled',
                $is_required ? 'yes' : 'no'
            ));
        }

        $classes = array('gsap-wp-library-option');
        if ($is_premium) {
            $classes[] = 'premium';
        }
        if ($is_required) {
            $classes[] = 'required';
        }
        if ($is_enabled) {
            $classes[] = 'enabled';
        }
        ?>
        <div class="<?php echo implode(' ', $classes); ?>">
            <label class="gsap-wp-checkbox-label">
                <input type="checkbox"
                       name="gsap_wp_settings[libraries][<?php echo esc_attr($library_key); ?>]"
                       value="1"
                       <?php checked($is_enabled, true); ?>
                       <?php disabled($is_required); ?>
                       <?php if ($has_dependencies): ?>
                           data-dependencies="<?php echo esc_attr(implode(',', $library['dependencies'])); ?>"
                       <?php endif; ?>
                       class="gsap-wp-library-checkbox">

                <div class="gsap-wp-library-info">
                    <div class="gsap-wp-library-header">
                        <span class="gsap-wp-library-name"><?php echo esc_html($library['name']); ?></span>
                        <span class="gsap-wp-library-badges">
                            <?php if ($is_premium): ?>
                                <span class="gsap-wp-badge premium"><?php _e('Premium', 'gsap-for-wordpress'); ?></span>
                            <?php else: ?>
                                <span class="gsap-wp-badge free"><?php _e('Free', 'gsap-for-wordpress'); ?></span>
                            <?php endif; ?>
                            <span class="gsap-wp-badge size"><?php echo esc_html($library['size']); ?></span>
                        </span>
                    </div>
                    <div class="gsap-wp-library-description">
                        <?php echo esc_html($library['description']); ?>
                    </div>
                    <?php if ($has_dependencies): ?>
                        <div class="gsap-wp-library-dependencies">
                            <small>
                                <?php
                                printf(
                                    __('Requires: %s', 'gsap-for-wordpress'),
                                    implode(', ', array_map(array($this, 'get_library_name_by_key'), $library['dependencies']))
                                );
                                ?>
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            </label>
        </div>
        <?php
    }

    /**
     * Render performance settings section
     */
    private function render_performance_settings() {
        $performance = isset($this->settings['performance']) ? $this->settings['performance'] : array();
        ?>
        <div class="gsap-wp-settings-section">
            <h2><?php _e('Performance Settings', 'gsap-for-wordpress'); ?></h2>
            <p class="description">
                <?php _e('Configure how GSAP files are loaded and optimized for your website.', 'gsap-for-wordpress'); ?>
            </p>

            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('File Format', 'gsap-for-wordpress'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   name="gsap_wp_settings[performance][minified]"
                                   value="1"
                                   <?php checked(isset($performance['minified']) ? $performance['minified'] : true); ?>>
                            <?php _e('Use minified files', 'gsap-for-wordpress'); ?>
                        </label>
                        <p class="description">
                            <?php _e('Load compressed versions for better performance (recommended).', 'gsap-for-wordpress'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php _e('Loading Position', 'gsap-for-wordpress'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   name="gsap_wp_settings[performance][load_in_footer]"
                                   value="1"
                                   <?php checked(isset($performance['load_in_footer']) ? $performance['load_in_footer'] : true); ?>>
                            <?php _e('Load scripts in footer', 'gsap-for-wordpress'); ?>
                        </label>
                        <p class="description">
                            <?php _e('Improves page load speed by loading scripts after content (recommended).', 'gsap-for-wordpress'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php _e('File Optimization', 'gsap-for-wordpress'); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox"
                                       name="gsap_wp_settings[performance][auto_merge]"
                                       value="1"
                                       <?php checked(isset($performance['auto_merge']) ? $performance['auto_merge'] : false); ?>>
                                <?php _e('Auto-merge JavaScript files', 'gsap-for-wordpress'); ?>
                            </label><br>

                            <label>
                                <input type="checkbox"
                                       name="gsap_wp_settings[performance][compression]"
                                       value="1"
                                       <?php checked(isset($performance['compression']) ? $performance['compression'] : true); ?>>
                                <?php _e('Enable compression', 'gsap-for-wordpress'); ?>
                            </label><br>

                            <label>
                                <input type="checkbox"
                                       name="gsap_wp_settings[performance][cache_busting]"
                                       value="1"
                                       <?php checked(isset($performance['cache_busting']) ? $performance['cache_busting'] : true); ?>>
                                <?php _e('Enable cache busting', 'gsap-for-wordpress'); ?>
                            </label>
                        </fieldset>
                        <p class="description">
                            <?php _e('Additional optimizations for file loading and caching.', 'gsap-for-wordpress'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php _e('CDN', 'gsap-for-wordpress'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   name="gsap_wp_settings[performance][use_cdn]"
                                   value="1"
                                   <?php checked(isset($performance['use_cdn']) ? $performance['use_cdn'] : false); ?>>
                            <?php _e('Load from CDN (jsDelivr)', 'gsap-for-wordpress'); ?>
                        </label>
                        <p class="description">
                            <?php _e('Load GSAP files from CDN for better global performance. Note: Premium plugins cannot be loaded from CDN.', 'gsap-for-wordpress'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

    /**
     * Render conditional loading settings section
     */
    private function render_conditional_loading_settings() {
        $conditional = isset($this->settings['conditional_loading']) ? $this->settings['conditional_loading'] : array();
        ?>
        <div class="gsap-wp-settings-section">
            <h2><?php _e('Conditional Loading', 'gsap-for-wordpress'); ?></h2>
            <p class="description">
                <?php _e('Control where GSAP libraries are loaded to optimize performance on specific pages.', 'gsap-for-wordpress'); ?>
            </p>

            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Enable Conditional Loading', 'gsap-for-wordpress'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   name="gsap_wp_settings[conditional_loading][enabled]"
                                   value="1"
                                   <?php checked(isset($conditional['enabled']) ? $conditional['enabled'] : false); ?>
                                   id="gsap-wp-conditional-enabled">
                            <?php _e('Only load GSAP on specific pages', 'gsap-for-wordpress'); ?>
                        </label>
                    </td>
                </tr>

                <tr class="gsap-wp-conditional-row">
                    <th scope="row"><?php _e('Include Pages', 'gsap-for-wordpress'); ?></th>
                    <td>
                        <?php
                        $pages = get_pages();
                        $include_pages = isset($conditional['include_pages']) ? $conditional['include_pages'] : array();
                        ?>
                        <fieldset>
                            <?php foreach ($pages as $page): ?>
                                <label>
                                    <input type="checkbox"
                                           name="gsap_wp_settings[conditional_loading][include_pages][]"
                                           value="<?php echo esc_attr($page->ID); ?>"
                                           <?php checked(in_array($page->ID, $include_pages)); ?>>
                                    <?php echo esc_html($page->post_title); ?>
                                </label><br>
                            <?php endforeach; ?>
                        </fieldset>
                        <p class="description">
                            <?php _e('Select pages where GSAP should be loaded.', 'gsap-for-wordpress'); ?>
                        </p>
                    </td>
                </tr>

                <tr class="gsap-wp-conditional-row">
                    <th scope="row"><?php _e('Post Types', 'gsap-for-wordpress'); ?></th>
                    <td>
                        <?php
                        $post_types = get_post_types(array('public' => true), 'objects');
                        $include_post_types = isset($conditional['include_post_types']) ? $conditional['include_post_types'] : array();
                        ?>
                        <fieldset>
                            <?php foreach ($post_types as $post_type): ?>
                                <label>
                                    <input type="checkbox"
                                           name="gsap_wp_settings[conditional_loading][include_post_types][]"
                                           value="<?php echo esc_attr($post_type->name); ?>"
                                           <?php checked(in_array($post_type->name, $include_post_types)); ?>>
                                    <?php echo esc_html($post_type->label); ?>
                                </label><br>
                            <?php endforeach; ?>
                        </fieldset>
                        <p class="description">
                            <?php _e('Select post types where GSAP should be loaded.', 'gsap-for-wordpress'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

    /**
     * Render info panel
     */
    private function render_info_panel() {
        ?>
        <div class="gsap-wp-info-panel">
            <h3><?php _e('Quick Start Guide', 'gsap-for-wordpress'); ?></h3>

            <div class="gsap-wp-code-example">
                <h4><?php _e('Basic Animation Example', 'gsap-for-wordpress'); ?></h4>
                <pre><code>// Animate an element
gsap.to(".my-element", {
    duration: 2,
    x: 100,
    rotation: 360,
    ease: "power2.out"
});

// ScrollTrigger animation
gsap.to(".scroll-element", {
    scrollTrigger: ".scroll-element",
    x: 100,
    duration: 3
});</code></pre>
            </div>

            <div class="gsap-wp-resources">
                <h4><?php _e('Helpful Resources', 'gsap-for-wordpress'); ?></h4>
                <ul>
                    <li><a href="https://greensock.com/docs/" target="_blank"><?php _e('GSAP Documentation', 'gsap-for-wordpress'); ?></a></li>
                    <li><a href="https://greensock.com/cheatsheet/" target="_blank"><?php _e('GSAP Cheat Sheet', 'gsap-for-wordpress'); ?></a></li>
                    <li><a href="https://codepen.io/collection/DyJRrY" target="_blank"><?php _e('GSAP Examples', 'gsap-for-wordpress'); ?></a></li>
                    <li><a href="https://greensock.com/licensing/" target="_blank"><?php _e('GSAP Licensing', 'gsap-for-wordpress'); ?></a></li>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * Get library name by key
     *
     * @param string $key
     * @return string
     */
    public function get_library_name_by_key($key) {
        foreach ($this->library_definitions as $category) {
            if (isset($category['libraries'][$key])) {
                return $category['libraries'][$key]['name'];
            }
        }
        return $key;
    }

    /**
     * Get all library definitions
     *
     * @return array
     */
    public function get_library_definitions() {
        return $this->library_definitions;
    }

    /**
     * Get current settings
     *
     * @return array
     */
    public function get_settings() {
        return $this->settings;
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