<?php
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly
//CSS
wp_enqueue_script('jquery');
wp_enqueue_style('pfg-metabox-css', PFG_PLUGIN_URL . 'css/metabox.css');

//load settings
$pf_gallery_settings = get_post_meta($post->ID, 'awl_filter_gallery' . $post->ID, true);

/*echo "<pre>";
print_r($pf_gallery_settings);
echo "</pre>";*/
?>

<style>
	.pfg-hide {
		display: none;
	}
</style>

<div class="row gallery-content-photo-wall">
	<!--Add New Image Button-->
	<div class="kwt-file">
		<div class="kwt-file__drop-area">
			<span class="kwt-file__choose-file">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor" width="24px">
					<path
						d="M67.508 468.467c-58.005-58.013-58.016-151.92 0-209.943l225.011-225.04c44.643-44.645 117.279-44.645 161.92 0 44.743 44.749 44.753 117.186 0 161.944l-189.465 189.49c-31.41 31.413-82.518 31.412-113.926.001-31.479-31.482-31.49-82.453 0-113.944L311.51 110.491c4.687-4.687 12.286-4.687 16.972 0l16.967 16.971c4.685 4.686 4.685 12.283 0 16.969L184.983 304.917c-12.724 12.724-12.73 33.328 0 46.058 12.696 12.697 33.356 12.699 46.054-.001l189.465-189.489c25.987-25.989 25.994-68.06.001-94.056-25.931-25.934-68.119-25.932-94.049 0l-225.01 225.039c-39.249 39.252-39.258 102.795-.001 142.057 39.285 39.29 102.885 39.287 142.162-.028A739446.174 739446.174 0 0 1 439.497 238.49c4.686-4.687 12.282-4.684 16.969.004l16.967 16.971c4.685 4.686 4.689 12.279.004 16.965a755654.128 755654.128 0 0 0-195.881 195.996c-58.034 58.092-152.004 58.093-210.048.041z">
					</path>
				</svg>
			</span>
			<span class="kwt-file__msg">ADD / UPLOAD ITEMS</span>
			<?php wp_nonce_field('pfg_add_images', 'pfg_add_images_nonce'); ?>
			<input class="kwt-file__input add-new-images" id="upload_image_button" name="upload_image_button"
				value="Upload Image">
			<div class="kwt-file__delete"></div>
		</div>
	</div>
</div>

