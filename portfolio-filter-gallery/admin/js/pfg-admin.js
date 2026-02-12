/**
 * Portfolio Filter Gallery - Admin JavaScript
 * 
 * @package Portfolio_Filter_Gallery
 * @version 2.0.0
 */

(function($) {
    'use strict';

    /**
     * Admin gallery editor functionality
     */
    const PFGAdmin = {

        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.initTabs();
            this.initSortable();
            this.initColorPickers();
            this.initRangeSliders();
            this.initMediaUploader();
            this.initConditionalFields();
        },

        /**
         * Initialize conditional field visibility
         * Shows/hides settings based on parent toggle state
         */
        initConditionalFields: function() {
            const self = this;
            
            // Process all conditional elements
            $('.pfg-conditional').each(function() {
                self.updateConditionalVisibility($(this));
            });
            
            // Listen for changes on checkboxes that control conditional fields
            $(document).on('change', 'input[type="checkbox"]', function() {
                const inputName = $(this).attr('name');
                if (!inputName) return;
                
                // Find all elements that depend on this checkbox
                $('.pfg-conditional[data-depends="' + inputName + '"]').each(function() {
                    self.updateConditionalVisibility($(this));
                });
            });
        },
        
        /**
         * Update visibility of a conditional element based on its controller
         */
        updateConditionalVisibility: function($element) {
            const depends = $element.data('depends');
            if (!depends) return;
            
            const $controller = $('input[name="' + depends + '"]');
            if (!$controller.length) return;
            
            if ($controller.is(':checked')) {
                $element.slideDown(200);
            } else {
                $element.slideUp(200);
            }
        },


        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Copy shortcode
            $(document).on('click', '.pfg-copy-shortcode', this.copyShortcode);

            // Delete image
            $(document).on('click', '.pfg-image-delete', this.deleteImage);

            // Edit image
            $(document).on('click', '.pfg-image-edit', this.editImage);

            // Toggle settings
            $(document).on('change', '.pfg-toggle input', this.handleToggle);

            // Filter actions
            $(document).on('click', '.pfg-add-filter', this.addFilter);
            $(document).on('click', '.pfg-filter-delete', this.deleteFilter);
            $(document).on('blur', '.pfg-filter-name-input', this.updateFilter);
        },

        /**
         * Initialize tabs
         */
        initTabs: function() {
            $('.pfg-tab').on('click', function(e) {
                e.preventDefault();
                
                const $this = $(this);
                const target = $this.data('tab');

                // Update tab buttons
                $this.siblings().removeClass('active');
                $this.addClass('active');

                // Update tab content
                $this.closest('.pfg-tabs-wrapper').find('.pfg-tab-content').removeClass('active');
                $('#' + target).addClass('active');
            });
        },

        /**
         * Initialize sortable image grid
         */
        initSortable: function() {
            if (!$.fn.sortable) return;

            $('.pfg-image-grid').sortable({
                items: '.pfg-image-item',
                cursor: 'move',
                opacity: 0.8,
                placeholder: 'pfg-image-placeholder',
                tolerance: 'pointer',
                update: function(event, ui) {
                    PFGAdmin.updateImageOrder();
                }
            });

            // Filters list
            $('.pfg-filters-list').sortable({
                items: '.pfg-filter-item',
                handle: '.pfg-filter-drag',
                cursor: 'move',
                opacity: 0.8,
                update: function(event, ui) {
                    PFGAdmin.updateFilterOrder();
                }
            });
        },

        initColorPickers: function() {
            if (!$.fn.wpColorPicker) return;

            $('.pfg-color-input').wpColorPicker({
                change: function(event, ui) {
                    // Update the input value when color is changed via drag
                    $(this).val(ui.color.toCSS()).trigger('change');
                },
                clear: function() {
                    $(this).trigger('change');
                }
            });
        },

        /**
         * Initialize range sliders
         */
        initRangeSliders: function() {
            $('.pfg-range input[type="range"]').on('input', function() {
                const $this = $(this);
                const value = $this.val();
                const suffix = $this.data('suffix') || '';
                
                $this.closest('.pfg-range').find('.pfg-range-value').text(value + suffix);
            });
        },

        /**
         * Initialize media uploader
         */
        initMediaUploader: function() {
            let mediaFrame;

            $(document).on('click', '.pfg-upload-area, .pfg-add-images', function(e) {
                e.preventDefault();

                if (mediaFrame) {
                    mediaFrame.open();
                    return;
                }

                mediaFrame = wp.media({
                    title: pfgAdmin.i18n.selectImages,
                    button: {
                        text: pfgAdmin.i18n.useSelected
                    },
                    multiple: true,
                    library: {
                        type: 'image'
                    }
                });

                mediaFrame.on('select', function() {
                    const selection = mediaFrame.state().get('selection');
                    const imageIds = [];

                    selection.each(function(attachment) {
                        imageIds.push(attachment.id);
                    });

                    if (imageIds.length) {
                        PFGAdmin.uploadImages(imageIds);
                    }
                });

                mediaFrame.open();
            });
            
            // Initialize drag and drop file upload
            this.initDragDropUpload();
        },
        
        /**
         * Initialize drag and drop file upload
         */
        initDragDropUpload: function() {
            const $uploadArea = $('.pfg-upload-area');
            
            if (!$uploadArea.length) return;
            
            // Prevent default behavior for drag events on the whole document
            $(document).on('dragover dragleave drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
            });
            
            // Highlight upload area on drag over
            $uploadArea.on('dragover dragenter', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('pfg-dragover');
            });
            
            // Remove highlight on drag leave
            $uploadArea.on('dragleave dragend drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('pfg-dragover');
            });
            
            // Handle file drop
            $uploadArea.on('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('pfg-dragover');
                
                const files = e.originalEvent.dataTransfer.files;
                
                if (files.length > 0) {
                    PFGAdmin.uploadDroppedFiles(files);
                }
            });
        },
        
        /**
         * Upload dropped files via AJAX
         */
        uploadDroppedFiles: function(files) {
            const $grid = $('.pfg-image-grid');
            const $uploadArea = $('.pfg-upload-area');
            
            // Show loading state
            $uploadArea.addClass('pfg-uploading');
            $uploadArea.find('.pfg-upload-text').text('Uploading...');
            
            // Create FormData object
            const formData = new FormData();
            formData.append('action', 'pfg_upload_dropped_files');
            formData.append('security', pfgAdmin.nonce);
            formData.append('gallery_id', pfgAdmin.galleryId);
            
            // Add all image files
            let imageCount = 0;
            for (let i = 0; i < files.length; i++) {
                if (files[i].type.match(/^image\//)) {
                    formData.append('files[]', files[i]);
                    imageCount++;
                }
            }
            
            if (imageCount === 0) {
                PFGAdmin.showNotice('error', 'Please drop only image files.');
                PFGAdmin.resetUploadArea();
                return;
            }
            
            $.ajax({
                url: pfgAdmin.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $grid.addClass('pfg-loading');
                },
                success: function(response) {
                    if (response.success) {
                        PFGAdmin.refreshImageGrid(response.data.images);
                        PFGAdmin.showNotice('success', response.data.message || 'Images uploaded successfully!');
                    } else {
                        PFGAdmin.showNotice('error', response.data.message || 'Upload failed.');
                    }
                },
                error: function() {
                    PFGAdmin.showNotice('error', 'Upload failed. Please try again.');
                },
                complete: function() {
                    $grid.removeClass('pfg-loading');
                    PFGAdmin.resetUploadArea();
                }
            });
        },
        
        /**
         * Reset upload area after upload
         */
        resetUploadArea: function() {
            const $uploadArea = $('.pfg-upload-area');
            $uploadArea.removeClass('pfg-uploading');
            $uploadArea.find('.pfg-upload-text').text('Drag & drop images here or click to upload');
        },

        /**
         * Upload images via AJAX
         */
        uploadImages: function(imageIds) {
            const $grid = $('.pfg-image-grid');

            $.ajax({
                url: pfgAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pfg_upload_images',
                    security: pfgAdmin.nonce,
                    gallery_id: pfgAdmin.galleryId,
                    image_ids: imageIds
                },
                beforeSend: function() {
                    $grid.addClass('pfg-loading');
                },
                success: function(response) {
                    if (response.success) {
                        PFGAdmin.refreshImageGrid(response.data.images);
                    } else {
                        PFGAdmin.showNotice('error', response.data.message);
                    }
                },
                error: function() {
                    PFGAdmin.showNotice('error', pfgAdmin.i18n.error);
                },
                complete: function() {
                    $grid.removeClass('pfg-loading');
                }
            });
        },

        /**
         * Delete image
         */
        deleteImage: function(e) {
            e.preventDefault();

            if (!confirm(pfgAdmin.i18n.confirmDelete)) {
                return;
            }

            const $item = $(this).closest('.pfg-image-item');
            const imageId = $item.data('id');

            $.ajax({
                url: pfgAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pfg_remove_image',
                    security: pfgAdmin.nonce,
                    gallery_id: pfgAdmin.galleryId,
                    image_id: imageId
                },
                success: function(response) {
                    if (response.success) {
                        // Remove from master array BEFORE removing from DOM
                        if (typeof window.pfgRemoveImageFromMaster === 'function') {
                            window.pfgRemoveImageFromMaster(imageId);
                        }
                        
                        $item.fadeOut(300, function() {
                            $(this).remove();
                            
                            // Mark as structurally modified
                            if (typeof window.pfgMarkImagesModified === 'function') {
                                window.pfgMarkImagesModified();
                            }
                            
                            // Update pagination UI
                            if (typeof window.pfgUpdatePaginationUI === 'function') {
                                window.pfgUpdatePaginationUI();
                            }
                        });
                    } else {
                        PFGAdmin.showNotice('error', response.data.message);
                    }
                }
            });
        },

        /**
         * Edit image (open modal)
         */
        editImage: function(e) {
            e.preventDefault();

            const $item = $(this).closest('.pfg-image-item');
            const imageId = $item.data('id');

            // TODO: Open edit modal with image details
            console.log('Edit image:', imageId);
        },

        /**
         * Update image order
         */
        updateImageOrder: function() {
            const order = [];
            
            $('.pfg-image-item').each(function() {
                order.push($(this).data('id'));
            });

            console.log('PFG Free: updateImageOrder called with ' + order.length + ' images');

            $.ajax({
                url: pfgAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pfg_reorder_images',
                    security: pfgAdmin.nonce,
                    gallery_id: pfgAdmin.galleryId,
                    order: order
                }
            });
            
            // Reorder master array to ensure save works correctly
            if (typeof window.pfgReorderMasterArray === 'function') {
                window.pfgReorderMasterArray(order);
                console.log('PFG Free: Master array reordered via pfgReorderMasterArray');
            } else {
                console.error('PFG Free: pfgReorderMasterArray function NOT FOUND!');
            }
            
            // Mark images as modified for chunked save
            if (typeof window.pfgMarkImagesModified === 'function') {
                window.pfgMarkImagesModified();
                console.log('PFG Free: Images marked as modified');
            }
        },

        /**
         * Copy shortcode to clipboard
         */
        copyShortcode: function(e) {
            e.preventDefault();

            const $btn = $(this);
            const $code = $($btn.data('clipboard-target'));
            const text = $code.text();

            // Try modern clipboard API first, fallback to execCommand
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function() {
                    PFGAdmin.showCopySuccess($btn);
                }).catch(function() {
                    PFGAdmin.fallbackCopy(text, $btn);
                });
            } else {
                PFGAdmin.fallbackCopy(text, $btn);
            }
        },

        /**
         * Fallback copy using execCommand for non-HTTPS
         */
        fallbackCopy: function(text, $btn) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-9999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                PFGAdmin.showCopySuccess($btn);
            } catch (err) {
                console.error('Copy failed:', err);
            }
            
            document.body.removeChild(textArea);
        },

        /**
         * Show copy success feedback
         */
        showCopySuccess: function($btn) {
            const originalHtml = $btn.html();
            $btn.text('Copied!');
            
            setTimeout(function() {
                $btn.html('<span class="dashicons dashicons-clipboard"></span> Copy');
            }, 2000);
        },

        /**
         * Handle toggle change
         */
        handleToggle: function() {
            const $toggle = $(this);
            const $related = $($toggle.data('toggle-related'));

            if ($toggle.is(':checked')) {
                $related.slideDown(200);
            } else {
                $related.slideUp(200);
            }
        },

        /**
         * Add new filter
         */
        addFilter: function(e) {
            e.preventDefault();

            const $input = $('.pfg-new-filter-input');
            const name = $input.val().trim();

            if (!name) {
                $input.focus();
                return;
            }

            $.ajax({
                url: pfgAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pfg_add_filter',
                    security: pfgAdmin.nonce,
                    name: name
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        PFGAdmin.showNotice('error', response.data.message);
                    }
                }
            });
        },

        /**
         * Delete filter
         */
        deleteFilter: function(e) {
            e.preventDefault();

            if (!confirm(pfgAdmin.i18n.confirmDelete)) {
                return;
            }

            const $item = $(this).closest('.pfg-filter-item');
            const filterId = $item.data('id');

            $.ajax({
                url: pfgAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pfg_delete_filter',
                    security: pfgAdmin.nonce,
                    filter_id: filterId
                },
                success: function(response) {
                    if (response.success) {
                        $item.fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        PFGAdmin.showNotice('error', response.data.message);
                    }
                }
            });
        },

        /**
         * Update filter name
         */
        updateFilter: function() {
            const $input = $(this);
            const $item = $input.closest('.pfg-filter-item');
            const filterId = $item.data('id');
            const name = $input.val().trim();

            if (!name) {
                return;
            }

            $.ajax({
                url: pfgAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pfg_update_filter',
                    security: pfgAdmin.nonce,
                    filter_id: filterId,
                    name: name
                }
            });
        },

        /**
         * Update filter order
         */
        updateFilterOrder: function() {
            const order = [];
            
            $('.pfg-filter-item').each(function() {
                order.push($(this).data('id'));
            });

            $.ajax({
                url: pfgAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pfg_reorder_filters',
                    security: pfgAdmin.nonce,
                    order: order
                }
            });
        },

        /**
         * Refresh image grid
         */
        refreshImageGrid: function(images) {
            const $grid = $('.pfg-image-grid');
            
            // Remove "no images" message if present
            $grid.find('.pfg-no-images').remove();
            
            // Get current highest index
            let currentIndex = $('.pfg-image-item').length;
            
            images.forEach(function(image) {
                const html = PFGAdmin.getImageItemHtml(image, currentIndex);
                $grid.append(html);
                currentIndex++;
            });
            
            // Show bulk actions bar if we have images
            if ($('.pfg-image-item').length > 0) {
                $('#pfg-bulk-actions').css('display', 'flex');
            }
            
            // Mark images as modified for chunked save
            if (typeof window.pfgMarkImagesModified === 'function') {
                window.pfgMarkImagesModified();
            }
        },

        /**
         * Get image item HTML
         */
        getImageItemHtml: function(image, index) {
            if (typeof index === 'undefined') {
                index = $('.pfg-image-item').length;
            }
            
            // Handle alt text and description from image data
            var altText = image.alt || '';
            var description = image.description || '';
            
            return `
                <div class="pfg-image-item" data-id="${image.id}" data-index="${index}">
                    <label class="pfg-image-checkbox" style="position: absolute; top: 8px; left: 8px; z-index: 10;">
                        <input type="checkbox" class="pfg-image-select" style="width: 18px; height: 18px; cursor: pointer;">
                    </label>
                    <img src="${image.thumbnail}" alt="${image.title}" class="pfg-image-thumb" loading="lazy">
                    <div class="pfg-image-actions">
                        <button type="button" class="pfg-image-action pfg-image-edit" title="Edit">
                            <span class="dashicons dashicons-edit"></span>
                        </button>
                        <button type="button" class="pfg-image-action pfg-image-delete" title="Delete">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                    <div class="pfg-image-info">
                        <p class="pfg-image-title">${image.title}</p>
                    </div>
                    <input type="hidden" name="pfg_images[${index}][id]" value="${image.id}">
                    <input type="hidden" name="pfg_images[${index}][title]" value="${image.title}">
                    <input type="hidden" name="pfg_images[${index}][alt]" value="${altText}">
                    <input type="hidden" name="pfg_images[${index}][description]" value="${description}">
                    <input type="hidden" name="pfg_images[${index}][link]" value="">
                    <input type="hidden" name="pfg_images[${index}][type]" value="image">
                    <input type="hidden" name="pfg_images[${index}][filters]" value="">
                    <input type="hidden" name="pfg_images[${index}][product_id]" value="">
                    <input type="hidden" name="pfg_images[${index}][product_name]" value="">
                    <input type="hidden" name="pfg_images[${index}][original_id]" value="${image.id}">
                </div>
            `;
        },

        /**
         * Show admin notice
         */
        showNotice: function(type, message) {
            const $notice = $(`
                <div class="pfg-notice pfg-notice-${type}">
                    <span class="pfg-notice-content">${message}</span>
                </div>
            `);

            $('.pfg-admin-wrap').prepend($notice);

            setTimeout(function() {
                $notice.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        PFGAdmin.init();
    });

    // Expose to global scope
    window.PFGAdmin = PFGAdmin;

})(jQuery);
