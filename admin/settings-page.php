<?php
/**
 * Admin Settings Page for GSAP for WordPress
 *
 * Note: This file is deprecated and kept only for backwards compatibility.
 * The actual settings rendering is handled by GSAP_WP_Settings class.
 * Form submission is handled by GSAP_WP_Admin class.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current settings for display only
$settings = get_option('gsap_wp_settings', array());
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="gsap-wp-admin-container">
        <form method="post" action="">

            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><?php _e('Core Libraries', 'gsap-for-wordpress'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="load_gsap_core" value="1" <?php checked(isset($settings['load_gsap_core']) ? $settings['load_gsap_core'] : true); ?>>
                                    <?php _e('Load GSAP Core', 'gsap-for-wordpress'); ?>
                                </label>
                                <p class="description"><?php _e('The main GSAP animation engine (required for all animations)', 'gsap-for-wordpress'); ?></p>

                                <label>
                                    <input type="checkbox" name="load_css_plugin" value="1" <?php checked(isset($settings['load_css_plugin']) ? $settings['load_css_plugin'] : true); ?>>
                                    <?php _e('Load CSS Plugin', 'gsap-for-wordpress'); ?>
                                </label>
                                <p class="description"><?php _e('Enables CSS property animations (recommended)', 'gsap-for-wordpress'); ?></p>
                            </fieldset>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Free Plugins', 'gsap-for-wordpress'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="load_scroll_trigger" value="1" <?php checked(isset($settings['load_scroll_trigger']) ? $settings['load_scroll_trigger'] : false); ?>>
                                    <?php _e('ScrollTrigger', 'gsap-for-wordpress'); ?>
                                </label>
                                <p class="description"><?php _e('Create scroll-driven animations', 'gsap-for-wordpress'); ?></p>

                                <label>
                                    <input type="checkbox" name="load_text_plugin" value="1" <?php checked(isset($settings['load_text_plugin']) ? $settings['load_text_plugin'] : false); ?>>
                                    <?php _e('TextPlugin', 'gsap-for-wordpress'); ?>
                                </label>
                                <p class="description"><?php _e('Animate text content with typewriter effects', 'gsap-for-wordpress'); ?></p>
                            </fieldset>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Premium Plugins', 'gsap-for-wordpress'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="load_morphsvg" value="1" <?php checked(isset($settings['load_morphsvg']) ? $settings['load_morphsvg'] : false); ?>>
                                    <?php _e('MorphSVG Plugin', 'gsap-for-wordpress'); ?>
                                </label>
                                <p class="description"><?php _e('Morph any SVG shape into any other shape', 'gsap-for-wordpress'); ?></p>

                                <label>
                                    <input type="checkbox" name="load_drawsvg" value="1" <?php checked(isset($settings['load_drawsvg']) ? $settings['load_drawsvg'] : false); ?>>
                                    <?php _e('DrawSVG Plugin', 'gsap-for-wordpress'); ?>
                                </label>
                                <p class="description"><?php _e('Animate SVG strokes to create drawing effects', 'gsap-for-wordpress'); ?></p>

                                <label>
                                    <input type="checkbox" name="load_split_text" value="1" <?php checked(isset($settings['load_split_text']) ? $settings['load_split_text'] : false); ?>>
                                    <?php _e('SplitText Plugin', 'gsap-for-wordpress'); ?>
                                </label>
                                <p class="description"><?php _e('Split text into individual characters, words, or lines for animation', 'gsap-for-wordpress'); ?></p>
                            </fieldset>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Loading Options', 'gsap-for-wordpress'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="load_in_footer" value="1" <?php checked(isset($settings['load_in_footer']) ? $settings['load_in_footer'] : true); ?>>
                                    <?php _e('Load scripts in footer', 'gsap-for-wordpress'); ?>
                                </label>
                                <p class="description"><?php _e('Recommended for better page load performance', 'gsap-for-wordpress'); ?></p>

                                <label>
                                    <input type="checkbox" name="minified_version" value="1" <?php checked(isset($settings['minified_version']) ? $settings['minified_version'] : true); ?>>
                                    <?php _e('Use minified versions', 'gsap-for-wordpress'); ?>
                                </label>
                                <p class="description"><?php _e('Smaller file sizes for production sites (recommended)', 'gsap-for-wordpress'); ?></p>
                            </fieldset>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php submit_button(); ?>
        </form>

        <div class="gsap-wp-info">
            <h2><?php _e('Plugin Information', 'gsap-for-wordpress'); ?></h2>
            <p><?php _e('GSAP for WordPress version:', 'gsap-for-wordpress'); ?> <strong><?php echo GSAP_WP_VERSION; ?></strong></p>
            <p><?php _e('Developer:', 'gsap-for-wordpress'); ?> <a href="https://zahidaramai.com" target="_blank">Zahid Aramai</a></p>

            <h3><?php _e('Usage', 'gsap-for-wordpress'); ?></h3>
            <p><?php _e('After enabling the desired GSAP libraries, you can use them in your theme files or custom JavaScript:', 'gsap-for-wordpress'); ?></p>
            <pre><code>// Basic animation
gsap.to(".my-element", {duration: 2, x: 100, rotation: 360});

// ScrollTrigger animation (if enabled)
gsap.to(".scroll-element", {
    scrollTrigger: ".scroll-element",
    x: 100,
    duration: 3
});</code></pre>
        </div>
    </div>
</div>