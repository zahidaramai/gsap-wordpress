<?php
/**
 * GSAP Loader class for GSAP WordPress plugin
 *
 * @package GSAP_For_WordPress
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GSAP WordPress Loader class
 */
class GSAP_WP_GSAP_Loader {

    /**
     * Single instance of the class
     *
     * @var GSAP_WP_GSAP_Loader
     */
    private static $instance = null;

    /**
     * Plugin settings
     *
     * @var array
     */
    private $settings = array();

    /**
     * Library definitions with dependencies
     *
     * @var array
     */
    private $library_definitions = array();

    /**
     * CDN base URL
     *
     * @var string
     */
    private $cdn_base_url = 'https://cdn.jsdelivr.net/npm/gsap@3.12.2/dist/';

    /**
     * Get single instance
     *
     * @return GSAP_WP_GSAP_Loader
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
        $this->load_settings();
        $this->init_library_definitions();
        $this->init_hooks();
    }

    /**
     * Load plugin settings
     */
    private function load_settings() {
        $this->settings = get_option('gsap_wp_settings', array());
    }

    /**
     * Initialize library definitions with dependencies and file info
     */
    private function init_library_definitions() {
        $this->library_definitions = array(
            'gsap_core' => array(
                'file' => 'gsap.min.js',
                'dependencies' => array(),
                'cdn_available' => true,
                'free' => true
            ),
            'css_plugin' => array(
                'file' => 'CSSPlugin.min.js',
                'dependencies' => array('gsap_core'),
                'cdn_available' => true,
                'free' => true
            ),
            'scroll_trigger' => array(
                'file' => 'ScrollTrigger.min.js',
                'dependencies' => array('gsap_core'),
                'cdn_available' => true,
                'free' => true
            ),
            'observer' => array(
                'file' => 'Observer.min.js',
                'dependencies' => array('gsap_core'),
                'cdn_available' => true,
                'free' => true
            ),
            'flip' => array(
                'file' => 'Flip.min.js',
                'dependencies' => array('gsap_core'),
                'cdn_available' => true,
                'free' => true
            ),
            'text_plugin' => array(
                'file' => 'TextPlugin.min.js',
                'dependencies' => array('gsap_core'),
                'cdn_available' => true,
                'free' => true
            ),
            'scroll_to' => array(
                'file' => 'ScrollToPlugin.min.js',
                'dependencies' => array('gsap_core'),
                'cdn_available' => true,
                'free' => true
            ),
            'drawsvg' => array(
                'file' => 'DrawSVGPlugin.min.js',
                'dependencies' => array('gsap_core'),
                'cdn_available' => false,
                'free' => false
            ),
            'morphsvg' => array(
                'file' => 'MorphSVGPlugin.min.js',
                'dependencies' => array('gsap_core'),
                'cdn_available' => false,
                'free' => false
            ),
            'split_text' => array(
                'file' => 'SplitText.min.js',
                'dependencies' => array('gsap_core'),
                'cdn_available' => false,
                'free' => false
            ),
            'scroll_smoother' => array(
                'file' => 'ScrollSmoother.min.js',
                'dependencies' => array('gsap_core', 'scroll_trigger'),
                'cdn_available' => false,
                'free' => false
            ),
            'gsdev_tools' => array(
                'file' => 'GSDevTools.min.js',
                'dependencies' => array('gsap_core'),
                'cdn_available' => false,
                'free' => false
            ),
            'motion_path' => array(
                'file' => 'MotionPathPlugin.min.js',
                'dependencies' => array('gsap_core'),
                'cdn_available' => false,
                'free' => false
            ),
            'draggable' => array(
                'file' => 'Draggable.min.js',
                'dependencies' => array('gsap_core'),
                'cdn_available' => false,
                'free' => false
            ),
            'inertia' => array(
                'file' => 'InertiaPlugin.min.js',
                'dependencies' => array('gsap_core', 'draggable'),
                'cdn_available' => false,
                'free' => false
            ),
            'custom_ease' => array(
                'file' => 'CustomEase.min.js',
                'dependencies' => array('gsap_core'),
                'cdn_available' => false,
                'free' => false
            ),
            'custom_bounce' => array(
                'file' => 'CustomBounce.min.js',
                'dependencies' => array('gsap_core'),
                'cdn_available' => false,
                'free' => false
            ),
            'custom_wiggle' => array(
                'file' => 'CustomWiggle.min.js',
                'dependencies' => array('gsap_core'),
                'cdn_available' => false,
                'free' => false
            ),
            'scramble_text' => array(
                'file' => 'ScrambleTextPlugin.min.js',
                'dependencies' => array('gsap_core'),
                'cdn_available' => false,
                'free' => false
            ),
            'physics_2d' => array(
                'file' => 'Physics2DPlugin.min.js',
                'dependencies' => array('gsap_core'),
                'cdn_available' => false,
                'free' => false
            ),
            'physics_props' => array(
                'file' => 'PhysicsPropsPlugin.min.js',
                'dependencies' => array('gsap_core'),
                'cdn_available' => false,
                'free' => false
            ),
            'css_rule' => array(
                'file' => 'CSSRulePlugin.min.js',
                'dependencies' => array('gsap_core'),
                'cdn_available' => false,
                'free' => false
            ),
            'easel' => array(
                'file' => 'EaselPlugin.min.js',
                'dependencies' => array('gsap_core'),
                'cdn_available' => false,
                'free' => false
            ),
            'pixi' => array(
                'file' => 'PixiPlugin.min.js',
                'dependencies' => array('gsap_core'),
                'cdn_available' => false,
                'free' => false
            )
        );

        // Allow filtering of library definitions
        $this->library_definitions = apply_filters('gsap_wp_library_definitions', $this->library_definitions);
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_footer', array($this, 'output_custom_scripts'), 99);
        add_filter('script_loader_tag', array($this, 'add_script_attributes'), 10, 3);
    }

