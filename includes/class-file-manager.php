<?php
/**
 * File Manager class for GSAP WordPress plugin
 *
 * @package GSAP_For_WordPress
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GSAP WordPress File Manager class
 */
class GSAP_WP_File_Manager {

    /**
     * Single instance of the class
     *
     * @var GSAP_WP_File_Manager
     */
    private static $instance = null;

    /**
     * Upload directory path
     *
     * @var string
     */
    private $upload_dir = '';

    /**
     * Upload directory URL
     *
     * @var string
     */
    private $upload_url = '';

    /**
     * Maximum file size (in bytes)
     *
     * @var int
     */
    private $max_file_size = 1048576; // 1MB

    /**
     * Allowed file extensions
     *
     * @var array
     */
    private $allowed_extensions = array('js', 'css');

    /**
     * Get single instance
     *
     * @return GSAP_WP_File_Manager
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
        $this->init_directories();
        $this->init_hooks();
    }

    /**
     * Initialize upload directories
     */
    private function init_directories() {
        $upload_dir = wp_upload_dir();
        $this->upload_dir = $upload_dir['basedir'] . '/gsap-wordpress/';
        $this->upload_url = $upload_dir['baseurl'] . '/gsap-wordpress/';

        // Create directory if it doesn't exist
        if (!file_exists($this->upload_dir)) {
            wp_mkdir_p($this->upload_dir);
            $this->create_htaccess_file();
            $this->create_index_file();
        }
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('wp_ajax_gsap_wp_backup_files', array($this, 'ajax_backup_files'));
        add_action('wp_ajax_gsap_wp_restore_backup', array($this, 'ajax_restore_backup'));
        add_action('wp_ajax_gsap_wp_export_files', array($this, 'ajax_export_files'));
        add_action('wp_ajax_gsap_wp_import_files', array($this, 'ajax_import_files'));

        // Cleanup old backups weekly
        add_action('gsap_wp_cleanup_backups', array($this, 'cleanup_old_backups'));
        if (!wp_next_scheduled('gsap_wp_cleanup_backups')) {
            wp_schedule_event(time(), 'weekly', 'gsap_wp_cleanup_backups');
        }
    }

    /**
     * Write file safely
     *
     * @param string $file_name
     * @param string $content
     * @param bool $create_backup
     * @return bool|WP_Error
     */
    public function write_file($file_name, $content, $create_backup = true) {
        // Validate file name
        $validation = $this->validate_file_name($file_name);
        if (is_wp_error($validation)) {
            return $validation;
        }

        // Validate content
        $content_validation = $this->validate_content($content, $file_name);
        if (is_wp_error($content_validation)) {
            return $content_validation;
        }

        $file_path = $this->upload_dir . $file_name;

        // Create backup if file exists and backup is requested
        if ($create_backup && file_exists($file_path)) {
            $backup_result = $this->create_backup($file_name);
            if (is_wp_error($backup_result)) {
                return $backup_result;
            }
        }

        // Use WordPress filesystem
        $filesystem = $this->get_filesystem();
        if (is_wp_error($filesystem)) {
            return $filesystem;
        }

        // Write file atomically
        $temp_file = $file_path . '.tmp';
        $result = $filesystem->put_contents($temp_file, $content, FS_CHMOD_FILE);

        if (!$result) {
            return new WP_Error('write_failed', __('Failed to write file.', 'gsap-for-wordpress'));
        }

        // Atomic move
        if (!rename($temp_file, $file_path)) {
            $filesystem->delete($temp_file);
            return new WP_Error('move_failed', __('Failed to finalize file write.', 'gsap-for-wordpress'));
        }

        // Log the action
        if (class_exists('GSAP_WP_Security')) {
            GSAP_WP_Security::get_instance()->log_security_event(
                'file_written',
                "File {$file_name} was written",
                array('file' => $file_name, 'size' => strlen($content))
            );
        }

        do_action('gsap_wp_file_written', $file_name, $content);

        return true;
    }

