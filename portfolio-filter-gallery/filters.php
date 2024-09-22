<?php
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly
//filters page
//wp_enqueue_style('awl-pfg-bootstrap-css', PFG_PLUGIN_URL . 'css/fb-buttons-bootstrap.css');
wp_enqueue_style('awl-pfg-filter-css', PFG_PLUGIN_URL . 'css/filter-templet.css');
//wp_enqueue_style('awl-pfg-font-css', PFG_PLUGIN_URL . 'css/font-awesome.min.css');
if ( is_admin() ) {
	add_thickbox(); // Enqueue Thickbox
}
$all_category = [];
$all_category = get_option('awl_portfolio_filter_gallery_categories');

if (current_user_can('manage_options')) {
	if (is_array($all_category)) {
		if (!isset($all_category[0])) {
			$all_category[0] = "all";
			update_option("awl_portfolio_filter_gallery_categories", $all_category);
		}
	} else {
		$all_category[0] = "all";
		update_option("awl_portfolio_filter_gallery_categories", $all_category);
	}
}


/*echo "<pre>";
print_r($all_category);
echo "</pre>";*/
?>
<!--Category Section Start-->
<div id="poststuff" class="wrap">
	<div id="post-body" class="metabox-holder columns-2">
		<div id="postbox-container-2" class="postbox-container">
			<div class="row awl-spacing-md" id="update_div">
				<div class="container">
					<div class="form-btns text-center">
						<input type="button" class="pfg-btn button-primary button-hero " id="save_category"
							name="save_category" value="<?php _e('Add New Category', 'portfolio-filter-gallery'); ?>"
							onclick="return DoAction('showaddform', '');" />
						<fieldset>
							<div id="add-form-div" class="row" style="display:none;">
								<form id="add-form" name="add-form">

									<div class="col-md-8">
										<input type="text" id="name" name="name"
											placeholder="<?php _e('Type Category Name', 'portfolio-filter-gallery'); ?>">
										<input type="button" class="pfg-btn button-primary button-hero lower-btn"
											id="save_category" name="save_category"
											value="<?php _e('Add Category', 'portfolio-filter-gallery'); ?>"
											onclick="return DoAction('add', '');" />
									</div>
									<div class="col-md-3">

									</div>
									<?php wp_nonce_field('pfg_add_filter_action', 'pgf_add_filter'); ?>
								</form>
							</div>
							<div id="update-form-div" style="display: none;"></div>
						</fieldset>
					</div>

					<div id="cat-table-div">

						<table class="wp-list-table widefat fixed striped table-view-list pages" id="cat-table">
							<thead>
								<tr>
									<th class="row-title"><strong>#</strong></th>
									<th class="row-title"><strong>
											<?php _e('Category Name', 'portfolio-filter-gallery'); ?>
										</strong></th>
									<th class="row-title"><strong>
											<?php _e('Action', 'portfolio-filter-gallery'); ?>
										</strong></th>
									<th class="check-column"><input type="checkbox" name="check-all" id="check-all">
									</th>
								</tr>
							</thead>
							<tbody id="update_div" name="update_div">
								<?php
								$all_category = get_option('awl_portfolio_filter_gallery_categories');
								$n = 1;
								if ($all_category) {
									foreach ($all_category as $key => $value) {
										?>
										<tr id="record-<?php echo esc_attr($key); ?>">
											<td class="row-title">
												<?php echo esc_html($n); ?>
											</td>
											<td class="row-title" id="cat_name" name="cat_name"><strong>
													<?php echo stripslashes(esc_html($value)); ?>
												</strong></td>
											<td class="row-title">&nbsp;
												<?php if ($key != 0) { ?><i class="dashicons dashicons-edit cat_icon"
														id="update_category" name="update_category"
														onclick="return DoAction('edit', '<?php echo esc_attr($key); ?>');"></i>
												<?php } ?>&nbsp;&nbsp;&nbsp;
												<?php if ($key != 0) { ?><i class="dashicons dashicons-trash cat_icon"
														id="delete_category" name="delete_category"
														onclick="return pfg_delete_filter('<?php echo esc_attr($key); ?>');"></i>
												<?php } ?>
											</td>
											<td class="row-title" class="text-center">
												<?php if ($key != 0) { ?><input type="checkbox" id="cat_all_check"
														value="<?php echo esc_attr($key); ?>">
												<?php } ?>
											</td>
										</tr>
										<?php
										$n++;
									} // end foreach
								}
								?>
								<tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<?php if ($all_category) {
										?>
										<td class="text-center">
											<input type="button" class="pfg-btn button-primary button-hero lower-btn"
												value="<?php _e('Delete All', 'portfolio-filter-gallery'); ?>"
												id="delete_all_category" name="delete_all_category"
												onclick="return pfg_delete_all_filter();" />
										</td>
										<?php
									}
									?>
								</tr>
							</tbody>
						</table>

						<?php if (count($all_category) == 7) { ?>
							<h5 class="notice notice-info notice-alt">
								<?php _e('You can only add 7 category in free version for more upgrade to our pro version.', 'portfolio-filter-gallery'); ?>
								<a href="https://awplife.com/account/signup/portfolio-filter-gallery">
									<?php _e('Upgrade Now', 'portfolio-filter-gallery'); ?>
								</a>
							</h5>
						<?php } ?>


					</div>
				</div>
			</div>
		</div>
		<div id="postbox-container-1" class="postbox-container">
			<div id="side-sortables" class="meta-box-sortables ui-sortable pfg-meta-box-filter-sec">

				<div id="Upgrade Portfolio Gallery Pro" class="postbox ">
					<div class="postbox-header">
						<h2 class="hndle ui-sortable-handle">Filter Drag & Drop</h2>
						<div class="handle-actions hide-if-no-js">
							<button type="button" class="handle-order-higher" aria-disabled="false"
								aria-describedby="Upgrade Portfolio Gallery Pro-handle-order-higher-description"><span
									class="screen-reader-text">Move up</span><span class="order-higher-indicator"
									aria-hidden="true"></span></button><span class="hidden"
								id="Upgrade Portfolio Gallery Pro-handle-order-higher-description">Move Upgrade
								Portfolio Gallery Pro box up</span><button type="button" class="handle-order-lower"
								aria-disabled="false"
								aria-describedby="Upgrade Portfolio Gallery Pro-handle-order-lower-description"><span
									class="screen-reader-text">Move down</span><span class="order-lower-indicator"
									aria-hidden="true"></span></button><span class="hidden"
								id="Upgrade Portfolio Gallery Pro-handle-order-lower-description">Move Upgrade Portfolio
								Gallery Pro box down</span><button type="button" class="handlediv"
								aria-expanded="true"><span class="screen-reader-text">Toggle panel: Upgrade Portfolio
									Gallery Pro</span><span class="toggle-indicator" aria-hidden="true"></span></button>
						</div>
					</div>
					<div class="inside">
						<a href="https://awplife.com/account/signup/portfolio-filter-gallery" target="_new"><img
								src="<?php echo PFG_PLUGIN_URL ?>img/wordpress filter gallery.gif" / width="250"
								height="184"></a>
						<a href="https://awplife.com/demo/portfolio-filter-gallery-premium/" target="_new"
							class="pfg-btn button button-primary" style="background: #496481; text-shadow: none;"><span
								class="dashicons dashicons-search" style="line-height:1.4;"></span> Live Demo</a>
						<a href="https://awplife.com/account/signup/portfolio-filter-gallery" target="_new"
							class="pfg-btn button button-primary" style="background: #496481; text-shadow: none;"><span
								class="dashicons dashicons-unlock" style="line-height:1.4;"></span> Upgrade To Pro</a>
					</div>
				</div>

				<div id="Upgrade Portfolio Gallery Pro" class="postbox ">
					<div class="postbox-header">
						<h2 class="hndle ui-sortable-handle">Upgrade Portfolio Gallery</h2>
						<div class="handle-actions hide-if-no-js">
							<button type="button" class="handle-order-higher" aria-disabled="false"
								aria-describedby="Upgrade Portfolio Gallery Pro-handle-order-higher-description"><span
									class="screen-reader-text">Move up</span><span class="order-higher-indicator"
									aria-hidden="true"></span></button><span class="hidden"
								id="Upgrade Portfolio Gallery Pro-handle-order-higher-description">Move Upgrade
								Portfolio Gallery Pro box up</span><button type="button" class="handle-order-lower"
								aria-disabled="false"
								aria-describedby="Upgrade Portfolio Gallery Pro-handle-order-lower-description"><span
									class="screen-reader-text">Move down</span><span class="order-lower-indicator"
									aria-hidden="true"></span></button><span class="hidden"
								id="Upgrade Portfolio Gallery Pro-handle-order-lower-description">Move Upgrade Portfolio
								Gallery Pro box down</span><button type="button" class="handlediv"
								aria-expanded="true"><span class="screen-reader-text">Toggle panel: Upgrade Portfolio
									Gallery Pro</span><span class="toggle-indicator" aria-hidden="true"></span></button>
						</div>
					</div>
					<div class="inside">
						<a href="https://awplife.com/account/signup/portfolio-filter-gallery" target="_new"><img
								src="<?php echo PFG_PLUGIN_URL ?>img/portfolio-upgrade.jpg" / width="250"
								height="280"></a>
						<a href="https://awplife.com/demo/portfolio-filter-gallery-premium/" target="_new"
							class="pfg-btn button button-primary" style="background: #496481; text-shadow: none;"><span
								class="dashicons dashicons-search" style="line-height:1.4;"></span> Live Demo</a>
						<a href="https://awplife.com/account/signup/portfolio-filter-gallery" target="_new"
							class="pfg-btn button button-primary" style="background: #496481; text-shadow: none;"><span
								class="dashicons dashicons-unlock" style="line-height:1.4;"></span> Upgrade To Pro</a>
					</div>
				</div>

				<div id="Ultimate Portfolio" class="postbox ">
					<div class="postbox-header">
						<h2 class="hndle ui-sortable-handle">Most Advanced Version</h2>
						<div class="handle-actions hide-if-no-js"><button type="button" class="handle-order-higher"
								aria-disabled="false"
								aria-describedby="Ultimate Portfolio-handle-order-higher-description"><span
									class="screen-reader-text">Move up</span><span class="order-higher-indicator"
									aria-hidden="true"></span></button><span class="hidden"
								id="Ultimate Portfolio-handle-order-higher-description">Move Most Advanced Version box
								up</span><button type="button" class="handle-order-lower" aria-disabled="false"
								aria-describedby="Ultimate Portfolio-handle-order-lower-description"><span
									class="screen-reader-text">Move down</span><span class="order-lower-indicator"
									aria-hidden="true"></span></button><span class="hidden"
								id="Ultimate Portfolio-handle-order-lower-description">Move Most Advanced Version box
								down</span><button type="button" class="handlediv" aria-expanded="true"><span
									class="screen-reader-text">Toggle panel: Most Advanced Version</span><span
									class="toggle-indicator" aria-hidden="true"></span></button></div>
					</div>
					<div class="inside">
						<a href="https://webenvo.com/ultimate-portfolio/" target="_new">
							<img src="https://awplife.com/wp-content/uploads/2024/07/ultimate-portfolio-wordpress-plugin.webp"
								width="250" height=""></a>

						<br>
						<div style="text-align:center">
							<p>
								The Ultimate Portfolio is the most advanced version of the portfolio plugin, fresh
								layouts, multiple design, fully integrated with the Gutenberg editor.

							</p>
						</div>
						<a href="<?php echo esc_url( self_admin_url( 'plugin-install.php?tab=plugin-information&plugin=ultimate-portfolio&TB_iframe=true&width=772&height=878' ) ); ?>"  class="pfg-btn button button-primary thickbox"
							style="background: #496481; text-shadow: none;"><span class="dashicons dashicons-download"
								style="line-height:1.4;"></span>
							<?php _e('Install Free', 'portfolio-filter-gallery'); ?>
						</a>
						<a href="https://webenvo.com/ultimate-portfolio/" target="_new" class="pfg-btn button button-primary"
							style="background: #496481; text-shadow: none;"><span class="dashicons dashicons-unlock"
								style="line-height:1.4;"></span>
							<?php _e('Get Premium', 'portfolio-filter-gallery'); ?>
						</a>

					</div>
				</div>

				<div id="Rate Our Plugin" class="postbox ">
					<div class="postbox-header">
						<h2 class="hndle ui-sortable-handle">Rate Our Plugin</h2>
						<div class="handle-actions hide-if-no-js"><button type="button" class="handle-order-higher"
								aria-disabled="false"
								aria-describedby="Rate Our Plugin-handle-order-higher-description"><span
									class="screen-reader-text">Move up</span><span class="order-higher-indicator"
									aria-hidden="true"></span></button><span class="hidden"
								id="Rate Our Plugin-handle-order-higher-description">Move Rate Our Plugin box
								up</span><button type="button" class="handle-order-lower" aria-disabled="false"
								aria-describedby="Rate Our Plugin-handle-order-lower-description"><span
									class="screen-reader-text">Move down</span><span class="order-lower-indicator"
									aria-hidden="true"></span></button><span class="hidden"
								id="Rate Our Plugin-handle-order-lower-description">Move Rate Our Plugin box
								down</span><button type="button" class="handlediv" aria-expanded="true"><span
									class="screen-reader-text">Toggle panel: Rate Our Plugin</span><span
									class="toggle-indicator" aria-hidden="true"></span></button></div>
					</div>
					<div class="inside">
						<div style="text-align:center">
							<p>If you like our plugin then please <b>Rate us</b> on WordPress</p>
						</div>
						<div style="text-align:center">
							<span class="dashicons dashicons-star-filled"></span>
							<span class="dashicons dashicons-star-filled"></span>
							<span class="dashicons dashicons-star-filled"></span>
							<span class="dashicons dashicons-star-filled"></span>
							<span class="dashicons dashicons-star-filled"></span>
						</div>
						<br>
						<div style="text-align:center">
							<a href="https://wordpress.org/support/plugin/portfolio-filter-gallery/reviews/"
								target="_new" class="pfg-btn button button-primary button-large"
								style="background: #496481; text-shadow: none;"><span class="dashicons dashicons-heart"
									style="line-height:1.4;"></span> Please Rate Us</a>
						</div>
					</div>
				</div>
				



			</div>
		</div>
	</div>
