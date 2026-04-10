<div class="<?php echo esc_attr($container_class); ?>">
    <div class="eventin-block-container">
        <div class="etn-event-meta-info etn-widget etn-datetime-style-2">
            <div class="etn-datetime-style-2-wrapper">
                <!-- Time Card -->
                <div class="etn-datetime-card etn-time-card">
                    <div class="etn-datetime-icon-wrapper">
                        <div class="etn-datetime-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                <path d="M16.75 8.75C16.75 13.166 13.166 16.75 8.75 16.75C4.334 16.75 0.75 13.166 0.75 8.75C0.75 4.334 4.334 0.75 8.75 0.75C13.166 0.75 16.75 4.334 16.75 8.75Z" stroke="#5D5DFF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M11.7171 11.294L9.23712 9.81396C8.80512 9.55796 8.45312 8.94196 8.45312 8.43796V5.15796" stroke="#5D5DFF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </div>
                    <div class="etn-datetime-content">
                        <span class="etn-time-range"><?php echo esc_html($start_time); ?> -<?php echo esc_html($end_time); ?> <?php if (! empty($timezone)): ?>(<?php echo esc_html($timezone); ?>)<?php endif; ?></span>
                    </div>
                </div>
                <!-- Date Card -->
                <div class="etn-datetime-card etn-date-card">
                    <div class="etn-datetime-icon-wrapper">
                        <div class="etn-datetime-icon">
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
                    </div>
                    <div class="etn-datetime-content">
                        <span class="etn-date-range"><?php echo esc_html($start_date); ?> -<?php echo esc_html($end_date); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