    /**
     * Read file safely
     *
     * @param string $file_name
     * @return string|WP_Error
     */
    public function read_file($file_name) {
        // Validate file name
        $validation = $this->validate_file_name($file_name);
        if (is_wp_error($validation)) {
            return $validation;
        }

        $file_path = $this->upload_dir . $file_name;

        if (!file_exists($file_path)) {
            return new WP_Error('file_not_found', __('File not found.', 'gsap-for-wordpress'));
        }

        // Use WordPress filesystem
        $filesystem = $this->get_filesystem();
        if (is_wp_error($filesystem)) {
            return $filesystem;
        }

        $content = $filesystem->get_contents($file_path);

        if ($content === false) {
            return new WP_Error('read_failed', __('Failed to read file.', 'gsap-for-wordpress'));
        }

        return $content;
    }

    /**
     * Delete file safely
     *
     * @param string $file_name
     * @param bool $create_backup
     * @return bool|WP_Error
     */
    public function delete_file($file_name, $create_backup = true) {
        // Validate file name
        $validation = $this->validate_file_name($file_name);
        if (is_wp_error($validation)) {
            return $validation;
        }

        $file_path = $this->upload_dir . $file_name;

        if (!file_exists($file_path)) {
            return new WP_Error('file_not_found', __('File not found.', 'gsap-for-wordpress'));
        }

        // Create backup before deletion
        if ($create_backup) {
            $backup_result = $this->create_backup($file_name);
            if (is_wp_error($backup_result)) {
                return $backup_result;
            }
        }

        // Use WordPress filesystem
        $filesystem = $this->get_filesystem();
        if (is_wp_error($filesystem)) {
            return $filesystem;
        }

        $result = $filesystem->delete($file_path);

        if (!$result) {
            return new WP_Error('delete_failed', __('Failed to delete file.', 'gsap-for-wordpress'));
        }

        // Log the action
        if (class_exists('GSAP_WP_Security')) {
            GSAP_WP_Security::get_instance()->log_security_event(
                'file_deleted',
                "File {$file_name} was deleted",
                array('file' => $file_name)
            );
        }

        do_action('gsap_wp_file_deleted', $file_name);

        return true;
    }

    /**
     * Create backup of file
     *
     * @param string $file_name
     * @return bool|WP_Error
     */
    public function create_backup($file_name) {
        $file_path = $this->upload_dir . $file_name;

        if (!file_exists($file_path)) {
            return new WP_Error('file_not_found', __('File not found.', 'gsap-for-wordpress'));
        }

        $backup_dir = $this->upload_dir . 'backups/';
        if (!file_exists($backup_dir)) {
            wp_mkdir_p($backup_dir);
        }

        $timestamp = date('Y-m-d_H-i-s');
        $backup_name = pathinfo($file_name, PATHINFO_FILENAME) . '_' . $timestamp . '.' . pathinfo($file_name, PATHINFO_EXTENSION);
        $backup_path = $backup_dir . $backup_name;

        $filesystem = $this->get_filesystem();
        if (is_wp_error($filesystem)) {
            return $filesystem;
        }

        $result = $filesystem->copy($file_path, $backup_path);

        if (!$result) {
            return new WP_Error('backup_failed', __('Failed to create backup.', 'gsap-for-wordpress'));
        }

        // Store backup metadata
        $this->store_backup_metadata($backup_name, $file_name);

        return $backup_name;
    }

    /**
     * Get file list
     *
     * @return array
     */
    public function get_file_list() {
        $files = array();

        if (!is_dir($this->upload_dir)) {
            return $files;
        }

        $iterator = new DirectoryIterator($this->upload_dir);

        foreach ($iterator as $file) {
            if ($file->isDot() || $file->isDir()) {
                continue;
            }

            $extension = strtolower($file->getExtension());
            if (!in_array($extension, $this->allowed_extensions)) {
                continue;
            }

            $files[] = array(
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
                'modified' => $file->getMTime(),
                'extension' => $extension,
                'url' => $this->upload_url . $file->getFilename()
            );
        }

        // Sort by modification time
        usort($files, function($a, $b) {
            return $b['modified'] - $a['modified'];
        });

        return $files;
    }

