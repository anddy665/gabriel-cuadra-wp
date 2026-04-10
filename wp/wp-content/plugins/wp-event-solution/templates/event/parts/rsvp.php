<?php 
    use \Etn_Pro\Core\Modules\Rsvp\Admin\Admin;

	$single_event_id = isset($single_event_id) ? absint($single_event_id) : 0;
    if ( ! $single_event_id ) {
        // Try to fall back to the current queried/post ID
        $single_event_id = absint( get_the_ID() ?: get_queried_object_id() );
    }

    // If still no valid ID, stop rendering the RSVP block
    if ( ! $single_event_id ) {
        return;
    }	

	$rsv_settings 				= get_post_meta( $single_event_id, 'rsvp_settings', true );

	if ( ! $rsv_settings ) {
		return;
	}
	
	$etn_enable_rsvp_form		= $rsv_settings['enable_rsvp_form'] ?: false; 

    $settings                   = etn_get_option();
     $etn_show_rsvp_attendee     = ! empty( $rsv_settings['show_rsvp_attendee'] ) ? $rsv_settings['show_rsvp_attendee'] : false;
    $etn_rsvp_form_type         = ! empty( $rsv_settings['rsvp_form_type'] ) ? $rsv_settings['rsvp_form_type'] : []; 
    $attendee_avatar            = apply_filters("etn/speakers/avatar", \Wpeventin::assets_url() . "images/avatar.jpg");
    
    $number_of_rsvp_limit       = ! empty( $rsv_settings['rsvp_attendee_form_limit'] ) ? $rsv_settings['rsvp_attendee_form_limit'] : 0;
	$etn_rsvp_total_capacity    = ! empty( $rsv_settings['etn_rsvp_limit_amount'] ) ? $rsv_settings['etn_rsvp_limit_amount'] : 0;
    $number_of_rsvp_attendee    = intval( Admin::instance()->rsvp_form_type_count( $single_event_id ,$meta_value = "yes" ) );
	$is_upcoming                = Etn\Core\Event\Helper::instance()->get_upcoming_event( $single_event_id );
	$number_of_rsvp_limit       = $number_of_rsvp_limit === 0 ? 1 : $number_of_rsvp_limit;
	$etn_rsvp_total_capacity    = $etn_rsvp_total_capacity === 0 ? 1 : $etn_rsvp_total_capacity;
	$etn_min_attendee_to_start  = ( isset($rsv_settings['rsvp_miminum_attendee_to_start']) && ! empty( $rsv_settings['rsvp_miminum_attendee_to_start'] ) ) ? $rsv_settings['rsvp_miminum_attendee_to_start'] : 0;

	$attendee_display_limit 	= ! empty( $rsv_settings['attendee_list_limit'] ) ? $rsv_settings['attendee_list_limit'] : false;

	$miminum_attendee_to_start  = get_post_meta( $single_event_id, 'etn_rsvp_miminum_attendee_to_start', true );
	if($miminum_attendee_to_start > 0){
		$mimimum_attendee_start = $miminum_attendee_to_start;	
	} else {
		$mimimum_attendee_start = ( isset( $settings['rsvp_min_attendees'] ) ? $settings['rsvp_min_attendees'] : 0 );
	}
	
	$etn_rsvp = \Etn_Pro\Core\Modules\Rsvp\Helper::instance()->data_query( $single_event_id );
	$total_attendee = [];
	$total_report_number = 0;
	if ( count($etn_rsvp) > 0 ) {
		foreach ($etn_rsvp as $key => $value) {
			$status = get_post_meta( $value->ID, 'etn_rsvp_value' , true);
			if ( !empty($value) && !empty($value->ID) && $status == 'going' ) {
				$total_attendee[] = get_post_meta( $value->ID, 'number_of_attendee' , true) !== "" ?  get_post_meta( $value->ID, 'number_of_attendee' , true ) : "";
			}
		}
		$total_report_number = array_sum($total_attendee);
	}	

	$remaining_capacity = intval( $etn_rsvp_total_capacity ) - intval( $total_report_number );
	if($remaining_capacity < 0){
		$remaining_capacity = 0;
	}

