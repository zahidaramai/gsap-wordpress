<?php
/**
 * File Editor class for GSAP WordPress plugin
 *
 * @package GSAP_For_WordPress
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GSAP WordPress File Editor class
 */
class GSAP_WP_File_Editor {

    /**
     * Single instance of the class
     *
     * @var GSAP_WP_File_Editor
     */
    private static $instance = null;

    /**
     * Editable files
     *
     * @var array
     */
    private $editable_files = array();

    /**
     * Current file being edited
     *
     * @var string
     */
    private $current_file = '';

    /**
     * Upload directory path
     *
     * @var string
     */
    private $upload_dir = '';

    /**
     * Get single instance
     *
     * @return GSAP_WP_File_Editor
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
        $this->init_upload_dir();
        $this->init_editable_files();
        $this->init_hooks();
    }

    /**
     * Initialize upload directory
     */
    private function init_upload_dir() {
        // Use plugin assets directory instead of uploads
        $this->upload_dir = GSAP_WP_PLUGIN_PATH . 'assets/';

        // Ensure directories exist
        if (!file_exists($this->upload_dir . 'js/')) {
            wp_mkdir_p($this->upload_dir . 'js/');
        }
        if (!file_exists($this->upload_dir . 'css/')) {
            wp_mkdir_p($this->upload_dir . 'css/');
        }
    }

    /**
     * Initialize editable files
     */
    private function init_editable_files() {
        $this->editable_files = array(
            'js/global.js' => array(
                'name' => __('Global Animations', 'gsap-for-wordpress'),
                'description' => __('Add your custom GSAP animations that will load globally on your website', 'gsap-for-wordpress'),
                'type' => 'javascript',
                'icon' => 'dashicons-media-code',
                'default_content' => $this->get_default_js_content()
            ),
            'css/animation.css' => array(
                'name' => __('Animation Styles', 'gsap-for-wordpress'),
                'description' => __('Add custom CSS styles to support your GSAP animations', 'gsap-for-wordpress'),
                'type' => 'css',
                'icon' => 'dashicons-admin-appearance',
                'default_content' => $this->get_default_css_content()
            )
        );

        // Allow third-party plugins to add files
        $this->editable_files = apply_filters('gsap_wp_editable_files', $this->editable_files);
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('wp_ajax_gsap_wp_save_file', array($this, 'ajax_save_file'));
        add_action('wp_ajax_gsap_wp_load_file', array($this, 'ajax_load_file'));
        add_action('wp_ajax_gsap_wp_reset_file', array($this, 'ajax_reset_file'));
        add_action('wp_ajax_gsap_wp_validate_syntax', array($this, 'ajax_validate_syntax'));
    }