    /**
     * Get backup list
     *
     * @param string $file_name
     * @return array
     */
    public function get_backup_list($file_name = '') {
        $backup_dir = $this->upload_dir . 'backups/';
        $backups = array();

        if (!is_dir($backup_dir)) {
            return $backups;
        }

        $iterator = new DirectoryIterator($backup_dir);

        foreach ($iterator as $file) {
            if ($file->isDot() || $file->isDir()) {
                continue;
            }

            $filename = $file->getFilename();

            // Filter by original file name if specified
            if (!empty($file_name)) {
                $original_name = pathinfo($file_name, PATHINFO_FILENAME);
                if (strpos($filename, $original_name . '_') !== 0) {
                    continue;
                }
            }

            $backups[] = array(
                'name' => $filename,
                'size' => $file->getSize(),
                'created' => $file->getMTime(),
                'original_file' => $this->get_original_file_from_backup($filename)
            );
        }

        // Sort by creation time (newest first)
        usort($backups, function($a, $b) {
            return $b['created'] - $a['created'];
        });

        return $backups;
    }

    /**
     * Restore from backup
     *
     * @param string $backup_name
     * @return bool|WP_Error
     */
    public function restore_backup($backup_name) {
        $backup_path = $this->upload_dir . 'backups/' . $backup_name;

        if (!file_exists($backup_path)) {
            return new WP_Error('backup_not_found', __('Backup file not found.', 'gsap-for-wordpress'));
        }

        $original_file = $this->get_original_file_from_backup($backup_name);
        if (!$original_file) {
            return new WP_Error('invalid_backup', __('Invalid backup file.', 'gsap-for-wordpress'));
        }

        $filesystem = $this->get_filesystem();
        if (is_wp_error($filesystem)) {
            return $filesystem;
        }

        // Create backup of current file before restoring
        $original_path = $this->upload_dir . $original_file;
        if (file_exists($original_path)) {
            $this->create_backup($original_file);
        }

        $result = $filesystem->copy($backup_path, $original_path);

        if (!$result) {
            return new WP_Error('restore_failed', __('Failed to restore backup.', 'gsap-for-wordpress'));
        }

        // Log the action
        if (class_exists('GSAP_WP_Security')) {
            GSAP_WP_Security::get_instance()->log_security_event(
                'backup_restored',
                "Backup {$backup_name} restored to {$original_file}",
                array('backup' => $backup_name, 'file' => $original_file)
            );
        }

        do_action('gsap_wp_backup_restored', $backup_name, $original_file);

        return true;
    }

    /**
     * Export files
     *
     * @param array $file_names
     * @return array|WP_Error
     */
    public function export_files($file_names = array()) {
        if (empty($file_names)) {
            $file_list = $this->get_file_list();
            $file_names = array_column($file_list, 'name');
        }

        $export_data = array(
            'plugin_version' => GSAP_WP_VERSION,
            'export_date' => current_time('mysql'),
            'files' => array()
        );

        foreach ($file_names as $file_name) {
            $content = $this->read_file($file_name);
            if (!is_wp_error($content)) {
                $export_data['files'][$file_name] = array(
                    'content' => $content,
                    'modified' => filemtime($this->upload_dir . $file_name)
                );
            }
        }

        return $export_data;
    }

    /**
     * Import files
     *
     * @param array $import_data
     * @param bool $overwrite
     * @return array|WP_Error
     */
    public function import_files($import_data, $overwrite = false) {
        if (!isset($import_data['files']) || !is_array($import_data['files'])) {
            return new WP_Error('invalid_import', __('Invalid import data.', 'gsap-for-wordpress'));
        }

        $imported_files = array();
        $errors = array();

        foreach ($import_data['files'] as $file_name => $file_data) {
            if (!isset($file_data['content'])) {
                $errors[] = sprintf(__('Missing content for file: %s', 'gsap-for-wordpress'), $file_name);
                continue;
            }

            $file_path = $this->upload_dir . $file_name;

            // Check if file exists and overwrite is not allowed
            if (!$overwrite && file_exists($file_path)) {
                $errors[] = sprintf(__('File already exists: %s', 'gsap-for-wordpress'), $file_name);
                continue;
            }

            $result = $this->write_file($file_name, $file_data['content'], true);

            if (is_wp_error($result)) {
                $errors[] = sprintf(__('Failed to import %s: %s', 'gsap-for-wordpress'), $file_name, $result->get_error_message());
            } else {
                $imported_files[] = $file_name;
            }
        }

        $result = array(
            'imported' => $imported_files,
            'errors' => $errors,
            'total_imported' => count($imported_files),
            'total_errors' => count($errors)
        );

        // Log the action
        if (class_exists('GSAP_WP_Security')) {
            GSAP_WP_Security::get_instance()->log_security_event(
                'files_imported',
                sprintf('Imported %d files', count($imported_files)),
                array('files' => $imported_files, 'errors' => count($errors))
            );
        }

        return $result;
    }