</div>

<script>
	jQuery(document).ready(function () {
		jQuery("input:checkbox").prop('checked', false);
		jQuery("#check-all").change(function () {
			jQuery("input:checkbox").prop('checked', jQuery(this).prop("checked"));
		});
	});

	var numItems = jQuery('#update_div tr').length;
	if (numItems == 10) {
		jQuery("#save_category").prop("disabled", true);
	}
	function DoAction(action, id) {

		//show add form
		if (action == "showaddform") {
			jQuery('#name').val("");
			jQuery("#add-form-div").show();
			jQuery("#save_category").hide();
		}
		//add category
		if (action == "add") {
			jQuery.ajax({
				type: 'POST',
				url: location.href,
				data: jQuery('#add-form').serialize() + '&action=' + action,
				success: function (response) {
					//jQuery("#cat-table").remove();
					//var result = jQuery(response).filter('#cat-table-div');
					//jQuery( "#cat-table-div" ).after( result );
					jQuery('#cat-table-div').html(jQuery(response).find('div#cat-table-div'));
					jQuery('#hide_btn').html(jQuery(response).find('div.hide_btn'));
					jQuery("#hide_this").remove();
					jQuery("#cat-table").remove();
					jQuery("#add-form-div").hide();
					jQuery("#save_category").show();
					jQuery("#check-all").change(function () {
						jQuery("input:checkbox").prop('checked', jQuery(this).prop("checked"));
					});

					var numItems = jQuery('#update_div tr').length;
					console.log(numItems);
					if (numItems == 10) {
						jQuery("#save_category").prop("disabled", true);
					}

				}
			});
		}

		//edit and show update form
		if (action == "edit") {
			jQuery("#save_category").hide();
			jQuery("#add-form-div").hide();
			jQuery.ajax({
				type: 'POST',
				url: location.href,
				data: '&action=' + action + "&id=" + id,
				success: function (response) {
					//var result = jQuery(response).filter('#update-form');
					jQuery("#update-form-div").show();
					//jQuery( "#update-form-div" ).after( result );
					jQuery('#update-form-div').html(jQuery(response).find('div#update-form'));

					jQuery("#check-all").change(function () {
						jQuery("input:checkbox").prop('checked', jQuery(this).prop("checked"));
					});

				}
			});
		}

		//update the category
		if (action == "update") {
			var edit_name = jQuery("#edit_name").val();
			jQuery.ajax({
				type: 'POST',
				url: location.href,
				data: jQuery('#edit-form').serialize() + '&action=' + action,
				success: function (response) {
					jQuery("#update-form").remove();
					jQuery("#update-form-div").hide();
					// new updated response
					jQuery('#cat-table-div').html(jQuery(response).find('div#cat-table-div'));
					jQuery("#cat-table").remove();
					jQuery("#save_category").show();

					jQuery("#check-all").change(function () {
						jQuery("input:checkbox").prop('checked', jQuery(this).prop("checked"));
					});
				}
			});
		}
	}
