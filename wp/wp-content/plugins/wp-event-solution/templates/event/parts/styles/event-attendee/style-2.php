<div class="<?php echo esc_attr($container_class); ?> etn-event-attendees">

    <div class="eventin-block-container">
        <?php if (! empty($event_attendees) && is_array($event_attendees)): ?>

             <?php
                 $event_attendees = array_filter($event_attendees, function ($attendee) {
                     return ! empty($attendee['attendee_post_status']) && $attendee['attendee_post_status'] === 'publish' && empty($attendee['trash']);
                 });

             ?>

        <div class="etn-attendee-wrap etn-attendee-style-2">
            <?php foreach (array_slice($event_attendees, 0, $items_per_row) as $attendee): ?>
            <?php
                $attendee_id    = ! empty($attendee['id']) ? $attendee['id'] : 0;
                $attendee_name  = ! empty($attendee['etn_name']) ? $attendee['etn_name'] : '';
                $attendee_email = ! empty($attendee['etn_email']) ? $attendee['etn_email'] : '';

                // Get attendee avatar
                $attendee_avatar = '';
                if (! empty($attendee_email)) {
                    $attendee_avatar = get_avatar_url($attendee_email, ['size' => 150]);
                } else {
                    $default_avatar_url = Wpeventin::assets_url() . 'images/avatar.jpg';
                    $attendee_avatar    = apply_filters('etn/attendee/default_avatar', $default_avatar_url);
                }
            ?>
            <div class="etn-attendee-item">
                <div class="etn-attendee-avatar">
                    <img src="<?php echo esc_url($attendee_avatar); ?>"
                        alt="<?php echo esc_attr($attendee_name); ?>"
                    >
                </div>
                <div class="etn-attendee-content">
                    <h4 class="etn-attendee-name"><?php echo esc_html($attendee_name); ?></h4>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if (count($event_attendees) > $items_per_row): ?>
        <div class="etn-attendee-more">
            <a href="<?php echo esc_url($attendee_page_url); ?>" class="etn-attendee-more-link" target="_blank">
                <?php esc_html_e('See All Attendee', 'eventin'); ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="13" viewBox="0 0 16 13" fill="none">
                    <path d="M5.0071e-07 5.97206L15 5.97206" stroke="#5D5DFF" stroke-width="2"/>
                    <path d="M9.96338 0.323146C10.4242 1.67272 12.0371 4.70761 14.8021 6.0506" stroke="#5D5DFF" stroke-width="2"/>
                    <path d="M9.96338 11.778C10.4242 10.4285 12.0371 7.39359 14.8021 6.0506" stroke="#5D5DFF" stroke-width="2"/>
                </svg>
            </a>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <p><?php esc_html_e('No attendee found', 'eventin'); ?></p>
        <?php endif; ?>
    </div>
</div>



