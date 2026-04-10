<div class="<?php echo esc_attr($container_class); ?>">
    <div class="eventin-block-container">
        <div class="etn-event-meta-info etn-widget etn-datetime-card">
            <div class="etn-datetime-card-wrapper">
                <div class="etn-datetime-card-content">
                    <div class="etn-date-range">
                        <span><?php echo esc_html($start_date); ?></span>
                        <span style="text-align: center;">-</span>
                        <span><?php echo esc_html($end_date); ?></span>
                    </div>
                </div>
                <div class="etn-time-info">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <path d="M16.75 8.75C16.75 13.166 13.166 16.75 8.75 16.75C4.334 16.75 0.75 13.166 0.75 8.75C0.75 4.334 4.334 0.75 8.75 0.75C13.166 0.75 16.75 4.334 16.75 8.75Z" stroke="#5D5DFF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M11.7171 11.294L9.23712 9.81396C8.80512 9.55796 8.45312 8.94196 8.45312 8.43796V5.15796" stroke="#5D5DFF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="etn-time-range"><?php echo esc_html($start_time); ?> -<?php echo esc_html($end_time); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
