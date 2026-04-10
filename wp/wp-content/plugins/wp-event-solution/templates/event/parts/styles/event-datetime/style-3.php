<div class="<?php echo esc_attr($container_class); ?>">
    <div class="eventin-block-container">
        <div class="etn-event-meta-info etn-datetime-style-3">
            <div class="etn-datetime-style-3-wrapper">
                <!-- Time Card -->
                <div class="etn-datetime-card">
                    <div class="etn-datetime-content">
                        <p class="etn-date-range"><?php echo esc_html($start_date); ?> -
                        <?php echo esc_html($end_date); ?></p>
                        <p class="etn-time-range">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                <path d="M16.75 8.75C16.75 13.166 13.166 16.75 8.75 16.75C4.334 16.75 0.75 13.166 0.75 8.75C0.75 4.334 4.334 0.75 8.75 0.75C13.166 0.75 16.75 4.334 16.75 8.75Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M11.7171 11.2923L9.23712 9.81225C8.80512 9.55625 8.45312 8.94025 8.45312 8.43625V5.15625" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <?php echo esc_html($start_time); ?> -
                            <?php echo esc_html($end_time); ?> <?php if (! empty($timezone)): ?>(<?php echo esc_html($timezone); ?>)<?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
