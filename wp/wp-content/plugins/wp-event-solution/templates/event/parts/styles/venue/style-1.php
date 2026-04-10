<div class="<?php echo esc_attr($container_class); ?>">
    <div class="eventin-block-container">
        <div class="etn-venue-info-wrapper">
            <div class="etn-venue-info-content">
                <h3 class="etn-venue-info-title"><?php echo esc_html__('Venue Info', 'eventin'); ?></h3>

                <div class="etn-venue-info-list">
                    <?php if (! empty($event_location)): ?>
                    <div class="etn-venue-info-item">
                        <div class="etn-venue-info-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="18" viewBox="0 0 16 18" fill="none">
                                <path d="M7.65043 9.89632C9.02928 9.89632 10.1471 8.77855 10.1471 7.3997C10.1471 6.02085 9.02928 4.90308 7.65043 4.90308C6.27158 4.90308 5.15381 6.02085 5.15381 7.3997C5.15381 8.77855 6.27158 9.89632 7.65043 9.89632Z" stroke="#5D5DFF" stroke-width="1.5"/>
                                <path d="M0.94523 5.9433C2.52162 -0.986432 12.7882 -0.97843 14.3566 5.9513C15.2768 10.0163 12.7482 13.4572 10.5316 15.5857C8.92322 17.1381 6.37859 17.1381 4.76218 15.5857C2.55363 13.4572 0.0249999 10.0083 0.94523 5.9433Z" stroke="#5D5DFF" stroke-width="1.5"/>
                            </svg>
                        </div>
                        <div class="etn-venue-info-text"><?php echo esc_html($event_location); ?></div>
                    </div>
                    <?php endif; ?>

                    <?php if (! empty($start_date) || ! empty($end_date)): ?>
                    <div class="etn-venue-info-item">
                        <div class="etn-venue-info-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="18" viewBox="0 0 16 18" fill="none">
                                <path d="M4.6001 0.599976V2.99998" stroke="#5D5DFF" stroke-width="1.2" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10.9985 0.599976V2.99998" stroke="#5D5DFF" stroke-width="1.2" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M0.998535 6.27197H14.5985" stroke="#5D5DFF" stroke-width="1.2" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M15.0001 5.79993V12.5999C15.0001 14.9999 13.8001 16.5999 11.0001 16.5999H4.6001C1.8001 16.5999 0.600098 14.9999 0.600098 12.5999V5.79993C0.600098 3.39993 1.8001 1.79993 4.6001 1.79993H11.0001C13.8001 1.79993 15.0001 3.39993 15.0001 5.79993Z" stroke="#5D5DFF" stroke-width="1.2" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10.7535 9.95994H10.7607" stroke="#5D5DFF" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10.7535 12.36H10.7607" stroke="#5D5DFF" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M7.79648 9.95994H7.80367" stroke="#5D5DFF" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M7.79648 12.36H7.80367" stroke="#5D5DFF" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M4.83555 9.95994H4.84273" stroke="#5D5DFF" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M4.83555 12.36H4.84273" stroke="#5D5DFF" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="etn-venue-info-text">
                            <?php
                                if (! empty($start_date) && ! empty($end_date)) {
                                    echo esc_html($start_date) . ' - ' . esc_html($end_date);
                                } elseif (! empty($start_date)) {
                                    echo esc_html($start_date);
                                } elseif (! empty($end_date)) {
                                    echo esc_html($end_date);
                                }
                            ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (! empty($start_time) || ! empty($end_time)): ?>
                    <div class="etn-venue-info-item">
                        <div class="etn-venue-info-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                <path d="M16.75 8.75C16.75 13.166 13.166 16.75 8.75 16.75C4.334 16.75 0.75 13.166 0.75 8.75C0.75 4.334 4.334 0.75 8.75 0.75C13.166 0.75 16.75 4.334 16.75 8.75Z" stroke="#5D5DFF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M11.7171 11.294L9.23712 9.81396C8.80512 9.55796 8.45312 8.94196 8.45312 8.43796V5.15796" stroke="#5D5DFF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="etn-venue-info-text">
                            <?php
                                if (! empty($start_time) && ! empty($end_time)) {
                                    echo esc_html($start_time) . ' - ' . esc_html($end_time);
                                } elseif (! empty($start_time)) {
                                    echo esc_html($start_time);
                                } elseif (! empty($end_time)) {
                                    echo esc_html($end_time);
                                }
                            ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (! empty($venue_latitude) && ! empty($venue_longitude)): ?>
                <a href="https://www.google.com/maps?q=<?php echo esc_attr($venue_latitude); ?>,<?php echo esc_attr($venue_longitude); ?>" target="_blank" class="etn-venue-get-direction">
                    <?php echo esc_html__('Get Direction', 'eventin'); ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M6 12L10 8L6 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
                <?php endif; ?>
            </div>

            <?php if (! empty($venue_latitude) && ! empty($venue_longitude)):
                    $google_api_key = function_exists('etn_get_option') ? etn_get_option('google_api_key') : '';
                    $map_url        = 'https://www.google.com/maps/embed/v1/place';
                    if ($google_api_key) {
                        $map_url .= '?key=' . esc_attr($google_api_key) . '&q=' . esc_attr($venue_latitude) . ',' . esc_attr($venue_longitude);
                    } else {
                        // Fallback to static map or basic embed
                        $map_url = 'https://www.google.com/maps?q=' . esc_attr($venue_latitude) . ',' . esc_attr($venue_longitude) . '&output=embed';
                    }
                ?>
												                    <div class="etn-venue-map-container">
												                        <iframe
												                            class="etn-venue-map"
												                            src="<?php echo esc_url($map_url); ?>"
												                            allowfullscreen
												                            loading="lazy"
												                            referrerpolicy="no-referrer-when-downgrade">
												                        </iframe>
												                    </div>
																																								            <?php else: ?>
            <div class="etn-venue-map-container">
                <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #999; font-size: 14px;">
                    <?php echo esc_html__('Map location not available', 'eventin'); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
