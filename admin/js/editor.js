/**
 * File Editor JavaScript for GSAP for WordPress
 *
 * @package GSAP_For_WordPress
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * GSAP File Editor Object
     */
    const GSAPEditor = {

        /**
         * Current file being edited
         */
        currentFile: '',

        /**
         * Editor instance
         */
        editor: null,

        /**
         * Auto-save timeout
         */
        autoSaveTimeout: null,

        /**
         * Has unsaved changes
         */
        hasUnsavedChanges: false,

        /**
         * Initialize
         */
        init: function() {
            this.currentFile = $('#gsap-wp-code-editor').data('file') || '';
            this.bindEvents();
            this.initCodeEditor();
            this.initAutoSave();
            this.initKeyboardShortcuts();
            this.updateStatus();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            const self = this;

            // Save button
            $('.gsap-wp-save-file').on('click', function(e) {
                e.preventDefault();
                self.saveFile();
            });

            // Validate button
            $('.gsap-wp-validate-code').on('click', function(e) {
                e.preventDefault();
                self.validateCode();
            });

            // Format button
            $('.gsap-wp-format-code').on('click', function(e) {
                e.preventDefault();
                self.formatCode();
            });

            // Reset button
            $('.gsap-wp-reset-file').on('click', function(e) {
                e.preventDefault();
                self.resetFile();
            });

            // Create version button
            $('.gsap-wp-create-version-btn, .gsap-wp-create-version').on('click', function(e) {
                e.preventDefault();
                self.showCreateVersionModal();
            });

            // Version form submit
            $('#gsap-wp-version-form').on('submit', function(e) {
                e.preventDefault();
                self.createVersion();
            });

            // View version
            $(document).on('click', '.gsap-wp-view-version', function(e) {
                e.preventDefault();
                const versionId = $(this).data('version-id');
                self.loadVersion(versionId);
            });

            // Restore version
            $(document).on('click', '.gsap-wp-restore-version', function(e) {
                e.preventDefault();
                const versionId = $(this).data('version-id');
                self.restoreVersion(versionId);
            });

            // Delete version
            $(document).on('click', '.gsap-wp-delete-version', function(e) {
                e.preventDefault();
                const versionId = $(this).data('version-id');
                self.deleteVersion(versionId);
            });

            // Track changes
            $('#gsap-wp-code-editor').on('input', function() {
                self.hasUnsavedChanges = true;
                self.updateStatus('modified');
                self.scheduleAutoSave();
            });

            // Warn before leaving with unsaved changes
            $(window).on('beforeunload', function() {
                if (self.hasUnsavedChanges) {
                    return 'You have unsaved changes. Are you sure you want to leave?';
                }
            });

            // Modal close handlers
            $('.gsap-wp-modal-close, .gsap-wp-modal').on('click', function(e) {
                if ($(e.target).hasClass('gsap-wp-modal') || $(e.target).hasClass('gsap-wp-modal-close')) {
                    $('.gsap-wp-modal').fadeOut(200);
                    $('#version-comment').val('');
                }
            });

            // Prevent modal content clicks from closing modal
            $('.gsap-wp-modal-content').on('click', function(e) {
                e.stopPropagation();
            });
        },

        /**
         * Initialize code editor
         */
        initCodeEditor: function() {
            const $editor = $('#gsap-wp-code-editor');

            if (!$editor.length) return;

            // Track cursor position
            $editor.on('click keyup', this.updateCursorPosition);

            // Add tab support
            $editor.on('keydown', function(e) {
                if (e.key === 'Tab') {
                    e.preventDefault();
                    const start = this.selectionStart;
                    const end = this.selectionEnd;
                    const value = this.value;

                    // Insert tab
                    this.value = value.substring(0, start) + '  ' + value.substring(end);
                    this.selectionStart = this.selectionEnd = start + 2;
                }
            });

            // Syntax highlighting (basic)
            this.applySyntaxHighlighting();
        },

        /**
         * Update cursor position display
         */
        updateCursorPosition: function() {
            const $editor = $('#gsap-wp-code-editor');
            const text = $editor.val();
            const position = $editor[0].selectionStart;

            const lines = text.substring(0, position).split('\n');
            const line = lines.length;
            const column = lines[lines.length - 1].length + 1;

            $('.gsap-wp-cursor-position').text('Line ' + line + ', Column ' + column);
        },

        /**
         * Apply basic syntax highlighting
         */
        applySyntaxHighlighting: function() {
            // This is a placeholder for future CodeMirror or Monaco integration
            // For now, the editor uses a monospace font with dark theme
        },

        /**
         * Initialize auto-save
         */
        initAutoSave: function() {
            // Auto-save is triggered by scheduleAutoSave() on input
        },

        /**
         * Schedule auto-save
         */
        scheduleAutoSave: function() {
            clearTimeout(this.autoSaveTimeout);

            this.autoSaveTimeout = setTimeout(() => {
                this.saveFile(true); // true = auto-save
            }, 3000); // 3 seconds
        },

        /**
         * Initialize keyboard shortcuts
         */
        initKeyboardShortcuts: function() {
            const self = this;

            $(document).on('keydown', function(e) {
                // Ctrl/Cmd + S: Save
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    self.saveFile();
                    return false;
                }

                // Ctrl/Cmd + Shift + F: Format
                if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'F') {
                    e.preventDefault();
                    self.formatCode();
                    return false;
                }
            });
        },

        /**
         * Save file
         */
        saveFile: function(isAutoSave) {
            const self = this;
            const content = $('#gsap-wp-code-editor').val();
            const fileName = this.currentFile;

            if (!fileName) {
                this.showNotice('No file selected', 'error');
                return;
            }

            this.updateStatus('saving');

            $.ajax({
                url: gsapWpAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'gsap_wp_save_file',
                    nonce: gsapWpAjax.nonce,
                    file: fileName,
                    content: content
                },
                success: function(response) {
                    if (response.success) {
                        self.hasUnsavedChanges = false;
                        self.updateStatus('saved');

                        if (!isAutoSave) {
                            self.showNotice(response.data.message || 'File saved successfully!', 'success');
                        }
                    } else {
                        self.updateStatus('error');
                        self.showNotice(response.data || 'Failed to save file', 'error');
                    }
                },
                error: function() {
                    self.updateStatus('error');
                    self.showNotice('An error occurred while saving', 'error');
                }
            });
        },

        /**
         * Validate code
         */
        validateCode: function() {
            const self = this;
            const content = $('#gsap-wp-code-editor').val();
            const fileType = $('#gsap-wp-code-editor').data('type');

            $.ajax({
                url: gsapWpAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'gsap_wp_validate_syntax',
                    nonce: gsapWpAjax.nonce,
                    content: content,
                    type: fileType
                },
                success: function(response) {
                    if (response.success) {
                        self.showNotice(response.data.message || 'Validation passed!', 'success');
                    } else {
                        const errors = response.data.errors || [response.data];
                        self.showNotice('Validation errors:\n' + errors.join('\n'), 'error');
                    }
                },
                error: function() {
                    self.showNotice('Validation error occurred', 'error');
                }
            });
        },

        /**
         * Format code
         */
        formatCode: function() {
            const $editor = $('#gsap-wp-code-editor');
            const fileType = $editor.data('type');
            let content = $editor.val();

            if (fileType === 'javascript' || fileType === 'js') {
                // Basic JavaScript formatting
                try {
                    // Simple indentation fix
                    content = this.formatJavaScript(content);
                    $editor.val(content);
                    this.showNotice('Code formatted', 'success');
                    this.hasUnsavedChanges = true;
                    this.updateStatus('modified');
                } catch (e) {
                    this.showNotice('Could not format code: ' + e.message, 'error');
                }
            } else if (fileType === 'css') {
                // Basic CSS formatting
                content = this.formatCSS(content);
                $editor.val(content);
                this.showNotice('Code formatted', 'success');
                this.hasUnsavedChanges = true;
                this.updateStatus('modified');
            }
        },

        /**
         * Basic JavaScript formatting
         */
        formatJavaScript: function(code) {
            // Very basic formatting - just fix indentation
            const lines = code.split('\n');
            let indentLevel = 0;
            const formatted = [];

            lines.forEach(line => {
                const trimmed = line.trim();
                if (!trimmed) {
                    formatted.push('');
                    return;
                }

                // Decrease indent for closing braces
                if (trimmed.startsWith('}') || trimmed.startsWith(']') || trimmed.startsWith(')')) {
                    indentLevel = Math.max(0, indentLevel - 1);
                }

                // Add indented line
                formatted.push('  '.repeat(indentLevel) + trimmed);

                // Increase indent for opening braces
                if (trimmed.endsWith('{') || trimmed.endsWith('[') || trimmed.endsWith('(')) {
                    indentLevel++;
                }
            });

            return formatted.join('\n');
        },

        /**
         * Basic CSS formatting
         */
        formatCSS: function(code) {
            // Very basic CSS formatting
            return code
                .replace(/\{/g, ' {\n  ')
                .replace(/\}/g, '\n}\n\n')
                .replace(/;/g, ';\n  ')
                .replace(/\n\s*\n\s*\n/g, '\n\n')
                .trim();
        },

        /**
         * Reset file to default
         */
        resetFile: function() {
            if (!confirm('Are you sure you want to reset this file to its default content? This action cannot be undone.')) {
                return;
            }

            const self = this;
            const fileName = this.currentFile;

            $.ajax({
                url: gsapWpAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'gsap_wp_reset_file',
                    nonce: gsapWpAjax.nonce,
                    file: fileName
                },
                success: function(response) {
                    if (response.success) {
                        $('#gsap-wp-code-editor').val(response.data.content);
                        self.hasUnsavedChanges = false;
                        self.updateStatus('saved');
                        self.showNotice(response.data.message || 'File reset successfully!', 'success');
                    } else {
                        self.showNotice(response.data || 'Failed to reset file', 'error');
                    }
                },
                error: function() {
                    self.showNotice('An error occurred while resetting', 'error');
                }
            });
        },

        /**
         * Show create version modal
         */
        showCreateVersionModal: function() {
            $('#gsap-wp-create-version-modal').fadeIn(200);
            $('#version-comment').focus();
        },

        /**
         * Create version
         */
        createVersion: function() {
            const self = this;
            const comment = $('#version-comment').val();

            // Save current file first
            this.saveFile();

            setTimeout(function() {
                $.ajax({
                    url: gsapWpAjax.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'gsap_wp_create_version',
                        nonce: gsapWpAjax.nonce,
                        file_path: self.currentFile,
                        comment: comment
                    },
                    success: function(response) {
                        if (response.success) {
                            self.showNotice(response.data.message || 'Version created successfully!', 'success');
                            $('#gsap-wp-create-version-modal').fadeOut(200);
                            $('#version-comment').val('');

                            // Reload version list
                            location.reload();
                        } else {
                            self.showNotice(response.data || 'Failed to create version', 'error');
                        }
                    },
                    error: function() {
                        self.showNotice('An error occurred while creating version', 'error');
                    }
                });
            }, 500);
        },

        /**
         * Load version
         */
        loadVersion: function(versionId) {
            const self = this;

            $.ajax({
                url: gsapWpAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'gsap_wp_load_version',
                    nonce: gsapWpAjax.nonce,
                    version_id: versionId
                },
                success: function(response) {
                    if (response.success) {
                        $('#gsap-wp-code-editor').val(response.data.content);
                        self.hasUnsavedChanges = true;
                        self.updateStatus('modified');
                        self.showNotice('Version loaded. Save to apply changes.', 'info');
                    } else {
                        self.showNotice(response.data || 'Failed to load version', 'error');
                    }
                },
                error: function() {
                    self.showNotice('An error occurred while loading version', 'error');
                }
            });
        },

        /**
         * Restore version
         */
        restoreVersion: function(versionId) {
            if (!confirm('Are you sure you want to restore this version? Current content will be backed up.')) {
                return;
            }

            const self = this;

            $.ajax({
                url: gsapWpAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'gsap_wp_restore_version',
                    nonce: gsapWpAjax.nonce,
                    version_id: versionId
                },
                success: function(response) {
                    if (response.success) {
                        self.showNotice(response.data.message || 'Version restored successfully!', 'success');
                        location.reload();
                    } else {
                        self.showNotice(response.data || 'Failed to restore version', 'error');
                    }
                },
                error: function() {
                    self.showNotice('An error occurred while restoring version', 'error');
                }
            });
        },

        /**
         * Delete version
         */
        deleteVersion: function(versionId) {
            if (!confirm('Are you sure you want to delete this version? This action cannot be undone.')) {
                return;
            }

            const self = this;

            $.ajax({
                url: gsapWpAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'gsap_wp_delete_version',
                    nonce: gsapWpAjax.nonce,
                    version_id: versionId
                },
                success: function(response) {
                    if (response.success) {
                        self.showNotice(response.data.message || 'Version deleted successfully!', 'success');
                        $('.gsap-wp-version-item[data-version-id="' + versionId + '"]').fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        self.showNotice(response.data || 'Failed to delete version', 'error');
                    }
                },
                error: function() {
                    self.showNotice('An error occurred while deleting version', 'error');
                }
            });
        },

        /**
         * Update status display
         */
        updateStatus: function(status) {
            const $statusDisplay = $('.gsap-wp-save-status');

            switch(status) {
                case 'saving':
                    $statusDisplay.text('Saving...').removeClass('gsap-wp-success gsap-wp-error');
                    break;
                case 'saved':
                    $statusDisplay.text('Saved').addClass('gsap-wp-success').removeClass('gsap-wp-error');
                    setTimeout(() => $statusDisplay.text(''), 3000);
                    break;
                case 'modified':
                    $statusDisplay.text('Modified').removeClass('gsap-wp-success gsap-wp-error');
                    break;
                case 'error':
                    $statusDisplay.text('Error').addClass('gsap-wp-error').removeClass('gsap-wp-success');
                    break;
                default:
                    $statusDisplay.text('');
            }
        },

        /**
         * Show notice
         */
        showNotice: function(message, type) {
            type = type || 'info';

            // Remove existing notices
            $('.gsap-wp-editor-notice').remove();

            const $notice = $('<div>')
                .addClass('gsap-wp-editor-notice notice notice-' + type + ' is-dismissible')
                .html('<p><strong>' + message + '</strong></p>')
                .hide();

            // Insert before the editor layout
            $('.gsap-wp-file-editor').prepend($notice);
            $notice.fadeIn(200);

            // Make dismissible
            $notice.append('<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>');

            $notice.on('click', '.notice-dismiss', function() {
                $notice.fadeOut(200, function() {
                    $(this).remove();
                });
            });

            // Auto dismiss after 5 seconds
            setTimeout(function() {
                $notice.fadeOut(200, function() {
                    $(this).remove();
                });
            }, 5000);
        }
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        if ($('#gsap-wp-code-editor').length) {
            GSAPEditor.init();
        }
    });

    // Make GSAPEditor globally available
    window.GSAPEditor = GSAPEditor;

})(jQuery);