    /**
     * AJAX: Backup files
     */
    public function ajax_backup_files() {
        if (!wp_verify_nonce($_POST['nonce'], 'gsap_wp_ajax_nonce') || !current_user_can('manage_options')) {
            wp_die(__('Security check failed.', 'gsap-for-wordpress'));
        }

        $file_names = isset($_POST['files']) ? (array) $_POST['files'] : array();

        if (empty($file_names)) {
            wp_send_json_error(__('No files specified.', 'gsap-for-wordpress'));
        }

        $backups_created = array();
        $errors = array();

        foreach ($file_names as $file_name) {
            $file_name = sanitize_file_name($file_name);
            $result = $this->create_backup($file_name);

            if (is_wp_error($result)) {
                $errors[] = $result->get_error_message();
            } else {
                $backups_created[] = $result;
            }
        }

        if (empty($errors)) {
            wp_send_json_success(array(
                'message' => sprintf(__('Created %d backup(s) successfully.', 'gsap-for-wordpress'), count($backups_created)),
                'backups' => $backups_created
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Some backups failed.', 'gsap-for-wordpress'),
                'errors' => $errors,
                'backups' => $backups_created
            ));
        }
    }

    /**
     * AJAX: Restore backup
     */
    public function ajax_restore_backup() {
        if (!wp_verify_nonce($_POST['nonce'], 'gsap_wp_ajax_nonce') || !current_user_can('manage_options')) {
            wp_die(__('Security check failed.', 'gsap-for-wordpress'));
        }

        $backup_name = sanitize_file_name($_POST['backup_name']);
        $result = $this->restore_backup($backup_name);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success(array(
            'message' => __('Backup restored successfully.', 'gsap-for-wordpress')
        ));
    }

    /**
     * AJAX: Export files
     */
    public function ajax_export_files() {
        if (!wp_verify_nonce($_POST['nonce'], 'gsap_wp_ajax_nonce') || !current_user_can('manage_options')) {
            wp_die(__('Security check failed.', 'gsap-for-wordpress'));
        }

        $file_names = isset($_POST['files']) ? (array) $_POST['files'] : array();
        $export_data = $this->export_files($file_names);

        if (is_wp_error($export_data)) {
            wp_send_json_error($export_data->get_error_message());
        }

        wp_send_json_success(array(
            'export_data' => $export_data,
            'download_name' => 'gsap-wordpress-export-' . date('Y-m-d-H-i-s') . '.json'
        ));
    }

    /**
     * AJAX: Import files
     */
    public function ajax_import_files() {
        if (!wp_verify_nonce($_POST['nonce'], 'gsap_wp_ajax_nonce') || !current_user_can('manage_options')) {
            wp_die(__('Security check failed.', 'gsap-for-wordpress'));
        }

        $import_data = json_decode(stripslashes($_POST['import_data']), true);
        $overwrite = isset($_POST['overwrite']) ? (bool) $_POST['overwrite'] : false;

        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error(__('Invalid JSON data.', 'gsap-for-wordpress'));
        }

