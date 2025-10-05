/**
 * Version Control JavaScript for GSAP for WordPress
 *
 * @package GSAP_For_WordPress
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * GSAP Version Control Object
     */
    const GSAPVersionControl = {

        /**
         * Current file path
         */
        currentFile: '',

        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            const self = this;

            // Create version
            $('.gsap-wp-create-version-btn').on('click', function(e) {
                e.preventDefault();
                self.currentFile = $(this).data('file');
                self.showCreateVersionDialog();
            });

            // View version
            $(document).on('click', '.gsap-wp-view-version', function(e) {
                e.preventDefault();
                const versionId = $(this).data('version-id');
                self.viewVersion(versionId);
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

            // Compare versions
            $('.gsap-wp-compare-versions').on('click', function(e) {
                e.preventDefault();
                self.compareVersions();
            });

            // Export versions
            $('.gsap-wp-export-versions').on('click', function(e) {
                e.preventDefault();
                self.exportVersions();
            });

            // Load more versions
            $('.gsap-wp-load-more-versions').on('click', function(e) {
                e.preventDefault();
                self.loadMoreVersions();
            });
        },

        /**
         * Show create version dialog
         */
        showCreateVersionDialog: function() {
            const comment = prompt('Enter a description for this version (optional):');

            if (comment === null) {
                return; // User cancelled
            }

            this.createVersion(comment);
        },

        /**
         * Create version
         */
        createVersion: function(comment) {
            const self = this;

            $.ajax({
                url: gsapWpAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'gsap_wp_create_version',
                    nonce: gsapWpAjax.nonce,
                    file_path: this.currentFile,
                    comment: comment || ''
                },
                beforeSend: function() {
                    self.showLoading();
                },
                success: function(response) {
                    self.hideLoading();

                    if (response.success) {
                        self.showNotice(response.data.message || 'Version created successfully!', 'success');

                        // Reload version list
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        self.showNotice(response.data || 'Failed to create version', 'error');
                    }
                },
                error: function() {
                    self.hideLoading();
                    self.showNotice('An error occurred while creating version', 'error');
                }
            });
        },

        /**
         * View version
         */
        viewVersion: function(versionId) {
            const self = this;

            $.ajax({
                url: gsapWpAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'gsap_wp_load_version',
                    nonce: gsapWpAjax.nonce,
                    version_id: versionId
                },
                beforeSend: function() {
                    self.showLoading();
                },
                success: function(response) {
                    self.hideLoading();

                    if (response.success) {
                        // Show version in modal or editor
                        self.displayVersionContent(response.data);
                    } else {
                        self.showNotice(response.data || 'Failed to load version', 'error');
                    }
                },
                error: function() {
                    self.hideLoading();
                    self.showNotice('An error occurred while loading version', 'error');
                }
            });
        },

        /**
         * Display version content
         */
        displayVersionContent: function(data) {
            // If editor exists, load into it
            if ($('#gsap-wp-code-editor').length && typeof GSAPEditor !== 'undefined') {
                $('#gsap-wp-code-editor').val(data.content);
                this.showNotice('Version loaded into editor. Save to apply changes.', 'info');
            } else {
                // Otherwise show in a modal
                const modalHtml = `
                    <div class="gsap-wp-modal" id="gsap-wp-version-view-modal">
                        <div class="gsap-wp-modal-content" style="max-width: 800px;">
                            <div class="gsap-wp-modal-header">
                                <h3>Version ${data.version.version_number}</h3>
                                <button type="button" class="gsap-wp-modal-close">&times;</button>
                            </div>
                            <div class="gsap-wp-modal-body">
                                <div class="gsap-wp-version-meta" style="margin-bottom: 15px;">
                                    <p><strong>Created:</strong> ${data.version.created_at}</p>
                                    ${data.version.comment ? '<p><strong>Comment:</strong> ' + data.version.comment + '</p>' : ''}
                                    ${data.version.author_name ? '<p><strong>By:</strong> ' + data.version.author_name + '</p>' : ''}
                                </div>
                                <pre style="background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 6px; max-height: 400px; overflow-y: auto; font-size: 12px;">${this.escapeHtml(data.content)}</pre>
                            </div>
                        </div>
                    </div>
                `;

                $('body').append(modalHtml);
                $('#gsap-wp-version-view-modal').fadeIn(200);

                // Bind close event
                $('#gsap-wp-version-view-modal .gsap-wp-modal-close, #gsap-wp-version-view-modal').on('click', function(e) {
                    if ($(e.target).hasClass('gsap-wp-modal') || $(e.target).hasClass('gsap-wp-modal-close')) {
                        $('#gsap-wp-version-view-modal').fadeOut(200, function() {
                            $(this).remove();
                        });
                    }
                });
            }
        },

        /**
         * Restore version
         */
        restoreVersion: function(versionId) {
            if (!confirm('Are you sure you want to restore this version? The current content will be backed up automatically.')) {
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
                beforeSend: function() {
                    self.showLoading();
                },
                success: function(response) {
                    self.hideLoading();

                    if (response.success) {
                        self.showNotice(response.data.message || 'Version restored successfully!', 'success');

                        // Reload page
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        self.showNotice(response.data || 'Failed to restore version', 'error');
                    }
                },
                error: function() {
                    self.hideLoading();
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
                beforeSend: function() {
                    self.showLoading();
                },
                success: function(response) {
                    self.hideLoading();

                    if (response.success) {
                        self.showNotice(response.data.message || 'Version deleted successfully!', 'success');

                        // Remove version item from DOM
                        $('.gsap-wp-version-item[data-version-id="' + versionId + '"]').fadeOut(300, function() {
                            $(this).remove();

                            // Check if no more versions
                            if ($('.gsap-wp-version-item').length === 0) {
                                $('.gsap-wp-version-list-container').html(
                                    '<div class="gsap-wp-no-versions">' +
                                    '<span class="dashicons dashicons-info"></span>' +
                                    '<p>No versions saved yet.</p>' +
                                    '</div>'
                                );
                            }
                        });
                    } else {
                        self.showNotice(response.data || 'Failed to delete version', 'error');
                    }
                },
                error: function() {
                    self.hideLoading();
                    self.showNotice('An error occurred while deleting version', 'error');
                }
            });
        },

        /**
         * Compare versions
         */
        compareVersions: function() {
            const version1 = $('.gsap-wp-version-select-1').val();
            const version2 = $('.gsap-wp-version-select-2').val();

            if (!version1 || !version2) {
                this.showNotice('Please select two versions to compare', 'warning');
                return;
            }

            const self = this;

            $.ajax({
                url: gsapWpAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'gsap_wp_compare_versions',
                    nonce: gsapWpAjax.nonce,
                    version1_id: version1,
                    version2_id: version2
                },
                beforeSend: function() {
                    self.showLoading();
                },
                success: function(response) {
                    self.hideLoading();

                    if (response.success) {
                        self.displayDiff(response.data.diff);
                    } else {
                        self.showNotice(response.data || 'Failed to compare versions', 'error');
                    }
                },
                error: function() {
                    self.hideLoading();
                    self.showNotice('An error occurred while comparing versions', 'error');
                }
            });
        },

        /**
         * Display diff
         */
        displayDiff: function(diff) {
            let diffHtml = '<div class="gsap-wp-diff-viewer">';

            diff.forEach(function(line) {
                let className = 'gsap-wp-diff-' + line.type;
                let content = '';

                if (line.type === 'equal') {
                    content = '<span class="gsap-wp-line-number">' + line.line_number + '</span>' +
                             '<span class="gsap-wp-line-content">' + GSAPVersionControl.escapeHtml(line.line1) + '</span>';
                } else if (line.type === 'delete') {
                    content = '<span class="gsap-wp-line-number">' + line.line_number + '</span>' +
                             '<span class="gsap-wp-line-marker">-</span>' +
                             '<span class="gsap-wp-line-content">' + GSAPVersionControl.escapeHtml(line.line) + '</span>';
                } else if (line.type === 'insert') {
                    content = '<span class="gsap-wp-line-number">' + line.line_number + '</span>' +
                             '<span class="gsap-wp-line-marker">+</span>' +
                             '<span class="gsap-wp-line-content">' + GSAPVersionControl.escapeHtml(line.line) + '</span>';
                }

                diffHtml += '<div class="gsap-wp-diff-line ' + className + '">' + content + '</div>';
            });

            diffHtml += '</div>';

            // Show in modal
            const modalHtml = `
                <div class="gsap-wp-modal" id="gsap-wp-diff-modal">
                    <div class="gsap-wp-modal-content" style="max-width: 900px;">
                        <div class="gsap-wp-modal-header">
                            <h3>Version Comparison</h3>
                            <button type="button" class="gsap-wp-modal-close">&times;</button>
                        </div>
                        <div class="gsap-wp-modal-body">
                            ${diffHtml}
                        </div>
                    </div>
                </div>
            `;

            $('body').append(modalHtml);
            $('#gsap-wp-diff-modal').fadeIn(200);

            // Bind close event
            $('#gsap-wp-diff-modal .gsap-wp-modal-close, #gsap-wp-diff-modal').on('click', function(e) {
                if ($(e.target).hasClass('gsap-wp-modal') || $(e.target).hasClass('gsap-wp-modal-close')) {
                    $('#gsap-wp-diff-modal').fadeOut(200, function() {
                        $(this).remove();
                    });
                }
            });
        },

        /**
         * Export versions
         */
        exportVersions: function() {
            const self = this;

            $.ajax({
                url: gsapWpAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'gsap_wp_export_versions',
                    nonce: gsapWpAjax.nonce,
                    file_path: this.currentFile
                },
                success: function(response) {
                    if (response.success) {
                        // Create download
                        const dataStr = JSON.stringify(response.data.export_data, null, 2);
                        const dataUri = 'data:application/json;charset=utf-8,' + encodeURIComponent(dataStr);

                        const linkElement = document.createElement('a');
                        linkElement.setAttribute('href', dataUri);
                        linkElement.setAttribute('download', response.data.filename);
                        linkElement.click();

                        self.showNotice('Versions exported successfully!', 'success');
                    } else {
                        self.showNotice(response.data || 'Failed to export versions', 'error');
                    }
                },
                error: function() {
                    self.showNotice('An error occurred while exporting versions', 'error');
                }
            });
        },

        /**
         * Load more versions
         */
        loadMoreVersions: function() {
            // This would load additional versions via AJAX
            // Implementation depends on pagination strategy
            this.showNotice('Feature coming soon', 'info');
        },

        /**
         * Show loading indicator
         */
        showLoading: function() {
            if ($('.gsap-wp-version-loading').length === 0) {
                $('.gsap-wp-version-list-container').prepend(
                    '<div class="gsap-wp-version-loading">Loading...</div>'
                );
            }
        },

        /**
         * Hide loading indicator
         */
        hideLoading: function() {
            $('.gsap-wp-version-loading').remove();
        },

        /**
         * Show notice
         */
        showNotice: function(message, type) {
            type = type || 'info';

            const $notice = $('<div>')
                .addClass('notice notice-' + type + ' is-dismissible')
                .html('<p>' + message + '</p>')
                .hide();

            $('.wrap h1').first().after($notice);
            $notice.fadeIn(200);

            // Auto dismiss after 5 seconds
            setTimeout(function() {
                $notice.fadeOut(200, function() {
                    $(this).remove();
                });
            }, 5000);
        },

        /**
         * Escape HTML
         */
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        if ($('.gsap-wp-version-control-panel').length) {
            GSAPVersionControl.init();
        }
    });

    // Make GSAPVersionControl globally available
    window.GSAPVersionControl = GSAPVersionControl;

})(jQuery);
