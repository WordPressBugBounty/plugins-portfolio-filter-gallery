/**
 * Portfolio Filter Gallery - Gutenberg Block
 * 
 * @package Portfolio_Filter_Gallery
 * @version 2.0.0
 */

(function() {
    'use strict';

    // Wait for DOM and WordPress to be ready
    if (typeof wp === 'undefined' || typeof wp.blocks === 'undefined') {
        return;
    }

    // Check if block data exists
    if (typeof pfgBlockData === 'undefined') {
        return;
    }

    var blocks = wp.blocks;
    var element = wp.element;
    var blockEditor = wp.blockEditor;
    var components = wp.components;
    var i18n = wp.i18n;

    var registerBlockType = blocks.registerBlockType;
    var createElement = element.createElement;
    var Fragment = element.Fragment;
    var InspectorControls = blockEditor.InspectorControls;
    var useBlockProps = blockEditor.useBlockProps;
    var PanelBody = components.PanelBody;
    var SelectControl = components.SelectControl;
    var ToggleControl = components.ToggleControl;
    var RangeControl = components.RangeControl;
    var Button = components.Button;
    var Placeholder = components.Placeholder;
    var __ = i18n.__;

    // Get data passed from PHP
    var data = pfgBlockData;
    var galleries = data.galleries || [];
    var strings = data.strings || {};

    // Block icon
    var blockIcon = createElement('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        viewBox: '0 0 24 24',
        width: 24,
        height: 24
    }, createElement('path', {
        fill: 'currentColor',
        d: 'M3 3v8h8V3H3zm6 6H5V5h4v4zm-6 4v8h8v-8H3zm6 6H5v-4h4v4zm4-16v8h8V3h-8zm6 6h-4V5h4v4zm-6 4v8h8v-8h-8zm6 6h-4v-4h4v4z'
    }));

    // Gallery options for dropdown
    var galleryOptions = [
        { label: strings.selectGallery || 'Select a Gallery', value: 0 }
    ];
    
    for (var i = 0; i < galleries.length; i++) {
        galleryOptions.push({
            label: galleries[i].title,
            value: galleries[i].id
        });
    }

    // Find gallery by id
    function findGallery(id) {
        for (var i = 0; i < galleries.length; i++) {
            if (galleries[i].id === id) {
                return galleries[i];
            }
        }
        return null;
    }

    /**
     * Register the block
     */
    registerBlockType('portfolio-filter-gallery/gallery', {
        title: strings.title || 'Portfolio Filter Gallery',
        description: strings.description || 'Display a filterable portfolio gallery.',
        icon: blockIcon,
        category: 'widgets',
        keywords: ['gallery', 'portfolio', 'filter', 'masonry', 'grid'],
        supports: {
            align: ['wide', 'full'],
            html: false
        },
        attributes: {
            galleryId: {
                type: 'number',
                default: 0
            },
            showTitle: {
                type: 'boolean',
                default: false
            },
            columnsOverride: {
                type: 'number',
                default: 0  // 0 = use gallery default
            },
            hoverEffectOverride: {
                type: 'string',
                default: ''  // '' = use gallery default
            },
            showFiltersOverride: {
                type: 'string',
                default: ''  // '' = use gallery default, 'true'/'false' = override
            }
        },

        /**
         * Edit component
         */
        edit: function(props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;
            var galleryId = attributes.galleryId;
            var showTitle = attributes.showTitle;
            var columnsOverride = attributes.columnsOverride;
            var hoverEffectOverride = attributes.hoverEffectOverride;
            var showFiltersOverride = attributes.showFiltersOverride;
            var blockProps = useBlockProps();
            
            // Hover effect options
            var hoverOptions = [
                { label: __('Use Gallery Default', 'portfolio-filter-gallery'), value: '' },
                { label: __('None', 'portfolio-filter-gallery'), value: 'none' },
                { label: __('Zoom', 'portfolio-filter-gallery'), value: 'zoom' },
                { label: __('Fade', 'portfolio-filter-gallery'), value: 'fade' },
                { label: __('Slide Up', 'portfolio-filter-gallery'), value: 'slide-up' },
                { label: __('Blur', 'portfolio-filter-gallery'), value: 'blur' }
            ];
            
            // Show filters options
            var showFiltersOptions = [
                { label: __('Use Gallery Default', 'portfolio-filter-gallery'), value: '' },
                { label: __('Show Filters', 'portfolio-filter-gallery'), value: 'true' },
                { label: __('Hide Filters', 'portfolio-filter-gallery'), value: 'false' }
            ];
            
            // Find selected gallery
            var selectedGallery = findGallery(galleryId);

            // If no gallery selected, show placeholder
            if (!galleryId) {
                return createElement(Fragment, null,
                    createElement(InspectorControls, null,
                        createElement(PanelBody, { title: __('Gallery Settings', 'portfolio-filter-gallery') },
                            createElement(SelectControl, {
                                label: strings.selectGallery,
                                value: galleryId,
                                options: galleryOptions,
                                onChange: function(value) {
                                    setAttributes({ galleryId: parseInt(value, 10) });
                                }
                            })
                        )
                    ),
                    createElement('div', blockProps,
                        createElement(Placeholder, {
                            icon: blockIcon,
                            label: strings.title,
                            instructions: galleries.length > 0 
                                ? strings.selectGallery 
                                : strings.noGalleries
                        },
                            galleries.length > 0 
                                ? createElement(SelectControl, {
                                    value: galleryId,
                                    options: galleryOptions,
                                    onChange: function(value) {
                                        setAttributes({ galleryId: parseInt(value, 10) });
                                    }
                                })
                                : createElement(Button, {
                                    variant: 'primary',
                                    href: 'post-new.php?post_type=awl_filter_gallery',
                                    target: '_blank'
                                }, strings.createGallery)
                        )
                    )
                );
            }

            // Gallery selected - show preview
            return createElement(Fragment, null,
                createElement(InspectorControls, null,
                    createElement(PanelBody, { title: __('Gallery Settings', 'portfolio-filter-gallery') },
                        createElement(SelectControl, {
                            label: strings.selectGallery,
                            value: galleryId,
                            options: galleryOptions,
                            onChange: function(value) {
                                setAttributes({ galleryId: parseInt(value, 10) });
                            }
                        }),
                        createElement(ToggleControl, {
                            label: strings.showTitle,
                            checked: showTitle,
                            onChange: function(value) {
                                setAttributes({ showTitle: value });
                            }
                        }),
                        createElement(Button, {
                            variant: 'secondary',
                            href: 'post.php?post=' + galleryId + '&action=edit',
                            target: '_blank',
                            style: { marginTop: '10px' }
                        }, strings.editGallery)
                    ),
                    // Quick Settings Panel
                    createElement(PanelBody, { 
                        title: __('Quick Settings', 'portfolio-filter-gallery'),
                        initialOpen: false
                    },
                        createElement('p', { 
                            style: { fontSize: '12px', color: '#757575', marginTop: 0 } 
                        }, __('Override gallery defaults for this block only.', 'portfolio-filter-gallery')),
                        createElement(RangeControl, {
                            label: __('Columns', 'portfolio-filter-gallery'),
                            value: columnsOverride,
                            onChange: function(value) {
                                setAttributes({ columnsOverride: value });
                            },
                            min: 0,
                            max: 6,
                            help: columnsOverride === 0 ? __('Using gallery default', 'portfolio-filter-gallery') : ''
                        }),
                        createElement(SelectControl, {
                            label: __('Hover Effect', 'portfolio-filter-gallery'),
                            value: hoverEffectOverride,
                            options: hoverOptions,
                            onChange: function(value) {
                                setAttributes({ hoverEffectOverride: value });
                            }
                        }),
                        createElement(SelectControl, {
                            label: __('Show Filters', 'portfolio-filter-gallery'),
                            value: showFiltersOverride,
                            options: showFiltersOptions,
                            onChange: function(value) {
                                setAttributes({ showFiltersOverride: value });
                            }
                        })
                    )
                ),
                createElement('div', Object.assign({}, blockProps, {
                    className: (blockProps.className || '') + ' pfg-block-preview'
                }),
                    createElement('div', { className: 'pfg-block-header' },
                        blockIcon,
                        createElement('span', null, strings.title)
                    ),
                    showTitle && selectedGallery && createElement('h3', { 
                        className: 'pfg-block-gallery-title' 
                    }, selectedGallery.title),
                    createElement('div', { className: 'pfg-block-gallery-preview' },
                        createElement('div', { className: 'pfg-block-gallery-icon' },
                            createElement('span', { className: 'dashicons dashicons-format-gallery' })
                        ),
                        createElement('p', null, 
                            selectedGallery 
                                ? selectedGallery.title 
                                : __('Gallery', 'portfolio-filter-gallery')
                        ),
                        createElement('small', null, strings.previewNote)
                    )
                )
            );
        },

        /**
         * Save - rendered on server
         */
        save: function() {
            return null; // Server-side rendering
        }
    });

})();
