<?php
/**
 * Uninstall script for GSAP for WordPress plugin
 *
 * @package GSAP_For_WordPress
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * GSAP WordPress Plugin Uninstaller
 */
class GSAP_WP_Uninstaller {

    /**
     * Run uninstall process
     */
    public static function uninstall() {
        // Check if user has permission to uninstall plugins
        if (!current_user_can('delete_plugins')) {
            return;
        }

        // Get user preferences for what to remove
        $remove_settings = get_option('gsap_wp_remove_settings_on_uninstall', true);
        $remove_files = get_option('gsap_wp_remove_files_on_uninstall', false);
        $remove_versions = get_option('gsap_wp_remove_versions_on_uninstall', false);

        // Remove database tables
        self::remove_database_tables();

        // Remove plugin options
        if ($remove_settings) {
            self::remove_plugin_options();
        }

        // Remove custom files
        if ($remove_files) {
            self::remove_custom_files();
        }

        // Remove version history
        if ($remove_versions) {
            self::remove_version_history();
        }

        // Remove scheduled events
        self::remove_scheduled_events();

        // Remove capabilities (if any were added)
        self::remove_capabilities();

        // Remove transients
        self::remove_transients();

        // Clean up user meta
        self::cleanup_user_meta();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Log uninstallation
        self::log_uninstallation();

        // Trigger action for extensions
        do_action('gsap_wp_uninstalled');
    }

