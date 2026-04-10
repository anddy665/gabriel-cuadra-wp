<?php

use \Etn\Utils\Helper;

defined( 'ABSPATH' ) || exit;

$etn_event_schedule     = get_post_meta( $single_event_id, 'etn_event_schedule', true);
date_default_timezone_set('UTC'); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.timezone_change_date_default_timezone_set -- intentional UTC context for schedule sorting.

if ( is_array($etn_event_schedule) && !empty($etn_event_schedule) ) {
    $args = array(
        'post__in' => $etn_event_schedule,
        'orderby' => 'post_date',
        'order' => 'asc',
        'post_type' => 'etn-schedule',
        'post_status' => 'publish',
        'suppress_filters' => false,
    );

    $schedule_query = get_posts($args);
    ?>
<!-- schedule tab start -->
<div class="schedule-tab-wrapper etn-tab-wrapper">
    <ul class='etn-nav'>
        <?php
            $i = -1;
            if( is_array( $schedule_query ) ){
                usort(
                    $schedule_query,
                    function( $a, $b ) {
                        $a_timestamp = strtotime( get_post_meta( $a->ID, 'etn_schedule_date', true ) ?: '0' );
                        $b_timestamp = strtotime( get_post_meta( $b->ID, 'etn_schedule_date', true ) ?: '0' );
            
                        return $a_timestamp <=> $b_timestamp;
                    }
                );
                
                foreach ($schedule_query as $post) :
                    $single_schedule_id = $post->ID;
                    $i++;
                    $schedule_meta = get_post_meta($single_schedule_id);
                    $schedule_date = !empty( $schedule_meta['etn_schedule_date'][0] ) ? date_i18n("d M", strtotime($schedule_meta['etn_schedule_date'][0])) : "";
                    $active_class = (($i == 0) ? 'etn-active' : ' ');
                    $hide_date_on_event_page = get_post_meta($post->ID, 'hide_date_on_event_page', true);
                    ?>
                    <li>
                        <a href='#' class='etn-tab-a <?php echo esc_attr($active_class); ?>'
                            data-id='tab<?php echo esc_attr($i); ?>'>
                            <?php if ( empty($hide_date_on_event_page) ) : ?>
                            <span class='etn-date'><?php echo esc_html($schedule_date); ?></span>
                            <?php endif; ?>
                            <span class=etn-day><?php echo esc_html($post->post_title); ?></span>
                        </a>
                    </li>
                <?php 
                endforeach; 
            }
            ?>
    </ul>
    <div class='etn-tab-content clearfix etn-schedule-wrap'>
        <?php
            $j = -1;
            if( is_array( $schedule_query ) ){
                foreach ($schedule_query as $post) :
                    $single_schedule_id = $post->ID;
                    $j++;
                    $schedule_meta = get_post_meta($single_schedule_id);
                    $schedule_topics_raw = etn_safe_decode($schedule_meta['etn_schedule_topics'][0] ?? '');
                    $schedule_topics = is_array($schedule_topics_raw) ? $schedule_topics_raw : [];
                    $schedule_date = !empty( $schedule_meta['etn_schedule_date'][0] ) ? date_i18n("d M", strtotime($schedule_meta['etn_schedule_date'][0]) ) : "";
                    $active_class = (($j == 0) ? 'tab-active' : ' ');
                    ?>
        <!-- start repeatable item -->
        <div class='etn-tab <?php echo esc_attr($active_class); ?>' data-id='tab<?php echo esc_attr($j); ?>'>
            <?php
                        $etn_tab_time_format = ( isset( $event_options["time_format"] ) && $event_options["time_format"] == '24' ) ? "H:i" : get_option( 'time_format' );
                        if( is_array( $schedule_topics ) && !empty($schedule_topics) ){
                            usort( $schedule_topics, function( $a, $b ) {
                                $a_time = ! empty( $a['etn_shedule_start_time'] ) ? strtotime( $a['etn_shedule_start_time'] ) : 0;
                                $b_time = ! empty( $b['etn_shedule_start_time'] ) ? strtotime( $b['etn_shedule_start_time'] ) : 0;
                                return $a_time <=> $b_time;
                            } );
                            foreach ($schedule_topics as $topic) :
                                // Skip topic if is_active is set and equals 0
                                if ( !isset($topic['is_active']) || $topic['is_active'] != 0 ) :
                                    $etn_schedule_topic         = (isset($topic['etn_schedule_topic']) ? $topic['etn_schedule_topic'] : '');
                                    $etn_schedule_start_time    = !empty($topic['etn_shedule_start_time']) ? date_i18n($etn_tab_time_format, strtotime($topic['etn_shedule_start_time'])) : '';
                                    $etn_schedule_end_time      = !empty($topic['etn_shedule_end_time']) ? date_i18n($etn_tab_time_format, strtotime($topic['etn_shedule_end_time'])) : '';
                                    $etn_schedule_room          = (isset($topic['etn_shedule_room']) ? $topic['etn_shedule_room'] : '');
                                    $etn_schedule_objective     = (isset($topic['etn_shedule_objective']) ? $topic['etn_shedule_objective'] : '');
                                    $etn_schedule_speaker       = (isset($topic['speakers']) ? $topic['speakers'] : []);
                                    $dash_sign	                = ( !empty( $etn_schedule_start_time ) && !empty( $etn_schedule_end_time ) ) ? " - " : " ";
                                    ?>
            <div class='etn-single-schedule-item etn-row'>
                <div class='etn-schedule-info etn-col-sm-4'>
                    <?php
                                        if(!empty($etn_schedule_start_time) || !empty( $etn_schedule_end_time )){
                                            ?>
                    <span class='etn-schedule-time'>
                        <?php echo esc_html($etn_schedule_start_time) . esc_html($dash_sign) . esc_html($etn_schedule_end_time); ?>
                    </span>
                    <?php
                                        }
                                        if( !empty( $etn_schedule_room ) ){
                                            ?>
                    <span class='etn-schedule-location'>
                        <i class='etn-icon etn-location'></i><?php echo esc_html($etn_schedule_room);?>
                    </span>
                    <?php
                                        }
                                        ?>
                </div>
                <div class='etn-schedule-content etn-col-sm-8'>
                    <h4 class='etn-title'><?php echo esc_html($etn_schedule_topic); ?></h4>
                    <p><?php echo Helper::kses($etn_schedule_objective) ; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Helper::kses() wraps wp_kses() with an allowed tags list. ?></p>
                    <?php 
                                            $etn_show_speaker_with_schedule = get_post_meta( $single_event_id, 'etn_select_speaker_schedule_type', true );
                                            $etn_show_speaker_with_schedule = !empty( $etn_show_speaker_with_schedule) ? $etn_show_speaker_with_schedule : 'schedule_with_speaker';
                                        ; ?>
                    <?php  if($etn_show_speaker_with_schedule === 'schedule_with_speaker') : ?>
                    <!-- Show speaker block if it's selected from event meta -->
                    <div class='etn-schedule-content'>
                        <div class='etn-schedule-speaker'>
                            <?php
                                $speaker_avatar = apply_filters("etn/speakers/avatar", \Wpeventin::assets_url() . "images/avatar.jpg");
                                if (is_array($etn_schedule_speaker) && !empty($etn_schedule_speaker)) {
                                foreach ($etn_schedule_speaker as $key => $value) {
                                    $etn_speaker_permalink = Helper::get_author_page_url_by_id($value);
                                    $etn_speaker_image     = get_user_meta( $value, 'image', true);
                                    $speaker_title         = get_the_author_meta( 'display_name', $value );
                                    ?>
                            <div class='etn-schedule-single-speaker'>
                                <a href='<?php echo esc_url($etn_speaker_permalink); ?>'
                                    aria-label="<?php echo esc_html($speaker_title); ?>">
                                    <?php if($etn_speaker_image): ?>
                                        <img src="<?php echo esc_url($etn_speaker_image); ?>" alt="<?php echo esc_attr($speaker_title); ?>" height="50" width="50">
                                    <?php echo get_the_post_thumbnail($value, 'thumbnail'); ?>
                                    <?php else: ?>
                                    <img src='<?php echo esc_url($speaker_avatar); ?>'
                                        alt='<?php echo esc_attr($speaker_title); ?>'>
                                    <?php endif; ?>
                                </a>
                                <span class='etn-schedule-speaker-title'><?php echo esc_html($speaker_title); ?></span>
                            </div>
                            <?php
                                                        }
                                                    }
                                                    ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php
                                endif; // End is_active check
                            endforeach; 
                        }
                        ?>
        </div>
        <!-- end repeatable item -->
        <?php 
                endforeach;
            }
            wp_reset_postdata(); ?>
    </div>
</div>
<!-- schedule tab end -->
<?php 
}