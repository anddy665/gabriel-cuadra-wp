<?php

    use \Etn\Utils\Helper;

    defined('ABSPATH') || exit;

    $etn_event_schedule = $event->etn_event_schedule;
    date_default_timezone_set('UTC'); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.timezone_change_date_default_timezone_set -- intentional UTC context for schedule sorting.

    if (is_array($etn_event_schedule) && ! empty($etn_event_schedule)) {
        $args = [
            'post__in'         => $etn_event_schedule,
            'orderby'          => 'post_date',
            'order'            => 'asc',
            'post_type'        => 'etn-schedule',
            'post_status'      => 'publish',
            'suppress_filters' => false,
        ];

        $schedule_query = get_posts($args);
    ?>
<!-- schedule tab start -->
 <div id="schedule-style-4-wrapper">
    <div class="schedule-tab-wrapper etn-tab-wrapper schedule-style-1 schedule-style-4
    <?php echo esc_attr($container_class); ?>">
        <div class="eventin-block-container schedule-style-two schedule-style-4">
            <div class="schedule-block-wrapper">
                <ul class='etn-nav'>
                    <?php
                        $i = -1;
                            if (is_array($schedule_query)) {
                                foreach ($schedule_query as $post):
                                    $single_schedule_id = $post->ID;
                                    $i++;
                                    $schedule_meta = get_post_meta($single_schedule_id);
                                    $schedule_date = ! empty($schedule_meta['etn_schedule_date'][0]) ? date_i18n("d M", strtotime($schedule_meta['etn_schedule_date'][0])) : "";
                                    $active_class  = (($i == 0) ? 'etn-active' : ' ');
                                ?>
	                                                                        <li>
	                                                                            <a href='#' class='etn-tab-a	                                                                                                        													                                                												                                                											                                                										                                                									                                                								                                                							                                                						                                                					                                                				                                                			                                                		                                                	                                                																	                                                                																                                                                															                                                                														                                                                													                                                                												                                                                											                                                                										                                                                									                                                                								                                                                							                                                                						                                                                					                                                                				                                                                			                                                                		                                                                	                                                                												                                                            											                                                            										                                                            									                                                            								                                                            							                                                            						                                                            					                                                            				                                                            			                                                            		                                                            	                                                            						                                                            					                                                            				                                                             <?php echo esc_attr($active_class); ?>' data-id='tab<?php echo esc_attr($i); ?>'>
	                                                                                <span class='etn-date'><?php echo esc_html($schedule_date); ?></span>
	                                                                                <span class='etn-day'><?php echo esc_html($post->post_title); ?></span>
	                                                                            </a>
	                                                                        </li>
	                                                                    <?php endforeach;
                                                                            }?>
                </ul>
                <div class='etn-tab-content clearfix etn-schedule-wrap schedule-style-4-grid'>
                    <?php
                        $j = -1;
                            if (is_array($schedule_query)) {
                                foreach ($schedule_query as $post):
                                    $single_schedule_id = $post->ID;
                                    $j++;
                                    $schedule_meta   = get_post_meta($single_schedule_id);
                                    $schedule_topics = unserialize($schedule_meta['etn_schedule_topics'][0] ?? '') ?: [];
                                    $schedule_date   = ! empty($schedule_meta['etn_schedule_date'][0]) ? date_i18n("d M", strtotime($schedule_meta['etn_schedule_date'][0])) : "";
                                    $active_class    = (($j == 0) ? 'tab-active' : ' ');
                                ?>
	                                                                                <!-- start repeatable item -->
	                                                                                <div class='etn-tab	                                                                                                   												                                               											                                               										                                               									                                               								                                               							                                               						                                               					                                               				                                               			                                               		                                               	                                               									                                               								                                               							                                               						                                               					                                               				                                               			                                               		                                               	                                               				                                           			                                           		                                           	                                           				                                           			                                           		                                           	                                           				                                               			                                               		                                               	                                               								                                               							                                               						                                               					                                               				                                               			                                               		                                               	                                               						                                               					                                               				                                               			                                                <?php echo esc_attr($active_class); ?>' data-id='tab<?php echo esc_attr($j); ?>'>
	                                                                                    <?php $etn_tab_time_format = (isset($event_options["time_format"]) && $event_options["time_format"] == '24') ? "H:i" : get_option('time_format');
                                                                                                        if (is_array($schedule_topics) && ! empty($schedule_topics)) {
                                                                                                            foreach ($schedule_topics as $topic):
                                                                                                                $etn_schedule_topic      = (isset($topic['etn_schedule_topic']) ? $topic['etn_schedule_topic'] : '');
                                                                                                                $etn_schedule_start_time = ! empty($topic['etn_shedule_start_time']) ? date_i18n($etn_tab_time_format, strtotime($topic['etn_shedule_start_time'])) : '';
                                                                                                                $etn_schedule_end_time   = ! empty($topic['etn_shedule_end_time']) ? date_i18n($etn_tab_time_format, strtotime($topic['etn_shedule_end_time'])) : '';
                                                                                                                $etn_schedule_room       = (isset($topic['etn_shedule_room']) ? $topic['etn_shedule_room'] : '');
                                                                                                                $etn_schedule_objective  = (isset($topic['etn_shedule_objective']) ? $topic['etn_shedule_objective'] : '');
                                                                                                                $etn_schedule_speaker    = (isset($topic['speakers']) ? $topic['speakers'] : []);
                                                                                                                $dash_sign               = (! empty($etn_schedule_start_time) && ! empty($etn_schedule_end_time)) ? " - " : " ";
                                                                                                            ?>
		                                                                                                                                        <div class='etn-schedule-card-item'>
		                                                                                                                                            <div class='etn-schedule-card-meta'>
		                                                                                                                                            <?php if (! empty($etn_schedule_start_time) || ! empty($etn_schedule_end_time)): ?>
		                                                                                                                                                <div class='etn-schedule-meta-row'>
		                                                                                                                                                    <i class='etn-icon etn-clock'></i>
		                                                                                                                                                    <span class='etn-schedule-time'>
				                                                                                                                                                    <?php echo esc_html($etn_schedule_start_time) . $dash_sign . esc_html($etn_schedule_end_time); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		                                                                                                                                                    </span>
		                                                                                                                                                </div>
		                                                                                                                                        <?php endif; ?>
	                                                                                    <?php if (! empty($etn_schedule_room)): ?>
	                                                                                        <div class='etn-schedule-meta-row'>
	                                                                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
	                                                                                                <path d="M11.625 8.25C11.625 9.69975 10.4497 10.875 9 10.875C7.55025 10.875 6.375 9.69975 6.375 8.25C6.375 6.80025 7.55025 5.625 9 5.625C10.4497 5.625 11.625 6.80025 11.625 8.25Z" stroke="#575859" stroke-width="1.125"/>
	                                                                                                <path d="M9 1.5C12.653 1.5 15.75 4.52474 15.75 8.19435C15.75 11.9224 12.6025 14.5385 9.69525 16.3175C9.48338 16.4372 9.24375 16.5 9 16.5C8.75625 16.5 8.51662 16.4372 8.30475 16.3175C5.40292 14.5212 2.25 11.9353 2.25 8.19435C2.25 4.52474 5.34708 1.5 9 1.5Z" stroke="#575859" stroke-width="1.125"/>
	                                                                                            </svg>
	                                                                                            <span class='etn-schedule-location'><?php echo esc_html($etn_schedule_room); ?></span>
	                                                                                </div>
	                                                                            <?php endif; ?>
                                </div>
                                <div class='etn-schedule-item-content'>
                                    <h4 class='etn-title'><?php echo ! empty($etn_schedule_topic) ? esc_html($etn_schedule_topic) : ''; ?></h4>
                                    <p><?php echo Helper::kses($etn_schedule_objective); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>

                                    <?php
                                        $etn_show_speaker_with_schedule = get_post_meta($event_id, 'etn_select_speaker_schedule_type', true);
                                                    $etn_show_speaker_with_schedule = ! empty($etn_show_speaker_with_schedule) ? $etn_show_speaker_with_schedule : 'schedule_with_speaker';
                                                ?>
                                    <?php if ($etn_show_speaker_with_schedule === 'schedule_with_speaker' && is_array($etn_schedule_speaker) && ! empty($etn_schedule_speaker)): ?>
                                        <div class='etn-schedule-card-speakers'>
                                            <?php
                                                $speaker_avatar = apply_filters("etn/speakers/avatar", \Wpeventin::assets_url() . "images/avatar.jpg");
                                                            foreach ($etn_schedule_speaker as $key => $value) {
                                                                $etn_speaker_permalink = Helper::get_author_page_url_by_id($value);
                                                                $etn_speaker_image     = get_user_meta($value, 'image', true);
                                                                $speaker_title         = get_the_author_meta('display_name', $value);
                                                                $speaker_image_url     = ! empty($etn_speaker_image) ? $etn_speaker_image : $speaker_avatar;
                                                            ?>
                                                <div class='etn-schedule-card-speaker'>
                                                    <a href='<?php echo esc_url($etn_speaker_permalink); ?>' aria-label="<?php echo esc_attr($speaker_title); ?>">
                                                        <img src="<?php echo esc_url($speaker_image_url); ?>" alt="<?php echo esc_attr($speaker_title); ?>" class='etn-schedule-speaker-avatar'>
                                                    </a>
                                                    <span class='etn-schedule-speaker-name'><?php echo esc_html($speaker_title); ?></span>
                                                </div>
                                            <?php
                                                }
                                                        ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                <?php
                    endforeach;
                            }
                        ?>
    </div>
                    <?php
                        endforeach;
                            }
                        wp_reset_postdata(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
}
