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
            $('.gsap-wp-settings-form').on('submit', function(e) {
                const $checkedLibraries = $('.gsap-wp-library-checkbox:checked').not(':disabled');

                if ($checkedLibraries.length === 0) {
                    alert(gsapWpAjax.strings.no_libraries_selected);
                    e.preventDefault();
                    return false;
                }

                // Show loading state
                $(this).find('.button-primary').prop('disabled', true)
                    .text(gsapWpAjax.strings.saving);
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

            const $notice = $('<div>')
                .addClass('notice notice-' + type + ' is-dismissible')
                .html('<p>' + message + '</p>')
                .hide();

            $('.wrap h1').after($notice);
            $notice.fadeIn(200);

            // Auto dismiss after 5 seconds
            setTimeout(function() {
                $notice.fadeOut(200, function() {
                    $(this).remove();
                });
            }, 5000);
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
    });

    // Make GSAPAdmin globally available
    window.GSAPAdmin = GSAPAdmin;

})(jQuery);
