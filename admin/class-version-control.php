<?php
/**
 * Version Control UI class for GSAP WordPress plugin
 *
 * @package GSAP_For_WordPress
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GSAP WordPress Version Control class
 * Handles the admin UI for version control functionality
 */
class GSAP_WP_Version_Control {

    /**
     * Single instance of the class
     *
     * @var GSAP_WP_Version_Control
     */
    private static $instance = null;

    /**
     * Version manager instance
     *
     * @var GSAP_WP_Version_Manager
     */
    private $version_manager = null;

    /**
     * Get single instance
     *
     * @return GSAP_WP_Version_Control
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
        if (class_exists('GSAP_WP_Version_Manager')) {
            $this->version_manager = GSAP_WP_Version_Manager::get_instance();
        }
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('wp_ajax_gsap_wp_create_version', array($this, 'ajax_create_version'));
        add_action('wp_ajax_gsap_wp_load_version', array($this, 'ajax_load_version'));
        add_action('wp_ajax_gsap_wp_restore_version', array($this, 'ajax_restore_version'));
        add_action('wp_ajax_gsap_wp_delete_version', array($this, 'ajax_delete_version'));
        add_action('wp_ajax_gsap_wp_compare_versions', array($this, 'ajax_compare_versions'));
        add_action('wp_ajax_gsap_wp_export_versions', array($this, 'ajax_export_versions'));
        add_action('wp_ajax_gsap_wp_get_restore_history', array($this, 'ajax_get_restore_history'));
    }

    /**
     * Render version control panel
     *
     * @param string $file_path
     */
    public function render_version_panel($file_path) {
        if (!$this->version_manager) {
            $this->render_error_message(__('Version manager not available.', 'gsap-for-wordpress'));
            return;
        }

        $versions = $this->version_manager->get_file_versions($file_path, 20);
        ?>
        <div class="gsap-wp-version-control-panel">
            <div class="gsap-wp-version-header">
                <h3>
                    <span class="dashicons dashicons-backup"></span>
                    <?php _e('Version History', 'gsap-for-wordpress'); ?>
                </h3>
                <button type="button" class="button button-primary gsap-wp-create-version-btn" data-file="<?php echo esc_attr($file_path); ?>">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <?php _e('Create Version', 'gsap-for-wordpress'); ?>
                </button>
            </div>

            <div class="gsap-wp-version-list-container">
                <?php if (empty($versions)): ?>
                    <div class="gsap-wp-no-versions">
                        <span class="dashicons dashicons-info"></span>
                        <p><?php _e('No versions saved yet. Create your first version to start tracking changes.', 'gsap-for-wordpress'); ?></p>
                    </div>
                <?php else: ?>
                    <ul class="gsap-wp-version-list">
                        <?php foreach ($versions as $version): ?>
                            <li class="gsap-wp-version-item" data-version-id="<?php echo esc_attr($version->id); ?>">
                                <div class="gsap-wp-version-info">
                                    <div class="gsap-wp-version-number">
                                        <strong><?php printf(__('Version %d', 'gsap-for-wordpress'), $version->version_number); ?></strong>
                                    </div>
                                    <div class="gsap-wp-version-meta">
                                        <span class="gsap-wp-version-date">
                                            <?php echo esc_html(mysql2date('M j, Y g:i A', $version->created_at)); ?>
                                        </span>
                                        <?php if (!empty($version->author_name)): ?>
                                            <span class="gsap-wp-version-author">
                                                <?php printf(__('by %s', 'gsap-for-wordpress'), esc_html($version->author_name)); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($version->user_comment)): ?>
                                        <div class="gsap-wp-version-comment">
                                            <?php echo esc_html($version->user_comment); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="gsap-wp-version-actions">
                                    <button type="button" class="button button-small gsap-wp-view-version"
                                            data-version-id="<?php echo esc_attr($version->id); ?>"
                                            title="<?php esc_attr_e('View this version', 'gsap-for-wordpress'); ?>">
                                        <span class="dashicons dashicons-visibility"></span>
                                        <?php _e('View', 'gsap-for-wordpress'); ?>
                                    </button>
                                    <button type="button" class="button button-small gsap-wp-restore-version"
                                            data-version-id="<?php echo esc_attr($version->id); ?>"
                                            title="<?php esc_attr_e('Restore this version', 'gsap-for-wordpress'); ?>">
                                        <span class="dashicons dashicons-undo"></span>
                                        <?php _e('Restore', 'gsap-for-wordpress'); ?>
                                    </button>
                                    <button type="button" class="button button-small button-link-delete gsap-wp-delete-version"
                                            data-version-id="<?php echo esc_attr($version->id); ?>"
                                            title="<?php esc_attr_e('Delete this version', 'gsap-for-wordpress'); ?>">
                                        <span class="dashicons dashicons-trash"></span>
                                        <?php _e('Delete', 'gsap-for-wordpress'); ?>
                                    </button>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <?php if (count($versions) >= 20): ?>
                        <div class="gsap-wp-version-pagination">
                            <button type="button" class="button gsap-wp-load-more-versions">
                                <?php _e('Load More Versions', 'gsap-for-wordpress'); ?>
                            </button>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="gsap-wp-version-stats">
                <?php $this->render_version_stats($file_path); ?>
            </div>