<div class="row">
	<div class="d-flex align-items-start bhoechie-tab-container">
		<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 bhoechie-tab-menu">
			<div class="list-group">
				<a href="#" class="list-group-item active">
					<span class="dashicons dashicons-format-image"></span>
					<?php _e('Photos', 'portfolio-filter-gallery'); ?>
				</a>
				<a href="#" class="list-group-item">
					<span class="dashicons dashicons-admin-generic"></span>
					<?php _e('Config', 'portfolio-filter-gallery'); ?>
				</a>
				<a href="#" class="list-group-item">
					<span class="dashicons dashicons-editor-insertmore"></span>
					<?php _e('Filters', 'portfolio-filter-gallery'); ?>
				</a>
				<a href="#" class="list-group-item">
					<span class="dashicons dashicons-welcome-view-site"></span>
					<?php _e('LightBox', 'portfolio-filter-gallery'); ?>
				</a>
				<a href="#" class="list-group-item">
					<span class="dashicons dashicons-media-code"></span>
					<?php _e('Custom CSS', 'portfolio-filter-gallery'); ?>
				</a>
				<a href="#" class="list-group-item " style="background-color:#f6f7f7a6;">
					<span class="dashicons dashicons-layout"></span>
					<?php _e('Layouts', 'portfolio-filter-gallery'); ?>
				</a>
				<a href="#" class="list-group-item" style="background:#f6f7f7a6;">
					<span class="dashicons dashicons-screenoptions"></span>
					<?php _e('Load More', 'portfolio-filter-gallery'); ?>
				</a>

				<a href="#" class="list-group-item" style="background:#f6f7f7a6;">
					<span class="dashicons dashicons-unlock"></span>
					<?php _e('Upgrade To Pro', 'portfolio-filter-gallery'); ?>
				</a>
			</div>
		</div>
		<div class="col-lg-10 col-md-10 col-sm-10 col-xs-10 bhoechie-tab">
			<!-- flight section -->
			<div class="bhoechie-tab-content active">
				<h1>
					<?php _e('Photos', 'portfolio-filter-gallery'); ?>
				</h1>

				<!--Photos from wordpress-->
				<div id="image-gallery">
					<p class="filter-notice">
						<?php _e('First add filters for images by click on', 'portfolio-filter-gallery'); ?> <a
							href="edit.php?post_type=awl_filter_gallery&page=pfg-filter-page">
							<?php _e('FILTERS', 'portfolio-filter-gallery'); ?>
						</a>
						<?php _e('menu link.', 'portfolio-filter-gallery'); ?><span
							class="pfg-dismiss dashicons dashicons-no-alt"></span>
					</p>
					<p class="repeat-notice">
						<?php _e('Please do not reapeat images. Use control ( Ctrl ) or shift ( Shift ) key for select multiple filters. For unselect filters use ( Ctrl ) key.', 'portfolio-filter-gallery'); ?>
						<span class="pfg-dismiss dashicons dashicons-no-alt"></span>
					</p>

					<input type="button" id="remove-all-images" name="remove-all-images"
						class="pfg-btn button button-large remove-all-images" rel=""
						value="<?php _e('Delete All Images', 'portfolio-filter-gallery'); ?>">
					<div class="btn-group" id="sort-all-slides">
						<button onclick="return SortSlides('Shuffle');" type="button"
							class="pfg-btn button button-large">
							<?php _e('Shuffle', 'portfolio-filter-gallery'); ?>
						</button>
					</div>

					<br>
					<ul id="remove-images" class="sbox listitems">
						<?php
						$allimagesetting = get_post_meta($post->ID, 'awl_filter_gallery' . $post->ID, true);
						$all_category = get_option('awl_portfolio_filter_gallery_categories');

						if (isset($allimagesetting['image-ids'])) {
							if (array_key_exists("filters", $allimagesetting)) {
								$filters = $allimagesetting['filters'];
							}
							$count = 0;
							foreach ($allimagesetting['image-ids'] as $id) {
								$thumbnail = wp_get_attachment_image_src($id, 'thumbnail', true);
								$attachment = get_post($id);
								$title = get_the_title($id);
								if (isset($allimagesetting['image-link'][$count])) {
									$image_link = $allimagesetting['image-link'][$count];
								} else {
									$image_link = "";
								}
								if (isset($allimagesetting['image-desc'][$count])) {
									$image_desc = $allimagesetting['image-desc'][$count];
								} else {
									$image_desc = "";
								}
								if (isset($allimagesetting['slide-type'][$count])) {
									$image_type = $allimagesetting['slide-type'][$count];
								} else {
									$image_type = "";
								}
								?>
								<li class="item image">
									<img class="new-image" src="<?php echo esc_url($thumbnail[0]); ?>"
										alt="<?php echo esc_attr($title); ?>"
										style="height: 150px; width: 98%; border-radius: 8px;">
									<input type="hidden" name="image-ids[]" value="<?php echo esc_attr($id); ?>" />

									<select name="slide-type[]" class="form-control" style="width: 98% !important;"
										placeholder="Image Title" value="<?php echo esc_html($image_type); ?>">
										<option value="image" <?php if ($image_type == "image")
											echo "selected=selected"; ?>>
											<?php _e('Image', 'portfolio-filter-gallery'); ?>
										</option>
										<option value="video" <?php if ($image_type == "video")
											echo "selected=selected"; ?>>
											<?php _e('Video', 'portfolio-filter-gallery'); ?>
										</option>
									</select>

									<input type="text" name="image-title[]" style="width: 98%;" placeholder="Image Title"
										value="<?php echo esc_attr($title); ?>">
									<textarea name="image-desc[]" style="width: 98%; display:none;"
										placeholder="Type discription here.."><?php echo stripcslashes(esc_html($image_desc)); ?></textarea>
									<input type="text" name="image-link[]" style="width: 98%;"
										placeholder="Video URL / Link URL" value="<?php echo esc_url($image_link); ?>">
									<?php
									if (isset($filters[$id])) {
										$selected_filters_array = $filters[$id];
									} else {
										$selected_filters_array = array();
									}
									?>
									<select class="pfg-filters form-control" name="filters[<?php echo esc_attr($id); ?>][]"
										multiple="multiple">
										<?php
										foreach ($all_category as $key => $value) {
											if ($key != 0) {
												?>
													<option value="<?php echo esc_attr($key); ?>" <?php if (count($selected_filters_array)) {
														   if (in_array($key, $selected_filters_array))
															   echo "selected=selected";
													   } ?>>
														<?php echo stripslashes(esc_html($value)); ?>
													</option>
												
												<?php
											}
										}
										?>
									</select>

									<?php foreach ($selected_filters_array as $key => $value) {
										//print_r($selected_filters_array);
										?>
										<input type="hidden" name="filter-image[<?php echo esc_attr($value); ?>][]" style="width: 98%;"
											value="<?php echo esc_attr($id); ?>">
									<?php } ?>
									<a class="pw-trash-icon" name="remove-image" id="remove-image" href="#"><span
											class="dashicons dashicons-trash"></span></a>
								</li>

								<?php $count++;
							} // end of foreach
						} //end of if
						?>
					</ul>
				</div>
				<script type="text/javascript">
					function SortSlides(order) {
						if (order == "Shuffle") {
							var $items = jQuery(".listitems li.item").get();
							// Fisher-Yates (Durstenfeld) shuffle algorithm
							for (var i = $items.length - 1; i > 0; i--) {
								var j = Math.floor(Math.random() * (i + 1));
								var temp = $items[i];
								$items[i] = $items[j];
								$items[j] = temp;
							}
							jQuery('.listitems').append($items);
						}
					}
				</script>
			</div>

			<!-- Configuration -->
			<div class="bhoechie-tab-content">
				<h1>
					<?php _e('Configuration', 'portfolio-filter-gallery'); ?>
				</h1>

				<!--Grid-->
				<div class="pw_grid_layout_config">
					<div class="row">
						<div class="col-md-4">
							<div class="ma_field_discription">
								<h4>
									<?php _e('Gallery Thumbnail Size', 'portfolio-filter-gallery'); ?>
								</h4>
								<p>
									<?php _e('Choose gallery thumbnail size', 'portfolio-filter-gallery'); ?>
								</p>
							</div>
						</div>
						<div class="col-md-8">
							<div class="ma_field panel-body">
								<?php if (isset($pf_gallery_settings['gal_size']))
									$gal_size = $pf_gallery_settings['gal_size'];
								else
									$gal_size = "large"; ?>
								<select id="gal_size" name="gal_size" class="selectbox_settings form-control">
									<option value="thumbnail" <?php if ($gal_size == "thumbnail")
										echo "selected=selected"; ?>>
										<?php _e('Thumbnail - 150 x 150', 'portfolio-filter-gallery'); ?>
									</option>
									<option value="medium" <?php if ($gal_size == "medium")
										echo "selected=selected"; ?>>
										<?php _e('Medium - 300 x 169', 'portfolio-filter-gallery'); ?>
									</option>
									<option value="large" <?php if ($gal_size == "large")
										echo "selected=selected"; ?>>
										<?php _e('Large - 840 x 473', 'portfolio-filter-gallery'); ?>
									</option>
									<option value="full" <?php if ($gal_size == "full")
										echo "selected=selected"; ?>>
										<?php _e('Full Size - 1280 x 720', 'portfolio-filter-gallery'); ?>
									</option>
								</select>
							</div>
						</div>
					</div>
					<div id="" class="meta_box_holder_inside">
						<h2>
							<?php _e('Columns Settings', 'portfolio-filter-gallery'); ?>
						</h2>
						<div class="row">
							<div class="col-md-4">
								<div class="ma_field_discription">
									<h4>
										<?php _e('Columns On Desktops', 'portfolio-filter-gallery'); ?>
									</h4>
									<p>
										<?php _e('Set columns for large desktops', 'portfolio-filter-gallery'); ?>
									</p>
								</div>
							</div>
							<div class="col-md-8">
								<div class="ma_field panel-body">
									<div class="switch-field em_size_field">
										<?php if (isset($pf_gallery_settings['col_large_desktops']))
											$col_large_desktops = $pf_gallery_settings['col_large_desktops'];
										else
											$col_large_desktops = "col-lg-3"; ?>
										<select id="col_large_desktops" name="col_large_desktops"
											class="selectbox_settings form-control">
											<option value="col-lg-12" <?php if ($col_large_desktops == "col-lg-12")
												echo "selected=selected"; ?>>
												<?php _e('1 Column', 'portfolio-filter-gallery'); ?>
											</option>
											<option value="col-lg-6" <?php if ($col_large_desktops == "col-lg-6")
												echo "selected=selected"; ?>>
												<?php _e('2 Column', 'portfolio-filter-gallery'); ?>
											</option>
											<option value="col-lg-4" <?php if ($col_large_desktops == "col-lg-4")
												echo "selected=selected"; ?>>
												<?php _e('3 Column', 'portfolio-filter-gallery'); ?>
											</option>
											<option value="col-lg-3" <?php if ($col_large_desktops == "col-lg-3")
												echo "selected=selected"; ?>>
												<?php _e('4 Column', 'portfolio-filter-gallery'); ?>
											</option>
											<option value="col-lg-2" <?php if ($col_large_desktops == "col-lg-2")
												echo "selected=selected"; ?>>
												<?php _e('6 Column', 'portfolio-filter-gallery'); ?>
											</option>
											<option value="col-lg-1" <?php if ($col_large_desktops == "col-lg-1")
												echo "selected=selected"; ?>>
												<?php _e('12 Columns', 'portfolio-filter-gallery'); ?>
											</option>
										</select>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-4">
								<div class="ma_field_discription">
									<h4>
										<?php _e('Columns On Tablet', 'portfolio-filter-gallery'); ?>
									</h4>
									<p>
										<?php _e('Set columns for desktops', 'portfolio-filter-gallery'); ?>
									</p>
								</div>
							</div>
							<div class="col-md-8">
								<div class="ma_field panel-body">
									<div class="switch-field em_size_field">
										<?php if (isset($pf_gallery_settings['col_desktops']))
											$col_desktops = $pf_gallery_settings['col_desktops'];
										else
											$col_desktops = "col-lg-3"; ?>
										<select id="col_desktops" name="col_desktops"
											class="selectbox_settings form-control">
											<option value="col-md-12" <?php if ($col_desktops == "col-md-12")
												echo "selected=selected"; ?>>
												<?php _e('1 Column', 'portfolio-filter-gallery'); ?>
											</option>
											<option value="col-md-6" <?php if ($col_desktops == "col-md-6")
												echo "selected=selected"; ?>>
												<?php _e('2 Column', 'portfolio-filter-gallery'); ?>
											</option>
											<option value="col-md-4" <?php if ($col_desktops == "col-md-4")
												echo "selected=selected"; ?>>
												<?php _e('3 Column', 'portfolio-filter-gallery'); ?>
											</option>
											<option value="col-md-3" <?php if ($col_desktops == "col-md-3")
												echo "selected=selected"; ?>>
												<?php _e('4 Column', 'portfolio-filter-gallery'); ?>
											</option>
											<option value="col-md-2" <?php if ($col_desktops == "col-md-2")
												echo "selected=selected"; ?>>
												<?php _e('6 Column', 'portfolio-filter-gallery'); ?>
											</option>
											<option value="col-md-1" <?php if ($col_desktops == "col-md-1")
												echo "selected=selected"; ?>>
												<?php _e('12 Column', 'portfolio-filter-gallery'); ?>
											</option>
										</select>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-4">
								<div class="ma_field_discription">
									<h4>
										<?php _e('Columns On Phone Landscape', 'portfolio-filter-gallery'); ?>
									</h4>
									<p>
										<?php _e('Set columns for tablets', 'portfolio-filter-gallery'); ?>
									</p>
								</div>
							</div>
							<div class="col-md-8">
								<div class="ma_field panel-body">
									<div class="switch-field em_size_field">
										<?php if (isset($pf_gallery_settings['col_tablets']))
											$col_tablets = $pf_gallery_settings['col_tablets'];
										else
											$col_tablets = "col-sm-4"; ?>
										<select id="col_tablets" name="col_tablets"
											class="selectbox_settings form-control">
											<option value="col-sm-12" <?php if ($col_tablets == "col-sm-12")
												echo "selected=selected"; ?>>
												<?php _e('1 Column', 'portfolio-filter-gallery'); ?>
											</option>
											<option value="col-sm-6" <?php if ($col_tablets == "col-sm-6")
												echo "selected=selected"; ?>>
												<?php _e('2 Column', 'portfolio-filter-gallery'); ?>
											</option>
											<option value="col-sm-4" <?php if ($col_tablets == "col-sm-4")
												echo "selected=selected"; ?>>
												<?php _e('3 Column', 'portfolio-filter-gallery'); ?>
											</option>
											<option value="col-sm-3" <?php if ($col_tablets == "col-sm-3")
												echo "selected=selected"; ?>>
												<?php _e('4 Column', 'portfolio-filter-gallery'); ?>
											</option>
											<option value="col-sm-2" <?php if ($col_tablets == "col-sm-2")
												echo "selected=selected"; ?>>
												<?php _e('6 Column', 'portfolio-filter-gallery'); ?>
											</option>
										</select>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-4">
								<div class="ma_field_discription">
									<h4>
										<?php _e('Columns On Phone Portrait', 'portfolio-filter-gallery'); ?>
									</h4>
									<p>
										<?php _e('Set columns for large desktops', 'portfolio-filter-gallery'); ?>
									</p>
								</div>
							</div>
							<div class="col-md-8">
								<div class="ma_field panel-body">
									<div class="switch-field em_size_field">
										<?php if (isset($pf_gallery_settings['col_phones']))
											$col_phones = $pf_gallery_settings['col_phones'];
										else
											$col_phones = "col-xs-6"; ?>
										<select id="col_phones" name="col_phones"
											class="selectbox_settings form-control">
											<option value="col-12" <?php if ($col_phones == "col-12")
												echo "selected=selected"; ?>>
												<?php _e('1 Column', 'portfolio-filter-gallery'); ?>
											</option>
											<option value="col-6" <?php if ($col_phones == "col-6")
												echo "selected=selected"; ?>>
												<?php _e('2 Column', 'portfolio-filter-gallery'); ?>
											</option>
											<option value="col-4" <?php if ($col_phones == "col-4")
												echo "selected=selected"; ?>>
												<?php _e('3 Column', 'portfolio-filter-gallery'); ?>
											</option>
											<option value="col-3" <?php if ($col_phones == "col-3")
												echo "selected=selected"; ?>>
												<?php _e('4 Column', 'portfolio-filter-gallery'); ?>
											</option>
										</select>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!--Direction-->
					<div class="row">
						<div class="col-md-4">
							<div class="ma_field_discription">
								<h4>
									<?php _e('Gallery Direction', 'portfolio-filter-gallery'); ?>
								</h4>
								<p>
									<?php _e('Change direction for RTL site', 'portfolio-filter-gallery'); ?>
								</p>
							</div>
						</div>
						<div class="col-md-8">
							<div class="ma_field panel-body">
								<p class="switch-field em_size_field">
									<?php if (isset($pf_gallery_settings['gallery_direction']))
										$gallery_direction = $pf_gallery_settings['gallery_direction'];
									else
										$gallery_direction = "ltr"; ?>
									<input type="radio" name="gallery_direction" id="gallery_direction1" value="rtl"
										<?php if ($gallery_direction == "rtl")
											echo "checked=checked"; ?>>
									<label for="gallery_direction1">
										<?php _e('Rtl', 'portfolio-filter-gallery'); ?>
									</label>
									<input type="radio" name="gallery_direction" id="gallery_direction2" value="ltr"
										<?php if ($gallery_direction == "ltr")
											echo "checked=checked"; ?>>
									<label for="gallery_direction2">
										<?php _e('Ltr', 'portfolio-filter-gallery'); ?>
									</label>
								</p>
							</div>
						</div>
					</div>
					<!--Hover-->
					<div class="row">
						<div class="col-md-4">
							<div class="ma_field_discription">
								<h4>
									<?php _e('Image Hover Effects', 'portfolio-filter-gallery'); ?>
								</h4>
								<p>
									<?php _e('Choose Image Hover Effect', 'portfolio-filter-gallery'); ?>
								</p>
							</div>
						</div>
						<div class="col-md-8">
							<div class="ma_field panel-body">
								<!-- 2d -->
								<div class="he_four">
									<?php if (isset($pf_gallery_settings['image_hover_effect_four']))
										$image_hover_effect_four = $pf_gallery_settings['image_hover_effect_four'];
									else
										$image_hover_effect_four = "hvr-grow-shadow"; ?>
									<select name="image_hover_effect_four" id="image_hover_effect_four"
										class="selectbox_settings">
										<option value="none" <?php if ($image_hover_effect_four == "none")
											echo "selected=selected"; ?>>
											<?php _e('None', 'portfolio-filter-gallery'); ?>
										</option>
										<option value="hvr-grow-shadow" <?php if ($image_hover_effect_four == "hvr-grow-shadow")
											echo "selected=selected"; ?>>
											<?php _e('Grow Shadow', 'portfolio-filter-gallery'); ?>
										</option>
										<option value="hvr-float-shadow" <?php if ($image_hover_effect_four == "hvr-float-shadow")
											echo "selected=selected"; ?>>
											<?php _e('Float Shadow', 'portfolio-filter-gallery'); ?>
										</option>
										<option value="hvr-glow" <?php if ($image_hover_effect_four == "hvr-glow")
											echo "selected=selected"; ?>>
											<?php _e('Glow', 'portfolio-filter-gallery'); ?>
										</option>
										<option value="hvr-box-shadow-outset" <?php if ($image_hover_effect_four == "hvr-box-shadow-outset")
											echo "selected=selected"; ?>>
											<?php _e('Box Shadow Outset', 'portfolio-filter-gallery'); ?>
										</option>
										<option value="hvr-box-shadow-inset" <?php if ($image_hover_effect_four == "hvr-box-shadow-inset")
											echo "selected=selected"; ?>>
											<?php _e('Box Shadow Inset', 'portfolio-filter-gallery'); ?>
										</option>
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							<div class="ma_field_discription">
								<h4>
									<?php _e('Thumbnail Border', 'portfolio-filter-gallery'); ?>
								</h4>
								<p>
									<?php _e('You can remove image border', 'portfolio-filter-gallery'); ?>
								</p>
							</div>
						</div>
						<div class="col-md-8">
							<div class="ma_field panel-body">
								<p class="switch-field em_size_field">
									<?php if (isset($pf_gallery_settings['thumb_border']))
										$thumb_border = $pf_gallery_settings['thumb_border'];
									else
										$thumb_border = "yes"; ?>
									<input type="radio" name="thumb_border" id="thumb_border1" value="yes" <?php if ($thumb_border == "yes")
										echo "checked=checked"; ?>>
									<label for="thumb_border1">
										<?php _e('Yes', 'portfolio-filter-gallery'); ?>
									</label>
									<input type="radio" name="thumb_border" id="thumb_border2" value="no" <?php if ($thumb_border == "no")
										echo "checked=checked"; ?>>
									<label for="thumb_border2">
										<?php _e('No', 'portfolio-filter-gallery'); ?>
									</label>
								</p>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							<div class="ma_field_discription">
								<h4>
									<?php _e('Hide Thumbnails Spacing', 'portfolio-filter-gallery'); ?>
								</h4>
								<p>
									<?php _e('You can remove thumbnails spacing', 'portfolio-filter-gallery'); ?>
								</p>
							</div>
						</div>
						<div class="col-md-8">
							<div class="ma_field panel-body">
								<p class="switch-field em_size_field">
									<?php if (isset($pf_gallery_settings['no_spacing']))
										$no_spacing = $pf_gallery_settings['no_spacing'];
									else
										$no_spacing = 0; ?>
									<input type="radio" name="no_spacing" id="no_spacing1" value="1" <?php if ($no_spacing == 1)
										echo "checked=checked"; ?>>
									<label for="no_spacing1">
										<?php _e('Yes', 'portfolio-filter-gallery'); ?>
									</label>
									<input type="radio" name="no_spacing" id="no_spacing2" value="0" <?php if ($no_spacing == 0)
										echo "checked=checked"; ?>>
									<label for="no_spacing2">
										<?php _e('No', 'portfolio-filter-gallery'); ?>
									</label>
								</p>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							<div class="ma_field_discription">
								<h4>
									<?php _e('Title On Thumbnail', 'portfolio-filter-gallery'); ?>
								</h4>
								<p>
									<?php _e('Title on thumbnail', 'portfolio-filter-gallery'); ?>
								</p>
							</div>
						</div>
						<div class="col-md-8">
							<div class="ma_field panel-body">
								<p class="switch-field em_size_field">
									<?php if (isset($pf_gallery_settings['title_thumb']))
										$title_thumb = $pf_gallery_settings['title_thumb'];
									else
										$title_thumb = "show"; ?>
									<input type="radio" name="title_thumb" id="title_thumb1" value="show" <?php if ($title_thumb == "show")
										echo "checked=checked"; ?>>
									<label for="title_thumb1">
										<?php _e('Show', 'portfolio-filter-gallery'); ?>
									</label>
									<input type="radio" name="title_thumb" id="title_thumb2" value="hide" <?php if ($title_thumb == "hide")
										echo "checked=checked"; ?>>
									<label for="title_thumb2">
										<?php _e('Hide', 'portfolio-filter-gallery'); ?>
									</label>
								</p>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							<div class="ma_field_discription">
								<h4>
									<?php _e('Show Numbering On Thumbnails', 'portfolio-filter-gallery'); ?>
								</h4>
								<p>
									<?php _e('Show numbering on thumbnails', 'portfolio-filter-gallery'); ?>
								</p>
							</div>
						</div>
						<div class="col-md-8">
							<div class="ma_field panel-body">
								<p class="switch-field em_size_field">
									<?php if (isset($pf_gallery_settings['image_numbering']))
										$image_numbering = $pf_gallery_settings['image_numbering'];
									else
										$image_numbering = "0"; ?>
									<input type="radio" name="image_numbering" id="image_numbering1" value="1" <?php if ($image_numbering == 1)
										echo "checked=checked"; ?>>
									<label for="image_numbering1">
										<?php _e('Yes', 'portfolio-filter-gallery'); ?>
									</label>
									<input type="radio" name="image_numbering" id="image_numbering2" value="0" <?php if ($image_numbering == 0)
										echo "checked=checked"; ?>>
									<label for="image_numbering2">
										<?php _e('No', 'portfolio-filter-gallery'); ?>
									</label>
								</p>
							</div>
						</div>
					</div>
				</div>
				<!--URL Gray Scale-->
				<div class="row">
					<div class="col-md-4">
						<div class="ma_field_discription">
							<h4>
								<?php _e('Image Gray Scale (Gray Effect)', 'portfolio-filter-gallery'); ?>
							</h4>
							<p>
								<?php _e('Image gray scale', 'portfolio-filter-gallery'); ?>
							</p>
						</div>
					</div>
					<div class="col-md-8">
						<div class="ma_field panel-body">
							<p class="switch-field em_size_field">
								<?php if (isset($pf_gallery_settings['gray_scale']))
									$gray_scale = $pf_gallery_settings['gray_scale'];
								else
									$gray_scale = 0; ?>
								<input type="radio" name="gray_scale" id="gray_scale1" value="1" <?php if ($gray_scale == 1)
									echo "checked=checked"; ?>>
								<label for="gray_scale1">
									<?php _e('Yes', 'portfolio-filter-gallery'); ?>
								</label>
								<input type="radio" name="gray_scale" id="gray_scale2" value="0" <?php if ($gray_scale == 0)
									echo "checked=checked"; ?>>
								<label for="gray_scale2">
									<?php _e('No', 'portfolio-filter-gallery'); ?>
								</label>
							</p>
						</div>
					</div>
				</div>
				<!--Sort by title-->
				<div class="row">
					<div class="col-md-4">
						<div class="ma_field_discription">
							<h4>
								<?php _e('Image Sort by Title', 'portfolio-filter-gallery'); ?>
							</h4>
							<p>
								<?php _e('Image sort by title', 'portfolio-filter-gallery'); ?>
							</p>
						</div>
					</div>
					<div class="col-md-8">
						<div class="ma_field panel-body">
							<p class="switch-field em_size_field">
								<?php if (isset($pf_gallery_settings['sort_by_title']))
									$sort_by_title = $pf_gallery_settings['sort_by_title'];
								else
									$sort_by_title = "no"; ?>
								<input type="radio" name="sort_by_title" id="sort_by_title1" value="asc" <?php if ($sort_by_title == "asc")
									echo "checked=checked"; ?>>
								<label for="sort_by_title1">
									<?php _e('ASC', 'portfolio-filter-gallery'); ?>
								</label>
								<input type="radio" name="sort_by_title" id="sort_by_title2" value="desc" <?php if ($sort_by_title == "desc")
									echo "checked=checked"; ?>>
								<label for="sort_by_title2">
									<?php _e('DESC', 'portfolio-filter-gallery'); ?>
								</label>
								<input type="radio" name="sort_by_title" id="sort_by_title3" value="no" <?php if ($sort_by_title == "no")
									echo "checked=checked"; ?>>
								<label for="sort_by_title3">
									<?php _e('No', 'portfolio-filter-gallery'); ?>
								</label>
							</p>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-4">
						<div class="ma_field_discription">
							<h4>
								<?php _e('Open Image Link URL', 'portfolio-filter-gallery'); ?>
							</h4>
							<p>
								<?php _e('Open image link URL', 'portfolio-filter-gallery'); ?>
							</p>
						</div>
					</div>
					<div class="col-md-8">
						<div class=" panel-body">
							<p class="switch-field em_size_field hover_field">
								<?php if (isset($pf_gallery_settings['url_target']))
									$url_target = $pf_gallery_settings['url_target'];
								else
									$url_target = "_blank"; ?>
								<input type="radio" name="url_target" id="url_target1" value="_blank" <?php if ($url_target == "_blank")
									echo "checked=checked"; ?>>
								<label for="url_target1">
									<?php _e('New Tab', 'portfolio-filter-gallery'); ?>
								</label>
								<input type="radio" name="url_target" id="url_target2" value="_self" <?php if ($url_target == "_self")
									echo "checked=checked"; ?>>
								<label for="url_target2">
									<?php _e('Same Tab', 'portfolio-filter-gallery'); ?>
								</label>
							</p>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-4">
						<div class="ma_field_discription">
							<h4>
								<?php _e('Disable Bootstrap JS for Output', 'portfolio-filter-gallery'); ?>
							</h4>
							<p>
								<?php _e('You can disable bootstrap js for output if you have problem with it.', 'portfolio-filter-gallery'); ?>
							</p>
						</div>
					</div>
					<div class="col-md-8">
						<div class="ma_field panel-body">
							<p class="switch-field em_size_field">
								<?php if (isset($pf_gallery_settings['bootstrap_disable']))
									$bootstrap_disable = $pf_gallery_settings['bootstrap_disable'];
								else
									$bootstrap_disable = "no"; ?>
								<input type="radio" name="bootstrap_disable" id="bootstrap_disable1" value="yes" <?php if ($bootstrap_disable == "yes")
									echo "checked=checked"; ?>>
								<label for="bootstrap_disable1">
									<?php _e('Yes', 'portfolio-filter-gallery'); ?>
								</label>
								<input type="radio" name="bootstrap_disable" id="bootstrap_disable2" value="no" <?php if ($bootstrap_disable == "no")
									echo "checked=checked"; ?>>
								<label for="bootstrap_disable2">
									<?php _e('No', 'portfolio-filter-gallery'); ?>
								</label>
							</p>
						</div>
					</div>
				</div>
			</div>
			<div class="bhoechie-tab-content">
				<h1>
					<?php _e('Filters Settings', 'portfolio-filter-gallery'); ?>
				</h1>


				<!-- FIlters-->
				<div class="row">
					<div class="col-md-4">
						<div class="ma_field_discription">
							<h4>
								<?php _e('Hide Filters', 'portfolio-filter-gallery'); ?>
							</h4>
							<p>
								<?php _e('Hide filters', 'portfolio-filter-gallery'); ?>
							</p>
						</div>
					</div>
					<div class="col-md-8">
						<div class="ma_field panel-body">
							<?php if (isset($pf_gallery_settings['hide_filters']))
								$hide_filters = $pf_gallery_settings['hide_filters'];
							else
								$hide_filters = 0; ?>
							<p class="switch-field em_size_field">
								<input type="radio" name="hide_filters" id="hide_filters1" value="1" <?php if ($hide_filters == 1)
									echo "checked=checked"; ?>>
								<label for="hide_filters1">
									<?php _e('Yes', 'portfolio-filter-gallery'); ?>
								</label>
								<input type="radio" name="hide_filters" id="hide_filters2" value="0" <?php if ($hide_filters == 0)
									echo "checked=checked"; ?>>
								<label for="hide_filters2">
									<?php _e('No', 'portfolio-filter-gallery'); ?>
								</label>
							</p>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-4">
						<div class="ma_field_discription">
							<h4>
								<?php _e('Text For "All" Filter', 'portfolio-filter-gallery'); ?>
							</h4>
							<p>
								<?php _e('Text for "All" filter', 'portfolio-filter-gallery'); ?>
							</p>
						</div>
					</div>
					<div class="col-md-8">
						<div class="ma_field panel-body">
							<?php if (isset($pf_gallery_settings['all_txt']))
								$all_txt = $pf_gallery_settings['all_txt'];
							else
								$all_txt = 'All'; ?>
							<input type="text" class="selectbox_settings sort" id="all_txt" name="all_txt"
								value="<?php echo esc_attr($all_txt); ?>">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-4">
						<div class="ma_field_discription">
							<h4>
								<?php _e('Sort Filter In Alphabet Order', 'portfolio-filter-gallery'); ?>
							</h4>
							<p>
								<?php _e('Sort filter in alphabet order', 'portfolio-filter-gallery'); ?>
							</p>
						</div>
					</div>
					<div class="col-md-8">

						<div class="ma_field panel-body">
							<?php if (isset($pf_gallery_settings['sort_filter_order']))
								$sort_filter_order = $pf_gallery_settings['sort_filter_order'];
							else
								$sort_filter_order = 0; ?>
							<p class="switch-field em_size_field">
								<input type="radio" name="sort_filter_order" id="sort_filter_order1" value="1" <?php if ($sort_filter_order == 1)
									echo "checked=checked"; ?>>
								<label for="sort_filter_order1">
									<?php _e('Yes', 'portfolio-filter-gallery'); ?>
								</label>
								<input type="radio" name="sort_filter_order" id="sort_filter_order2" value="0" <?php if ($sort_filter_order == 0)
									echo "checked=checked"; ?>>
								<label for="sort_filter_order2">
									<?php _e('No', 'portfolio-filter-gallery'); ?>
								</label>
							</p>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-4">
						<div class="ma_field_discription">
							<h4>
								<?php _e('Show Image Count On Filters', 'portfolio-filter-gallery'); ?>
							</h4>
							<p>
								<?php _e('Show Image Count On Filters', 'portfolio-filter-gallery'); ?>
							</p>
						</div>
					</div>
					<div class="col-md-8">

						<div class="ma_field panel-body">
							<?php if (isset($pf_gallery_settings['show_image_count']))
								$show_image_count = $pf_gallery_settings['show_image_count'];
							else
								$show_image_count = 0; ?>
							<p class="switch-field em_size_field">
								<input type="radio" name="show_image_count" id="show_image_count1" value="1" <?php if ($show_image_count == 1)
									echo "checked=checked"; ?>>
								<label for="show_image_count1">
									<?php _e('Yes', 'portfolio-filter-gallery'); ?>
								</label>
								<input type="radio" name="show_image_count" id="show_image_count2" value="0" <?php if ($show_image_count == 0)
									echo "checked=checked"; ?>>
								<label for="show_image_count2">
									<?php _e('No', 'portfolio-filter-gallery'); ?>
								</label>
							</p>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-4">
						<div class="ma_field_discription">
							<h4>
								<?php _e('Filters Position', 'portfolio-filter-gallery'); ?>
							</h4>
							<p>
								<?php _e('Choose filters position', 'portfolio-filter-gallery'); ?>
							</p>
						</div>
					</div>
					<div class="col-md-8 ">
						<div class="ma_field panel-body">
							<?php if (isset($pf_gallery_settings['filter_position']))
								$filter_position = $pf_gallery_settings['filter_position'];
							else
								$filter_position = "center"; ?>
							<select id="filter_position" name="filter_position" class="selectbox_settings form-control">
								<option value="right" <?php if ($filter_position == "right")
									echo "selected=selected"; ?>>
									<?php _e('Right', 'portfolio-filter-gallery'); ?>
								</option>
								<option value="center" <?php if ($filter_position == "center")
									echo "selected=selected"; ?>>
									<?php _e('Center', 'portfolio-filter-gallery'); ?>
								</option>
								<option value="left" <?php if ($filter_position == "left")
									echo "selected=selected"; ?>>
									<?php _e('Left', 'portfolio-filter-gallery'); ?>
								</option>
							</select>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-4">
						<div class="ma_field_discription">
							<h4>
								<?php _e('Search Box', 'portfolio-filter-gallery'); ?>
							</h4>
							<p>
								<?php _e('Show search', 'portfolio-filter-gallery'); ?>
							</p>
						</div>
					</div>
					<div class="col-md-8">
						<div class="ma_field panel-body">
							<p class="switch-field em_size_field">
								<?php if (isset($pf_gallery_settings['search_box']))
									$search_box = $pf_gallery_settings['search_box'];
								else
									$search_box = 1; ?>
								<input type="radio" name="search_box" id="search_box1" value="1" <?php if ($search_box == 1)
									echo "checked=checked"; ?>>
								<label for="search_box1">
									<?php _e('Yes', 'portfolio-filter-gallery'); ?>
								</label>
								<input type="radio" name="search_box" id="search_box2" value="0" <?php if ($search_box == 0)
									echo "checked=checked"; ?>>
								<label for="search_box2">
									<?php _e('No', 'portfolio-filter-gallery'); ?>
								</label>
							</p>
							<?php if (isset($pf_gallery_settings['search_txt']))
								$search_txt = $pf_gallery_settings['search_txt'];
							else
								$search_txt = 'Search Images'; ?>
							<input type="text" class="selectbox_settings sort" id="search_txt" name="search_txt"
								value="<?php echo esc_attr($search_txt); ?>">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-4">
						<div class="ma_field_discription">
							<h4>
								<?php _e('Filter Background Color', 'portfolio-filter-gallery'); ?>
							</h4>
							<p>
								<?php _e('Filter background color', 'portfolio-filter-gallery'); ?>
							</p>
						</div>
					</div>
					<div class="col-md-8">
						<div class="ma_field panel-body">
							<?php if (isset($pf_gallery_settings['filter_bg']))
								$filter_bg = $pf_gallery_settings['filter_bg'];
							else
								$filter_bg = '#656565'; ?>
							<input type="text" class="form-control" id="filter_bg" name="filter_bg"
								placeholder="chose form color" value="<?php echo esc_html($filter_bg); ?>"
								default-color="<?php echo esc_html($filter_bg); ?>">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-4">
						<div class="ma_field_discription">
							<h4>
								<?php _e('Filter Title Color', 'portfolio-filter-gallery'); ?>
							</h4>
							<p>
								<?php _e('Filter title color', 'portfolio-filter-gallery'); ?>
							</p>
						</div>
					</div>
					<div class="col-md-8">
						<div class="ma_field panel-body">
							<?php if (isset($pf_gallery_settings['filter_title_color']))
								$filter_title_color = $pf_gallery_settings['filter_title_color'];
							else
								$filter_title_color = '#ffffff'; ?>
							<input type="text" class="form-control" id="filter_title_color" name="filter_title_color"
								placeholder="chose form color" value="<?php echo esc_html($filter_title_color); ?>"
								default-color="<?php echo esc_html($filter_title_color); ?>">
						</div>
					</div>
				</div>
			</div>
			<div class="bhoechie-tab-content">
				<h1>
					<?php _e('LightBox Configuration', 'portfolio-filter-gallery'); ?>
				</h1>

				<!-- lighbox -->
				<div class="row">
					<div class="col-md-4">
						<div class="ma_field_discription">
							<h4>
								<?php _e('Enable Lightbox', 'portfolio-filter-gallery'); ?>
							</h4>
							<p>
								<?php _e('You can change or disable lightbox for gallery', 'portfolio-filter-gallery'); ?>
							</p>
						</div>
					</div>
					<div class="col-md-8">
						<div class="ma_field panel-body">
							<!--Theme 2 lighbox -->
							<div class="">
								<?php if (isset($pf_gallery_settings['light-box']))
									$light_box = $pf_gallery_settings['light-box'];
								else
									$light_box = 4; ?>
								<select name="light-box" id="light-box" class="selectbox_settings form-control">
									<option value="0" <?php if ($light_box == 0)
										echo "selected=selected"; ?>>
										<?php _e('None', 'portfolio-filter-gallery'); ?>
									</option>
									<option value="5" <?php if ($light_box == 5)
										echo "selected=selected"; ?>>
										<?php _e('Bootstrap Light Box', 'portfolio-filter-gallery'); ?>
									</option>
									<option value="4" <?php if ($light_box == 4)
										echo "selected=selected"; ?>>
										<?php _e('LD Light Box (Works on filtred group images)'); ?>
									</option>
								</select>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- CSS -->
			<div class="bhoechie-tab-content">
				<h1>
					<?php _e('Custom CSS', 'portfolio-filter-gallery'); ?>
				</h1>

				<div class="row">
					<div class="col-md-4">
						<div class="ma_field_discription">
							<h4>
								<?php _e('Custom CSS', 'portfolio-filter-gallery'); ?>
							</h4>
							<p>
								<?php _e('Apply your own custom CSS. Don"t use style tag', 'portfolio-filter-gallery'); ?>
							</p>
						</div>
					</div>
					<div class="col-md-8">
						<div class="panel-body">
							<?php if (isset($pf_gallery_settings['custom-css']))
								$custom_css = $pf_gallery_settings['custom-css'];
							else
								$custom_css = ""; ?>
							<textarea class="form-control" rows="12" id="custom-css"
								name="custom-css"><?php echo esc_html($custom_css); ?></textarea>
						</div>
					</div>
				</div>
			</div>


			<!-- Gallery section -->
			<div class="bhoechie-tab-content">
				<h1>
					<?php _e('Pro Feature', 'portfolio-filter-gallery'); ?>
				</h1>
				<p>
					<br>
					<a href="http://awplife.com/account/signup/portfolio-filter-gallery" target="_blank"
						class="pfg-btn button button-primary button-hero load-customize hide-if-no-customize"> <span
							class="dashicons dashicons-arrow-right-alt" style="line-height:45px"></span>
						<?php _e('Buy Premium Version', 'portfolio-filter-gallery'); ?>
					</a>
					<a href="http://awplife.com/demo/portfolio-filter-gallery-premium/" target="_blank"
						class="pfg-btn button button-primary button-hero load-customize hide-if-no-customize"> <span
							class="dashicons dashicons-arrow-right-alt" style="line-height:45px"></span>
						<?php _e('Check Live Demo', 'portfolio-filter-gallery'); ?>
					</a>
					<a href="http://awplife.com/demo/portfolio-filter-gallery-premium-admin-demo" target="_blank"
						class="pfg-btn button button-primary button-hero load-customize hide-if-no-customize"> <span
							class="dashicons dashicons-arrow-right-alt" style="line-height:45px"></span>
						<?php _e('Try Admin Demo', 'portfolio-filter-gallery'); ?>
					</a>
				</p>
			</div>

			<!-- Load More -->
			<div class="bhoechie-tab-content">
				<h1>
					<?php _e('Pro Feature', 'portfolio-filter-gallery'); ?>
				</h1>
				<p>
					<br>
					<a href="http://awplife.com/account/signup/portfolio-filter-gallery" target="_blank"
						class="pfg-btn button button-primary button-hero load-customize hide-if-no-customize"> <span
							class="dashicons dashicons-arrow-right-alt" style="line-height:45px"></span>
						<?php _e('Buy Premium Version', 'portfolio-filter-gallery'); ?>
					</a>
					<a href="http://awplife.com/demo/portfolio-filter-gallery-premium/" target="_blank"
						class="pfg-btn button button-primary button-hero load-customize hide-if-no-customize"> <span
							class="dashicons dashicons-arrow-right-alt" style="line-height:45px"></span>
						<?php _e('Check Live Demo', 'portfolio-filter-gallery'); ?>
					</a>
					<a href="http://awplife.com/demo/portfolio-filter-gallery-premium-admin-demo" target="_blank"
						class="pfg-btn button button-primary button-hero load-customize hide-if-no-customize"> <span
							class="dashicons dashicons-arrow-right-alt" style="line-height:45px"></span>
						<?php _e('Try Admin Demo', 'portfolio-filter-gallery'); ?>
					</a>
				</p>
			</div>


			<!-- Upgrade -->
			<div class="bhoechie-tab-content text-center">
				<img src="<?php echo PFG_PLUGIN_URL ?>img/Banner Portfolio For Pro Update jp.jpg" width="100%"
					height="100%">
				<p class="">
					<br>
					<a href="http://awplife.com/account/signup/portfolio-filter-gallery" target="_blank"
						class="pfg-btn button button-primary button-hero load-customize hide-if-no-customize"> <span
							class="dashicons dashicons-arrow-right-alt" style="line-height:45px"></span>
						<?php _e('Buy Premium Version', 'portfolio-filter-gallery'); ?>
					</a>
					<a href="http://awplife.com/demo/portfolio-filter-gallery-premium/" target="_blank"
						class="pfg-btn button button-primary button-hero load-customize hide-if-no-customize"> <span
							class="dashicons dashicons-arrow-right-alt" style="line-height:45px"></span>
						<?php _e('Check Live Demo', 'portfolio-filter-gallery'); ?>
					</a>
					<a href="https://awplife.com/demo/portfolio-filter-gallery-premium/how-to-test-the-premium-plugin/" target="_blank"
						class="pfg-btn button button-primary button-hero load-customize hide-if-no-customize"> <span
							class="dashicons dashicons-arrow-right-alt" style="line-height:45px"></span>
						<?php _e('Try Before Buy', 'portfolio-filter-gallery'); ?>
					</a>
				</p>
				<hr>

				<div class="row awp_bale_offer">
					<div class="">
						<h1>
							<?php _e('Plugins & Themes Bundle Offer', 'portfolio-filter-gallery'); ?>
						</h1>
						<h3>
							<?php _e('Get All Premium Plugin in just', 'portfolio-filter-gallery'); ?> <strike>
								<?php _e('$399', 'portfolio-filter-gallery'); ?>
							</strike>
							<?php _e('$149 Only', 'portfolio-filter-gallery'); ?>
						</h3>
						<h3>
							<?php _e('Get All Premium Themes in just', 'portfolio-filter-gallery'); ?> <strike>
								<?php _e('$199', 'portfolio-filter-gallery'); ?>
							</strike>
							<?php _e('$99 Only', 'portfolio-filter-gallery'); ?>
						</h3>
					</div>
					<div class="">
						<a href="http://awplife.com/account/signup/all-premium-plugins" target="_blank"
							class="pfg-btn button button-primary button-hero load-customize hide-if-no-customize"> <span
								class="dashicons dashicons-arrow-right-alt" style="line-height:45px"></span>
							<?php _e('BUY PLUGINS', 'portfolio-filter-gallery'); ?>
						</a>
						<a href="https://awplife.com/account/signup/all-premium-themes" target="_blank"
							class="pfg-btn button button-primary button-hero load-customize hide-if-no-customize"> <span
								class="dashicons dashicons-arrow-right-alt" style="line-height:45px"></span>
							<?php _e('BUY THEMES', 'portfolio-filter-gallery'); ?>
						</a>
					</div>

				</div>
				<hr>
				<div class="recommend-plugin">
					<h1><strong>
							<?php _e('Try Our Other Free Plugins:', 'portfolio-filter-gallery'); ?>
						</strong></h1>
					<br>
					<a href="https://wordpress.org/plugins/new-grid-gallery/" target="_blank"
						class="pfg-btn button-primary load-customize hide-if-no-customize"> Grid Gallery </a>
					<a href="https://wordpress.org/plugins/new-social-media-widget/" target="_blank"
						class="pfg-btn button-primary load-customize hide-if-no-customize"> Social Media </a>
					<a href="https://wordpress.org/plugins/new-image-gallery/" target="_blank"
						class="pfg-btn button-primary load-customize hide-if-no-customize"> Image Gallery </a>
					<a href="https://wordpress.org/plugins/new-photo-gallery/" target="_blank"
						class="pfg-btn button-primary load-customize hide-if-no-customize"> Photo Gallery </a>
					<a href="https://wordpress.org/plugins/responsive-slider-gallery/" target="_blank"
						class="pfg-btn button-primary load-customize hide-if-no-customize"> Responsive Slider Gallery
					</a>
					<a href="https://wordpress.org/plugins/new-contact-form-widget/" target="_blank"
						class="pfg-btn button-primary load-customize hide-if-no-customize"> Contact Form Widget </a>
					<a href="https://wordpress.org/plugins/facebook-likebox-widget-and-shortcode/" target="_blank"
						class="pfg-btn button-primary load-customize hide-if-no-customize"> Facebook Likebox Plugin </a>
					<a href="https://wordpress.org/plugins/slider-responsive-slideshow/" target="_blank"
						class="pfg-btn button-primary load-customize hide-if-no-customize"> Slider Responsive Slideshow
					</a>
					<a href="https://wordpress.org/plugins/new-video-gallery/" target="_blank"
						class="pfg-btn button-primary load-customize hide-if-no-customize"> Video Gallery </a>
					<a href="https://wordpress.org/plugins/new-facebook-like-share-follow-button/" target="_blank"
						class="pfg-btn button-primary load-customize hide-if-no-customize"> Facebook Like Share Follow
						Button </a>
					<a href="https://wordpress.org/plugins/new-google-plus-badge/" target="_blank"
						class="pfg-btn button-primary load-customize hide-if-no-customize"> Google Plus Badge </a>
					<a href="https://wordpress.org/plugins/media-slider/" target="_blank"
						class="pfg-btn button-primary load-customize hide-if-no-customize"> Media Slider </a>
					<a href="https://wordpress.org/plugins/weather-effect/" target="_blank"
						class="pfg-btn button-primary load-customize hide-if-no-customize"> Weather Effect </a>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