    /**
     * Enqueue GSAP scripts based on settings
     */
    public function enqueue_scripts() {
        // Check if we should load GSAP on this page
        if (!$this->should_load_on_current_page()) {
            return;
        }

        // Get enabled libraries
        $enabled_libraries = $this->get_enabled_libraries();

        if (empty($enabled_libraries)) {
            return;
        }

        // Resolve dependencies
        $libraries_to_load = $this->resolve_dependencies($enabled_libraries);

        // Enqueue libraries in dependency order
        foreach ($libraries_to_load as $library_key) {
            $this->enqueue_library($library_key);
        }

        // Enqueue custom user files
        $this->enqueue_custom_files();

        // Add inline configuration
        $this->add_inline_config();
    }

    /**
     * Enqueue individual library
     *
     * @param string $library_key
     */
    private function enqueue_library($library_key) {
        if (!isset($this->library_definitions[$library_key])) {
            return;
        }

        $library = $this->library_definitions[$library_key];
        $use_minified = isset($this->settings['performance']['minified']) ? $this->settings['performance']['minified'] : true;
        $use_cdn = isset($this->settings['performance']['use_cdn']) ? $this->settings['performance']['use_cdn'] : false;
        $load_in_footer = isset($this->settings['performance']['load_in_footer']) ? $this->settings['performance']['load_in_footer'] : true;

        // Determine file name
        $file_name = $library['file'];
        if (!$use_minified) {
            $file_name = str_replace('.min.js', '.js', $file_name);
        }

        // Determine URL
        if ($use_cdn && $library['cdn_available'] && $library['free']) {
            $script_url = $this->cdn_base_url . $file_name;
        } else {
            $script_url = $this->get_local_script_url($file_name, $use_minified);
        }

        // Get dependencies
        $dependencies = $this->get_wp_dependencies($library['dependencies']);

        // Add cache busting
        $version = $this->get_script_version($library_key);

        // Enqueue script
        wp_enqueue_script(
            'gsap-' . $library_key,
            $script_url,
            $dependencies,
            $version,
            $load_in_footer
        );

        // Add script attributes if needed
        add_filter('script_loader_tag', function($tag, $handle, $src) use ($library_key) {
            if ($handle === 'gsap-' . $library_key) {
                return str_replace('<script ', '<script data-gsap-library="' . esc_attr($library_key) . '" ', $tag);
            }
            return $tag;
        }, 10, 3);
    }

    /**
     * Enqueue custom user files
     */
    private function enqueue_custom_files() {
        // Use plugin assets directory
        $base_url = GSAP_WP_ASSETS_URL;
        $base_path = GSAP_WP_PLUGIN_PATH . 'assets/';

        // Enqueue custom CSS
        $css_file = $base_path . 'css/animation.css';
        if (file_exists($css_file)) {
            wp_enqueue_style(
                'gsap-wp-custom-css',
                $base_url . 'css/animation.css',
                array(),
                filemtime($css_file)
            );
        }

        // Enqueue custom JS
        $js_file = $base_path . 'js/global.js';
        if (file_exists($js_file)) {
            wp_enqueue_script(
                'gsap-wp-custom-js',
                $base_url . 'js/global.js',
                array('gsap-gsap_core'),
                filemtime($js_file),
                true
            );
        }
    }