            <div class="gsap-wp-restore-history-section">
                <?php $this->render_restore_history($file_path); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render version statistics
     *
     * @param string $file_path
     */
    private function render_version_stats($file_path) {
        if (!$this->version_manager) {
            return;
        }

        $versions = $this->version_manager->get_file_versions($file_path, 100);
        $total_versions = count($versions);
        ?>
        <div class="gsap-wp-stats-container">
            <div class="gsap-wp-stat-item">
                <span class="dashicons dashicons-admin-generic"></span>
                <div class="gsap-wp-stat-content">
                    <strong><?php echo esc_html($total_versions); ?></strong>
                    <span><?php _e('Total Versions', 'gsap-for-wordpress'); ?></span>
                </div>
            </div>
            <div class="gsap-wp-stat-item">
                <span class="dashicons dashicons-clock"></span>
                <div class="gsap-wp-stat-content">
                    <?php if (!empty($versions)): ?>
                        <strong><?php echo esc_html(human_time_diff(strtotime($versions[0]->created_at))); ?></strong>
                        <span><?php _e('ago', 'gsap-for-wordpress'); ?></span>
                    <?php else: ?>
                        <strong>-</strong>
                        <span><?php _e('Last Modified', 'gsap-for-wordpress'); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * AJAX: Create version
     */
    public function ajax_create_version() {
        // Delegate to version manager
        if ($this->version_manager) {
            $this->version_manager->ajax_create_version();
        } else {
            wp_send_json_error(__('Version manager not available.', 'gsap-for-wordpress'));
        }
    }

    /**
     * AJAX: Load version
     */
    public function ajax_load_version() {
        // Delegate to version manager
        if ($this->version_manager) {
            $this->version_manager->ajax_load_version();
        } else {
            wp_send_json_error(__('Version manager not available.', 'gsap-for-wordpress'));
        }
    }

    /**
     * AJAX: Restore version
     */
    public function ajax_restore_version() {
        // Delegate to version manager
        if ($this->version_manager) {
            $this->version_manager->ajax_restore_version();
        } else {
            wp_send_json_error(__('Version manager not available.', 'gsap-for-wordpress'));
        }
    }

    /**
     * AJAX: Delete version
     */
    public function ajax_delete_version() {
        // Delegate to version manager
        if ($this->version_manager) {
            $this->version_manager->ajax_delete_version();
        } else {
            wp_send_json_error(__('Version manager not available.', 'gsap-for-wordpress'));
        }
    }

    /**
     * AJAX: Compare versions
     */
    public function ajax_compare_versions() {
        // Verify nonce and capabilities
        if (!wp_verify_nonce($_POST['nonce'], 'gsap_wp_ajax_nonce') || !current_user_can('edit_themes')) {
            wp_die(__('Security check failed.', 'gsap-for-wordpress'));
        }

        if (!$this->version_manager) {
            wp_send_json_error(__('Version manager not available.', 'gsap-for-wordpress'));
        }

        $version1_id = intval($_POST['version1_id']);
        $version2_id = isset($_POST['version2_id']) ? intval($_POST['version2_id']) : null;

        $diff = $this->version_manager->get_version_diff($version1_id, $version2_id);

        if (is_wp_error($diff)) {
            wp_send_json_error($diff->get_error_message());
        }

        wp_send_json_success(array(
            'diff' => $diff
        ));
    }

    /**
     * AJAX: Export versions
     */
    public function ajax_export_versions() {
        // Verify nonce and capabilities
        if (!wp_verify_nonce($_POST['nonce'], 'gsap_wp_ajax_nonce') || !current_user_can('manage_options')) {
            wp_die(__('Security check failed.', 'gsap-for-wordpress'));
        }

        if (!$this->version_manager) {
            wp_send_json_error(__('Version manager not available.', 'gsap-for-wordpress'));
        }

        $file_path = sanitize_file_name($_POST['file_path']);
        $export_data = $this->version_manager->export_versions($file_path);

        wp_send_json_success(array(
            'export_data' => $export_data,
            'filename' => sanitize_file_name($file_path) . '-versions-' . date('Y-m-d') . '.json'
        ));
    }

    /**
     * AJAX: Get restore history
     */
    public function ajax_get_restore_history() {
        // Delegate to version manager
        if ($this->version_manager) {
            $this->version_manager->ajax_get_restore_history();
        } else {
            wp_send_json_error(__('Version manager not available.', 'gsap-for-wordpress'));
        }
    }

    /**
     * Render error message
     *
     * @param string $message
     */
    private function render_error_message($message) {
        ?>
        <div class="notice notice-error">
            <p><?php echo esc_html($message); ?></p>
        </div>
        <?php
    }

    /**
     * Render version comparison view
     *
     * @param int $version1_id
     * @param int $version2_id
     */
    public function render_version_comparison($version1_id, $version2_id = null) {
        if (!$this->version_manager) {
            $this->render_error_message(__('Version manager not available.', 'gsap-for-wordpress'));
            return;
        }

        $diff = $this->version_manager->get_version_diff($version1_id, $version2_id);

        if (is_wp_error($diff)) {
            $this->render_error_message($diff->get_error_message());
            return;
        }

        ?>
        <div class="gsap-wp-version-comparison">
            <h3><?php _e('Version Comparison', 'gsap-for-wordpress'); ?></h3>
            <div class="gsap-wp-diff-viewer">
                <?php foreach ($diff as $line): ?>
                    <div class="gsap-wp-diff-line gsap-wp-diff-<?php echo esc_attr($line['type']); ?>">
                        <?php if ($line['type'] === 'equal'): ?>
                            <span class="gsap-wp-line-number"><?php echo esc_html($line['line_number']); ?></span>
                            <span class="gsap-wp-line-content"><?php echo esc_html($line['line1']); ?></span>
                        <?php elseif ($line['type'] === 'delete'): ?>
                            <span class="gsap-wp-line-number"><?php echo esc_html($line['line_number']); ?></span>
                            <span class="gsap-wp-line-marker">-</span>
                            <span class="gsap-wp-line-content"><?php echo esc_html($line['line']); ?></span>
                        <?php elseif ($line['type'] === 'insert'): ?>
                            <span class="gsap-wp-line-number"><?php echo esc_html($line['line_number']); ?></span>
                            <span class="gsap-wp-line-marker">+</span>
                            <span class="gsap-wp-line-content"><?php echo esc_html($line['line']); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render version selector dropdown
     *
     * @param string $file_path
     * @param string $name
     */
    public function render_version_selector($file_path, $name = 'version_id') {
        if (!$this->version_manager) {
            return;
        }

        $versions = $this->version_manager->get_file_versions($file_path, 50);
        ?>
        <select name="<?php echo esc_attr($name); ?>" class="gsap-wp-version-selector">
            <option value=""><?php _e('Select a version...', 'gsap-for-wordpress'); ?></option>
            <?php foreach ($versions as $version): ?>
                <option value="<?php echo esc_attr($version->id); ?>">
                    <?php
                    printf(
                        __('Version %d - %s', 'gsap-for-wordpress'),
                        $version->version_number,
                        mysql2date('M j, Y g:i A', $version->created_at)
                    );
                    if (!empty($version->user_comment)) {
                        echo ' - ' . esc_html($version->user_comment);
                    }
                    ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * Render restore history section
     *
     * @param string $file_path
     */
    private function render_restore_history($file_path) {
        if (!$this->version_manager) {
            return;
        }

        $restore_history = $this->version_manager->get_restore_history($file_path, 20);
        ?>
        <div class="gsap-wp-restore-history-container">
            <h3>
                <span class="dashicons dashicons-update"></span>
                <?php _e('Restore History', 'gsap-for-wordpress'); ?>
            </h3>

            <?php if (empty($restore_history)): ?>
                <div class="gsap-wp-no-restore-history">
                    <span class="dashicons dashicons-info"></span>
                    <p><?php _e('No restore operations yet. When you restore a version, it will be logged here.', 'gsap-for-wordpress'); ?></p>
                </div>
            <?php else: ?>
                <div class="gsap-wp-restore-timeline">
                    <?php foreach ($restore_history as $index => $restore): ?>
                        <div class="gsap-wp-restore-item" data-restore-id="<?php echo esc_attr($restore->id); ?>">
                            <div class="gsap-wp-restore-marker"></div>
                            <div class="gsap-wp-restore-content">
                                <div class="gsap-wp-restore-header">
                                    <strong class="gsap-wp-restore-action">
                                        <?php
                                        if (!empty($restore->previous_version_number)) {
                                            printf(
                                                __('Restored v%1$d → v%2$d', 'gsap-for-wordpress'),
                                                $restore->previous_version_number,
                                                $restore->restored_version_number
                                            );
                                        } else {
                                            printf(
                                                __('Restored v%d', 'gsap-for-wordpress'),
                                                $restore->restored_version_number
                                            );
                                        }
                                        ?>
                                    </strong>
                                    <span class="gsap-wp-restore-badge gsap-wp-badge <?php echo esc_attr($restore->restore_type); ?>">
                                        <?php echo esc_html(ucfirst($restore->restore_type)); ?>
                                    </span>
                                </div>
                                <div class="gsap-wp-restore-meta">
                                    <span class="gsap-wp-restore-date">
                                        <?php echo esc_html(human_time_diff(strtotime($restore->restored_at))); ?> <?php _e('ago', 'gsap-for-wordpress'); ?>
                                    </span>
                                    <span class="gsap-wp-restore-separator">•</span>
                                    <span class="gsap-wp-restore-author">
                                        <?php printf(__('by %s', 'gsap-for-wordpress'), esc_html($restore->restored_by_name)); ?>
                                    </span>
                                </div>
                                <?php if (!empty($restore->notes)): ?>
                                    <div class="gsap-wp-restore-notes">
                                        <span class="dashicons dashicons-format-quote"></span>
                                        <?php echo esc_html($restore->notes); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($restore->restored_version_comment)): ?>
                                    <div class="gsap-wp-restore-version-comment">
                                        <?php echo esc_html($restore->restored_version_comment); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (count($restore_history) >= 20): ?>
                    <div class="gsap-wp-restore-pagination">
                        <button type="button" class="button gsap-wp-load-more-restores" data-file="<?php echo esc_attr($file_path); ?>">
                            <?php _e('Load More', 'gsap-for-wordpress'); ?>
                        </button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    private function __wakeup() {}
}
