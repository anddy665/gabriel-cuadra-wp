<div class="<?php echo esc_attr($container_class); ?>">
    <div class="eventin-block-container">
        <div class="etn-event-meta-info etn-widget">
            <ul class="etn-ul">
                <li class="etn-li">
                    <?php
                    // translators: %1$s is the event start date, %2$s is the event end date.
                    printf( wp_kses( __( '<span class="etn-span">Date:</span> %1$s - %2$s', 'eventin' ), [ 'span' => [ 'class' => [] ] ] ), esc_html( $start_date ), esc_html( $end_date ) );
                    ?>
                </li>
                <li class="etn-li">
                    <?php
                    // translators: %1$s is the event start time, %2$s is the event end time, %3$s is the timezone.
                    printf( wp_kses( __( '<span class="etn-span">Time:</span> %1$s - %2$s (%3$s)', 'eventin' ), [ 'span' => [ 'class' => [] ] ] ), esc_html( $start_time ), esc_html( $end_time ), esc_html( $timezone ) );
                    ?>
                </li>
            </ul>
        </div>
    </div>
</div>
