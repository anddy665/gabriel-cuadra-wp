<?php
namespace Etn\Templates\Event\Parts;
use Etn\Utils\Helper;
use \Etn\Utils\Helper as EtnFreeHelper;

defined( 'ABSPATH' ) || exit;

/**
 * Event details class.
 *
 * @since 3.3.9
 */
class EventDetailsPartsPro {

	public static function group_speakers( $speakers ) {
		$grouped_speakers = [];
		foreach ($speakers as $speaker) {
			$grouped_speakers[$speaker['etn_speaker_group']][] = $speaker;
		}
		return $grouped_speakers;
	}

	/**
	 * Event details speaker.
	 *
	 * @since 3.3.9
	 */
	public static function event_single_speakers( $etn_speaker_events ) {
		if(!empty($etn_speaker_events)): ?>
		<div class="etn-event-speakers etn-single-event-speaker-block">
			<h3 class="speaker-block-title">
				<?php  
					$event_speakers_title = apply_filters( 'etn_event_organizers_title', esc_html__("Speaker",  'eventin' ) );
					echo esc_html( $event_speakers_title );
				?> 
			</h3>
			<?php $speakers = $etn_speaker_events; 
			?>
			<div class="etn-single-event-speaker-grid-wrapper">
				<?php
				if ($speakers) {
					foreach ($speakers as $value) { 
						$social = get_user_meta( $value , 'etn_speaker_social', true);
						$etn_speaker_designatioin   = get_user_meta( $value , 'etn_speaker_designation', true);
						$etn_speaker_permalink      = EtnFreeHelper::get_author_page_url_by_id($value);
						$etn_speaker_image          = get_user_meta( $value, 'image', true);
						$etn_speaker_image_2        = get_user_meta( $value, 'etn_speaker_image', true);
						$etn_speaker_image          = !empty( $etn_speaker_image ) ?  $etn_speaker_image : $etn_speaker_image_2; 
						$etn_speaker_name           = get_the_author_meta( 'display_name', $value );
						?>
							<div class="etn-se-speaker-item">
								<?php if ($etn_speaker_image) : ?>
									<div class="etn-speaker-image">
										<a href='<?php echo esc_url($etn_speaker_permalink); ?>' aria-label="<?php echo esc_html($etn_speaker_name); ?>">
											<img src="<?php echo esc_url($etn_speaker_image); ?>" alt="<?php echo esc_html($etn_speaker_name); ?>" width="100" height="100">	
										</a>
									</div>
								<?php endif; ?>
								<h4 class="etn-speaker-name">
									<a href='<?php echo esc_url($etn_speaker_permalink); ?>'> <?php echo esc_html($etn_speaker_name); ?> </a>
								</h4> 
								<?php if ($etn_speaker_designatioin) : ?>
									<div class="etn-speaker-designation">
										<p><?php echo esc_html($etn_speaker_designatioin); ?></p>
									</div>
								<?php endif; ?>
								<?php if (is_array( $social ) && !empty( $social ) ) : ?>
									<div class="etn-social etn-social-style-1">
										<?php 
										foreach ($social as $social_value) {
											// Validate and sanitize each value
											$icon = isset($social_value['icon']) && is_string($social_value['icon']) ? $social_value['icon'] : '';
											$etn_social_url = isset($social_value['etn_social_url']) ? $social_value['etn_social_url'] : '#';
											$etn_social_title = isset($social_value['etn_social_title']) ? $social_value['etn_social_title'] : '';
									
											// Generate the social class
											$etn_social_class = 'etn-' . str_replace('fab fa-', '', $icon);
											?>
											<a href="<?php echo esc_url($etn_social_url); ?>" 
												target="_blank" 
												class="<?php echo esc_attr($etn_social_class); ?>" 
												title="<?php echo esc_attr($etn_social_title); ?>" 
												aria-label="<?php echo esc_attr($etn_social_title); ?>">
												<i class="etn-icon <?php echo esc_attr($icon); ?>"></i>
											</a>
										<?php } ?>
									</div>
								<?php endif; ?>
							</div>
						<?php
					}
					wp_reset_postdata();
				}
				?>
			</div>
		</div>
		<?php
		endif;
	}