?>

<?php if( $etn_enable_rsvp_form ) : ?>
	<!-- RSVP forms -->
<!--
	<div class="etn-rsvp-form-wrapper">
		<?php if($remaining_capacity > 0): ?>
			<h2><?php echo esc_html__('RSVP Form', 'eventin'); ?></h2>
			<div class="rsvp-tab-wrapper">
				<span class="rsvp-tab-item active">
					<span class="marker"><?php echo esc_html__('1', 'eventin'); ?></span> 
					<?php echo esc_html__('Step 1', 'eventin'); ?>
					
				</span>
				<span class="rsvp-tab-item">
					<span class="marker"><?php echo esc_html__('2', 'eventin'); ?></span> 
					<?php echo esc_html__('Step 2', 'eventin'); ?>
				</span>
			</div>
			<form class="rsvp_submit">
				<div role="rsvp-tab-list">
					<div role="tabpanel" id="color" class="rsvp-tabpanel">
						<div class="rsvp-radio-wrapper">
							<?php
								if( !empty( $etn_rsvp_form_type ) ){
									foreach ( $etn_rsvp_form_type as $key => $value ) {

										$checked =  $value === 'going' ? true : false;
										$value = str_replace('_', ' ', $value);
										
										?>
											<div class="single-radio-option">
												<input 
													type="radio" 
													id="<?php echo esc_attr( $key )?>" 
													name="etn_rsvp_value" 
													value="<?php echo esc_attr( $value ); ?>"
													<?php if($checked) {
														?>
														checked="checked"
														<?php
													} ?>
												>
												<label for="<?php echo esc_attr( $key ); ?>">
													<?php echo esc_html( $value ); ?>
												</label>
											</div>
										<?php
									}
								}
							?>
						</div>
						<div class="rsvp-form-element number-of-attendee-wrapper">
							<label for="number-of-attendee">
								<?php echo esc_html__('Number of attendees *', 'eventin'); ?>
							</label>
							<input 
								name="number_of_attendee"
								id="number-of-attendee"
								class="number-of-attendee"
								type="number"
								min="1"
								max="<?php echo esc_attr($number_of_rsvp_limit); ?>",
								step="1"
								value="1"
								placeholder="<?php echo esc_html__('Number of attendee', 'eventin')?>"
								data-available-rsvp="<?php echo esc_attr($number_of_rsvp_limit); ?>" data-remaining-capacity="<?php echo esc_attr($remaining_capacity); ?>"
							>
							<div class="rsvp-error-message attendee-number-error"></div>
							<span class="rsvp-help-text">
								<?php echo esc_html__('Limit per submission:', 'eventin'); ?>
								<?php echo esc_attr($number_of_rsvp_limit); ?>
							</span>
						</div>
						<div class="rsvp-main-responder">
							<div class="rsvp-form-element">
								<label for="attendee-name-0"><?php echo esc_html__('Full name *', 'eventin'); ?></label>
								<input class="attendee-name" name="attendee_name[]" id="attendee-name-0" type="text" placeholder="<?php echo esc_html__('Enter name of attendee', 'eventin')?>" required>
								<div class="rsvp-error-message attendee-name-error"></div>
							</div>
							<div class="rsvp-form-element">
								<label for="attendee-email-0"><?php echo esc_html__('Email address *', 'eventin'); ?></label>
								<input class="attendee-email" name="attendee_email[]" id="attendee-email-0" type="email" placeholder="<?php echo esc_html__('Enter email address', 'eventin')?>" required>
								<div class="rsvp-error-message attendee-email-error"></div>
							</div>
						</div>
						<div class="rsvp-form-element not-going-reason-wrapper hidden">
							<label for="not-going-reason"><?php echo esc_html__('Reason (Optional)', 'eventin'); ?></label>
							<textarea name="rsvp_not_going_reason" id="not-going-reason" placeholder="<?php echo esc_html__('Add not going reason here', 'eventin'); ?>"></textarea>
						</div>
					</div>
					<div role="tabpanel" id="hobbies" class="rsvp-tabpanel hidden">
						<h3 class="form-inner-heading">
							<?php echo esc_html__('Attendee details:', 'eventin') ; ?>
						</h3>
						<div class="attendee-details-wrapper">
							<input type="checkbox" class="etn_multi_checkbox" name="" id="different-attendee-checkbox" checked="checked" >
							<label for="different-attendee-checkbox">
								<?php echo esc_html__('Keep the following response for attendees too', 'eventin'); ?>
							</label>
						</div>

						<div class="rsvp-attendee-form"></div>
					</div>
				</div>
				<div class="rsvp-submission-buttons">
					<input type="hidden" name="event_id" value="<?php echo intval($single_event_id)?>"/>
					<button class="rsvp-btn rsvp-previous-btn hidden" id="rsvp-previous-btn"><?php echo esc_html__('Previous', 'eventin'); ?></button>
					<button class="rsvp-btn" id="rsvp-next-btn"><?php echo esc_html__('Continue', 'eventin'); ?></button>
					<button class="rsvp-btn rsvp-submit-btn hidden" id="rsvp-submit-btn" type="submit"><?php echo esc_html__('Submit', 'eventin'); ?></button>
				</div>
			</form>
