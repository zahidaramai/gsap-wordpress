<?php
/**
 * Version Manager class for GSAP WordPress plugin
 *
 * @package GSAP_For_WordPress
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GSAP WordPress Version Manager class
 */
class GSAP_WP_Version_Manager {

    /**
     * Single instance of the class
     *
     * @var GSAP_WP_Version_Manager
     */
    private static $instance = null;

    /**
     * Database table name
     *
     * @var string
     */
    private $table_name = '';

    /**
     * Restore log table name
     *
     * @var string
     */
    private $restore_log_table = '';

    /**
     * Maximum versions to keep per file
     *
     * @var int
     */
    private $max_versions = 50;

    /**
     * Get single instance
     *
     * @return GSAP_WP_Version_Manager
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
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'gsap_versions';
        $this->restore_log_table = $wpdb->prefix . 'gsap_restore_log';
        $this->init_hooks();
        $this->create_tables();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('wp_ajax_gsap_wp_create_version', array($this, 'ajax_create_version'));
        add_action('wp_ajax_gsap_wp_load_version', array($this, 'ajax_load_version'));
        add_action('wp_ajax_gsap_wp_restore_version', array($this, 'ajax_restore_version'));
        add_action('wp_ajax_gsap_wp_delete_version', array($this, 'ajax_delete_version'));
        add_action('wp_ajax_gsap_wp_get_version_diff', array($this, 'ajax_get_version_diff'));
        add_action('wp_ajax_gsap_wp_get_restore_history', array($this, 'ajax_get_restore_history'));

        // Cleanup old versions daily
        add_action('gsap_wp_cleanup_versions', array($this, 'cleanup_old_versions'));
        if (!wp_next_scheduled('gsap_wp_cleanup_versions')) {
            wp_schedule_event(time(), 'daily', 'gsap_wp_cleanup_versions');
        }
    }

    /**
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $charset_collate = $wpdb->get_charset_collate();

        // Restore log table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->restore_log_table} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            version_id bigint(20) UNSIGNED NOT NULL,
            file_path varchar(255) NOT NULL,
            restored_by bigint(20) UNSIGNED NOT NULL,
            restored_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            restore_type varchar(20) NOT NULL DEFAULT 'manual',
            previous_version_id bigint(20) UNSIGNED NULL,
            notes text NULL,
            PRIMARY KEY  (id),
            KEY version_id (version_id),
            KEY file_path (file_path),
            KEY restored_by (restored_by),
            KEY restored_at (restored_at)
        ) $charset_collate;";

        dbDelta($sql);
    }

    /**
     * Create a new version
     *
     * @param string $file_path The file path
     * @param string $content The file content
     * @param string $comment Optional comment
     * @return int|WP_Error Version ID or error
     */
    public function create_version($file_path, $content, $comment = '') {
        global $wpdb;

        // Validate inputs
        if (empty($file_path) || empty($content)) {
            return new WP_Error('invalid_input', __('File path and content are required.', 'gsap-for-wordpress'));
        }

        // Get next version number
        $version_number = $this->get_next_version_number($file_path);

        // Insert version
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'file_path' => sanitize_text_field($file_path),
                'content' => $content,
                'version_number' => $version_number,
                'user_comment' => sanitize_textarea_field($comment),
                'created_by' => get_current_user_id()
            ),
            array('%s', '%s', '%d', '%s', '%d')
        );

        if ($result === false) {
            return new WP_Error('database_error', __('Failed to create version.', 'gsap-for-wordpress'));
        }

        $version_id = $wpdb->insert_id;

        // Cleanup old versions if we exceed the limit
        $this->cleanup_file_versions($file_path);

        // Log the action
        if (class_exists('GSAP_WP_Security')) {
            GSAP_WP_Security::get_instance()->log_security_event(
                'version_created',
                "Version {$version_number} created for {$file_path}",
                array('version_id' => $version_id, 'file' => $file_path)
            );
        }

        do_action('gsap_wp_version_created', $version_id, $file_path, $version_number);

        return $version_id;
    }

    /**
     * Get file versions
     *
     * @param string $file_path The file path
     * @param int $limit Number of versions to retrieve
     * @return array
     */
    public function get_file_versions($file_path, $limit = 20) {
        global $wpdb;

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT v.*, u.display_name as author_name
             FROM {$this->table_name} v
             LEFT JOIN {$wpdb->users} u ON v.created_by = u.ID
             WHERE v.file_path = %s
             ORDER BY v.version_number DESC
             LIMIT %d",
            $file_path,
            $limit
        ));

        return $results ? $results : array();
    }

    /**
     * Get version by ID
     *
     * @param int $version_id
     * @return object|null
     */
    public function get_version($version_id) {
        global $wpdb;

        return $wpdb->get_row($wpdb->prepare(
            "SELECT v.*, u.display_name as author_name
             FROM {$this->table_name} v
             LEFT JOIN {$wpdb->users} u ON v.created_by = u.ID
             WHERE v.id = %d",
            $version_id
        ));
    }

    /**
     * Restore version
     *
     * @param int $version_id
     * @param string $notes Optional notes for the restore
     * @return bool|WP_Error
     */
    public function restore_version($version_id, $notes = '') {
        $version = $this->get_version($version_id);

        if (!$version) {
            return new WP_Error('version_not_found', __('Version not found.', 'gsap-for-wordpress'));
        }

        // Use plugin assets directory
        $file_path = GSAP_WP_PLUGIN_PATH . 'assets/' . $version->file_path;

        // Get current version ID before creating backup
        $current_versions = $this->get_file_versions($version->file_path, 1);
        $previous_version_id = !empty($current_versions) ? $current_versions[0]->id : null;

        // Create backup of current version before restoring
        if (file_exists($file_path)) {
            $current_content = file_get_contents($file_path);
            $backup_id = $this->create_version($version->file_path, $current_content, __('Auto-backup before restore', 'gsap-for-wordpress'));

            // If we just created a backup, use that as the previous version
            if (!is_wp_error($backup_id)) {
                $previous_version_id = $backup_id;
            }
        }

        // Write restored content
        $result = file_put_contents($file_path, $version->content);

        if ($result === false) {
            return new WP_Error('restore_failed', __('Failed to restore version.', 'gsap-for-wordpress'));
        }

        // Log the restore operation to database
        $this->create_restore_log($version_id, $version->file_path, $previous_version_id, 'manual', $notes);

        // Log the action to security log
        if (class_exists('GSAP_WP_Security')) {
            GSAP_WP_Security::get_instance()->log_security_event(
                'version_restored',
                "Version {$version->version_number} restored for {$version->file_path}",
                array('version_id' => $version_id, 'file' => $version->file_path)
            );
        }

        do_action('gsap_wp_version_restored', $version_id, $version->file_path);

        return true;
    }

    /**
     * Delete version
     *
     * @param int $version_id
     * @return bool|WP_Error
     */
    public function delete_version($version_id) {
        global $wpdb;

        $version = $this->get_version($version_id);

        if (!$version) {
            return new WP_Error('version_not_found', __('Version not found.', 'gsap-for-wordpress'));
        }

        $result = $wpdb->delete(
            $this->table_name,
            array('id' => $version_id),
            array('%d')
        );

        if ($result === false) {
            return new WP_Error('delete_failed', __('Failed to delete version.', 'gsap-for-wordpress'));
        }

        // Log the action
        if (class_exists('GSAP_WP_Security')) {
            GSAP_WP_Security::get_instance()->log_security_event(
                'version_deleted',
                "Version {$version->version_number} deleted for {$version->file_path}",
                array('version_id' => $version_id, 'file' => $version->file_path)
            );
        }

        do_action('gsap_wp_version_deleted', $version_id, $version->file_path);

        return true;
    }

    /**
     * Get version diff
     *
     * @param int $version1_id
     * @param int $version2_id
     * @return array|WP_Error
     */
    public function get_version_diff($version1_id, $version2_id = null) {
        $version1 = $this->get_version($version1_id);

        if (!$version1) {
            return new WP_Error('version_not_found', __('Version not found.', 'gsap-for-wordpress'));
        }

        $content1 = $version1->content;
        $content2 = '';

        if ($version2_id) {
            $version2 = $this->get_version($version2_id);
            if (!$version2) {
                return new WP_Error('version_not_found', __('Second version not found.', 'gsap-for-wordpress'));
            }
            $content2 = $version2->content;
        } else {
            // Compare with current file using plugin assets directory
            $file_path = GSAP_WP_PLUGIN_PATH . 'assets/' . $version1->file_path;
            if (file_exists($file_path)) {
                $content2 = file_get_contents($file_path);
            }
        }

        return $this->generate_diff($content1, $content2);
    }

    /**
     * AJAX: Create version
     */
    public function ajax_create_version() {
        // Verify nonce and capabilities
        if (!wp_verify_nonce($_POST['nonce'], 'gsap_wp_ajax_nonce') || !current_user_can('edit_themes')) {
            wp_die(__('Security check failed.', 'gsap-for-wordpress'));
        }

        $file_path = sanitize_text_field($_POST['file_path']);
        $file_path = str_replace(array('..', '\\'), array('', '/'), $file_path);
        $comment = isset($_POST['comment']) ? sanitize_textarea_field($_POST['comment']) : '';

        // Get current file content using plugin assets directory
        $full_file_path = GSAP_WP_PLUGIN_PATH . 'assets/' . $file_path;

        if (!file_exists($full_file_path)) {
            wp_send_json_error(__('File not found.', 'gsap-for-wordpress'));
        }

        $content = file_get_contents($full_file_path);

        $result = $this->create_version($file_path, $content, $comment);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        $version = $this->get_version($result);

        wp_send_json_success(array(
            'message' => __('Version created successfully!', 'gsap-for-wordpress'),
            'version' => array(
                'id' => $version->id,
                'version_number' => $version->version_number,
                'comment' => $version->user_comment,
                'created_at' => $version->created_at,
                'author_name' => $version->author_name
            )
        ));
    }

    /**
     * AJAX: Load version
     */
    public function ajax_load_version() {
        // Verify nonce and capabilities
        if (!wp_verify_nonce($_POST['nonce'], 'gsap_wp_ajax_nonce') || !current_user_can('edit_themes')) {
            wp_die(__('Security check failed.', 'gsap-for-wordpress'));
        }

        $version_id = intval($_POST['version_id']);
        $version = $this->get_version($version_id);

        if (!$version) {
            wp_send_json_error(__('Version not found.', 'gsap-for-wordpress'));
        }

        wp_send_json_success(array(
            'content' => $version->content,
            'version' => array(
                'id' => $version->id,
                'version_number' => $version->version_number,
                'comment' => $version->user_comment,
                'created_at' => $version->created_at,
                'author_name' => $version->author_name
            )
        ));
    }

    /**
     * AJAX: Restore version
     */
    public function ajax_restore_version() {
        // Verify nonce and capabilities
        if (!wp_verify_nonce($_POST['nonce'], 'gsap_wp_ajax_nonce') || !current_user_can('edit_themes')) {
            wp_die(__('Security check failed.', 'gsap-for-wordpress'));
        }

        $version_id = intval($_POST['version_id']);
        $result = $this->restore_version($version_id);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success(array(
            'message' => __('Version restored successfully!', 'gsap-for-wordpress')
        ));
    }

    /**
     * AJAX: Delete version
     */
    public function ajax_delete_version() {
        // Verify nonce and capabilities
        if (!wp_verify_nonce($_POST['nonce'], 'gsap_wp_ajax_nonce') || !current_user_can('manage_options')) {
            wp_die(__('Security check failed.', 'gsap-for-wordpress'));
        }

        $version_id = intval($_POST['version_id']);
        $result = $this->delete_version($version_id);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success(array(
            'message' => __('Version deleted successfully!', 'gsap-for-wordpress')
        ));
    }

    /**
     * AJAX: Get version diff
     */
    public function ajax_get_version_diff() {
        // Verify nonce and capabilities
        if (!wp_verify_nonce($_POST['nonce'], 'gsap_wp_ajax_nonce') || !current_user_can('edit_themes')) {
            wp_die(__('Security check failed.', 'gsap-for-wordpress'));
        }

        $version1_id = intval($_POST['version1_id']);
        $version2_id = isset($_POST['version2_id']) ? intval($_POST['version2_id']) : null;

        $diff = $this->get_version_diff($version1_id, $version2_id);

        if (is_wp_error($diff)) {
            wp_send_json_error($diff->get_error_message());
        }

        wp_send_json_success(array(
            'diff' => $diff
        ));
    }

    /**
     * Get next version number
     *
     * @param string $file_path
     * @return int
     */
    private function get_next_version_number($file_path) {
        global $wpdb;

        $max_version = $wpdb->get_var($wpdb->prepare(
            "SELECT MAX(version_number) FROM {$this->table_name} WHERE file_path = %s",
            $file_path
        ));

        return $max_version ? $max_version + 1 : 1;
    }

    /**
     * Cleanup old versions for a specific file
     *
     * @param string $file_path
     */
    private function cleanup_file_versions($file_path) {
        global $wpdb;

        // Get version count
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE file_path = %s",
            $file_path
        ));

        if ($count > $this->max_versions) {
            $excess = $count - $this->max_versions;

            // Delete oldest versions
            $wpdb->query($wpdb->prepare(
                "DELETE FROM {$this->table_name}
                 WHERE file_path = %s
                 ORDER BY version_number ASC
                 LIMIT %d",
                $file_path,
                $excess
            ));
        }
    }

    /**
     * Cleanup old versions (scheduled task)
     */
    public function cleanup_old_versions() {
        global $wpdb;

        // Delete versions older than 90 days
        $wpdb->query(
            "DELETE FROM {$this->table_name}
             WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)"
        );

        // Optimize table
        $wpdb->query("OPTIMIZE TABLE {$this->table_name}");

        do_action('gsap_wp_versions_cleaned');
    }

    /**
     * Generate simple diff between two contents
     *
     * @param string $content1
     * @param string $content2
     * @return array
     */
    private function generate_diff($content1, $content2) {
        $lines1 = explode("\n", $content1);
        $lines2 = explode("\n", $content2);

        $diff = array();
        $max_lines = max(count($lines1), count($lines2));

        for ($i = 0; $i < $max_lines; $i++) {
            $line1 = isset($lines1[$i]) ? $lines1[$i] : '';
            $line2 = isset($lines2[$i]) ? $lines2[$i] : '';

            if ($line1 === $line2) {
                $diff[] = array(
                    'type' => 'equal',
                    'line1' => $line1,
                    'line2' => $line2,
                    'line_number' => $i + 1
                );
            } else {
                if (!empty($line1)) {
                    $diff[] = array(
                        'type' => 'delete',
                        'line' => $line1,
                        'line_number' => $i + 1
                    );
                }
                if (!empty($line2)) {
                    $diff[] = array(
                        'type' => 'insert',
                        'line' => $line2,
                        'line_number' => $i + 1
                    );
                }
            }
        }

        return $diff;
    }

    /**
     * Get version statistics
     *
     * @return array
     */
    public function get_version_stats() {
        global $wpdb;

        $stats = array();

        // Total versions
        $stats['total_versions'] = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");

        // Versions by file
        $stats['versions_by_file'] = $wpdb->get_results(
            "SELECT file_path, COUNT(*) as count
             FROM {$this->table_name}
             GROUP BY file_path
             ORDER BY count DESC"
        );

        // Recent activity
        $stats['recent_activity'] = $wpdb->get_results(
            "SELECT file_path, version_number, created_at, user_comment
             FROM {$this->table_name}
             ORDER BY created_at DESC
             LIMIT 10"
        );

        return $stats;
    }

    /**
     * Export versions
     *
     * @param string $file_path
     * @return array
     */
    public function export_versions($file_path) {
        $versions = $this->get_file_versions($file_path, 100);

        $export_data = array(
            'file_path' => $file_path,
            'export_date' => current_time('mysql'),
            'plugin_version' => GSAP_WP_VERSION,
            'versions' => array()
        );

        foreach ($versions as $version) {
            $export_data['versions'][] = array(
                'version_number' => $version->version_number,
                'content' => $version->content,
                'user_comment' => $version->user_comment,
                'created_at' => $version->created_at
            );
        }

        return $export_data;
    }

    /**
     * Import versions
     *
     * @param array $import_data
     * @return bool|WP_Error
     */
    public function import_versions($import_data) {
        if (!isset($import_data['file_path']) || !isset($import_data['versions'])) {
            return new WP_Error('invalid_data', __('Invalid import data.', 'gsap-for-wordpress'));
        }

        $imported_count = 0;

        foreach ($import_data['versions'] as $version_data) {
            $result = $this->create_version(
                $import_data['file_path'],
                $version_data['content'],
                $version_data['user_comment'] . ' (imported)'
            );

            if (!is_wp_error($result)) {
                $imported_count++;
            }
        }

        return $imported_count;
    }

    /**
     * Create restore log entry
     *
     * @param int $version_id The version that was restored
     * @param string $file_path The file path
     * @param int|null $previous_version_id The version that was active before restore
     * @param string $restore_type Type of restore (manual/auto)
     * @param string $notes Optional notes
     * @return int|WP_Error Log ID or error
     */
    public function create_restore_log($version_id, $file_path, $previous_version_id = null, $restore_type = 'manual', $notes = '') {
        global $wpdb;

        $result = $wpdb->insert(
            $this->restore_log_table,
            array(
                'version_id' => $version_id,
                'file_path' => sanitize_text_field($file_path),
                'restored_by' => get_current_user_id(),
                'restore_type' => sanitize_text_field($restore_type),
                'previous_version_id' => $previous_version_id,
                'notes' => sanitize_textarea_field($notes)
            ),
            array('%d', '%s', '%d', '%s', '%d', '%s')
        );

        if ($result === false) {
            return new WP_Error('log_failed', __('Failed to create restore log.', 'gsap-for-wordpress'));
        }

        return $wpdb->insert_id;
    }

    /**
     * Get restore history for a file
     *
     * @param string $file_path The file path
     * @param int $limit Number of records to retrieve
     * @return array
     */
    public function get_restore_history($file_path = '', $limit = 50) {
        global $wpdb;

        $where = '';
        $params = array();

        if (!empty($file_path)) {
            $where = 'WHERE rl.file_path = %s';
            $params[] = $file_path;
        }

        $params[] = $limit;

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT rl.*,
                    u.display_name as restored_by_name,
                    v.version_number as restored_version_number,
                    v.user_comment as restored_version_comment,
                    pv.version_number as previous_version_number
             FROM {$this->restore_log_table} rl
             LEFT JOIN {$wpdb->users} u ON rl.restored_by = u.ID
             LEFT JOIN {$this->table_name} v ON rl.version_id = v.id
             LEFT JOIN {$this->table_name} pv ON rl.previous_version_id = pv.id
             {$where}
             ORDER BY rl.restored_at DESC
             LIMIT %d",
            ...$params
        ));

        return $results ? $results : array();
    }

    /**
     * AJAX: Get restore history
     */
    public function ajax_get_restore_history() {
        // Verify nonce and capabilities
        if (!wp_verify_nonce($_POST['nonce'], 'gsap_wp_ajax_nonce') || !current_user_can('edit_themes')) {
            wp_die(__('Security check failed.', 'gsap-for-wordpress'));
        }

        $file_path = isset($_POST['file_path']) ? sanitize_text_field($_POST['file_path']) : '';
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 50;

        $history = $this->get_restore_history($file_path, $limit);

        wp_send_json_success(array(
            'history' => $history,
            'count' => count($history)
        ));
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