    /**
     * Output custom inline scripts
     */
    public function output_custom_scripts() {
        // Only output if GSAP is loaded
        if (!wp_script_is('gsap-gsap_core', 'enqueued')) {
            return;
        }

        // Output custom inline JS from options
        $custom_js = get_option('gsap_wp_custom_js', '');
        if (!empty($custom_js)) {
            echo '<script type="text/javascript">';
            echo '/* GSAP WordPress Custom Inline Scripts */';
            echo $custom_js;
            echo '</script>';
        }

        // Output GSAP configuration
        $this->output_gsap_config();
    }

    /**
     * Add script attributes
     *
     * @param string $tag
     * @param string $handle
     * @param string $src
     * @return string
     */
    public function add_script_attributes($tag, $handle, $src) {
        // Add async loading for non-critical GSAP plugins
        if (strpos($handle, 'gsap-') === 0 && $handle !== 'gsap-gsap_core') {
            $async_plugins = array('gsap-gsdev_tools', 'gsap-motion_path');
            if (in_array($handle, $async_plugins)) {
                $tag = str_replace('<script ', '<script async ', $tag);
            }
        }

        return $tag;
    }

    /**
     * Check if GSAP should load on current page
     *
     * @return bool
     */
    private function should_load_on_current_page() {
        $conditional = isset($this->settings['conditional_loading']) ? $this->settings['conditional_loading'] : array();

        // If conditional loading is disabled, load everywhere
        if (!isset($conditional['enabled']) || !$conditional['enabled']) {
            return true;
        }

        global $post;

        // Check included pages
        if (!empty($conditional['include_pages']) && is_page()) {
            return in_array($post->ID, $conditional['include_pages']);
        }

        // Check included post types
        if (!empty($conditional['include_post_types'])) {
            return is_singular($conditional['include_post_types']);
        }

        // Check excluded pages
        if (!empty($conditional['exclude_pages']) && is_page()) {
            return !in_array($post->ID, $conditional['exclude_pages']);
        }

        // Check excluded post types
        if (!empty($conditional['exclude_post_types'])) {
            return !is_singular($conditional['exclude_post_types']);
        }

        // Default behavior based on settings
        return true;
    }

    /**
     * Get enabled libraries from settings
     *
     * @return array
     */
    private function get_enabled_libraries() {
        $libraries = isset($this->settings['libraries']) ? $this->settings['libraries'] : array();
        $enabled = array();

        foreach ($libraries as $library_key => $is_enabled) {
            if ($is_enabled && isset($this->library_definitions[$library_key])) {
                $enabled[] = $library_key;
            }
        }

        return $enabled;
    }

    /**
     * Resolve library dependencies
     *
     * @param array $libraries
     * @return array
     */
    private function resolve_dependencies($libraries) {
        $resolved = array();
        $processed = array();

        foreach ($libraries as $library) {
            $this->add_library_with_dependencies($library, $resolved, $processed);
        }

        return $resolved;
    }

    /**
     * Recursively add library with its dependencies
     *
     * @param string $library_key
     * @param array $resolved
     * @param array $processed
     */
    private function add_library_with_dependencies($library_key, &$resolved, &$processed) {
        if (in_array($library_key, $processed)) {
            return;
        }

        if (!isset($this->library_definitions[$library_key])) {
            return;
        }

        $library = $this->library_definitions[$library_key];

        // Add dependencies first
        foreach ($library['dependencies'] as $dependency) {
            $this->add_library_with_dependencies($dependency, $resolved, $processed);
        }

        // Add the library itself
        if (!in_array($library_key, $resolved)) {
            $resolved[] = $library_key;
        }

        $processed[] = $library_key;
    }

    /**
     * Get WordPress script dependencies
     *
     * @param array $gsap_dependencies
     * @return array
     */
    private function get_wp_dependencies($gsap_dependencies) {
        $wp_deps = array();

        foreach ($gsap_dependencies as $dep) {
            $wp_deps[] = 'gsap-' . $dep;
        }

        return $wp_deps;
    }

