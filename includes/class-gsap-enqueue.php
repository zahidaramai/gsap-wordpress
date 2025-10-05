<?php
/**
 * GSAP Script Enqueue Handler
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class GSAP_Enqueue {

    /**
     * Available GSAP plugins with their dependencies
     */
    private static $available_plugins = array(
        'gsap-core' => array(
            'file' => 'gsap',
            'deps' => array(),
            'free' => true
        ),
        'css-plugin' => array(
            'file' => 'CSSPlugin',
            'deps' => array('gsap-core'),
            'free' => true
        ),
        'scroll-trigger' => array(
            'file' => 'ScrollTrigger',
            'deps' => array('gsap-core'),
            'free' => true
        ),
        'text-plugin' => array(
            'file' => 'TextPlugin',
            'deps' => array('gsap-core'),
            'free' => true
        ),
        'observer' => array(
            'file' => 'Observer',
            'deps' => array('gsap-core'),
            'free' => true
        ),
        'flip' => array(
            'file' => 'Flip',
            'deps' => array('gsap-core'),
            'free' => true
        ),
        'morphsvg' => array(
            'file' => 'MorphSVGPlugin',
            'deps' => array('gsap-core'),
            'free' => false
        ),
        'drawsvg' => array(
            'file' => 'DrawSVGPlugin',
            'deps' => array('gsap-core'),
            'free' => false
        ),
        'split-text' => array(
            'file' => 'SplitText',
            'deps' => array('gsap-core'),
            'free' => false
        ),
        'draggable' => array(
            'file' => 'Draggable',
            'deps' => array('gsap-core'),
            'free' => false
        ),
        'inertia' => array(
            'file' => 'InertiaPlugin',
            'deps' => array('gsap-core', 'draggable'),
            'free' => false
        ),
        'scroll-smoother' => array(
            'file' => 'ScrollSmoother',
            'deps' => array('gsap-core', 'scroll-trigger'),
            'free' => false
        ),
        'custom-ease' => array(
            'file' => 'CustomEase',
            'deps' => array('gsap-core'),
            'free' => false
        ),
        'motion-path' => array(
            'file' => 'MotionPathPlugin',
            'deps' => array('gsap-core'),
            'free' => false
        )
    );

    /**
     * Enqueue GSAP scripts based on settings
     */
    public static function enqueue_scripts() {
        $settings = get_option('gsap_wp_settings', array());
        $use_minified = isset($settings['minified_version']) ? $settings['minified_version'] : true;
        $in_footer = isset($settings['load_in_footer']) ? $settings['load_in_footer'] : true;

        $js_path = $use_minified ? 'assets/js/minified/' : 'assets/js/src/';
        $suffix = $use_minified ? '.min.js' : '.js';

        // Determine which plugins to load based on settings
        $plugins_to_load = self::get_enabled_plugins($settings);

        // Enqueue scripts in dependency order
        foreach ($plugins_to_load as $plugin_key) {
            if (isset(self::$available_plugins[$plugin_key])) {
                $plugin = self::$available_plugins[$plugin_key];

                wp_enqueue_script(
                    'gsap-' . $plugin_key,
                    GSAP_WP_PLUGIN_URL . $js_path . $plugin['file'] . $suffix,
                    $plugin['deps'],
                    GSAP_WP_VERSION,
                    $in_footer
                );
            }
        }
    }

    /**
     * Get enabled plugins from settings
     */
    private static function get_enabled_plugins($settings) {
        $enabled = array();

        // Core libraries
        if (isset($settings['load_gsap_core']) && $settings['load_gsap_core']) {
            $enabled[] = 'gsap-core';
        }

        if (isset($settings['load_css_plugin']) && $settings['load_css_plugin']) {
            $enabled[] = 'css-plugin';
        }

        // Free plugins
        if (isset($settings['load_scroll_trigger']) && $settings['load_scroll_trigger']) {
            $enabled[] = 'scroll-trigger';
        }

        if (isset($settings['load_text_plugin']) && $settings['load_text_plugin']) {
            $enabled[] = 'text-plugin';
        }

        // Premium plugins
        if (isset($settings['load_morphsvg']) && $settings['load_morphsvg']) {
            $enabled[] = 'morphsvg';
        }

        if (isset($settings['load_drawsvg']) && $settings['load_drawsvg']) {
            $enabled[] = 'drawsvg';
        }

        if (isset($settings['load_split_text']) && $settings['load_split_text']) {
            $enabled[] = 'split-text';
        }

        // Resolve dependencies
        return self::resolve_dependencies($enabled);
    }

    /**
     * Resolve plugin dependencies
     */
    private static function resolve_dependencies($requested_plugins) {
        $resolved = array();
        $processed = array();

        foreach ($requested_plugins as $plugin) {
            self::add_plugin_with_deps($plugin, $resolved, $processed);
        }

        return $resolved;
    }

    /**
     * Recursively add plugin with its dependencies
     */
    private static function add_plugin_with_deps($plugin, &$resolved, &$processed) {
        if (in_array($plugin, $processed)) {
            return;
        }

        if (isset(self::$available_plugins[$plugin])) {
            $plugin_info = self::$available_plugins[$plugin];

            // Add dependencies first
            foreach ($plugin_info['deps'] as $dep) {
                self::add_plugin_with_deps($dep, $resolved, $processed);
            }

            // Add the plugin itself
            if (!in_array($plugin, $resolved)) {
                $resolved[] = $plugin;
            }
        }

        $processed[] = $plugin;
    }

    /**
     * Get available plugins list
     */
    public static function get_available_plugins() {
        return self::$available_plugins;
    }
}