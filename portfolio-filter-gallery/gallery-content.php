<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Light Box 
 */
if($light_box == 4) {
	wp_enqueue_style('awl-ld-lightbox-css', PFG_PLUGIN_URL .'lightbox/ld-lightbox/css/lightbox.css');
	wp_enqueue_script('awl-ld-lightbox-js', PFG_PLUGIN_URL .'lightbox/ld-lightbox/js/lightbox.js', array('jquery'), '' , true);
}
if($light_box == 5) {
	wp_enqueue_script('awl-bootstrap-index-bundle-js', PFG_PLUGIN_URL .'lightbox/bootstrap/js/index.bundle.min.js', array('jquery'), '' , true);
}

$allimages = array(  'p' => $pf_gallery_id, 'post_type' => 'awl_filter_gallery', 'orderby' => 'ASC');
$loop = new WP_Query( $allimages );
while ( $loop->have_posts() ) : $loop->the_post();
	$post_id = get_the_ID();
	$all_category = get_option('awl_portfolio_filter_gallery_categories');

	// collect all selected filters assigned on images
	$all_selected_filters = array();
	foreach ($filters as $filters_key => $filters_value) {
		if(is_array($filters_value)) {
			$all_selected_filters = array_merge($all_selected_filters, $filters_value);
		}
	}
	if(isset($pf_gallery_settings['filter-image'])) {
		$filter_image = $pf_gallery_settings['filter-image'];
	} else {
		$filter_image = '';
	}
	if(isset($pf_gallery_settings['image-ids'])) {
		$pf_total_images = count($pf_gallery_settings['image-ids']);
	} else {
		$pf_total_images = 0;
	}
	// show image count on filter	
	if($show_image_count == 1) {
		$show_all_image_count  = ' ('. $pf_total_images .')';
	} else {
		$show_all_image_count  = "";
	}
	?>
	<div class="portfolio-wraper pfg-bootstrap" version="<?php echo PFG_PLUGIN_VER; ?>">
		<?php
		$filter_align = '';
		if($hide_filters == 0 ) {
			if($filter_position == 'center') {
				$filter_align = 'text-center';
			}
			if($filter_position == 'right') {
				$filter_align = 'text-right';
			}
			if($filter_position == 'left') {
				$filter_align = '';
			} ?>
			<div class="col-lg-12 col-md-12 col-xs-12 <?php echo esc_attr($filter_align); ?>">
				<ul id="all-simplefilter" class="filtr-control-<?php echo esc_attr($pf_gallery_id); ?> simplefilter simplefilter_<?php echo esc_attr($pf_gallery_id); ?>">
					<li id="all" class="active filtr-controls-<?php echo esc_attr($pf_gallery_id); ?>" data-filter="all"><?php _e($all_txt. $show_all_image_count, 'portfolio-filter-gallery'); ?></li>
				</ul>
				<ul id="f-simplefilter-<?php echo $pf_gallery_id; ?>" class="filtr-control-<?php echo esc_attr($pf_gallery_id); ?> simplefilter simplefilter_<?php echo esc_attr($pf_gallery_id); ?>">
					<?php
					if (is_array($all_selected_filters) && count($all_selected_filters)) {
						$all_selected_filters = array_unique($all_selected_filters); // remove same key
						foreach ($all_selected_filters as $filter_key) {
							$show_filter_image_count = '';

							if ($show_image_count == 1 && isset($filter_image[$filter_key])) {
								$show_filter_image_count = ' (' . count($filter_image[$filter_key]) . ')';
							}
							// Check if $filter_key exists in $all_category before using it
							if (isset($all_category[$filter_key])) {
								?>
								<li data-filter="<?php echo esc_attr($filter_key); ?>"
									class="filtr-controls-<?php echo esc_attr($pf_gallery_id); ?>"><?php echo stripslashes(esc_html($all_category[$filter_key])) . $show_filter_image_count; ?></li>
								<?php
							}
						}
					}
					?>
				</ul>

			</div>
		<?php 
		} ?>
		<!-- Shuffle & Sort Controls -->
		<?php 
		if($search_box) { ?>
			<div class="col-md-12 filter-wrap <?php echo esc_attr($filter_align); ?>">
				<input type="text" class="filtr-controls-<?php echo esc_attr($pf_gallery_id); ?> filtr-search filtr_search_<?php echo esc_attr($pf_gallery_id); ?>" name="filtr-search" placeholder="<?php if($search_txt) echo ucwords(esc_html($search_txt)); else echo 'Search Images'; ?>" data-search></li>
			</div>
		<?php 
		} ?>
		
		<div class="row loading-wrapper text-center">
			<img src="<?php echo PFG_PLUGIN_URL ?>/img/loading-icon.gif" width="60">
		</div>
		<div class="filter_gallery_<?php echo $pf_gallery_id; ?> row filtr-container filters-div" style="opacity:0; display:none;">
			<?php
			if(isset($pf_gallery_settings['image-ids']) && count($pf_gallery_settings['image-ids']) > 0) {
				$count = 0;
				if($thumbnail_order == "DESC") {
					$pf_gallery_settings['image-ids'] = array_reverse($pf_gallery_settings['image-ids']);
				}
				if($thumbnail_order == "RANDOM") {
					shuffle($pf_gallery_settings['image-ids']);
				}			
				$no = 1;
				foreach($pf_gallery_settings['image-ids'] as $attachment_id) {
					//$attachment_id;
					$image_link_url =  $pf_gallery_settings['image-link'][$count];
					$image_type = $pf_gallery_settings['slide-type'][$count];
					$thumb = wp_get_attachment_image_src($attachment_id, 'thumb', true);
					$thumbnail = wp_get_attachment_image_src($attachment_id, 'thumbnail', true);
					$medium = wp_get_attachment_image_src($attachment_id, 'medium', true);
					$large = wp_get_attachment_image_src($attachment_id, 'large', true);
					$full = wp_get_attachment_image_src($attachment_id, 'full', true);
					$postthumbnail = wp_get_attachment_image_src($attachment_id, 'post-thumbnail', true);
					$attachment_details = get_post( $attachment_id );
					$href = get_permalink( $attachment_details->ID );
					$src = $attachment_details->guid;
					$title = $attachment_details->post_title;
					$description = $attachment_details->post_content;
					
					//set thumbnail size
					if($gal_thumb_size == "thumbnail") { $thumbnail_url = $thumbnail[0]; }
					if($gal_thumb_size == "medium") { $thumbnail_url = $medium[0]; }
					if($gal_thumb_size == "large") { $thumbnail_url = $large[0]; }
					if($gal_thumb_size == "full") { $thumbnail_url = $full[0]; }
					
					// seach attachment id in to $filters and get all filter ids
					//$pfg_filters = $pf_gallery_settings['filters'];
					foreach ($filters as $pfg_filters_key => $pfg_filters_values) {
					}
					if (array_key_exists($attachment_id, $filters)) {
						$filter_key_array = $filters[$attachment_id];
						$prefix = $filter_keys = '';
						if(count($filter_key_array) > 1) {
							foreach ($filter_key_array as $filter_key => $filter_value) {
								$filter_keys .= $prefix . $filter_value;
								$prefix = ', ';
							}
						} else {
							$filter_keys = $filter_key_array[0];						
						}
					}
					$filter_keys2 = '';
					// SwipeBox Class
					if (array_key_exists($attachment_id, $filters)) {
						$filter_key_array2 = $filters[$attachment_id];
						$prefix2 = $filter_keys2 = '';
						foreach ($filter_key_array2 as $filter_key2 => $filter_value2) {
							$filter_keys2 .= $prefix2 . $filter_value2;
							$prefix2 = ' pfg-lightbox-';
						}
					}
					if(!isset($filter_keys)) {
						$filter_keys = 1;
					} 
					
					
					// Image Link and lightbox
					// Bootstrap Lighbox
					if ($light_box == 5) {
						if ($image_type == 'video') {
							// Check if it's a Vimeo video
							if (strpos($image_link_url, 'vimeo.com') !== false) {
								preg_match('/vimeo\.com\/(\d+)/', $image_link_url, $matches);
								$vimeo_id = isset($matches[1]) ? $matches[1] : '';
								$vimeo_url = 'https://player.vimeo.com/video/' . $vimeo_id;
								
								$a_attrs = 'href="' . esc_url($image_link_url) . '" data-remote="' . esc_url($vimeo_url) . '" class="pfg-lightbox-all pfg-lightbox-'.esc_html($filter_keys2).'" data-toggle="lightbox" data-gallery="mixedgallery" data-title="' . esc_attr($title) . '" data-alt="' . esc_attr($title) . '"';
							}
							// Check if it's a YouTube video
							elseif (strpos($image_link_url, 'youtube.com') !== false || strpos($image_link_url, 'youtu.be') !== false) {
								// Extract YouTube video ID
								$youtube_video_id = '';
								if (preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v(?:i)?=))([^\s&]+)/', $image_link_url, $matches)) {
									$youtube_video_id = $matches[1];
								}
								// Construct YouTube video URL
								$youtube_url = 'https://www.youtube.com/embed/' . $youtube_video_id;
								
								$a_attrs = 'href="' . esc_url($image_link_url) . '" data-remote="' . esc_url($youtube_url) . '" class="pfg-lightbox-all pfg-lightbox-'.esc_html($filter_keys2).'" data-toggle="lightbox" data-gallery="mixedgallery" data-title="' . esc_attr($title) . '" data-alt="' . esc_attr($title) . '"';
							}
						} else {
							// For non-video types
							$a_attrs = 'href="' . esc_url($full[0]) . '" class="pfg-lightbox-all pfg-lightbox-'.esc_html($filter_keys2).'" data-toggle="lightbox" data-gallery="mixedgallery" data-title="' . esc_attr($title) . '" data-alt="' . esc_attr($title) . '"';
						}
					}

					// LD Lighbox
					if($light_box == 4) {
						$a_attrs = 'href="'. esc_url($full[0]) .'" class="pfg-lightbox-all pfg-lightbox-'.esc_html($filter_keys2).'" data-lightbox="pfg-lightbox"  data-title="'.esc_html($title).'" data-alt="'.esc_html($title).'"';
					
						if($image_type == 'video') {
							$a_attrs = 'href="'.esc_url($image_link_url).'" data-title="'.esc_attr($title).'" data-alt="'.esc_attr($title).'" class="video-box-'.esc_attr($pf_gallery_id).'"';
						}
					
					}
					// No LightBox
					if($light_box == 0 ) {
						$a_attrs = 'href="#"';
					}
					// Image and video types
					if($image_type == 'image') {
						if($image_link_url) {
							$a_attrs = 'href="'.esc_url($image_link_url).'" target="'.esc_attr($url_target).'" title="'.esc_attr($title).'"';
						}
					}
					//Content
					?>
					<a <?php echo $a_attrs; ?> title="<?php echo esc_html_e($title, 'portfolio-filter-gallery'); ?>">
						<div data-category="<?php echo esc_html($filter_keys); ?>" data-sort="<?php echo esc_html($title); ?>" class="animateonload filtr-item filtr_item_<?php echo esc_html($pf_gallery_id); ?> single_one <?php echo esc_html($col_large_desktops); ?> <?php echo esc_html($col_desktops); ?> <?php echo esc_html($col_tablets); ?> <?php echo esc_html($col_phones); ?>">
							<?php
							// Type Image
							if($image_type == 'image') {
								?>
								<img class="<?php if($thumb_border == "yes") { ?> thumbnail <?php } ?> thumbnail_<?php echo esc_html($pf_gallery_id); ?> pfg-img pfg_img_<?php echo esc_html($pf_gallery_id); ?> img-responsive <?php echo esc_html($image_hover_effect); ?>" src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ); ?>">
								<?php 
								if($image_numbering) { ?>
									<div class="item-position item_position_<?php echo esc_html($pf_gallery_id); ?>"><?php echo esc_html($no); ?></div>
									<?php 
								} ?>
									<span class="item-desc item_desc_<?php echo esc_html($pf_gallery_id); ?>"><?php esc_html_e($title, 'portfolio-filter-gallery'); ?></span>
								<?php
							}
							//Type Video
							if($image_type == 'video') {
								?>
								<figure class="snipv12">
									<img class="<?php if($thumb_border == "yes") { ?> thumbnail <?php } ?> thumbnail_<?php echo esc_html($pf_gallery_id); ?> pfg-img pfg_img_<?php echo esc_html($pf_gallery_id); ?> img-responsive <?php echo esc_html($image_hover_effect); ?>" src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ); ?>">
									<?php 
									if($image_numbering) { ?>
										<div class="item-position item_position_<?php echo esc_html($pf_gallery_id); ?>"><?php echo esc_html($no); ?></div>
										<?php 
									} ?>
										<span class="item-desc item_desc_<?php echo esc_html($pf_gallery_id); ?>"><?php esc_html_e($title, 'portfolio-filter-gallery'); ?></span>
										<?php 
									if (!strpos($image_link_url, 'vimeo')) { ?>
										<i class=""><img src="<?php echo PFG_PLUGIN_URL ?>/img/p-youtube.png"></i>
										<?php
									} else { ?>
										<i class="fa fa-youtube-play"></i>
										<?php
									} ?>
								</figure>
								<?php
							}
							?>
						</div>
					</a>
					<?php 
					
					$no++;
					$count++;
				}// end of attachment foreach
			} else {
				_e('Sorry! No image gallery found ', 'portfolio-filter-gallery');
				echo ":[PFG id=$post_id]";
			} // end of if esle of images avaialble check into imager ?>
		</div>
	</div>