    /**
     * Get local script URL
     *
     * @param string $file_name
     * @param bool $use_minified
     * @return string
     */
    private function get_local_script_url($file_name, $use_minified = true) {
        $base_path = $use_minified ? 'assets/js/minified/' : 'assets/js/src/';
        return GSAP_WP_PLUGIN_URL . $base_path . $file_name;
    }

    /**
     * Get script version for cache busting
     *
     * @param string $library_key
     * @return string
     */
    private function get_script_version($library_key) {
        $cache_busting = isset($this->settings['performance']['cache_busting']) ? $this->settings['performance']['cache_busting'] : true;

        if (!$cache_busting) {
            return GSAP_WP_VERSION;
        }

        // Use file modification time for local files
        $use_cdn = isset($this->settings['performance']['use_cdn']) ? $this->settings['performance']['use_cdn'] : false;
        $library = $this->library_definitions[$library_key];

        if (!$use_cdn || !$library['cdn_available'] || !$library['free']) {
            $use_minified = isset($this->settings['performance']['minified']) ? $this->settings['performance']['minified'] : true;
            $file_path = $this->get_local_script_path($library['file'], $use_minified);

            if (file_exists($file_path)) {
                return filemtime($file_path);
            }
        }

        return GSAP_WP_VERSION;
    }

    /**
     * Get local script file path
     *
     * @param string $file_name
     * @param bool $use_minified
     * @return string
     */
    private function get_local_script_path($file_name, $use_minified = true) {
        $base_path = $use_minified ? 'assets/js/minified/' : 'assets/js/src/';
        return GSAP_WP_PLUGIN_PATH . $base_path . $file_name;
    }

    /**
     * Add inline GSAP configuration
     */
    private function add_inline_config() {
        $config = array(
            'version' => GSAP_WP_VERSION,
            'debug' => GSAP_WP_DEBUG,
            'settings' => array(
                'performance' => isset($this->settings['performance']) ? $this->settings['performance'] : array()
            )
        );

        wp_localize_script('gsap-gsap_core', 'gsapWpConfig', $config);
    }

