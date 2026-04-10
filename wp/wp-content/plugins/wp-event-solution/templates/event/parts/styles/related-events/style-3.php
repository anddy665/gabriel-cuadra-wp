<div class="<?php echo esc_attr($container_class); ?>  etn-related-events-style-1 etn-related-events-style-3">
    <div class="etn-event-related-post eventin-block-container">
        <?php if ($related_events): ?>
        <div class="etn-related-events-grid etn-row">
            <?php foreach ($related_events as $event_item): ?>
            <div class="etn-related-event-card-style-2">
                <div class="etn-event-item">
                   <div class="etn-event-date">
                        <?php
                            // Show month (uppercase) on top, then "day, year" below.
                            $date_month = '';
                            $date_day   = '';
                            $date_year  = '';

                            // Prefer start date, fall back to end date or raw property
                            if (is_object($event_item) && method_exists($event_item, 'get_start_date')) {
                                $date_month = $event_item->get_start_date('F');
                                $date_day   = $event_item->get_start_date('j');
                                $date_year  = $event_item->get_start_date('Y');
                            } elseif (is_object($event_item) && method_exists($event_item, 'get_end_date')) {
                                $date_month = $event_item->get_end_date('F');
                                $date_day   = $event_item->get_end_date('j');
                                $date_year  = $event_item->get_end_date('Y');
                            } elseif (isset($event_item->start_date) || isset($event_item->end_date)) {
                                $raw_date = isset($event_item->start_date) ? $event_item->start_date : $event_item->end_date;
                                $ts       = strtotime($raw_date);
                                if ($ts !== false) {
                                    $date_month = date_i18n('F', $ts);
                                    $date_day   = date_i18n('j', $ts);
                                    $date_year  = date_i18n('Y', $ts);
                                }
                            }

                            if (! empty($date_month) || ! empty($date_day) || ! empty($date_year)):
                        ?>
                            <p class="etn-date-month"><?php echo esc_html(mb_strtoupper($date_month)); ?></p>
                            <p class="etn-date-dayyear"><?php echo esc_html(trim($date_day ? $date_day . ', ' . $date_year : $date_year)); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="etn-event-content">
                        <h3 class="etn-title etn-event-title">
                            <a href="<?php echo esc_url(get_the_permalink($event_item->id)); ?>">
                                <?php echo esc_html($event_item->get_title()); ?>
                            </a>
                        </h3>
                        <div class="etn-event-footer">
                            <div class="etn-event-location">
                                <?php echo esc_html($event_item->get_address()); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p><?php esc_html_e('No events found', 'eventin'); ?></p>
        <?php endif; ?>
    </div>
</div>
