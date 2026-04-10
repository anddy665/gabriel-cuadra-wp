<?php

use \Etn\Utils\Helper as Helper;

$post_perpage = 3;

$data = Helper::post_data_query('etn-schedule', $post_perpage, $order, null, null, (array) $etn_schedule_id, null, null, 'meta_value', 'etn_schedule_date');
date_default_timezone_set('UTC'); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.timezone_change_date_default_timezone_set -- intentional UTC context for schedule sorting.

$time_format = Helper::get_option("time_format");
$time_format = !empty($time_format) ? $time_format : '12';
$etn_sched_time_format = ($time_format == '24') ? "H:i" : get_option('time_format');

if (is_array($data) && !empty($data)) {
    ?>
    <div class="schedule-list-wrapper">
        <div class="container">
            <?php foreach ($data as $schedule_data) : ?>
            <div class="row">
                <div class="col-lg-12">
                    <h4 class="schedule-list-title"><?php echo esc_html(get_the_title($schedule_data->ID)); ?></h4>
                    <div class="">
                        <?php
                        $schedule_meta = get_post_meta($schedule_data->ID);
                        $schedule_date = strtotime($schedule_meta['etn_schedule_date'][0] ?? '');
                        $schedule_date = $schedule_date ? date_i18n(get_option("date_format"), $schedule_date) : '';
                        $schedule_topics_raw = etn_safe_decode($schedule_meta['etn_schedule_topics'][0] ?? '');
                        $schedule_topics = is_array($schedule_topics_raw) ? $schedule_topics_raw : [];
                        ?>
                        <div class="schedule-list-date">
                            <?php if (!empty($schedule_date)) : ?>
                                <span class="etn-schedule-date-label"><?php echo esc_html($schedule_date); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php
                        if (is_array($schedule_topics) && !empty($schedule_topics)) {
                            foreach ($schedule_topics as $topic) {
                                $etn_schedule_topic         = (isset($topic['etn_schedule_topic']) ? $topic['etn_schedule_topic'] : '');
                                $etn_schedule_start_time    = !empty($topic['etn_shedule_start_time']) ? date_i18n($etn_sched_time_format, strtotime($topic['etn_shedule_start_time'])) : '';
                                $etn_schedule_end_time      = !empty($topic['etn_shedule_end_time']) ? date_i18n($etn_sched_time_format, strtotime($topic['etn_shedule_end_time'])) : '';
                                $etn_schedule_room          = (isset($topic['etn_shedule_room']) ? $topic['etn_shedule_room'] : '');
                                $etn_schedule_objective     = (isset($topic['etn_shedule_objective']) ? $topic['etn_shedule_objective'] : '');
                                $etn_schedule_speaker       = (isset($topic['speakers']) ? $topic['speakers'] : []);
                        ?>
                                <div class="schedule-listing multi-schedule-list">
                                    <div class="schedule-slot-time">
                                        <?php
                                        if (!empty($etn_schedule_start_time) || !empty($etn_schedule_end_time)) {
                                        ?>
                                            <span> <?php echo esc_attr($etn_schedule_start_time); ?> - <?php echo esc_attr($etn_schedule_end_time); ?> </span>
                                        <?php
                                        }
                                        echo esc_attr($etn_schedule_room);
                                        ?>
                                    </div>
                                    <div class="schedule-slot-info">
                                        <div class="schedule-slot-info-content">
                                            <h3 class="schedule-slot-title">
                                                <?php echo esc_html($etn_schedule_topic); ?>
                                            </h3>
                                            <p class="schedule-slot-details"><?php echo Helper::render($etn_schedule_objective); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Helper::render() outputs trusted admin-entered HTML content. ?></p>
                                            <div class="multi-speaker">
                                                <?php
                                                $speaker_avatar = apply_filters("etn/speakers/avatar", \Wpeventin::assets_url() . "images/avatar.jpg");
                                                if (is_array($etn_schedule_speaker) && !empty($etn_schedule_speaker)) {
                                                    foreach ($etn_schedule_speaker as $key => $value) {
                                                        $etn_speaker_permalink = Helper::get_author_page_url_by_id($value);
                                                        $etn_speaker_image     = get_user_meta($value, 'image', true);
                                                        $speaker_title         = get_the_author_meta('display_name', $value);
                                                ?>

                                                        <div class="speaker-content">

                                                            <a href='<?php echo esc_url($etn_speaker_permalink); ?>' target="_blank" rel="noopener" aria-label='<?php echo esc_attr($speaker_title); ?>'>
                                                                <?php if ($etn_speaker_image): ?>
                                                                    <img src="<?php echo esc_url($etn_speaker_image); ?>" alt="<?php echo esc_attr($speaker_title); ?>">
                                                                <?php else: ?>
                                                                    <img src='<?php echo esc_url($speaker_avatar); ?>' class="schedule-slot-speakers" alt='<?php echo esc_attr($speaker_title); ?>'>
                                                                <?php endif; ?>
                                                            </a>
                                                            <p class="schedule-speaker <?php echo esc_attr("speaker-" . $key); ?>">
                                                                <?php echo esc_html($speaker_title); ?>
                                                            </p>
                                                        </div>
                                                <?php
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
} else {
    ?>
    <p class="etn-not-found-post"><?php echo esc_html__('No Schedule Found', 'eventin'); ?></p>
    <?php
}