-->
			<!-- Form submission message -->
<!--
			<div class="rsvp-submission-message hidden">
				<p class="rsvp-submission-success"><?php echo esc_html__('Your RSVP response has been submitted.', 'eventin'); ?></p>
			</div>
-->
		<?php else: ?>
		<!--
			<h4 class="etn-mb-0"><?php echo esc_html__('RSVP Capacity is Over', 'eventin'); ?></h4>
		-->
		<?php endif; ?>

		<!-- RSVP Responder section Start -->
		<?php if( $etn_show_rsvp_attendee ) : ?>                    
			<!--
			<div class="rsvp-attendee-list">
				<div class="title-wrapper">
					<h2><?php esc_html_e( "RSVP Attendees", "eventin" ); ?>
						<?php if( $mimimum_attendee_start > 0 ): ?>
						<span><?php echo esc_html__( 'Minimum Attendee to Start the Event: ', 'eventin' ); ?><?php echo esc_html( $mimimum_attendee_start ); ?></span>
						<?php endif; ?>
					</h2>
					<button class="view-all-button"><?php esc_html_e( 'Hide Attendees', 'eventin' ); ?></button>
				</div>

				<?php 
					$get_going_attendee = \Etn_Pro\Core\Modules\Rsvp\Helper::instance()->get_rsvp_going_attendee( $single_event_id, $attendee_display_limit );
				if ( !empty( $get_going_attendee ) ) : ?>
					<div class="rsvp-attendee-grid">
						<?php
						$count = 0;
						$attendee_display_limit = $attendee_display_limit ? esc_attr( $attendee_display_limit ) : count( $get_going_attendee );
						foreach ($get_going_attendee as $key => $value) : 

							if( $count >= $attendee_display_limit) {
								break;
							}
							$count++;
							?>
							<div class="rsvp-attendee">
								<img src="<?php echo esc_url( $attendee_avatar ); ?>" height="50" width="50" alt="<?php the_title_attribute(); ?>"/>
								<h4><?php echo esc_html( $value['name'] ); ?></h4>
							</div>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<p><?php esc_html_e("No one has responded yet.","eventin");?></p>
				<?php endif; ?>
			</div>
		-->
		<?php endif; ?>
		<!-- RSVP Responder section End -->
	</div>
	<div class="etn-rsvp-form-root" data-post_id="<?php echo esc_attr($single_event_id); ?>"></div>
<?php endif; ?>