    /**
     * Render file editor page
     */
    public function render_page() {
        // Render WordPress-style file editor interface
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">
                <?php _e('GSAP for WordPress - Customize', 'gsap-for-wordpress'); ?>
            </h1>
            <hr class="wp-header-end">

            <div class="gsap-wp-customize-container">
                <?php $this->render_content(); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render file editor content
     */
    public function render_content() {
        // Check if user can edit files
        if (!$this->can_edit_files()) {
            $this->render_no_permission_message();
            return;
        }

        $this->current_file = isset($_GET['file']) ? sanitize_text_field($_GET['file']) : 'js/global.js';

        // Sanitize file path to prevent directory traversal
        $this->current_file = str_replace(array('..', '\\'), array('', '/'), $this->current_file);

        if (!isset($this->editable_files[$this->current_file])) {
            $this->current_file = 'js/global.js';
        }

        ?>
        <!-- WordPress-style file editor interface -->
        <div class="gsap-wp-file-editor">
            <div class="gsap-wp-editor-layout">
                <!-- Left sidebar with file tree and version history -->
                <div class="gsap-wp-file-tree">
                    <?php $this->render_file_tree(); ?>
                    <?php $this->render_version_history(); ?>
                </div>

                <!-- Main editor area -->
                <div class="gsap-wp-editor-main">
                    <!-- Editor toolbar -->
                    <div class="gsap-wp-editor-toolbar">
                        <?php $this->render_editor_toolbar(); ?>
                    </div>

                    <!-- Code editor container -->
                    <div class="gsap-wp-editor-container">
                        <?php $this->render_code_editor(); ?>
                    </div>

                    <!-- Editor footer with help -->
                    <div class="gsap-wp-editor-footer">
                        <?php $this->render_editor_footer(); ?>
                    </div>
                </div>
            </div>
        </div>

        <?php $this->render_modals(); ?>
        <?php
    }

    /**
     * Render file tree
     */
    private function render_file_tree() {
        ?>
        <div class="gsap-wp-file-tree-section">
            <h3>
                <span class="dashicons dashicons-portfolio"></span>
                <?php _e('Files', 'gsap-for-wordpress'); ?>
            </h3>

            <ul class="gsap-wp-file-list">
                <?php foreach ($this->editable_files as $file_key => $file_data): ?>
                    <li class="gsap-wp-file-item <?php echo $this->current_file === $file_key ? 'active' : ''; ?>">
                        <a href="<?php echo esc_url($this->get_editor_url($file_key)); ?>" class="gsap-wp-file-link">
                            <span class="dashicons <?php echo esc_attr($file_data['icon']); ?>"></span>
                            <div class="gsap-wp-file-info">
                                <div class="gsap-wp-file-name"><?php echo esc_html($file_data['name']); ?></div>
                                <div class="gsap-wp-file-path"><?php echo esc_html($file_key); ?></div>
                            </div>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }

    /**
     * Render version history
     */
    private function render_version_history() {
        if (!class_exists('GSAP_WP_Version_Manager')) {
            return;
        }

        $version_manager = GSAP_WP_Version_Manager::get_instance();
        $versions = $version_manager->get_file_versions($this->current_file);
        ?>
        <div class="gsap-wp-version-history-section">
            <h3>
                <span class="dashicons dashicons-backup"></span>
                <?php _e('Version History', 'gsap-for-wordpress'); ?>
            </h3>

            <?php if (empty($versions)): ?>
                <p class="gsap-wp-no-versions">
                    <?php _e('No versions saved yet.', 'gsap-for-wordpress'); ?>
                </p>
            <?php else: ?>
                <ul class="gsap-wp-version-list">
                    <?php foreach (array_slice($versions, 0, 5) as $version): ?>
                        <li class="gsap-wp-version-item">
                            <button type="button" class="gsap-wp-version-link"
                                    data-version-id="<?php echo esc_attr($version->id); ?>">
                                <div class="gsap-wp-version-info">
                                    <div class="gsap-wp-version-number">
                                        v<?php echo esc_html($version->version_number); ?>
                                    </div>
                                    <div class="gsap-wp-version-date">
                                        <?php echo esc_html(mysql2date('M j, Y g:i A', $version->created_at)); ?>
                                    </div>
                                    <?php if (!empty($version->user_comment)): ?>
                                        <div class="gsap-wp-version-comment">
                                            <?php echo esc_html($version->user_comment); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <?php if (count($versions) > 5): ?>
                    <button type="button" class="button button-small gsap-wp-view-all-versions">
                        <?php _e('View All Versions', 'gsap-for-wordpress'); ?>
                    </button>
                <?php endif; ?>
            <?php endif; ?>

            <button type="button" class="button button-primary gsap-wp-create-version">
                <?php _e('Create Version', 'gsap-for-wordpress'); ?>
            </button>
        </div>
        <?php
    }

    /**
     * Render editor toolbar
     */
    private function render_editor_toolbar() {
        $current_file_data = $this->editable_files[$this->current_file];
        ?>
        <div class="gsap-wp-toolbar-left">
            <h2 class="gsap-wp-file-title">
                <span class="dashicons <?php echo esc_attr($current_file_data['icon']); ?>"></span>
                <?php echo esc_html($current_file_data['name']); ?>
            </h2>
            <p class="gsap-wp-file-description">
                <?php echo esc_html($current_file_data['description']); ?>
            </p>
        </div>

        <div class="gsap-wp-toolbar-right">
            <div class="gsap-wp-editor-actions">
                <button type="button" class="button button-secondary gsap-wp-validate-code">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <?php _e('Validate', 'gsap-for-wordpress'); ?>
                </button>

                <button type="button" class="button button-secondary gsap-wp-format-code">
                    <span class="dashicons dashicons-editor-alignleft"></span>
                    <?php _e('Format', 'gsap-for-wordpress'); ?>
                </button>

                <button type="button" class="button button-secondary gsap-wp-reset-file">
                    <span class="dashicons dashicons-undo"></span>
                    <?php _e('Reset', 'gsap-for-wordpress'); ?>
                </button>

                <button type="button" class="button button-primary gsap-wp-save-file">
                    <span class="dashicons dashicons-saved"></span>
                    <?php _e('Save File', 'gsap-for-wordpress'); ?>
                </button>
            </div>
        </div>
        <?php
    }

    /**
     * Render code editor
     */
    private function render_code_editor() {
        $file_content = $this->get_file_content($this->current_file);
        $file_type = $this->editable_files[$this->current_file]['type'];
        ?>
        <div class="gsap-wp-code-editor-wrapper">
            <textarea id="gsap-wp-code-editor"
                      class="gsap-wp-code-editor"
                      data-file="<?php echo esc_attr($this->current_file); ?>"
                      data-type="<?php echo esc_attr($file_type); ?>"
                      spellcheck="false"><?php echo esc_textarea($file_content); ?></textarea>

            <div class="gsap-wp-editor-status">
                <div class="gsap-wp-status-left">
                    <span class="gsap-wp-file-type"><?php echo esc_html(strtoupper($file_type)); ?></span>
                    <span class="gsap-wp-cursor-position">Line 1, Column 1</span>
                </div>
                <div class="gsap-wp-status-right">
                    <span class="gsap-wp-save-status"></span>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render editor footer
     */
    private function render_editor_footer() {
        ?>
        <div class="gsap-wp-editor-help">
            <div class="gsap-wp-keyboard-shortcuts">
                <h4><?php _e('Keyboard Shortcuts', 'gsap-for-wordpress'); ?></h4>
                <ul>
                    <li><kbd>Ctrl + S</kbd> - <?php _e('Save file', 'gsap-for-wordpress'); ?></li>
                    <li><kbd>Ctrl + Z</kbd> - <?php _e('Undo', 'gsap-for-wordpress'); ?></li>
                    <li><kbd>Ctrl + Y</kbd> - <?php _e('Redo', 'gsap-for-wordpress'); ?></li>
                    <li><kbd>Ctrl + F</kbd> - <?php _e('Find', 'gsap-for-wordpress'); ?></li>
                    <li><kbd>Ctrl + H</kbd> - <?php _e('Replace', 'gsap-for-wordpress'); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * Render modals
     */
    private function render_modals() {
        ?>
        <!-- Create Version Modal -->
        <div id="gsap-wp-create-version-modal" class="gsap-wp-modal" style="display: none;">
            <div class="gsap-wp-modal-content">
                <div class="gsap-wp-modal-header">
                    <h3><?php _e('Create New Version', 'gsap-for-wordpress'); ?></h3>
                    <button type="button" class="gsap-wp-modal-close">&times;</button>
                </div>
                <div class="gsap-wp-modal-body">
                    <form id="gsap-wp-version-form">
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="version-comment"><?php _e('Version Comment', 'gsap-for-wordpress'); ?></label>
                                </th>
                                <td>
                                    <textarea id="version-comment" name="comment" rows="3" class="large-text"
                                              placeholder="<?php esc_attr_e('Describe the changes in this version...', 'gsap-for-wordpress'); ?>"></textarea>
                                </td>
                            </tr>
                        </table>
                        <div class="gsap-wp-modal-actions">
                            <button type="submit" class="button button-primary">
                                <?php _e('Create Version', 'gsap-for-wordpress'); ?>
                            </button>
                            <button type="button" class="button button-secondary gsap-wp-modal-close">
                                <?php _e('Cancel', 'gsap-for-wordpress'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Import/Export Modal -->
        <div id="gsap-wp-import-export-modal" class="gsap-wp-modal" style="display: none;">
            <div class="gsap-wp-modal-content">
                <div class="gsap-wp-modal-header">
                    <h3><?php _e('Import/Export', 'gsap-for-wordpress'); ?></h3>
                    <button type="button" class="gsap-wp-modal-close">&times;</button>
                </div>
                <div class="gsap-wp-modal-body">
                    <div class="gsap-wp-tab-container">
                        <ul class="gsap-wp-tab-nav">
                            <li><a href="#export-tab" class="active"><?php _e('Export', 'gsap-for-wordpress'); ?></a></li>
                            <li><a href="#import-tab"><?php _e('Import', 'gsap-for-wordpress'); ?></a></li>
                        </ul>

                        <div id="export-tab" class="gsap-wp-tab-content active">
                            <p><?php _e('Copy the code below to backup your animations:', 'gsap-for-wordpress'); ?></p>
                            <textarea id="export-content" readonly class="large-text" rows="10"></textarea>
                            <button type="button" class="button button-secondary gsap-wp-copy-export">
                                <?php _e('Copy to Clipboard', 'gsap-for-wordpress'); ?>
                            </button>
                        </div>

                        <div id="import-tab" class="gsap-wp-tab-content">
                            <p><?php _e('Paste your code below to import animations:', 'gsap-for-wordpress'); ?></p>
                            <textarea id="import-content" class="large-text" rows="10"
                                      placeholder="<?php esc_attr_e('Paste your code here...', 'gsap-for-wordpress'); ?>"></textarea>
                            <button type="button" class="button button-primary gsap-wp-import-code">
                                <?php _e('Import Code', 'gsap-for-wordpress'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * AJAX: Save file
     */
    public function ajax_save_file() {
        // Verify nonce and capabilities
        if (!wp_verify_nonce($_POST['nonce'], 'gsap_wp_ajax_nonce') || !$this->can_edit_files()) {
            wp_send_json_error(__('Security check failed.', 'gsap-for-wordpress'));
        }

        // Rate limiting
        if (class_exists('GSAP_WP_Security')) {
            $security = GSAP_WP_Security::get_instance();
            if (!$security->check_rate_limit('save_file', 30, 3600)) {
                wp_send_json_error(__('Rate limit exceeded. Please try again later.', 'gsap-for-wordpress'));
            }
        }

        // Sanitize file path (preserve directory separator)
        $file_name = sanitize_text_field($_POST['file']);
        $file_name = str_replace(array('..', '\\'), array('', '/'), $file_name);
        $content = $_POST['content'];

        // Validate file
        if (!isset($this->editable_files[$file_name])) {
            wp_send_json_error(__('Invalid file.', 'gsap-for-wordpress'));
        }

        // Validate content
        $file_type = $this->editable_files[$file_name]['type'];
        if (class_exists('GSAP_WP_Security')) {
            $security = GSAP_WP_Security::get_instance();
            $validation_result = $security->validate_file_content($content, $file_type);

            if (is_wp_error($validation_result)) {
                wp_send_json_error($validation_result->get_error_message());
            }

            $content = $security->sanitize_file_content($content, $file_type);
        }

        // Save file
        $file_path = $this->upload_dir . $file_name;
        $result = file_put_contents($file_path, $content);

        if ($result === false) {
            wp_send_json_error(__('Failed to save file.', 'gsap-for-wordpress'));
        }

        // Log the change
        if (class_exists('GSAP_WP_Security')) {
            $security = GSAP_WP_Security::get_instance();
            $security->log_security_event('file_saved', "File {$file_name} was saved", array(
                'file' => $file_name,
                'size' => strlen($content)
            ));
        }

        wp_send_json_success(array(
            'message' => __('File saved successfully!', 'gsap-for-wordpress'),
            'size' => strlen($content),
            'modified' => current_time('mysql')
        ));
    }

    /**
     * AJAX: Load file
     */
    public function ajax_load_file() {
        if (!wp_verify_nonce($_POST['nonce'], 'gsap_wp_ajax_nonce') || !$this->can_edit_files()) {
            wp_send_json_error(__('Security check failed.', 'gsap-for-wordpress'));
        }

        // Sanitize file path (preserve directory separator)
        $file_name = sanitize_text_field($_POST['file']);
        $file_name = str_replace(array('..', '\\'), array('', '/'), $file_name);

        if (!isset($this->editable_files[$file_name])) {
            wp_send_json_error(__('Invalid file.', 'gsap-for-wordpress'));
        }

        $content = $this->get_file_content($file_name);

        wp_send_json_success(array(
            'content' => $content,
            'file' => $file_name,
            'type' => $this->editable_files[$file_name]['type']
        ));
    }

    /**
     * AJAX: Reset file
     */
    public function ajax_reset_file() {
        if (!wp_verify_nonce($_POST['nonce'], 'gsap_wp_ajax_nonce') || !$this->can_edit_files()) {
            wp_send_json_error(__('Security check failed.', 'gsap-for-wordpress'));
        }

        // Sanitize file path (preserve directory separator)
        $file_name = sanitize_text_field($_POST['file']);
        $file_name = str_replace(array('..', '\\'), array('', '/'), $file_name);

        if (!isset($this->editable_files[$file_name])) {
            wp_send_json_error(__('Invalid file.', 'gsap-for-wordpress'));
        }

        $default_content = $this->editable_files[$file_name]['default_content'];
        $file_path = $this->upload_dir . $file_name;

        $result = file_put_contents($file_path, $default_content);

        if ($result === false) {
            wp_send_json_error(__('Failed to reset file.', 'gsap-for-wordpress'));
        }

        wp_send_json_success(array(
            'content' => $default_content,
            'message' => __('File reset to default content.', 'gsap-for-wordpress')
        ));
    }

    /**
     * AJAX: Validate syntax
     */
    public function ajax_validate_syntax() {
        if (!wp_verify_nonce($_POST['nonce'], 'gsap_wp_ajax_nonce')) {
            wp_die(__('Security check failed.', 'gsap-for-wordpress'));
        }

        $content = $_POST['content'];
        $file_type = $_POST['type'];

        $errors = array();

        if ($file_type === 'javascript') {
            // Basic JavaScript syntax validation
            // Check for unclosed brackets, braces, parentheses
            $brackets = 0;
            $braces = 0;
            $parentheses = 0;

            for ($i = 0; $i < strlen($content); $i++) {
                $char = $content[$i];
                switch ($char) {
                    case '[':
                        $brackets++;
                        break;
                    case ']':
                        $brackets--;
                        break;
                    case '{':
                        $braces++;
                        break;
                    case '}':
                        $braces--;
                        break;
                    case '(':
                        $parentheses++;
                        break;
                    case ')':
                        $parentheses--;
                        break;
                }
            }

            if ($brackets !== 0) {
                $errors[] = __('Unclosed square brackets detected.', 'gsap-for-wordpress');
            }
            if ($braces !== 0) {
                $errors[] = __('Unclosed curly braces detected.', 'gsap-for-wordpress');
            }
            if ($parentheses !== 0) {
                $errors[] = __('Unclosed parentheses detected.', 'gsap-for-wordpress');
            }
        }

        if (empty($errors)) {
            wp_send_json_success(array(
                'message' => __('Syntax validation passed!', 'gsap-for-wordpress')
            ));
        } else {
            wp_send_json_error(array(
                'errors' => $errors
            ));
        }
    }

    /**
     * Get file content
     *
     * @param string $file_name
     * @return string
     */
    private function get_file_content($file_name) {
        $file_path = $this->upload_dir . $file_name;

        if (file_exists($file_path)) {
            return file_get_contents($file_path);
        }

        // Return default content if file doesn't exist
        return isset($this->editable_files[$file_name]['default_content'])
            ? $this->editable_files[$file_name]['default_content']
            : '';
    }

    /**
     * Check if user can edit files
     *
     * @return bool
     */
    private function can_edit_files() {
        if (class_exists('GSAP_WP_Security')) {
            return GSAP_WP_Security::get_instance()->can_edit_files();
        }

        return current_user_can('edit_themes') || current_user_can('manage_options');
    }

    /**
     * Render no permission message
     */
    private function render_no_permission_message() {
        ?>
        <div class="gsap-wp-no-permission">
            <div class="notice notice-warning">
                <h3><?php _e('File Editing Disabled', 'gsap-for-wordpress'); ?></h3>
                <p>
                    <?php _e('File editing has been disabled in your WordPress configuration or you do not have sufficient permissions.', 'gsap-for-wordpress'); ?>
                </p>
                <?php if (defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT): ?>
                    <p>
                        <strong><?php _e('Administrator Note:', 'gsap-for-wordpress'); ?></strong>
                        <?php _e('To enable file editing, remove or set DISALLOW_FILE_EDIT to false in your wp-config.php file.', 'gsap-for-wordpress'); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Get editor URL for a specific file
     *
     * @param string $file_key
     * @return string
     */
    private function get_editor_url($file_key) {
        return add_query_arg(array(
            'page' => 'gsap-wordpress',
            'tab' => 'customize',
            'file' => $file_key
        ), admin_url('admin.php'));
    }

    /**
     * Get default JavaScript content
     *
     * @return string
     */
    private function get_default_js_content() {
        return '// GSAP Global Animations
// Add your custom GSAP animations here

// Example: Fade in elements on page load
document.addEventListener("DOMContentLoaded", function() {
    // Fade in all elements with class "gsap-fade-in"
    gsap.from(".gsap-fade-in", {
        duration: 1,
        opacity: 0,
        y: 20,
        stagger: 0.1,
        ease: "power2.out"
    });
});

// Example: ScrollTrigger animation
// gsap.registerPlugin(ScrollTrigger);
// gsap.to(".gsap-scroll", {
//     scrollTrigger: ".gsap-scroll",
//     x: 100,
//     duration: 2,
//     ease: "power2.out"
// });';
    }

    /**
     * Get default CSS content
     *
     * @return string
     */
    private function get_default_css_content() {
        return '/* GSAP Animation Styles */
/* Add your custom animation styles here */

/* Hide elements that will be animated */
.gsap-fade-in {
    opacity: 0;
}

/* Animation-ready classes */
.gsap-element {
    transform-origin: center center;
}

.gsap-hover {
    transition: transform 0.3s ease;
}

.gsap-hover:hover {
    transform: scale(1.05);
}

/* Responsive animations */
@media (max-width: 768px) {
    .gsap-mobile-hidden {
        display: none;
    }
}';
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