    /**
     * Remove database tables
     */
    private static function remove_database_tables() {
        global $wpdb;

        $tables = array(
            $wpdb->prefix . 'gsap_versions'
        );

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$table}");
        }
    }

    /**
     * Remove plugin options
     */
    private static function remove_plugin_options() {
        $options = array(
            'gsap_wp_settings',
            'gsap_wp_custom_js',
            'gsap_wp_custom_css',
            'gsap_wp_version',
            'gsap_wp_activated',
            'gsap_wp_backup_metadata',
            'gsap_wp_remove_settings_on_uninstall',
            'gsap_wp_remove_files_on_uninstall',
            'gsap_wp_remove_versions_on_uninstall',
            'gsap_wp_first_install',
            'gsap_wp_install_date',
            'gsap_wp_usage_stats',
            'gsap_wp_last_cleanup',
            'gsap_wp_error_log'
        );

        foreach ($options as $option) {
            delete_option($option);
        }

        // Remove network options for multisite
        if (is_multisite()) {
            foreach ($options as $option) {
                delete_site_option($option);
            }
        }
    }

    /**
     * Remove custom files
     */
    private static function remove_custom_files() {
        $upload_dir = wp_upload_dir();
        $gsap_dir = $upload_dir['basedir'] . '/gsap-wordpress/';

        if (is_dir($gsap_dir)) {
            self::remove_directory_recursive($gsap_dir);
        }
    }

    /**
     * Remove version history (already handled by database table removal)
     */
    private static function remove_version_history() {
        // Version history is stored in database table which is already removed
        // This method is kept for potential future file-based version storage
    }

    /**
     * Remove scheduled events
     */
    private static function remove_scheduled_events() {
        $scheduled_events = array(
            'gsap_wp_cleanup_versions',
            'gsap_wp_cleanup_backups',
            'gsap_wp_cleanup_logs',
            'gsap_wp_update_stats'
        );

        foreach ($scheduled_events as $event) {
            wp_clear_scheduled_hook($event);
        }
    }

    /**
     * Remove capabilities
     */
    private static function remove_capabilities() {
        // Remove any custom capabilities that might have been added
        $capabilities = array(
            'gsap_wp_manage_settings',
            'gsap_wp_edit_files',
            'gsap_wp_manage_versions'
        );

        $roles = wp_roles();

        foreach ($roles->roles as $role_name => $role) {
            $wp_role = get_role($role_name);
            if ($wp_role) {
                foreach ($capabilities as $capability) {
                    $wp_role->remove_cap($capability);
                }
            }
        }
    }

    /**
     * Remove transients
     */
    private static function remove_transients() {
        global $wpdb;

        // Remove plugin-specific transients
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
                '_transient_gsap_wp_%',
                '_transient_timeout_gsap_wp_%'
            )
        );

        // Remove site transients for multisite
        if (is_multisite()) {
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM {$wpdb->sitemeta} WHERE meta_key LIKE %s OR meta_key LIKE %s",
                    '_site_transient_gsap_wp_%',
                    '_site_transient_timeout_gsap_wp_%'
                )
            );
        }
    }

    /**
     * Clean up user meta
     */
    private static function cleanup_user_meta() {
        global $wpdb;

        // Remove user meta related to the plugin
        $meta_keys = array(
            'gsap_wp_editor_preferences',
            'gsap_wp_dismissed_notices',
            'gsap_wp_tour_completed',
            'gsap_wp_last_visited_tab'
        );

        foreach ($meta_keys as $meta_key) {
            $wpdb->delete(
                $wpdb->usermeta,
                array('meta_key' => $meta_key),
                array('%s')
            );
        }
    }

    /**
     * Recursively remove directory and all contents
     *
     * @param string $dir Directory path
     */
    private static function remove_directory_recursive($dir) {
        if (!is_dir($dir)) {
            return;
        }

        $iterator = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }

        rmdir($dir);
    }

    /**
     * Log uninstallation for debugging
     */
    private static function log_uninstallation() {
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'user_id' => get_current_user_id(),
            'site_url' => site_url(),
            'plugin_version' => get_option('gsap_wp_version', 'unknown'),
            'wp_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION
        );

        // Log to WordPress debug log if enabled
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log('[GSAP-WP Uninstall] ' . json_encode($log_entry));
        }

        // Store in a temporary option that will be cleaned up later
        update_option('gsap_wp_uninstall_log', $log_entry, false);

        // Send uninstall event (if analytics are enabled and user consented)
        $usage_stats = get_option('gsap_wp_usage_stats', array());
        if (isset($usage_stats['analytics_enabled']) && $usage_stats['analytics_enabled']) {
            self::send_uninstall_analytics($log_entry);
        }
    }

    /**
     * Send anonymous uninstall analytics
     *
     * @param array $log_entry
     */
    private static function send_uninstall_analytics($log_entry) {
        // Only send if user opted in to analytics
        $analytics_url = 'https://api.zahidaramai.com/gsap-wp/uninstall';

        $data = array(
            'plugin_version' => $log_entry['plugin_version'],
            'wp_version' => $log_entry['wp_version'],
            'php_version' => $log_entry['php_version'],
            'site_hash' => wp_hash($log_entry['site_url']), // Anonymous site identifier
            'uninstall_date' => $log_entry['timestamp']
        );

        // Send asynchronously
        wp_remote_post($analytics_url, array(
            'body' => $data,
            'timeout' => 5,
            'blocking' => false,
            'headers' => array(
                'User-Agent' => 'GSAP-WP/' . $log_entry['plugin_version']
            )
        ));
    }

    /**
     * Get confirmation from user about what to remove
     */
    public static function show_uninstall_options() {
        // This would be called from a admin page before uninstall
        // For now, we use default options
        add_option('gsap_wp_remove_settings_on_uninstall', true);
        add_option('gsap_wp_remove_files_on_uninstall', false);
        add_option('gsap_wp_remove_versions_on_uninstall', false);
    }

    /**
     * Clean up orphaned data (can be called independently)
     */
    public static function cleanup_orphaned_data() {
        global $wpdb;

        // Clean up orphaned user meta
        $wpdb->query("
            DELETE um FROM {$wpdb->usermeta} um
            LEFT JOIN {$wpdb->users} u ON um.user_id = u.ID
            WHERE u.ID IS NULL AND um.meta_key LIKE 'gsap_wp_%'
        ");

        // Clean up orphaned postmeta
        $wpdb->query("
            DELETE pm FROM {$wpdb->postmeta} pm
            LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE p.ID IS NULL AND pm.meta_key LIKE 'gsap_wp_%'
        ");

        // Clean up orphaned term meta
        $wpdb->query("
            DELETE tm FROM {$wpdb->termmeta} tm
            LEFT JOIN {$wpdb->terms} t ON tm.term_id = t.term_id
            WHERE t.term_id IS NULL AND tm.meta_key LIKE 'gsap_wp_%'
        ");
    }
}

// Run the uninstaller
GSAP_WP_Uninstaller::uninstall();