        $result = $this->import_files($import_data, $overwrite);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success($result);
    }

    /**
     * Validate file name
     *
     * @param string $file_name
     * @return bool|WP_Error
     */
    private function validate_file_name($file_name) {
        if (empty($file_name)) {
            return new WP_Error('empty_filename', __('File name cannot be empty.', 'gsap-for-wordpress'));
        }

        // Check for directory traversal
        if (strpos($file_name, '..') !== false || strpos($file_name, '/') !== false || strpos($file_name, '\\') !== false) {
            return new WP_Error('invalid_filename', __('Invalid file name.', 'gsap-for-wordpress'));
        }

        // Check extension
        $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowed_extensions)) {
            return new WP_Error('invalid_extension', __('File extension not allowed.', 'gsap-for-wordpress'));
        }

        return true;
    }

    /**
     * Validate file content
     *
     * @param string $content
     * @param string $file_name
     * @return bool|WP_Error
     */
    private function validate_content($content, $file_name) {
        // Check file size
        if (strlen($content) > $this->max_file_size) {
            return new WP_Error('file_too_large', __('File content is too large.', 'gsap-for-wordpress'));
        }

        // Use security class for content validation
        if (class_exists('GSAP_WP_Security')) {
            $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $file_type = $extension === 'js' ? 'js' : 'css';

            $security = GSAP_WP_Security::get_instance();
            return $security->validate_file_content($content, $file_type);
        }

        return true;
    }

    /**
     * Get WordPress filesystem
     *
     * @return WP_Filesystem_Base|WP_Error
     */
    private function get_filesystem() {
        global $wp_filesystem;

        if (!$wp_filesystem) {
            require_once ABSPATH . 'wp-admin/includes/file.php';

            if (!WP_Filesystem()) {
                return new WP_Error('filesystem_error', __('Could not access filesystem.', 'gsap-for-wordpress'));
            }
        }

        return $wp_filesystem;
    }

    /**
     * Create .htaccess file for security
     */
    private function create_htaccess_file() {
        $htaccess_content = "# GSAP WordPress Files\n";
        $htaccess_content .= "# Prevent direct access to sensitive files\n";
        $htaccess_content .= "<Files \"*.php\">\n";
        $htaccess_content .= "Order deny,allow\n";
        $htaccess_content .= "Deny from all\n";
        $htaccess_content .= "</Files>\n";

        $htaccess_path = $this->upload_dir . '.htaccess';
        file_put_contents($htaccess_path, $htaccess_content);
    }

    /**
     * Create index.php file for security
     */
    private function create_index_file() {
        $index_content = "<?php\n// Silence is golden.\n";
        $index_path = $this->upload_dir . 'index.php';
        file_put_contents($index_path, $index_content);
    }

    /**
     * Store backup metadata
     *
     * @param string $backup_name
     * @param string $original_file
     */
    private function store_backup_metadata($backup_name, $original_file) {
        $metadata = get_option('gsap_wp_backup_metadata', array());
        $metadata[$backup_name] = array(
            'original_file' => $original_file,
            'created' => current_time('mysql'),
            'created_by' => get_current_user_id()
        );
        update_option('gsap_wp_backup_metadata', $metadata);
    }

    /**
     * Get original file from backup name
     *
     * @param string $backup_name
     * @return string|false
     */
    private function get_original_file_from_backup($backup_name) {
        $metadata = get_option('gsap_wp_backup_metadata', array());

        if (isset($metadata[$backup_name]['original_file'])) {
            return $metadata[$backup_name]['original_file'];
        }

        // Fallback: try to extract from filename
        $parts = explode('_', $backup_name);
        if (count($parts) >= 2) {
            array_pop($parts); // Remove timestamp
            array_pop($parts); // Remove time part
            $base_name = implode('_', $parts);
            $extension = pathinfo($backup_name, PATHINFO_EXTENSION);
            return $base_name . '.' . $extension;
        }

        return false;
    }

    /**
     * Cleanup old backups
     */
    public function cleanup_old_backups() {
        $backup_dir = $this->upload_dir . 'backups/';

        if (!is_dir($backup_dir)) {
            return;
        }

        $cutoff_time = time() - (30 * DAY_IN_SECONDS); // 30 days
        $iterator = new DirectoryIterator($backup_dir);

        foreach ($iterator as $file) {
            if ($file->isDot() || $file->isDir()) {
                continue;
            }

            if ($file->getMTime() < $cutoff_time) {
                unlink($file->getPathname());
            }
        }

        do_action('gsap_wp_backups_cleaned');
    }

    /**
     * Get upload directory path
     *
     * @return string
     */
    public function get_upload_dir() {
        return $this->upload_dir;
    }

    /**
     * Get upload directory URL
     *
     * @return string
     */
    public function get_upload_url() {
        return $this->upload_url;
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