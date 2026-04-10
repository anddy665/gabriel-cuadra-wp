<?php

// showing recurring event in widget.
$i = 0;

?>
<!-- schedule tab start -->
<div class="event-tab-wrapper etn-tab-wrapper event-tab-1">
	<ul class='etn-nav'>
		<?php
		if ( ! empty( $tab_list ) ){
				foreach( (array) $tab_list as $key=> $cat_id ) {
						$i++;
						$active_class = ($i===1) ? 'etn-active' : '';
		?>
						<li>
								<a href='#' class='etn-tab-a <?php echo esc_attr($active_class); ?>' data-id='tab<?php echo esc_attr($widget_id) . "-" . esc_attr($i); ?>'>
										<?php
											echo esc_html($cat_id['tab_title']);
										?>
								</a>
						</li>
						<?php
				}
		}
		?>
	</ul>

	<div class='etn-tab-content clearfix etn-schedule-wrap'>
		<?php
		if( !empty($tab_list ) ){

			$j = 0;
			foreach($tab_list as $key=> $event_cats){
					$j++;

					$active_class = (($j == 1) ? 'tab-active' : '');

					?>
					<div class="etn-tab <?php echo esc_attr($active_class); ?>" data-id='tab<?php echo esc_attr($widget_id) . "-" . esc_attr($j); ?>'>
							<?php
								$event_cat = $event_cats['etn_event_cat'];
								$event_tag = $event_cats["etn_event_tag"];
								$order     = (isset($event_cats["order"]) ? $event_cats["order"] : 'DESC');
								$orderby   = $event_cats["orderby"];
								$filter_with_status = $event_cats['filter_with_status'];

								if ( $orderby == "etn_start_date" || $orderby == "etn_end_date" ) {
										$orderby_meta       = "meta_value";
								} else {
										$orderby_meta       = null;
								}

								// Handle pagination for each tab
								$etn_paged = 1;
								$pagination_param = 'etn_tab_' . ($j - 1) . '_paged'; // Unique param for each tab

								if ($enable_pagination === 'yes') {
									// Get the page number from URL parameter for this specific tab
									if (isset($_GET[$pagination_param]) && is_numeric($_GET[$pagination_param])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public event tab pagination param; value validated via is_numeric() and cast via intval().
										$etn_paged = max(1, intval($_GET[$pagination_param])); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- cast to integer via intval().
									}
								}

								// Determine posts to show based on pagination setting
								$posts_to_show = ($enable_pagination === 'yes') ? $posts_per_page : $event_count;

								// Validate file inclusion
								$sanitize_filename = sanitize_file_name($style);
								$style             = !empty($sanitize_filename) ? $sanitize_filename : 'event-1';
                                include \Wpeventin::plugin_dir() . "widgets/events/style/{$style}.php";
							?>
					</div>

			<?php
			}
		}
		?>
	</div>
</div>