// syntax: wp_nonce_field( 'name_of_my_action', 'name_of_nonce_field' );
wp_nonce_field('pfg_save_settings', 'pfg_save_nonce');
?>
<script>
	jQuery(document).ready(function () {

		// tab
		jQuery("div.bhoechie-tab-menu>div.list-group>a").click(function (e) {
			e.preventDefault();
			jQuery(this).siblings('a.active').removeClass("active");
			jQuery(this).addClass("active");
			var index = jQuery(this).index();
			jQuery("div.bhoechie-tab>div.bhoechie-tab-content").removeClass("active");
			jQuery("div.bhoechie-tab>div.bhoechie-tab-content").eq(index).addClass("active");
		});


	});


	/**========================================================================*/
	/**========================================================================*/
	/**========================================================================*/
	/**========================================================================*/
	jQuery(document).ready(function () {
		//range slider
		var rangeSlider = function () {
			var slider = jQuery('.range-slider'),
				range = jQuery('.range-slider__range'),
				value = jQuery('.range-slider__value');

			slider.each(function () {

				value.each(function () {
					var value = jQuery(this).prev().attr('value');
					jQuery(this).html(value);
				});

				range.on('input', function () {
					jQuery(this).next(value).html(this.value);
				});
			});
		};
		rangeSlider();
	});


	// title size range settings.  on change range value
	function updateRange(val, id) {
		jQuery("#" + id).val(val);
		jQuery("#" + id + "_text").val(val);
	}

	//color-picker
	(function (jQuery) {
		jQuery(function () {
			// Add Color Picker to all inputs that have 'color-field' class
			jQuery('#border_color').wpColorPicker();
			jQuery('#border_color2').wpColorPicker();
			jQuery('#filter_bg').wpColorPicker();
			jQuery('#filter_title_color').wpColorPicker();
			jQuery('#filter_titles_color').wpColorPicker();
			jQuery('#filter_under_line_color').wpColorPicker();
			jQuery('#search_border').wpColorPicker();
			jQuery('#load_button_color').wpColorPicker();
			jQuery('#load_text_color').wpColorPicker();

		});
	})(jQuery);

	jQuery(document).ajaxComplete(function () {
		jQuery('#border_color,#filter_bg,#filter_title_color,#search_border,#load_button_color,#load_text_color').wpColorPicker();
	});

	var title_thumbnail = jQuery('input[name="title_thumb"]:checked').val();
	if (title_thumbnail == "show") {
		jQuery('.title_set').show();
		jQuery('.title_ancore').hide();
	}
	if (title_thumbnail == "hide") {
		jQuery('.title_set').hide();
		jQuery('.title_ancore').show();
	}
	var title_thumbnail2 = jQuery('input[name="title_thumb2"]:checked').val();
	if (title_thumbnail2 == "show") {
		jQuery('.title_set2').show();
		jQuery('.title_ancore').hide();
	}
	if (title_thumbnail2 == "hide") {
		jQuery('.title_set2').hide();
		jQuery('.title_ancore').show();
	}

	//on change effect
	jQuery(document).ready(function () {

		jQuery('input[name="title_thumb"]').change(function () {
			var title_thumbnail2 = jQuery('input[name="title_thumb"]:checked').val();
			if (title_thumbnail2 == "show") {
				jQuery('.title_set').show();
				jQuery('.title_ancore').hide();
			}
			if (title_thumbnail2 == "hide") {
				jQuery('.title_set').hide();
				jQuery('.title_ancore').show();
			}
		});

		jQuery('input[name="title_thumb2"]').change(function () {
			var title_thumbnail = jQuery('input[name="title_thumb2"]:checked').val();
			if (title_thumbnail == "show") {
				jQuery('.title_set2').show();
				jQuery('.title_ancore').hide();
			}
			if (title_thumbnail == "hide") {
				jQuery('.title_set2').hide();
				jQuery('.title_ancore').show();
			}
		});

	});

	//dismiss
	jQuery(".pfg-dismiss").each(function (e) {
		jQuery(this).on("click", function () {
			jQuery(this).parent().addClass('pfg-hide');
			jQuery(this).parent().addClass('pfg-hide');
			jQuery(this).parent().addClass('pfg-hide');
			localStorage.ClassName = "pfg-hide";
		});
	});

	jQuery(".update-notice").addClass(localStorage.ClassName);
	jQuery(".filter-notice").addClass(localStorage.ClassName);
	jQuery(".repeat-notice").addClass(localStorage.ClassName);


</script>