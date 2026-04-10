<?php
    use \Etn\Utils\Helper as Helper;

    $event_options      = get_option("etn_event_options");
    $data               = Helper::single_template_options($event_id);
    $etn_event_schedule = isset($data['etn_event_schedule']) ? $data['etn_event_schedule'] : [];

    if (! isset($event_options["etn_hide_schedule_from_details"]) && ! empty($etn_event_schedule)) {
        if (is_array($etn_event_schedule)) {
            $args = [
                'post__in'         => $etn_event_schedule,
                'orderby'          => 'post_date',
                'order'            => 'asc',
                'post_type'        => 'etn-schedule',
                'post_status'      => 'publish',
                'suppress_filters' => false,
                'numberposts'      => -1,
            ];

            $schedule_query = get_posts($args);

        ?>
<!-- schedule tab start -->
<div class=" schedule-tab-wrapper etn-tab-wrapper schedule-style-1  no-shadow pt-0                                                                                                                                                                     <?php echo esc_attr($container_class); ?>">
    <div class="eventin-block-container">
    <h3 class="etn-tags-title"><?php echo esc_html__('Schedule:', 'eventin'); ?></h3>
        <ul class='etn-nav'>
            <?php
                $i = -1;
                        foreach ($schedule_query as $post):
                            $single_schedule_id = $post->ID;
                            $i++;
                            $schedule_meta = get_post_meta($single_schedule_id);
                            $schedule_date = ! empty($schedule_meta['etn_schedule_date'][0]) ? date_i18n(\Etn\Core\Event\Helper::instance()->etn_date_format(), strtotime($schedule_meta['etn_schedule_date'][0])) : "";
                            $active_class  = (($i == 0) ? 'etn-active' : ' ');
                        ?>
		            <li>
		                <a href='#' class='etn-tab-a		                                            	                                             <?php echo esc_attr($active_class); ?>'
		                    data-id='tab<?php echo esc_attr($i); ?>'>
		                    <span class='etn-date'><?php echo esc_html($post->post_title); ?></span>
		                    <span class='etn-day'><?php echo esc_html($schedule_date); ?></span>
		                </a>
		            </li>
		            <?php endforeach; ?>
        </ul>
        <div class='etn-tab-content clearfix etn-schedule-wrap'>
            <?php
                $j = -1;
                        foreach ($schedule_query as $post):
                            $single_schedule_id = $post->ID;
                            $j++;
                            $schedule_meta                  = get_post_meta($single_schedule_id);
                            $schedule_date                  = strtotime($schedule_meta['etn_schedule_date'][0]);
                            $schedule_topics_raw            = ! empty($schedule_meta['etn_schedule_topics'][0]) ? etn_safe_decode($schedule_meta['etn_schedule_topics'][0]) : [];
                            $schedule_topics                = is_array($schedule_topics_raw) ? $schedule_topics_raw : [];
                            $schedule_date                  = date_i18n("d M", $schedule_date);
                            $active_class                   = (($j == 0) ? 'tab-active' : ' ');
                            $etn_show_speaker_with_schedule = get_post_meta($event_id, 'etn_select_speaker_schedule_type', true);
                            $etn_show_speaker_with_schedule = ! empty($etn_show_speaker_with_schedule) ? $etn_show_speaker_with_schedule : 'schedule_with_speaker';
                        ?>
		            <!-- start repeatable item -->
		            <div class='etn-tab		                               	                                <?php echo esc_attr($active_class); ?>' data-id='tab<?php echo esc_attr($j); ?>'>
		                <?php
                                $etn_tab_time_format = (! empty($event_options["time_format"]) && $event_options["time_format"] == '24') ? "H:i" : get_option('time_format');
                                        if (is_array($schedule_topics) && ! empty($schedule_topics)) {
                                            foreach ($schedule_topics as $topic) {
                                                $etn_schedule_topic      = (isset($topic['etn_schedule_topic']) ? $topic['etn_schedule_topic'] : '');
                                                $etn_schedule_start_time = ! empty($topic['etn_shedule_start_time']) ? date_i18n($etn_tab_time_format, strtotime($topic['etn_shedule_start_time'])) : '';
                                                $etn_schedule_end_time   = ! empty($topic['etn_shedule_end_time']) ? date_i18n($etn_tab_time_format, strtotime($topic['etn_shedule_end_time'])) : '';
                                                $etn_schedule_room       = (isset($topic['etn_shedule_room']) ? $topic['etn_shedule_room'] : '');
                                                $etn_schedule_objective  = (isset($topic['etn_shedule_objective']) ? $topic['etn_shedule_objective'] : '');
                                                $etn_schedule_speaker    = (isset($topic['speakers']) ? (array) $topic['speakers'] : []);
                                                $dash_sign               = (! empty($etn_schedule_start_time) && ! empty($etn_schedule_end_time)) ? " - " : " ";

                                            ?>
		                <div class='etn-single-schedule-item etn-row'>
		                    <div class='etn-schedule-info etn-col-sm-4'>
		                        <?php

                                                        if (! empty($etn_schedule_start_time) || ! empty($etn_schedule_end_time)) {
                                                        ?>
		        <span class='etn-schedule-time'>
		            <?php echo esc_html($etn_schedule_start_time) . esc_html($dash_sign) . esc_html($etn_schedule_end_time); ?>
		        </span>

		        <?php
                        }

                                        if (! empty($etn_schedule_room)) {
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
		                        <?php if ($etn_show_speaker_with_schedule === 'schedule_with_speaker'): ?>
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
                                                            $speaker_thumbnail     = get_user_meta($value, 'image', true);
                                                            $speaker_title         = get_the_author_meta('display_name', $value);
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
                        ?>
            </div>
            <!-- end repeatable item -->
            <?php endforeach;
                    wp_reset_postdata(); ?>
        </div>
        </div>
    </div>
</div>
<!-- schedule tab end -->
<?php
    }
}
