<?php
/**
 * Documentation Page Template.
 *
 * @package    Portfolio_Filter_Gallery
 * @subpackage Portfolio_Filter_Gallery/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$is_premium = class_exists( 'PFG_Features' ) && PFG_Features::is_premium();
?>

<div class="wrap pfg-docs-wrap">
    <h1><?php esc_html_e( 'Portfolio Filter Gallery - Documentation', 'portfolio-filter-gallery' ); ?></h1>
    
    <div class="pfg-docs-container">
        <!-- Sidebar Navigation -->
        <div class="pfg-docs-sidebar">
            <nav class="pfg-docs-nav">
                <a href="#getting-started" class="pfg-doc-link active"><?php esc_html_e( 'Getting Started', 'portfolio-filter-gallery' ); ?></a>
                <a href="#creating-gallery" class="pfg-doc-link"><?php esc_html_e( 'Creating a Gallery', 'portfolio-filter-gallery' ); ?></a>
                <a href="#adding-images" class="pfg-doc-link"><?php esc_html_e( 'Adding Images', 'portfolio-filter-gallery' ); ?></a>
                <a href="#filters" class="pfg-doc-link"><?php esc_html_e( 'Working with Filters', 'portfolio-filter-gallery' ); ?></a>
                <a href="#layout-settings" class="pfg-doc-link"><?php esc_html_e( 'Layout Settings', 'portfolio-filter-gallery' ); ?></a>
                <a href="#lightbox" class="pfg-doc-link"><?php esc_html_e( 'Lightbox Options', 'portfolio-filter-gallery' ); ?></a>
                <a href="#shortcode" class="pfg-doc-link"><?php esc_html_e( 'Using Shortcodes', 'portfolio-filter-gallery' ); ?></a>
                <a href="#hover-effects" class="pfg-doc-link"><?php esc_html_e( 'Hover Effects', 'portfolio-filter-gallery' ); ?></a>
                <?php if ( $is_premium ) : ?>
                <a href="#woocommerce" class="pfg-doc-link pfg-doc-pro"><?php esc_html_e( 'WooCommerce Integration', 'portfolio-filter-gallery' ); ?></a>
                <a href="#video-galleries" class="pfg-doc-link pfg-doc-pro"><?php esc_html_e( 'Video Galleries', 'portfolio-filter-gallery' ); ?></a>
                <a href="#pagination" class="pfg-doc-link pfg-doc-pro"><?php esc_html_e( 'Pagination & Load More', 'portfolio-filter-gallery' ); ?></a>
                <a href="#analytics" class="pfg-doc-link pfg-doc-pro"><?php esc_html_e( 'Analytics', 'portfolio-filter-gallery' ); ?></a>
                <a href="#deep-linking" class="pfg-doc-link pfg-doc-pro"><?php esc_html_e( 'URL Deep Linking', 'portfolio-filter-gallery' ); ?></a>
                <?php endif; ?>
                <a href="#faq" class="pfg-doc-link"><?php esc_html_e( 'FAQ', 'portfolio-filter-gallery' ); ?></a>
                <a href="#support" class="pfg-doc-link"><?php esc_html_e( 'Support', 'portfolio-filter-gallery' ); ?></a>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="pfg-docs-content">
            
            <!-- Getting Started -->
            <section id="getting-started" class="pfg-doc-section">
                <h2><?php esc_html_e( 'Getting Started', 'portfolio-filter-gallery' ); ?></h2>
                <p><?php esc_html_e( 'Welcome to Portfolio Filter Gallery! This plugin allows you to create beautiful, filterable image galleries for your WordPress site.', 'portfolio-filter-gallery' ); ?></p>
                
                <h3><?php esc_html_e( 'Quick Start Guide', 'portfolio-filter-gallery' ); ?></h3>
                <ol class="pfg-steps">
                    <li>
                        <strong><?php esc_html_e( 'Create Filters', 'portfolio-filter-gallery' ); ?></strong>
                        <p><?php esc_html_e( 'Go to Portfolio Gallery → Filters to create categories for your images (e.g., Nature, Architecture, People).', 'portfolio-filter-gallery' ); ?></p>
                    </li>
                    <li>
                        <strong><?php esc_html_e( 'Create a Gallery', 'portfolio-filter-gallery' ); ?></strong>
                        <p><?php esc_html_e( 'Go to Portfolio Gallery → Add New Gallery to create your first gallery.', 'portfolio-filter-gallery' ); ?></p>
                    </li>
                    <li>
                        <strong><?php esc_html_e( 'Add Images', 'portfolio-filter-gallery' ); ?></strong>
                        <p><?php esc_html_e( 'Click "Add Images" to select images from your Media Library or upload new ones.', 'portfolio-filter-gallery' ); ?></p>
                    </li>
                    <li>
                        <strong><?php esc_html_e( 'Assign Filters', 'portfolio-filter-gallery' ); ?></strong>
                        <p><?php esc_html_e( 'Click the edit icon on each image to assign filters/categories to it.', 'portfolio-filter-gallery' ); ?></p>
                    </li>
                    <li>
                        <strong><?php esc_html_e( 'Configure Settings', 'portfolio-filter-gallery' ); ?></strong>
                        <p><?php esc_html_e( 'Use the Settings tab to configure layout, columns, lightbox, and more.', 'portfolio-filter-gallery' ); ?></p>
                    </li>
                    <li>
                        <strong><?php esc_html_e( 'Publish & Embed', 'portfolio-filter-gallery' ); ?></strong>
                        <p><?php esc_html_e( 'Click Publish, then copy the shortcode and paste it into any page or post.', 'portfolio-filter-gallery' ); ?></p>
                    </li>
                </ol>
            </section>
            
            <!-- Creating a Gallery -->
            <section id="creating-gallery" class="pfg-doc-section">
                <h2><?php esc_html_e( 'Creating a Gallery', 'portfolio-filter-gallery' ); ?></h2>
                <p><?php esc_html_e( 'Each gallery is a separate post that contains images and settings.', 'portfolio-filter-gallery' ); ?></p>
                
                <h3><?php esc_html_e( 'Gallery Title', 'portfolio-filter-gallery' ); ?></h3>
                <p><?php esc_html_e( 'Give your gallery a descriptive title. This is for your reference only and is not shown on the frontend by default.', 'portfolio-filter-gallery' ); ?></p>
                
                <h3><?php esc_html_e( 'Gallery Source', 'portfolio-filter-gallery' ); ?></h3>
                <ul>
                    <li><strong><?php esc_html_e( 'Media Library', 'portfolio-filter-gallery' ); ?></strong> - <?php esc_html_e( 'Manually select images from your WordPress Media Library.', 'portfolio-filter-gallery' ); ?></li>
                    <?php if ( $is_premium ) : ?>
                    <li><strong><?php esc_html_e( 'WooCommerce Products', 'portfolio-filter-gallery' ); ?></strong> - <?php esc_html_e( 'Automatically display products from your WooCommerce store.', 'portfolio-filter-gallery' ); ?></li>
                    <?php endif; ?>
                </ul>
            </section>
            
            <!-- Adding Images -->
            <section id="adding-images" class="pfg-doc-section">
                <h2><?php esc_html_e( 'Adding Images', 'portfolio-filter-gallery' ); ?></h2>
                
                <h3><?php esc_html_e( 'Methods to Add Images', 'portfolio-filter-gallery' ); ?></h3>
                <ul>
                    <li><strong><?php esc_html_e( 'Add Images Button', 'portfolio-filter-gallery' ); ?></strong> - <?php esc_html_e( 'Opens the Media Library to select existing images or upload new ones.', 'portfolio-filter-gallery' ); ?></li>
                    <li><strong><?php esc_html_e( 'Drag and Drop', 'portfolio-filter-gallery' ); ?></strong> - <?php esc_html_e( 'Drag images directly onto the gallery area to upload them.', 'portfolio-filter-gallery' ); ?></li>
                    <?php if ( $is_premium ) : ?>
                    <li><strong><?php esc_html_e( 'Import from Products', 'portfolio-filter-gallery' ); ?></strong> - <?php esc_html_e( 'Import product images from WooCommerce.', 'portfolio-filter-gallery' ); ?></li>
                    <?php endif; ?>
                </ul>
                
                <h3><?php esc_html_e( 'Editing Images', 'portfolio-filter-gallery' ); ?></h3>
                <p><?php esc_html_e( 'Click the edit (pencil) icon on any image to open the Edit Image modal where you can:', 'portfolio-filter-gallery' ); ?></p>
                <ul>
                    <li><?php esc_html_e( 'Change the image title', 'portfolio-filter-gallery' ); ?></li>
                    <li><?php esc_html_e( 'Add a description (shown in lightbox)', 'portfolio-filter-gallery' ); ?></li>
                    <li><?php esc_html_e( 'Set a custom link', 'portfolio-filter-gallery' ); ?></li>
                    <li><?php esc_html_e( 'Assign filters/categories', 'portfolio-filter-gallery' ); ?></li>
                    <?php if ( $is_premium ) : ?>
                    <li><?php esc_html_e( 'Link to a WooCommerce product', 'portfolio-filter-gallery' ); ?></li>
                    <li><?php esc_html_e( 'Add video URL (YouTube, Vimeo)', 'portfolio-filter-gallery' ); ?></li>
                    <?php endif; ?>
                </ul>
                
                <h3><?php esc_html_e( 'Reordering Images', 'portfolio-filter-gallery' ); ?></h3>
                <p><?php esc_html_e( 'Drag and drop images in the gallery editor to reorder them. The order is saved automatically when you update the gallery.', 'portfolio-filter-gallery' ); ?></p>
                
                <h3><?php esc_html_e( 'Bulk Actions', 'portfolio-filter-gallery' ); ?></h3>
                <p><?php esc_html_e( 'Use the "Select All" checkbox to select multiple images, then click "Delete Selected" to remove them from the gallery.', 'portfolio-filter-gallery' ); ?></p>
            </section>
            
            <!-- Filters -->
            <section id="filters" class="pfg-doc-section">
                <h2><?php esc_html_e( 'Working with Filters', 'portfolio-filter-gallery' ); ?></h2>
                <p><?php esc_html_e( 'Filters (categories) allow visitors to filter your gallery by clicking on buttons.', 'portfolio-filter-gallery' ); ?></p>
                
                <h3><?php esc_html_e( 'Creating Filters', 'portfolio-filter-gallery' ); ?></h3>
                <ol>
                    <li><?php esc_html_e( 'Go to Portfolio Gallery → Filters', 'portfolio-filter-gallery' ); ?></li>
                    <li><?php esc_html_e( 'Click "Add Filter" button', 'portfolio-filter-gallery' ); ?></li>
                    <li><?php esc_html_e( 'Enter a filter name (e.g., "Nature", "Architecture")', 'portfolio-filter-gallery' ); ?></li>
                    <li><?php esc_html_e( 'Optionally set a color for the filter tag', 'portfolio-filter-gallery' ); ?></li>
                </ol>
                
                <?php if ( $is_premium ) : ?>
                <h3><?php esc_html_e( 'Hierarchical Filters (Premium)', 'portfolio-filter-gallery' ); ?></h3>
                <p><?php esc_html_e( 'Create parent-child relationships between filters for multi-level filtering. Set a parent filter when creating a new filter to make it a child.', 'portfolio-filter-gallery' ); ?></p>
                
                <h3><?php esc_html_e( 'Filter Logic (Premium)', 'portfolio-filter-gallery' ); ?></h3>
                <ul>
                    <li><strong><?php esc_html_e( 'OR Logic', 'portfolio-filter-gallery' ); ?></strong> - <?php esc_html_e( 'Show images matching ANY of the selected filters (default).', 'portfolio-filter-gallery' ); ?></li>
                    <li><strong><?php esc_html_e( 'AND Logic', 'portfolio-filter-gallery' ); ?></strong> - <?php esc_html_e( 'Show only images matching ALL selected filters.', 'portfolio-filter-gallery' ); ?></li>
                </ul>
                <?php endif; ?>
                
                <h3><?php esc_html_e( 'Filter Slug', 'portfolio-filter-gallery' ); ?></h3>
                <p><?php esc_html_e( 'Each filter has a slug used for URL deep linking. You can edit the slug in the Filters page by clicking on it.', 'portfolio-filter-gallery' ); ?></p>
            </section>
            
            <!-- Layout Settings -->
            <section id="layout-settings" class="pfg-doc-section">
                <h2><?php esc_html_e( 'Layout Settings', 'portfolio-filter-gallery' ); ?></h2>
                
                <h3><?php esc_html_e( 'Layout Types', 'portfolio-filter-gallery' ); ?></h3>
                <ul>
                    <li><strong><?php esc_html_e( 'Masonry', 'portfolio-filter-gallery' ); ?></strong> - <?php esc_html_e( 'Pinterest-style layout with images of varying heights.', 'portfolio-filter-gallery' ); ?></li>
                    <li><strong><?php esc_html_e( 'Grid', 'portfolio-filter-gallery' ); ?></strong> - <?php esc_html_e( 'Uniform grid with equal-sized image thumbnails.', 'portfolio-filter-gallery' ); ?></li>
                </ul>
                
                <h3><?php esc_html_e( 'Column Settings', 'portfolio-filter-gallery' ); ?></h3>
                <p><?php esc_html_e( 'Set the number of columns for different screen sizes:', 'portfolio-filter-gallery' ); ?></p>
                <ul>
                    <li><?php esc_html_e( 'Desktop (1200px+): 1-6 columns', 'portfolio-filter-gallery' ); ?></li>
                    <li><?php esc_html_e( 'Tablet (768px-1199px): 1-4 columns', 'portfolio-filter-gallery' ); ?></li>
                    <li><?php esc_html_e( 'Mobile (<768px): 1-2 columns', 'portfolio-filter-gallery' ); ?></li>
                </ul>
                
                <h3><?php esc_html_e( 'Image Size', 'portfolio-filter-gallery' ); ?></h3>
                <p><?php esc_html_e( 'Choose which WordPress image size to use for thumbnails: Thumbnail, Medium, Large, or Full.', 'portfolio-filter-gallery' ); ?></p>
                
                <h3><?php esc_html_e( 'Gap / Spacing', 'portfolio-filter-gallery' ); ?></h3>
                <p><?php esc_html_e( 'Control the spacing between images in pixels.', 'portfolio-filter-gallery' ); ?></p>
            </section>
            
            <!-- Lightbox -->
            <section id="lightbox" class="pfg-doc-section">
                <h2><?php esc_html_e( 'Lightbox Options', 'portfolio-filter-gallery' ); ?></h2>
                
                <h3><?php esc_html_e( 'Available Lightboxes', 'portfolio-filter-gallery' ); ?></h3>
                <ul>
                    <li><strong><?php esc_html_e( 'LD Lightbox', 'portfolio-filter-gallery' ); ?></strong> - <?php esc_html_e( 'Modern, responsive lightbox (recommended).', 'portfolio-filter-gallery' ); ?></li>
                    <li><strong><?php esc_html_e( 'Simple Lightbox', 'portfolio-filter-gallery' ); ?></strong> - <?php esc_html_e( 'Lightweight, basic lightbox.', 'portfolio-filter-gallery' ); ?></li>
                    <li><strong><?php esc_html_e( 'None', 'portfolio-filter-gallery' ); ?></strong> - <?php esc_html_e( 'Disable lightbox, use custom links instead.', 'portfolio-filter-gallery' ); ?></li>
                </ul>
                
                <h3><?php esc_html_e( 'Lightbox Features', 'portfolio-filter-gallery' ); ?></h3>
                <ul>
                    <li><?php esc_html_e( 'Navigation arrows for browsing images', 'portfolio-filter-gallery' ); ?></li>
                    <li><?php esc_html_e( 'Image counter (e.g., "3 of 10")', 'portfolio-filter-gallery' ); ?></li>
                    <li><?php esc_html_e( 'Title and description display', 'portfolio-filter-gallery' ); ?></li>
                    <li><?php esc_html_e( 'Fullscreen mode', 'portfolio-filter-gallery' ); ?></li>
                    <?php if ( $is_premium ) : ?>
                    <li><?php esc_html_e( 'Social sharing buttons', 'portfolio-filter-gallery' ); ?></li>
                    <li><?php esc_html_e( 'Image download button', 'portfolio-filter-gallery' ); ?></li>
                    <li><?php esc_html_e( 'Video playback (YouTube, Vimeo)', 'portfolio-filter-gallery' ); ?></li>
                    <?php endif; ?>
                </ul>
            </section>
            
            <!-- Shortcode -->
            <section id="shortcode" class="pfg-doc-section">
                <h2><?php esc_html_e( 'Using Shortcodes', 'portfolio-filter-gallery' ); ?></h2>
                
                <h3><?php esc_html_e( 'Basic Shortcode', 'portfolio-filter-gallery' ); ?></h3>
                <p><?php esc_html_e( 'After publishing your gallery, copy the shortcode from the "Shortcode" meta box:', 'portfolio-filter-gallery' ); ?></p>
                <code class="pfg-code-block">[pfg id="123"]</code>
                
                <h3><?php esc_html_e( 'Where to Use', 'portfolio-filter-gallery' ); ?></h3>
                <ul>
                    <li><?php esc_html_e( 'Pages and Posts: Paste directly into the content editor.', 'portfolio-filter-gallery' ); ?></li>
                    <li><?php esc_html_e( 'Widgets: Use a Text or Custom HTML widget.', 'portfolio-filter-gallery' ); ?></li>
                    <li><?php esc_html_e( 'Page Builders: Use a shortcode block/module.', 'portfolio-filter-gallery' ); ?></li>
                    <li><?php esc_html_e( 'Theme Templates: Use do_shortcode() function.', 'portfolio-filter-gallery' ); ?></li>
                </ul>
                
                <h3><?php esc_html_e( 'PHP Usage', 'portfolio-filter-gallery' ); ?></h3>
                <code class="pfg-code-block">&lt;?php echo do_shortcode( '[pfg id="123"]' ); ?&gt;</code>
            </section>
            
            <!-- Hover Effects -->
            <section id="hover-effects" class="pfg-doc-section">
                <h2><?php esc_html_e( 'Hover Effects', 'portfolio-filter-gallery' ); ?></h2>
                <p><?php esc_html_e( 'Choose from various hover effects to enhance your gallery:', 'portfolio-filter-gallery' ); ?></p>
                <ul>
                    <li><strong><?php esc_html_e( 'Zoom', 'portfolio-filter-gallery' ); ?></strong> - <?php esc_html_e( 'Image scales up on hover.', 'portfolio-filter-gallery' ); ?></li>
                    <li><strong><?php esc_html_e( 'Slide', 'portfolio-filter-gallery' ); ?></strong> - <?php esc_html_e( 'Overlay slides in from direction.', 'portfolio-filter-gallery' ); ?></li>
                    <li><strong><?php esc_html_e( 'Fade', 'portfolio-filter-gallery' ); ?></strong> - <?php esc_html_e( 'Smooth fade-in effect.', 'portfolio-filter-gallery' ); ?></li>
                    <li><strong><?php esc_html_e( 'Blur', 'portfolio-filter-gallery' ); ?></strong> - <?php esc_html_e( 'Image blurs on hover.', 'portfolio-filter-gallery' ); ?></li>
                    <li><strong><?php esc_html_e( 'Grayscale', 'portfolio-filter-gallery' ); ?></strong> - <?php esc_html_e( 'Color on hover, grayscale otherwise.', 'portfolio-filter-gallery' ); ?></li>
                </ul>
            </section>
            
            <?php if ( $is_premium ) : ?>
            <!-- WooCommerce Integration -->
            <section id="woocommerce" class="pfg-doc-section pfg-doc-premium">
                <h2><span class="pfg-pro-badge-sm">Pro</span> <?php esc_html_e( 'WooCommerce Integration', 'portfolio-filter-gallery' ); ?></h2>
                <p><?php esc_html_e( 'Display your WooCommerce products in a beautiful gallery format.', 'portfolio-filter-gallery' ); ?></p>
                
                <h3><?php esc_html_e( 'Setting Up WooCommerce Gallery', 'portfolio-filter-gallery' ); ?></h3>
                <ol>
                    <li><?php esc_html_e( 'Create a new gallery', 'portfolio-filter-gallery' ); ?></li>
                    <li><?php esc_html_e( 'Change "Gallery Source" to "WooCommerce Products"', 'portfolio-filter-gallery' ); ?></li>
                    <li><?php esc_html_e( 'Select product categories to display (or leave empty for all)', 'portfolio-filter-gallery' ); ?></li>
                    <li><?php esc_html_e( 'Set display limit and sorting options', 'portfolio-filter-gallery' ); ?></li>
                </ol>
                
                <h3><?php esc_html_e( 'Product Display Options', 'portfolio-filter-gallery' ); ?></h3>
                <ul>
                    <li><?php esc_html_e( 'Show/hide product price', 'portfolio-filter-gallery' ); ?></li>
                    <li><?php esc_html_e( 'Show/hide "Add to Cart" button', 'portfolio-filter-gallery' ); ?></li>
                    <li><?php esc_html_e( 'Link to product page', 'portfolio-filter-gallery' ); ?></li>
                    <li><?php esc_html_e( 'Display sale badge', 'portfolio-filter-gallery' ); ?></li>
                </ul>
                
                <h3><?php esc_html_e( 'Linking Images to Products', 'portfolio-filter-gallery' ); ?></h3>
                <p><?php esc_html_e( 'In a Media Library gallery, you can link any image to a WooCommerce product. Edit the image and search for a product in the "Link to Product" field.', 'portfolio-filter-gallery' ); ?></p>
            </section>
            
            <!-- Video Galleries -->
            <section id="video-galleries" class="pfg-doc-section pfg-doc-premium">
                <h2><span class="pfg-pro-badge-sm">Pro</span> <?php esc_html_e( 'Video Galleries', 'portfolio-filter-gallery' ); ?></h2>
                <p><?php esc_html_e( 'Create mixed galleries with images and videos.', 'portfolio-filter-gallery' ); ?></p>
                
                <h3><?php esc_html_e( 'Supported Video Platforms', 'portfolio-filter-gallery' ); ?></h3>
                <ul>
                    <li><?php esc_html_e( 'YouTube', 'portfolio-filter-gallery' ); ?></li>
                    <li><?php esc_html_e( 'Vimeo', 'portfolio-filter-gallery' ); ?></li>
                </ul>
                
                <h3><?php esc_html_e( 'Adding Videos', 'portfolio-filter-gallery' ); ?></h3>
                <ol>
                    <li><?php esc_html_e( 'Edit any image in your gallery', 'portfolio-filter-gallery' ); ?></li>
                    <li><?php esc_html_e( 'Paste a YouTube or Vimeo URL in the "Video URL" field', 'portfolio-filter-gallery' ); ?></li>
                    <li><?php esc_html_e( 'Click "Fetch Thumbnail" to automatically import the video thumbnail', 'portfolio-filter-gallery' ); ?></li>
                    <li><?php esc_html_e( 'Save the image settings', 'portfolio-filter-gallery' ); ?></li>
                </ol>
                
                <p><?php esc_html_e( 'Videos will play in the lightbox when clicked.', 'portfolio-filter-gallery' ); ?></p>
            </section>
            
            <!-- Pagination -->
            <section id="pagination" class="pfg-doc-section pfg-doc-premium">
                <h2><span class="pfg-pro-badge-sm">Pro</span> <?php esc_html_e( 'Pagination & Load More', 'portfolio-filter-gallery' ); ?></h2>
                
                <h3><?php esc_html_e( 'Pagination Types', 'portfolio-filter-gallery' ); ?></h3>
                <ul>
                    <li><strong><?php esc_html_e( 'None', 'portfolio-filter-gallery' ); ?></strong> - <?php esc_html_e( 'Show all images at once.', 'portfolio-filter-gallery' ); ?></li>
                    <li><strong><?php esc_html_e( 'Numbered', 'portfolio-filter-gallery' ); ?></strong> - <?php esc_html_e( 'Traditional numbered pagination.', 'portfolio-filter-gallery' ); ?></li>
                    <li><strong><?php esc_html_e( 'Load More Button', 'portfolio-filter-gallery' ); ?></strong> - <?php esc_html_e( 'Click to load more images.', 'portfolio-filter-gallery' ); ?></li>
                    <li><strong><?php esc_html_e( 'Infinite Scroll', 'portfolio-filter-gallery' ); ?></strong> - <?php esc_html_e( 'Automatically load more when scrolling.', 'portfolio-filter-gallery' ); ?></li>
                </ul>
                
                <h3><?php esc_html_e( 'Images Per Page', 'portfolio-filter-gallery' ); ?></h3>
                <p><?php esc_html_e( 'Set how many images to show per page in the Settings tab.', 'portfolio-filter-gallery' ); ?></p>
            </section>
            
            <!-- Analytics -->
            <section id="analytics" class="pfg-doc-section pfg-doc-premium">
                <h2><span class="pfg-pro-badge-sm">Pro</span> <?php esc_html_e( 'Analytics', 'portfolio-filter-gallery' ); ?></h2>
                <p><?php esc_html_e( 'Track how visitors interact with your galleries.', 'portfolio-filter-gallery' ); ?></p>
                
                <h3><?php esc_html_e( 'Tracked Metrics', 'portfolio-filter-gallery' ); ?></h3>
                <ul>
                    <li><strong><?php esc_html_e( 'Views', 'portfolio-filter-gallery' ); ?></strong> - <?php esc_html_e( 'Total gallery views.', 'portfolio-filter-gallery' ); ?></li>
                    <li><strong><?php esc_html_e( 'Unique Visitors', 'portfolio-filter-gallery' ); ?></strong> - <?php esc_html_e( 'Number of unique visitors.', 'portfolio-filter-gallery' ); ?></li>
                    <li><strong><?php esc_html_e( 'Image Clicks', 'portfolio-filter-gallery' ); ?></strong> - <?php esc_html_e( 'Which images are clicked most.', 'portfolio-filter-gallery' ); ?></li>
                    <li><strong><?php esc_html_e( 'Filter Clicks', 'portfolio-filter-gallery' ); ?></strong> - <?php esc_html_e( 'Which filters are used most.', 'portfolio-filter-gallery' ); ?></li>
                </ul>
                
                <h3><?php esc_html_e( 'Enabling Analytics', 'portfolio-filter-gallery' ); ?></h3>
                <p><?php esc_html_e( 'Analytics is enabled by default. View your stats at Portfolio Gallery → Analytics.', 'portfolio-filter-gallery' ); ?></p>
                
                <h3><?php esc_html_e( 'Exporting Data', 'portfolio-filter-gallery' ); ?></h3>
                <p><?php esc_html_e( 'Use the "Export CSV" button on the Analytics page to download your data.', 'portfolio-filter-gallery' ); ?></p>
            </section>
            
            <!-- Deep Linking -->
            <section id="deep-linking" class="pfg-doc-section pfg-doc-premium">
                <h2><span class="pfg-pro-badge-sm">Pro</span> <?php esc_html_e( 'URL Deep Linking', 'portfolio-filter-gallery' ); ?></h2>
                <p><?php esc_html_e( 'Allow visitors to share or bookmark filtered gallery views.', 'portfolio-filter-gallery' ); ?></p>
                
                <h3><?php esc_html_e( 'How It Works', 'portfolio-filter-gallery' ); ?></h3>
                <p><?php esc_html_e( 'When a filter is clicked, the URL updates automatically (e.g., yoursite.com/gallery/#nature). Sharing this URL will show the gallery pre-filtered to that category.', 'portfolio-filter-gallery' ); ?></p>
                
                <h3><?php esc_html_e( 'Enabling Deep Linking', 'portfolio-filter-gallery' ); ?></h3>
                <p><?php esc_html_e( 'Enable "URL Deep Linking" in the gallery Settings tab.', 'portfolio-filter-gallery' ); ?></p>
            </section>
            <?php endif; ?>
            
            <!-- FAQ -->
            <section id="faq" class="pfg-doc-section">
                <h2><?php esc_html_e( 'Frequently Asked Questions', 'portfolio-filter-gallery' ); ?></h2>
                
                <div class="pfg-faq-item">
                    <h4><?php esc_html_e( 'How many images can I add to a gallery?', 'portfolio-filter-gallery' ); ?></h4>
                    <p><?php esc_html_e( 'There is no hard limit. The plugin supports galleries with hundreds of images. Large galleries are saved in chunks to avoid server limits.', 'portfolio-filter-gallery' ); ?></p>
                </div>
                
                <div class="pfg-faq-item">
                    <h4><?php esc_html_e( 'Can I use the same image in multiple galleries?', 'portfolio-filter-gallery' ); ?></h4>
                    <p><?php esc_html_e( 'Yes! Images are referenced from the Media Library, so one image can appear in multiple galleries with different settings or categories.', 'portfolio-filter-gallery' ); ?></p>
                </div>
                
                <div class="pfg-faq-item">
                    <h4><?php esc_html_e( 'How do I change the filter button style?', 'portfolio-filter-gallery' ); ?></h4>
                    <p><?php esc_html_e( 'Go to gallery Settings → Filter Appearance section to choose button styles, colors, and alignment.', 'portfolio-filter-gallery' ); ?></p>
                </div>
                
                <div class="pfg-faq-item">
                    <h4><?php esc_html_e( 'Why are my images not showing?', 'portfolio-filter-gallery' ); ?></h4>
                    <p><?php esc_html_e( 'Check that: 1) Your gallery is published, 2) You\'ve added at least one image, 3) The shortcode ID matches your gallery ID.', 'portfolio-filter-gallery' ); ?></p>
                </div>
                
                <div class="pfg-faq-item">
                    <h4><?php esc_html_e( 'Can I use custom CSS?', 'portfolio-filter-gallery' ); ?></h4>
                    <p><?php esc_html_e( 'Yes! Add custom CSS in Settings → Custom CSS field, or use your theme\'s custom CSS option.', 'portfolio-filter-gallery' ); ?></p>
                </div>
            </section>
            
            <!-- Support -->
            <section id="support" class="pfg-doc-section">
                <h2><?php esc_html_e( 'Support', 'portfolio-filter-gallery' ); ?></h2>
                
                <div class="pfg-support-links">
                    <a href="https://awplife.com/demo/portfolio-filter-gallery-premium/" target="_blank" class="pfg-support-card">
                        <span class="dashicons dashicons-visibility"></span>
                        <strong><?php esc_html_e( 'Live Demo', 'portfolio-filter-gallery' ); ?></strong>
                        <span><?php esc_html_e( 'See the plugin in action', 'portfolio-filter-gallery' ); ?></span>
                    </a>
                    
                    <a href="https://wordpress.org/support/plugin/portfolio-filter-gallery/" target="_blank" class="pfg-support-card">
                        <span class="dashicons dashicons-sos"></span>
                        <strong><?php esc_html_e( 'Support Forum', 'portfolio-filter-gallery' ); ?></strong>
                        <span><?php esc_html_e( 'Get help from our team', 'portfolio-filter-gallery' ); ?></span>
                    </a>
                    
                    <?php if ( ! $is_premium ) : ?>
                    <a href="https://awplife.com/account/signup/portfolio-filter-gallery" target="_blank" class="pfg-support-card pfg-support-upgrade">
                        <span class="dashicons dashicons-star-filled"></span>
                        <strong><?php esc_html_e( 'Upgrade to Pro', 'portfolio-filter-gallery' ); ?></strong>
                        <span><?php esc_html_e( 'Unlock all features', 'portfolio-filter-gallery' ); ?></span>
                    </a>
                    <?php endif; ?>
                </div>
            </section>
            
        </div>
    </div>
</div>

<style>
.pfg-docs-wrap {
    max-width: 1400px;
}
.pfg-docs-container {
    display: flex;
    gap: 30px;
    margin-top: 20px;
}
.pfg-docs-sidebar {
    width: 220px;
    flex-shrink: 0;
}
.pfg-docs-nav {
    position: sticky;
    top: 32px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 15px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.pfg-doc-link {
    display: block;
    padding: 10px 14px;
    color: #475569;
    text-decoration: none;
    font-size: 13px;
    border-radius: 6px;
    transition: all 0.2s;
}
.pfg-doc-link:hover,
.pfg-doc-link.active {
    background: #f1f5f9;
    color: #1e293b;
}
.pfg-doc-link.pfg-doc-pro {
    color: #7c3aed;
}
.pfg-doc-link.pfg-doc-pro::before {
    content: "★ ";
}
.pfg-docs-content {
    flex: 1;
    min-width: 0;
}
.pfg-doc-section {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 24px;
}
.pfg-doc-section h2 {
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 16px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e2e8f0;
}
.pfg-doc-section h3 {
    font-size: 16px;
    font-weight: 600;
    color: #334155;
    margin: 24px 0 12px;
}
.pfg-doc-section h4 {
    font-size: 14px;
    font-weight: 600;
    color: #475569;
    margin: 16px 0 8px;
}
.pfg-doc-section p,
.pfg-doc-section li {
    font-size: 14px;
    line-height: 1.7;
    color: #475569;
}
.pfg-doc-section ul,
.pfg-doc-section ol {
    margin: 10px 0;
    padding-left: 24px;
}
.pfg-doc-section li {
    margin-bottom: 6px;
}
.pfg-steps li {
    margin-bottom: 16px;
}
.pfg-steps li strong {
    color: #1e293b;
    display: block;
    margin-bottom: 4px;
}
.pfg-steps li p {
    margin: 0;
}
.pfg-code-block {
    display: block;
    background: #1e293b;
    color: #e2e8f0;
    padding: 15px 20px;
    border-radius: 8px;
    font-family: monospace;
    font-size: 14px;
    margin: 12px 0;
    overflow-x: auto;
}
.pfg-doc-premium h2 {
    border-color: #8b5cf6;
}
.pfg-pro-badge-sm {
    display: inline-block;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: #fff;
    font-size: 10px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 4px;
    margin-right: 8px;
    vertical-align: middle;
}
.pfg-faq-item {
    padding: 16px 0;
    border-bottom: 1px solid #f1f5f9;
}
.pfg-faq-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}
.pfg-faq-item h4 {
    margin-top: 0;
}
.pfg-faq-item p {
    margin: 0;
}
.pfg-support-links {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-top: 16px;
}
.pfg-support-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 24px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.2s;
}
.pfg-support-card:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
    transform: translateY(-2px);
}
.pfg-support-card .dashicons {
    font-size: 32px;
    width: 32px;
    height: 32px;
    color: #6366f1;
    margin-bottom: 12px;
}
.pfg-support-card strong {
    color: #1e293b;
    font-size: 14px;
    margin-bottom: 4px;
}
.pfg-support-card span:last-child {
    color: #64748b;
    font-size: 12px;
}
.pfg-support-upgrade {
    background: linear-gradient(135deg, #f5f3ff, #ede9fe);
    border-color: #c4b5fd;
}
.pfg-support-upgrade .dashicons {
    color: #8b5cf6;
}
@media (max-width: 960px) {
    .pfg-docs-container {
        flex-direction: column;
    }
    .pfg-docs-sidebar {
        width: 100%;
    }
    .pfg-docs-nav {
        position: static;
        flex-direction: row;
        flex-wrap: wrap;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Smooth scroll for docs nav
    $('.pfg-doc-link').on('click', function(e) {
        e.preventDefault();
        var target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 50
            }, 500);
            $('.pfg-doc-link').removeClass('active');
            $(this).addClass('active');
        }
    });
    
    // Update active nav on scroll
    $(window).on('scroll', function() {
        var scrollPos = $(window).scrollTop() + 100;
        
        $('.pfg-doc-section').each(function() {
            var top = $(this).offset().top;
            var bottom = top + $(this).outerHeight();
            var id = $(this).attr('id');
            
            if (scrollPos >= top && scrollPos < bottom) {
                $('.pfg-doc-link').removeClass('active');
                $('.pfg-doc-link[href="#' + id + '"]').addClass('active');
            }
        });
    });
});
</script>
