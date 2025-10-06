/**
 * Admin JavaScript for GSAP for WordPress
 *
 * @package GSAP_For_WordPress
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * GSAP WordPress Admin Object
     */
    const GSAPAdmin = {

        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.initDependencyManagement();
            this.initConditionalLoading();
            this.initFormValidation();
            this.initModals();
            this.initTooltips();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Settings form
            $('#gsap-wp-reset-settings').on('click', this.resetSettings);

            // Library checkboxes
            $('.gsap-wp-library-checkbox').on('change', this.handleLibraryChange);

            // Conditional loading toggle
            $('#gsap-wp-conditional-enabled').on('change', this.toggleConditionalOptions);

            // Tab navigation
            $('.nav-tab').on('click', this.handleTabClick);

            // Modal close
            $('.gsap-wp-modal-close, .gsap-wp-modal').on('click', this.closeModal);
            $('.gsap-wp-modal-content').on('click', function(e) {
                e.stopPropagation();
            });
        },

        /**
         * Initialize dependency management
         */
        initDependencyManagement: function() {
            const self = this;

            // GSAP Core is always required
            const $gsapCore = $('input[name="gsap_libraries[gsap_core]"]');
            if ($gsapCore.length) {
                $gsapCore.prop('checked', true).prop('disabled', true);
            }

            // Handle library dependencies
            $('.gsap-wp-library-checkbox').each(function() {
                const $checkbox = $(this);
                const dependencies = $checkbox.data('dependencies');

                if (dependencies) {
                    $checkbox.on('change', function() {
                        if ($(this).is(':checked')) {
                            self.enableDependencies(dependencies);
                        }
                    });
                }
            });

            // Check dependencies on page load
            this.validateAllDependencies();
        },

        /**
         * Enable dependencies for a library
         */
        enableDependencies: function(dependencies) {
            if (!dependencies) return;

            const depArray = dependencies.toString().split(',');
            depArray.forEach(function(dep) {
                const $depCheckbox = $('input[name="gsap_libraries[' + dep.trim() + ']"]');
                if ($depCheckbox.length && !$depCheckbox.is(':checked')) {
                    $depCheckbox.prop('checked', true);

                    // Visual feedback
                    $depCheckbox.closest('.gsap-wp-library-option')
                        .addClass('gsap-wp-fade-in')
                        .css('border-color', '#059669');

                    setTimeout(function() {
                        $depCheckbox.closest('.gsap-wp-library-option')
                            .css('border-color', '');
                    }, 1000);
                }
            });
        },

        /**
         * Validate all dependencies
         */
        validateAllDependencies: function() {
            const self = this;
            $('.gsap-wp-library-checkbox:checked').each(function() {
                const dependencies = $(this).data('dependencies');
                if (dependencies) {
                    self.enableDependencies(dependencies);
                }
            });
        },

        /**
         * Handle library checkbox change
         */
        handleLibraryChange: function(e) {
            const $checkbox = $(this);
            const $option = $checkbox.closest('.gsap-wp-library-option');
            const isPremium = $option.hasClass('premium');

            if ($checkbox.is(':checked') && isPremium) {
                const libraryName = $option.find('.gsap-wp-library-name').text();

                if (!confirm(gsapWpAjax.strings.premium_warning.replace('%s', libraryName))) {
                    $checkbox.prop('checked', false);
                    e.preventDefault();
                    return false;
                }
            }
        },

        /**
         * Initialize conditional loading
         */
        initConditionalLoading: function() {
            this.toggleConditionalOptions();
        },

        /**
         * Toggle conditional loading options
         */
        toggleConditionalOptions: function() {
            const isEnabled = $('#gsap-wp-conditional-enabled').is(':checked');
            $('.gsap-wp-conditional-row').toggle(isEnabled);
        },

        /**
         * Initialize form validation
         */
        initFormValidation: function() {
            const self = this;

            $('.gsap-wp-settings-form').on('submit', function(e) {
                e.preventDefault(); // Prevent default form submission

                const $form = $(this);
                const $submitButton = $form.find('button[name="submit_settings"]');
                const $checkedLibraries = $('.gsap-wp-library-checkbox:checked').not(':disabled');

                if ($checkedLibraries.length === 0) {
                    alert(gsapWpAjax.strings.no_libraries_selected);
                    return false;
                }

                // Show loading state
                $submitButton.prop('disabled', true).text(gsapWpAjax.strings.saving);

                // Submit via AJAX
                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: $form.serialize(),
                    success: function(response) {
                        // Form was submitted successfully, show success message
                        self.showNotice(gsapWpAjax.strings.saved, 'success');

                        $submitButton.prop('disabled', false).text('Save Settings');

                        // Reload page after 1 second to reflect changes
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    },
                    error: function() {
                        self.showNotice(gsapWpAjax.strings.error, 'error');
                        $submitButton.prop('disabled', false).text('Save Settings');
                    }
                });
            });
        },

        /**
         * Reset settings
         */
        resetSettings: function(e) {
            e.preventDefault();

            if (!confirm(gsapWpAjax.strings.confirm_reset)) {
                return;
            }

            // Uncheck all non-required libraries
            $('.gsap-wp-library-checkbox').not(':disabled').prop('checked', false);

            // Check default libraries
            $('input[name="gsap_libraries[gsap_core]"]').prop('checked', true);
            $('input[name="gsap_libraries[css_plugin]"]').prop('checked', true);

            // Reset performance settings
            $('input[name="performance[minified]"]').prop('checked', true);
            $('input[name="performance[load_in_footer]"]').prop('checked', true);
            $('input[name="performance[compression]"]').prop('checked', true);
            $('input[name="performance[cache_busting]"]').prop('checked', true);
            $('input[name="performance[auto_merge]"]').prop('checked', false);
            $('input[name="performance[use_cdn]"]').prop('checked', false);

            // Reset conditional loading
            $('#gsap-wp-conditional-enabled').prop('checked', false);
            GSAPAdmin.toggleConditionalOptions();

            // Show success message
            GSAPAdmin.showNotice('Settings reset to defaults.', 'success');
        },

        /**
         * Handle tab clicks
         */
        handleTabClick: function(e) {
            const $tab = $(this);
            const tabId = $tab.attr('href');

            if (tabId && tabId.indexOf('#') === 0) {
                e.preventDefault();

                // Update tab states
                $('.nav-tab').removeClass('nav-tab-active');
                $tab.addClass('nav-tab-active');

                // Update content
                $('.gsap-wp-tab-content').hide();
                $(tabId).show();
            }
        },

        /**
         * Initialize modals
         */
        initModals: function() {
            // Modal triggers can be added here
        },

        /**
         * Close modal
         */
        closeModal: function(e) {
            if ($(e.target).hasClass('gsap-wp-modal') ||
                $(e.target).hasClass('gsap-wp-modal-close')) {
                $('.gsap-wp-modal').fadeOut(200);
            }
        },

        /**
         * Show modal
         */
        showModal: function(modalId) {
            $('#' + modalId).fadeIn(200);
        },

        /**
         * Initialize tooltips
         */
        initTooltips: function() {
            // Add tooltips for library descriptions
            $('.gsap-wp-library-option').each(function() {
                const $option = $(this);
                const description = $option.find('.gsap-wp-library-description').text();

                if (description) {
                    $option.attr('title', description);
                }
            });
        },

        /**
         * Show admin notice
         */
        showNotice: function(message, type) {
            type = type || 'info';

            // Remove any existing notices first
            $('.gsap-wp-settings-notice').remove();

            const $notice = $('<div>')
                .addClass('notice notice-' + type + ' is-dismissible gsap-wp-settings-notice')
                .html('<p><strong>' + message + '</strong></p>')
                .hide();

            // Insert after the h1 heading
            if ($('.gsap-wp-admin .wp-heading-inline').length) {
                $('.gsap-wp-admin .wp-heading-inline').after($notice);
            } else {
                $('.wrap h1').first().after($notice);
            }

            $notice.fadeIn(200);

            // Add dismiss button functionality
            $notice.append('<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>');

            $notice.on('click', '.notice-dismiss', function() {
                $notice.fadeOut(200, function() {
                    $(this).remove();
                });
            });

            // Auto dismiss after 5 seconds for success messages
            if (type === 'success') {
                setTimeout(function() {
                    if ($notice.is(':visible')) {
                        $notice.fadeOut(200, function() {
                            $(this).remove();
                        });
                    }
                }, 5000);
            }
        },

        /**
         * AJAX helper
         */
        ajax: function(action, data, success, error) {
            data = data || {};
            data.action = action;
            data.nonce = gsapWpAjax.nonce;

            $.ajax({
                url: gsapWpAjax.ajaxurl,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        if (typeof success === 'function') {
                            success(response.data);
                        }
                    } else {
                        if (typeof error === 'function') {
                            error(response.data);
                        } else {
                            GSAPAdmin.showNotice(response.data || gsapWpAjax.strings.error, 'error');
                        }
                    }
                },
                error: function(xhr, status, err) {
                    if (typeof error === 'function') {
                        error(err);
                    } else {
                        GSAPAdmin.showNotice(gsapWpAjax.strings.error, 'error');
                    }
                }
            });
        },

        /**
         * Calculate total library size
         */
        calculateTotalSize: function() {
            let totalSize = 0;
            const $sizeDisplay = $('#gsap-wp-total-size');

            $('.gsap-wp-library-checkbox:checked').each(function() {
                const $badge = $(this).closest('.gsap-wp-library-option').find('.gsap-wp-badge.size');
                const sizeText = $badge.text();
                const sizeKB = parseFloat(sizeText);

                if (!isNaN(sizeKB)) {
                    totalSize += sizeKB;
                }
            });

            if ($sizeDisplay.length) {
                $sizeDisplay.text(totalSize.toFixed(1) + 'KB');
            }
        },

        /**
         * Export settings
         */
        exportSettings: function() {
            const settings = {
                libraries: {},
                performance: {},
                conditional_loading: {},
                export_date: new Date().toISOString(),
                plugin_version: gsapWpAjax.version || '1.0.0'
            };

            // Collect library settings
            $('.gsap-wp-library-checkbox').each(function() {
                const name = $(this).attr('name').match(/\[(.*?)\]/)[1];
                settings.libraries[name] = $(this).is(':checked');
            });

            // Collect performance settings
            $('input[name^="performance"]').each(function() {
                const name = $(this).attr('name').match(/\[(.*?)\]/)[1];
                settings.performance[name] = $(this).is(':checked');
            });

            // Create download
            const dataStr = JSON.stringify(settings, null, 2);
            const dataUri = 'data:application/json;charset=utf-8,' + encodeURIComponent(dataStr);
            const exportFileDefaultName = 'gsap-wordpress-settings-' + new Date().toISOString().slice(0, 10) + '.json';

            const linkElement = document.createElement('a');
            linkElement.setAttribute('href', dataUri);
            linkElement.setAttribute('download', exportFileDefaultName);
            linkElement.click();
        },

        /**
         * Import settings
         */
        importSettings: function(fileInput) {
            const file = fileInput.files[0];

            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const settings = JSON.parse(e.target.result);

                    // Apply library settings
                    if (settings.libraries) {
                        Object.keys(settings.libraries).forEach(function(key) {
                            const $checkbox = $('input[name="gsap_libraries[' + key + ']"]');
                            if ($checkbox.length) {
                                $checkbox.prop('checked', settings.libraries[key]);
                            }
                        });
                    }

                    // Apply performance settings
                    if (settings.performance) {
                        Object.keys(settings.performance).forEach(function(key) {
                            const $checkbox = $('input[name="performance[' + key + ']"]');
                            if ($checkbox.length) {
                                $checkbox.prop('checked', settings.performance[key]);
                            }
                        });
                    }

                    GSAPAdmin.showNotice('Settings imported successfully!', 'success');
                    GSAPAdmin.validateAllDependencies();
                } catch (err) {
                    GSAPAdmin.showNotice('Invalid settings file.', 'error');
                }
            };

            reader.readAsText(file);
        },

        /**
         * Load restore history
         */
        loadRestoreHistory: function(filePath, append) {
            append = append || false;

            GSAPAdmin.ajax('gsap_wp_get_restore_history', {
                file_path: filePath,
                limit: 20
            }, function(data) {
                if (data.history && data.history.length > 0) {
                    const $timeline = $('.gsap-wp-restore-timeline');
                    const $container = $('.gsap-wp-restore-history-container');

                    if (!append) {
                        $timeline.empty();
                    }

                    // Render restore history items
                    data.history.forEach(function(restore) {
                        const $item = GSAPAdmin.renderRestoreItem(restore);
                        $timeline.append($item);
                    });

                    // Show/hide "Load More" button
                    if (data.history.length < 20) {
                        $('.gsap-wp-restore-pagination').hide();
                    } else {
                        $('.gsap-wp-restore-pagination').show();
                    }

                    // Hide "no history" message
                    $('.gsap-wp-no-restore-history').hide();
                    $timeline.show();
                } else {
                    $('.gsap-wp-no-restore-history').show();
                    $('.gsap-wp-restore-timeline').hide();
                }
            });
        },

        /**
         * Render a restore history item
         */
        renderRestoreItem: function(restore) {
            const $item = $('<div>').addClass('gsap-wp-restore-item').attr('data-restore-id', restore.id);
            const $marker = $('<div>').addClass('gsap-wp-restore-marker');
            const $content = $('<div>').addClass('gsap-wp-restore-content');

            // Header
            const $header = $('<div>').addClass('gsap-wp-restore-header');
            let actionText = '';
            if (restore.previous_version_number) {
                actionText = 'Restored v' + restore.previous_version_number + ' → v' + restore.restored_version_number;
            } else {
                actionText = 'Restored v' + restore.restored_version_number;
            }
            const $action = $('<strong>').addClass('gsap-wp-restore-action').text(actionText);
            const $badge = $('<span>')
                .addClass('gsap-wp-restore-badge gsap-wp-badge ' + restore.restore_type)
                .text(restore.restore_type.charAt(0).toUpperCase() + restore.restore_type.slice(1));

            $header.append($action, $badge);

            // Meta
            const $meta = $('<div>').addClass('gsap-wp-restore-meta');
            const timeAgo = GSAPAdmin.timeAgo(new Date(restore.restored_at));
            $meta.append(
                $('<span>').addClass('gsap-wp-restore-date').text(timeAgo + ' ago'),
                $('<span>').addClass('gsap-wp-restore-separator').text('•'),
                $('<span>').addClass('gsap-wp-restore-author').text('by ' + restore.restored_by_name)
            );

            $content.append($header, $meta);

            // Notes
            if (restore.notes) {
                const $notes = $('<div>').addClass('gsap-wp-restore-notes');
                $notes.append(
                    $('<span>').addClass('dashicons dashicons-format-quote'),
                    $('<span>').text(restore.notes)
                );
                $content.append($notes);
            }

            // Version comment
            if (restore.restored_version_comment) {
                const $comment = $('<div>')
                    .addClass('gsap-wp-restore-version-comment')
                    .text(restore.restored_version_comment);
                $content.append($comment);
            }

            $item.append($marker, $content);
            return $item;
        },

        /**
         * Simple time ago calculator
         */
        timeAgo: function(date) {
            const seconds = Math.floor((new Date() - date) / 1000);

            const intervals = {
                year: 31536000,
                month: 2592000,
                week: 604800,
                day: 86400,
                hour: 3600,
                minute: 60
            };

            for (const [name, secondsInInterval] of Object.entries(intervals)) {
                const interval = Math.floor(seconds / secondsInInterval);
                if (interval >= 1) {
                    return interval + ' ' + name + (interval > 1 ? 's' : '');
                }
            }

            return 'just now';
        }
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        GSAPAdmin.init();

        // Update total size on library change
        $('.gsap-wp-library-checkbox').on('change', function() {
            GSAPAdmin.calculateTotalSize();
        });

        // Initial size calculation
        GSAPAdmin.calculateTotalSize();

        // Export/Import buttons (if added to UI)
        $('#gsap-wp-export-settings').on('click', function(e) {
            e.preventDefault();
            GSAPAdmin.exportSettings();
        });

        $('#gsap-wp-import-settings').on('change', function() {
            GSAPAdmin.importSettings(this);
        });

        // Load more restore history
        $(document).on('click', '.gsap-wp-load-more-restores', function(e) {
            e.preventDefault();
            const filePath = $(this).data('file');
            GSAPAdmin.loadRestoreHistory(filePath, true);
        });

        // Initialize restore history if on version control page
        if ($('.gsap-wp-restore-history-container').length) {
            const filePath = $('.gsap-wp-restore-history-container').data('file-path');
            if (filePath) {
                GSAPAdmin.loadRestoreHistory(filePath);
            }
        }
    });

    // Make GSAPAdmin globally available
    window.GSAPAdmin = GSAPAdmin;

    /**
     * Settings page enhancement - Auto-scroll to success notice
     */
    jQuery(document).ready(function($) {
        // Check if we just saved settings
        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('settings-updated') === 'true') {
            // Scroll to top smoothly to show success message
            $('html, body').animate({
                scrollTop: 0
            }, 300);

            // Add visual highlight to notice
            setTimeout(function() {
                $('.gsap-wp-success-notice').addClass('highlighted');
            }, 400);

            // Optional: Log to console for developer feedback
            if (window.console) {
                console.log('%c✓ GSAP Settings Saved', 'color: #00a32a; font-weight: bold; font-size: 14px;');
                console.log('%cSettings have been updated successfully.', 'color: #666;');
            }

            // Optional: Auto-reload to ensure fresh state (uncomment if needed)
            // This ensures all cached data is cleared and fresh settings are loaded
            /*
            setTimeout(function() {
                var cleanUrl = window.location.pathname + '?page=gsap-wordpress&tab=settings';
                window.location.href = cleanUrl;
            }, 2500);
            */
        }
    });

})(jQuery);