<?php
endwhile;
wp_reset_query(); ?>
<script>
jQuery(document).ready(function (jQuery) {
    jQuery('.filtr-item').addClass('animateonload');
    jQuery('#filter_gallery_<?php echo esc_js($pf_gallery_id); ?>').show();
    jQuery('.loading-wrapper').hide();
    jQuery(".loader_img").hide();
    jQuery(".lg_load_more").show();
    jQuery(".filtr-container").css("opacity", 1);

    // Define Filterizr Default Options
    const options = {
        animationDuration: 0.5,
        callbacks: {
            onFilteringStart: function() {
                // Apply sorting shortly after filtering starts
                setTimeout(function() {
                    <?php if ($sort_by_title == "asc") { ?>
                        filterizd.filterizr('sort', 'sortData', 'asc');
                    <?php } else if ($sort_by_title == "desc") { ?>
                        filterizd.filterizr('sort', 'sortData', 'desc');
                    <?php } ?>
                }, 50);
            },
            onFilteringEnd: function() {
                // Apply sorting shortly after filtering ends
                setTimeout(function() {
                    <?php if ($sort_by_title == "asc") { ?>
                        filterizd.filterizr('sort', 'sortData', 'asc');
                    <?php } else if ($sort_by_title == "desc") { ?>
                        filterizd.filterizr('sort', 'sortData', 'desc');
                    <?php } ?>
                }, 50);
            },
            onShufflingStart: function() { },
            onShufflingEnd: function() { },
            onSortingStart: function() { },
            onSortingEnd: function() { }
        },
        controlsSelector: '.filtr-controls-<?php echo esc_js($pf_gallery_id); ?>',
        filter: 'all',
        filterOutCss: {
            top: '0px',
            left: '0px',
            opacity: 0.001,
            transform: ''
        },
        filterInCss: {
            top: '0px',
            left: '0px',
            opacity: 1,
            transform: ''
        },
        layout: 'sameWidth',
        selector: '.filtr-item',
        setupControls: false
    };

    // Initialize Filterizr and ensure it is globally accessible
    window.filterizd = jQuery('.filter_gallery_<?php echo esc_js($pf_gallery_id); ?>').filterizr(options);

    jQuery('.filter_gallery_<?php echo $pf_gallery_id; ?>').imagesLoaded(function() {
        // Reapply sorting after initial load
        <?php if ($sort_by_title == "asc") { ?>
            filterizd.filterizr('sort', 'sortData', 'asc');
        <?php } else if ($sort_by_title == "desc") { ?>
            filterizd.filterizr('sort', 'sortData', 'desc');
        <?php } ?>

        // Resize handling
        jQuery(window).resize(function() {
            jQuery(".thumbnail_<?php echo $pf_gallery_id; ?>").each(function() {
                var h = jQuery(this).height();
                var w = jQuery(this).width();
                jQuery(this).height(h);
                jQuery(this).width(w);
            });

            // Reapply sorting after resizing
            <?php if ($sort_by_title == "asc") { ?>
                filterizd.filterizr('sort', 'sortData', 'asc');
            <?php } else if ($sort_by_title == "desc") { ?>
                filterizd.filterizr('sort', 'sortData', 'desc');
            <?php } ?>
        });
    });

	<?php
	if ( $hide_filters == 0 ) {
		if ( $sort_filter_order == 1 ) { ?>
			/* Sort li to alphabetically: */
			function sortList(ul) {
				var ul = document.getElementById("f-simplefilter-<?php echo $pf_gallery_id; ?>");
				Array.from(ul.getElementsByTagName("LI"))
				.sort((a, b) => a.textContent.localeCompare(b.textContent))
				.forEach(li => ul.appendChild(li));
			}
			sortList("f-simplefilter-<?php echo $pf_gallery_id; ?>");
		/* Sort li to alphabetically: */
		<?php 
		} 
	} ?>
	
		//bootstrap-lightbox-js
		// delegate calls to data-toggle="lightbox"
		document.addEventListener('DOMContentLoaded', (event) => {
            document.querySelectorAll('.my-lightbox-toggle').forEach((el) => {
                el.addEventListener('click', (e) => {
                    e.preventDefault();

                    const options = {
                        keyboard: true,
                        size: 'fullscreen'
                    };

                    // Assuming Lightbox is a constructor from the included library
                    // Check if the Lightbox class or function exists
                    if (typeof Lightbox !== 'undefined') {
                        const lightbox = new Lightbox(el, options);
                        lightbox.show();
                    } else {
                        console.error('Lightbox library is not loaded.');
                    }
                });
            });
        });
		<?php 
		if ($light_box == 5) { ?>
		jQuery('.filtr-control-<?php echo $pf_gallery_id; ?> [data-filter]').click(function() {
			
			//jQuery('.swiper-<?php echo $pf_gallery_id; ?>').swipebox('swipebox-destroy');
			var targetFilter = jQuery(this).data('filter');
			console.log(targetFilter);
			var lighbox_class_name = "pfg-lightbox-" + targetFilter;
			jQuery('.pfg-lightbox-' + targetFilter ).attr('data-gallery', lighbox_class_name); // add data filter for parent filters
		});
		<?php 
		} else if ($light_box == 4) { ?>
		jQuery('.filtr-control-<?php echo $pf_gallery_id; ?> [data-filter]').click(function() {
			//jQuery('.swiper-<?php echo $pf_gallery_id; ?>').swipebox('swipebox-destroy');
			var targetFilter = jQuery(this).data('filter');
			var lighbox_class_name = "pfg-lightbox-" + targetFilter;
			jQuery('.pfg-lightbox-' + targetFilter ).attr('data-lightbox', lighbox_class_name); // add data filter for parent filters
		});
		
		<?php 
		} ?>

	// video player
	jQuery(function(){
      jQuery("a.video-box-<?php echo $pf_gallery_id; ?>").YouTubePopUp( { autoplay: 0 } ); // Disable autoplay
	});
	
});   
</script>