	/**
	 * single event schedule
	 */
	public static function event_single_schedule( $single_event_id ) {
		$event_options          = get_option("etn_event_options");
		$data                   = Helper::single_template_options( $single_event_id );
		$etn_event_schedule     = isset( $data['etn_event_schedule']) ? $data['etn_event_schedule'] : [];

		if (!isset($event_options["etn_hide_schedule_from_details"]) && !empty($etn_event_schedule)) {
				if (is_array($etn_event_schedule)) {
						$args = array(
								'post__in' => $etn_event_schedule,
								'orderby' => 'post_date',
								'order' => 'asc',
								'post_type' => 'etn-schedule',
								'post_status' => 'publish',
								'suppress_filters' => false,
								'numberposts'	=> -1
						);

						$schedule_query = get_posts($args);

						?>
<!-- schedule tab start -->
<div class="schedule-tab-wrapper etn-tab-wrapper schedule-style-1  no-shadow pt-0">
    <h3 class="etn-tags-title"><?php echo esc_html__('Schedule:', 'eventin'); ?></h3>
    <ul class='etn-nav'>
        <?php
										$i = -1;
										foreach ($schedule_query as $post) :
												$single_schedule_id = $post->ID;
												$i++;
												$schedule_meta = get_post_meta($single_schedule_id);
												$schedule_date = !empty( $schedule_meta['etn_schedule_date'][0] ) ? date_i18n(\Etn\Core\Event\Helper::instance()->etn_date_format(), strtotime($schedule_meta['etn_schedule_date'][0])) : "";
												$active_class = (($i == 0) ? 'etn-active' : ' ');
												$hide_date_on_event_page = get_post_meta($post->ID, 'hide_date_on_event_page', true);
												?>
        <li>
            <a href='#' class='etn-tab-a <?php echo esc_attr($active_class); ?>'
                data-id='tab<?php echo esc_attr($i); ?>'>
                <span class='etn-date'><?php echo esc_html($post->post_title); ?></span>
                 <?php if ( empty($hide_date_on_event_page) ) : ?>
                    <span class='etn-date'><?php echo esc_html($schedule_date); ?></span>
                 <?php endif; ?>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
    <div class='etn-tab-content clearfix etn-schedule-wrap'>
        <?php
										$j = -1;
										foreach ($schedule_query as $post) :
												$single_schedule_id = $post->ID;
												$j++;
												$schedule_meta  = get_post_meta($single_schedule_id);
												$schedule_date  = strtotime($schedule_meta['etn_schedule_date'][0]);
												$schedule_topics_raw = !empty($schedule_meta['etn_schedule_topics'][0]) ? etn_safe_decode($schedule_meta['etn_schedule_topics'][0]) : [];
												$schedule_topics = is_array($schedule_topics_raw) ? $schedule_topics_raw : [];
												$schedule_date  = date_i18n("d M", $schedule_date);
												$active_class   = (($j == 0) ? 'tab-active' : ' ');
												$etn_show_speaker_with_schedule = get_post_meta( $single_event_id, 'etn_select_speaker_schedule_type', true );
												$etn_show_speaker_with_schedule = !empty( $etn_show_speaker_with_schedule) ? $etn_show_speaker_with_schedule : 'schedule_with_speaker';
												$is_active = get_post_meta($single_schedule_id,'is_active',true);
												?>
        <!-- start repeatable item -->
        <div class='etn-tab <?php echo esc_attr($active_class); ?>' data-id='tab<?php echo esc_attr($j); ?>'>
            <?php
														$etn_tab_time_format = (!empty($event_options["time_format"]) && $event_options["time_format"] == '24') ? "H:i" : get_option( 'time_format' );
														if( is_array( $schedule_topics ) && !empty( $schedule_topics )){
																foreach($schedule_topics as $topic) {
																		$etn_schedule_topic         = (isset($topic['etn_schedule_topic']) ? $topic['etn_schedule_topic'] : '');
																		$etn_schedule_start_time    = !empty($topic['etn_shedule_start_time']) ? date_i18n($etn_tab_time_format, strtotime($topic['etn_shedule_start_time'])) : '';
																		$etn_schedule_end_time      = !empty($topic['etn_shedule_end_time']) ? date_i18n($etn_tab_time_format, strtotime($topic['etn_shedule_end_time'])) : '';
																		$etn_schedule_room          = (isset($topic['etn_shedule_room']) ? $topic['etn_shedule_room'] : '');
																		$etn_schedule_objective     = (isset($topic['etn_shedule_objective']) ? $topic['etn_shedule_objective'] : '');
																		$etn_schedule_speaker       = (isset($topic['speakers']) ? (array) $topic['speakers'] : []);
																		$dash_sign	                = ( !empty( $etn_schedule_start_time ) && !empty( $etn_schedule_end_time ) ) ? " - " : " ";
																		$is_active					= $topic['is_active'];

																	 	if($is_active){
																	?>
            <div class='etn-single-schedule-item etn-row'>
                <div class='etn-schedule-info etn-col-sm-4'>
                    <?php
																						
																						if((!empty($etn_schedule_start_time) || !empty( $etn_schedule_end_time )) && $is_active){
																								?>
                    <span class='etn-schedule-time'>
                        <?php echo esc_html($etn_schedule_start_time) . esc_html($dash_sign) . esc_html($etn_schedule_end_time); ?>111
                    </span>

                    <?php
																						}
				
																						if( !empty( $etn_schedule_room ) ){
																								?>
                    <span class='etn-schedule-location'>
                        <i class='etn-icon etn-location'></i>
                        <?php echo esc_html($etn_schedule_room); ?>
                    </span>
                    <?php
																						}
																						?>
                </div>
                <div class='etn-col-sm-8'>
                    <div class="etn-accordion-wrap">
                        <div class="etn-content-item">
                            <h4 class='etn-accordion-heading'>
                                <?php echo esc_html($etn_schedule_topic); ?>
                                <?php if($etn_show_speaker_with_schedule === 'schedule_with_speaker') : ?>
                                <i class="etn-icon etn-plus"></i>
                                <?php endif; ?>
                            </h4>
                            <?php echo wp_kses_post( Helper::render(trim( $etn_schedule_objective )) ); ?>
                            <?php  if($etn_show_speaker_with_schedule === 'schedule_with_speaker') : ?>
                            <div class="etn-acccordion-contents">
                                <div class='etn-schedule-content'>
                                    <div class='etn-schedule-speaker'>
                                        <?php
											$speaker_avatar = apply_filters("etn/speakers/avatar", \Wpeventin::assets_url() . "images/avatar.jpg");
											if (count($etn_schedule_speaker) > 0) {
													foreach ($etn_schedule_speaker as $key => $value) {		
															
															$etn_speaker_permalink = Helper::get_author_page_url_by_id($value);
															$speaker_thumbnail     = get_user_meta( $value, 'image', true);
															$speaker_title         = get_the_author_meta( 'display_name', $value );
															?>
															<div class='etn-schedule-single-speaker'>
																<a href='<?php echo esc_url($etn_speaker_permalink); ?>'>
																		<img src='<?php echo esc_url($speaker_thumbnail); ?>' alt='<?php echo esc_attr($speaker_title); ?>' width="50" height="50">
																</a>
																<span class='etn-schedule-speaker-title'><?php echo esc_html($speaker_title); ?></span>
															</div>
															<?php
													}
											}
											?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php 
																}
														}
														} 
														?>
        </div>
        <!-- end repeatable item -->
        <?php endforeach;
										wp_reset_postdata(); ?>
    </div>
</div>
<!-- schedule tab end -->
<?php
				}
		}
	}

}