    /**
     * Output GSAP configuration script
     */
    private function output_gsap_config() {
        $enabled_libraries = $this->get_enabled_libraries();
        $settings = $this->settings;
        $performance = isset($settings['performance']) ? $settings['performance'] : array();
        ?>
        <script type="text/javascript">
        /* GSAP WordPress Configuration & Validation */
        (function() {
            // Styled console header
            console.log('%c🎬 GSAP for WordPress', 'color: #88ce02; font-size: 18px; font-weight: bold; padding: 5px 0;');
            console.log('%cVersion: <?php echo GSAP_WP_VERSION; ?>', 'color: #666; font-size: 12px;');
            console.log('');

            if (typeof gsap !== 'undefined') {
                console.log('%c✅ GSAP Core Loaded Successfully', 'color: #00a32a; font-weight: bold; font-size: 14px;');
                console.log('%cGSAP Version: ' + gsap.version, 'color: #666;');
                console.log('');

                <?php if (!empty($enabled_libraries)): ?>
                console.log('%c📚 Activated Libraries (<?php echo count($enabled_libraries); ?>):', 'color: #2271b1; font-weight: bold; font-size: 13px;');
                <?php foreach ($enabled_libraries as $library): ?>
                    <?php if (isset($this->library_definitions[$library])): ?>
                        <?php $lib = $this->library_definitions[$library]; ?>
                        <?php $lib_name = str_replace('_', ' ', ucwords($library)); ?>
                        console.log('  %c✓%c <?php echo esc_js($lib_name); ?> %c(<?php echo esc_js($lib['file']); ?>)',
                            'color: #00a32a; font-weight: bold;',
                            'color: #1d2327; font-weight: 600;',
                            'color: #666; font-size: 11px;'
                        );
                    <?php endif; ?>
                <?php endforeach; ?>
                console.log('');

                console.log('%c⚙️ Performance Settings:', 'color: #2271b1; font-weight: bold; font-size: 13px;');
                console.log('  Minified Files: %c<?php echo isset($performance['minified']) && $performance['minified'] ? 'Yes ✓' : 'No'; ?>',
                    'color: <?php echo isset($performance['minified']) && $performance['minified'] ? '#00a32a' : '#d63638'; ?>; font-weight: 600;'
                );
                console.log('  Load in Footer: %c<?php echo isset($performance['load_in_footer']) && $performance['load_in_footer'] ? 'Yes ✓' : 'No'; ?>',
                    'color: <?php echo isset($performance['load_in_footer']) && $performance['load_in_footer'] ? '#00a32a' : '#d63638'; ?>; font-weight: 600;'
                );
                console.log('  CDN Loading: %c<?php echo isset($performance['use_cdn']) && $performance['use_cdn'] ? 'Yes ✓' : 'No'; ?>',
                    'color: <?php echo isset($performance['use_cdn']) && $performance['use_cdn'] ? '#00a32a' : '#666'; ?>; font-weight: 600;'
                );
                console.log('  Cache Busting: %c<?php echo isset($performance['cache_busting']) && $performance['cache_busting'] ? 'Enabled ✓' : 'Disabled'; ?>',
                    'color: <?php echo isset($performance['cache_busting']) && $performance['cache_busting'] ? '#00a32a' : '#666'; ?>; font-weight: 600;'
                );
                console.log('');

                <?php else: ?>
                console.warn('%c⚠️ No Libraries Enabled', 'color: #f0b849; font-weight: bold; font-size: 14px;');
                console.log('%cEnable GSAP libraries in: WP Admin → GSAP → Settings', 'color: #666;');
                console.log('');
                <?php endif; ?>

                // Set default GSAP configuration
                gsap.defaults({
                    ease: "power2.out",
                    duration: 1
                });
                console.log('%cℹ️ Default Settings Applied:', 'color: #2271b1; font-size: 12px;');
                console.log('  Ease: power2.out');
                console.log('  Duration: 1s');
                console.log('');

                // ScrollTrigger integration
                if (typeof ScrollTrigger !== 'undefined') {
                    console.log('%c🔄 ScrollTrigger Active', 'color: #00a32a; font-weight: bold;');
                    console.log('  Auto-refresh on resize: Enabled ✓');
                    window.addEventListener('resize', function() {
                        ScrollTrigger.refresh();
                    });
                    console.log('');
                }

                // Success message
                console.log('%c🚀 GSAP is Ready!', 'color: #00a32a; font-weight: bold; font-size: 16px;');
                console.log('%cStart creating amazing animations with GSAP.', 'color: #666;');
                console.log('%cDocs: https://greensock.com/docs/', 'color: #2271b1;');

            } else {
                console.error('%c❌ GSAP Failed to Load', 'color: #d63638; font-weight: bold; font-size: 16px;');
                console.error('%cPossible causes:', 'color: #d63638; font-weight: bold;');
                console.log('  1. No libraries enabled in WP Admin → GSAP → Settings');
                console.log('  2. JavaScript conflict with another plugin');
                console.log('  3. File loading error (check browser Network tab)');
                console.log('');
                console.log('%cTroubleshooting:', 'color: #2271b1; font-weight: bold;');
                console.log('  • Enable GSAP Core in plugin settings');
                console.log('  • Check browser console for errors');
                console.log('  • Disable other plugins to test for conflicts');
            }
        })();
        </script>
        <?php
    }

    /**
     * Get loaded libraries for debugging
     *
     * @return array
     */
    public function get_loaded_libraries() {
        global $wp_scripts;

        $loaded_libraries = array();

        if (isset($wp_scripts->registered)) {
            foreach ($wp_scripts->registered as $handle => $script) {
                if (strpos($handle, 'gsap-') === 0) {
                    $library_key = str_replace('gsap-', '', $handle);
                    $loaded_libraries[] = $library_key;
                }
            }
        }

        return $loaded_libraries;
    }

    /**
     * Get library file size
     *
     * @param string $library_key
     * @return int|false
     */
    public function get_library_file_size($library_key) {
        if (!isset($this->library_definitions[$library_key])) {
            return false;
        }

        $library = $this->library_definitions[$library_key];
        $use_minified = isset($this->settings['performance']['minified']) ? $this->settings['performance']['minified'] : true;
        $file_path = $this->get_local_script_path($library['file'], $use_minified);

        if (file_exists($file_path)) {
            return filesize($file_path);
        }

        return false;
    }

    /**
     * Get total library file sizes
     *
     * @return array
     */
    public function get_total_library_sizes() {
        $enabled_libraries = $this->get_enabled_libraries();
        $total_size = 0;
        $library_sizes = array();

        foreach ($enabled_libraries as $library_key) {
            $size = $this->get_library_file_size($library_key);
            if ($size !== false) {
                $library_sizes[$library_key] = $size;
                $total_size += $size;
            }
        }

        return array(
            'total' => $total_size,
            'libraries' => $library_sizes,
            'formatted_total' => size_format($total_size)
        );
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