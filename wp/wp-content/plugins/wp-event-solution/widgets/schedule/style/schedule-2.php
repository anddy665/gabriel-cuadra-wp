<?php
use \Etn\Utils\Helper as Helper;

$post_perpage = 3;
if ( did_action( 'eventin-pro/after_load'  ) ) {
    $post_perpage = 50;
}

$data = Helper::post_data_query( 'etn-schedule' , $post_perpage, $order, null , null ,  $etn_schedule_ids ,
null , null , "meta_value" , "etn_schedule_date"  );
date_default_timezone_set('UTC'); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.timezone_change_date_default_timezone_set -- intentional UTC context for schedule sorting.

if ( is_array( $data ) && !empty( $data ) ) {
    
$i = -1;
?>
<!-- schedule tab start -->
<div class="schedule-tab-wrapper etn-tab-wrapper schedule-style-2">
    <ul class='etn-nav'>
        <?php
        $i = -1;
        foreach( $data as $value )  :
            $i++;
            $schedule_meta = get_post_meta($value->ID);
            $schedule_date = strtotime($schedule_meta['etn_schedule_date'][0]);
            $schedule_date = date_i18n("d M", $schedule_date);
            $active_class = (($i == 0) ? 'etn-active' : ' ');
            ?>
            <li>
                <a href='#' class='etn-tab-a <?php echo esc_attr($active_class); ?>' data-id='tab<?php echo esc_attr( $value->ID ) . "-" . esc_attr($i); ?>'>
                    <span class=etn-day><?php echo esc_html( get_the_title( $value->ID ) ); ?></span>
                    <span class='etn-date'><?php echo esc_html($schedule_date); ?></span>
                </a>

            </li>
        <?php endforeach; ?>
    </ul>
    <div class='etn-tab-content clearfix etn-schedule-wrap'>
        <?php
            $time_format = Helper::get_option("time_format");
            $time_format = !empty( $time_format ) ? $time_format : '12';
            $etn_sched_time_format = ( $time_format == '24') ? "H:i": get_option( 'time_format' ); 

        $j = -1;
        foreach ($data as $post) :
            $j++;
            $schedule_meta = get_post_meta($post->ID);
            $schedule_date = strtotime($schedule_meta['etn_schedule_date'][0]);
            $schedule_topics_raw = etn_safe_decode($schedule_meta['etn_schedule_topics'][0] ?? '');
            $schedule_topics = is_array($schedule_topics_raw) ? $schedule_topics_raw : [];
            $schedule_date = date_i18n("d M", $schedule_date);
            $active_class = (($j == 0) ? 'tab-active' : ' ');
            ?>
            <!-- start repeatable item -->
            <div class='etn-tab <?php echo esc_attr($active_class); ?>' data-id='tab<?php echo esc_attr($post->ID) . "-" . esc_attr($j); ?>'>
                <div class='etn-schedule-grid-container'>
                    <?php
                    if( is_array( $schedule_topics ) && !empty( $schedule_topics )){
                        foreach ($schedule_topics as $topic) {
                            $etn_schedule_topic         = (isset($topic['etn_schedule_topic']) ? $topic['etn_schedule_topic'] : '');
                            $etn_schedule_start_time    = !empty($topic['etn_shedule_start_time']) ? date_i18n($etn_sched_time_format, strtotime($topic['etn_shedule_start_time'])) : '';
                            $etn_schedule_end_time      = !empty($topic['etn_shedule_end_time']) ? date_i18n($etn_sched_time_format, strtotime($topic['etn_shedule_end_time'])) : '';
                            $etn_schedule_room          = (isset($topic['etn_shedule_room']) ? $topic['etn_shedule_room'] : '');
                            $etn_schedule_objective     = (isset($topic['etn_shedule_objective']) ? $topic['etn_shedule_objective'] : '');
                            $etn_schedule_speaker       = (isset($topic['speakers']) ? $topic['speakers'] : []);
                            ?>
                            <div class='etn-single-schedule-item etn-schedule-grid-item'>
                                <div class='etn-schedule-info'>
                                    <?php
                                    if(!empty($etn_schedule_start_time) || !empty( $etn_schedule_end_time )){
                                        ?>
                                        <span class='etn-schedule-time'> <?php echo esc_attr($etn_schedule_start_time); ?> - <?php echo esc_attr($etn_schedule_end_time); ?> </span>
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
                                <div class='etn-schedule-content'>
                                    <h4 class='etn-title'><?php echo esc_html($etn_schedule_topic); ?></h4>
                                    <p><?php echo Helper::render($etn_schedule_objective); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Helper::render() outputs trusted admin-entered HTML content. ?></p>
                                </div>
                                <?php if ( $show_speaker_section === 'yes' ) : ?>
                                <div class='etn-schedule-right-content'>
                                        <div class='etn-schedule-single-speaker'>
                                            <div class='etn-schedule-speaker'>
                                            <?php
                                                $speaker_avatar = apply_filters("etn/speakers/avatar", \Wpeventin::assets_url() . "images/avatar.jpg");
                                                if ( is_array( $etn_schedule_speaker ) && !empty($etn_schedule_speaker) ) {
                                                    foreach ($etn_schedule_speaker as $key => $value) {
                                                        $etn_speaker_permalink = Helper::get_author_page_url_by_id($value);
                                                        $etn_speaker_image     = get_user_meta( $value, 'image', true);
                                                        $speaker_title         = get_the_author_meta( 'display_name', $value );
                                                        ?>
                                                            <div class='etn-schedule-single-speaker'>
                                                                <a href='<?php echo esc_url($etn_speaker_permalink); ?>' aria-label="<?php echo esc_html($speaker_title); ?>">
                                                                    <?php if(!empty($etn_speaker_image)): ?>
                                                                        <img src="<?php echo esc_url($etn_speaker_image); ?>" alt="<?php echo esc_attr($speaker_title);?>">
                                                                    <?php else: ?>
                                                                        <img src='<?php echo esc_url( $speaker_avatar);?>' alt='<?php echo esc_attr($speaker_title);?>'>
                                                                    <?php endif; ?>
                                                                </a>
                                                                <span class='etn-schedule-speaker-title'><?php echo esc_html($speaker_title);?></span>
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
                            <?php 
                        }
                    }
                    ?>
                </div>
            </div>
            <!-- end repeatable item -->
        <?php endforeach; ?>
    </div>
</div>
<!-- schedule tab end -->
<?php }else{ ?>
        <p class="etn-not-found-post"><?php echo esc_html__('No Post Found', 'eventin'); ?></p>
    <?php
    }