</script>
<?php
if (isset($_POST['action'])) {
	//print_r($_POST);
	$action = sanitize_text_field($_POST['action']);

	if ($action == "add") {
		if (current_user_can('manage_options')) {
			if (isset($_POST['pgf_add_filter']) && wp_verify_nonce($_POST['pgf_add_filter'], 'pfg_add_filter_action')) {

				$category_name = sanitize_text_field($_POST['name']);
				//$category_slug = strtolower($category_name);
				$new_category = array($category_name);

				$all_category = get_option('awl_portfolio_filter_gallery_categories');
				if (is_array($all_category)) {
					$all_category = array_merge($all_category, $new_category);
				} else {
					$all_category = $new_category;
				}
				if (count($all_category) < 8) {

					if (current_user_can('manage_options')) {
						if (update_option('awl_portfolio_filter_gallery_categories', $all_category)) {
							//print_r( $insert_query);
							?>
							<div class="" <?php if ($action != "add" && $action != "update")
								; ?>"" id="cat-table-div">
								<table class="wp-list-table widefat fixed striped table-view-list pages" id="cat-table">
									<thead>
										<tr>
											<th class="row-title"><strong>#</strong></th>
											<th class="row-title"><strong>
													<?php _e('Category Name', 'portfolio-filter-gallery'); ?>
												</strong></th>
											<th class="row-title"><strong>
													<?php _e('Action', 'portfolio-filter-gallery'); ?>
												</strong></th>
											<th class="check-column"><input type="checkbox" name="check-all" id="check-all"></th>
										</tr>
									</thead>
									<tbody id="update_div" name="update_div">
										<?php
										$all_category = get_option('awl_portfolio_filter_gallery_categories');
										$n = 1;
										if ($all_category) {
											foreach ($all_category as $key => $value) {
												?>
												<tr id="record-<?php echo esc_attr($key); ?>">
													<td class="row-title"><strong>
															<?php echo esc_html($n); ?>
														</strong></td>
													<td class="row-title" id="cat_name" name="cat_name"><strong>
															<?php echo stripslashes(esc_html($value)); ?>
														</strong></td>
													<td class="row-title">&nbsp;
														<?php if ($key != 0) { ?><i class="dashicons dashicons-edit cat_icon" id="update_category"
																name="update_category" onclick="return DoAction('edit', '<?php echo esc_attr($key); ?>');"></i>
														<?php } ?>&nbsp;&nbsp;&nbsp;
														<?php if ($key != 0) { ?><i class="dashicons dashicons-trash cat_icon" id="delete_category"
																name="delete_category" onclick="return pfg_delete_filter('<?php echo esc_attr($key); ?>');"></i>
														<?php } ?>
													</td>
													<td class="row-title">
														<?php if ($key != 0) { ?><input type="checkbox" id="cat_all_check"
																value="<?php echo esc_attr($key); ?>">
														<?php } ?>
													</td>
												</tr>
												<?php
												$n++;
											} // end foreach
										}
										?>
										<tr>
											<td>&nbsp;</td>
											<td>&nbsp;</td>
											<td>&nbsp;</td>
											<?php if ($all_category) {
												?>
												<td class="text-center">
													<input type="button" class="pfg-btn button-primary button-hero lower-btn"
														value="<?php _e('Delete All', 'portfolio-filter-gallery'); ?>" id="delete_all_category"
														name="delete_all_category" onclick="return pfg_delete_all_filter();" />
												</td>
												<?php
											}
											?>
										</tr>
									</tbody>
								</table>
								<?php if (count($all_category) == 7) { ?>
									<h5 class="notice notice-info notice-alt">
										<?php _e('You can only add 7 category in free version for more upgrade to our pro version.', 'portfolio-filter-gallery'); ?>
										<a href="https://awplife.com/account/signup/portfolio-filter-gallery">
											<?php _e('Upgrade Now', 'portfolio-filter-gallery'); ?>
										</a>
									</h5>
									<?php
								} ?>
							</div>
							<?php
						} else {
							echo "<div id='result-msg'>failed</div>";
						}
					}
				}

			} else {
				print 'Sorry, your nonce did not verify.';
				exit;
			}
		}
	}

	if ($action == "edit") {
		$id = sanitize_text_field($_POST['id']);
		$all_category = get_option('awl_portfolio_filter_gallery_categories');
		$edit_cat_name = $all_category[$id];
		?>
		<div id="update-form">
			<form id="edit-form" name="edit-form">
				<div class="col-md-8">
					<input type="text" id="edit_name" name="edit_name" value="<?php echo esc_attr($edit_cat_name); ?>">
					<input type="button" class="pfg-btn button-primary button-hero" id="save_category" name="save_category"
						value="<?php _e('Update Category', 'portfolio-filter-gallery'); ?>"
						onclick="return DoAction('update');" />
				</div>
				<div class="col-md-3">

				</div>
				<input type="hidden" name="id" value="<?php echo esc_attr($id); ?>">
				<?php wp_nonce_field('pfg_edit_filter_action', 'pgf_edit_filter'); ?>
			</form>
		</div>
		<?php

	}

	if ($action == "update") {
		if (current_user_can('manage_options')) {
			if (isset($_POST['pgf_edit_filter']) && wp_verify_nonce($_POST['pgf_edit_filter'], 'pfg_edit_filter_action')) {

				$id = sanitize_text_field($_POST['id']);
				$edit_name = sanitize_text_field($_POST['edit_name']);
				$all_category = get_option('awl_portfolio_filter_gallery_categories');

				$replacements = array($id => $edit_name);
				$all_category = array_replace($all_category, $replacements);
				update_option('awl_portfolio_filter_gallery_categories', $all_category);
				?>

				<div class="" <?php if ($action != "add" && $action != "update")
					; ?>"" id="cat-table-div">
					<table class="wp-list-table widefat fixed striped table-view-list pages" id="cat-table">
						<thead>
							<tr>
								<th class="row-title"><strong>#</strong></th>
								<th class="row-title"><strong>
										<?php _e('Category Name', 'portfolio-filter-gallery'); ?>
									</strong></th>
								<th class="row-title"><strong>
										<?php _e('Action', 'portfolio-filter-gallery'); ?>
									</strong></th>
								<th class="check-column"><input type="checkbox" name="check-all" id="check-all"></th>
							</tr>
						</thead>
						<tbody id="update_div" name="update_div">
							<?php
							$all_category = get_option('awl_portfolio_filter_gallery_categories');
							$n = 1;
							if ($all_category) {
								foreach ($all_category as $key => $value) {
									?>
									<tr id="record-<?php echo esc_attr($key); ?>">
										<td class="row-title">
											<?php echo esc_html($n); ?>
										</td>
										<td class="row-title" id="cat_name" name="cat_name">
											<?php echo stripslashes(esc_html($value)); ?>
										</td>
										<td class="row-title">&nbsp;
											<?php if ($key != 0) { ?><i class="dashicons dashicons-edit cat_icon" id="update_category"
													name="update_category" onclick="return DoAction('edit', '<?php echo esc_attr($key); ?>');"></i>
											<?php } ?>&nbsp;&nbsp;&nbsp;
											<?php if ($key != 0) { ?><i class="dashicons dashicons-trash cat_icon" id="delete_category"
													name="delete_category" onclick="return pfg_delete_filter('<?php echo esc_attr($key); ?>');"></i>
											<?php } ?>
										</td>
										<td class="row-title">
											<?php if ($key != 0) { ?><input type="checkbox" id="cat_all_check"
													value="<?php echo esc_attr($key); ?>">
											<?php } ?>
										</td>
									</tr>
									<?php
									$n++;
								} // end foreach
							}
							?>
							<tr>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<?php if ($all_category) {
									?>
									<td class="text-center">
										<input type="button" class="pfg-btn button-primary button-hero lower-btn"
											value="<?php _e('Delete All', 'portfolio-filter-gallery'); ?>" id="delete_all_category"
											name="delete_all_category" onclick="return pfg_delete_all_filter();" />
									</td>
									<?php
								}
								?>
							</tr>
						</tbody>
					</table>
				</div>
				<?php
			} else {
				print 'Sorry, your nonce did not verify.';
				exit;
			}
		}
	}
}
?>
<div class="upgrade-btns">
	<br>
	<a href="https://awplife.com/account/signup/portfolio-filter-gallery" target="_blank"
		class="pfg-btn button-primary button-hero">Buy Premium Version</a>
	<a href="https://awplife.com/demo/portfolio-filter-gallery-premium/" target="_blank"
		class="pfg-btn button-primary button-hero">Check Live Demo</a>
	<a href="https://awplife.com/demo/portfolio-filter-gallery-premium-admin-demo/" target="_blank"
		class="pfg-btn button-primary button-hero ">Try Before Buy</a>
</div>