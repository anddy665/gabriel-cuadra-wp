<div class="<?php echo esc_attr($container_class); ?>  etn-related-events-style-1">
    <div class="etn-event-related-post eventin-block-container">
        <?php if ($related_events): ?>
        <div class="etn-related-events-grid etn-row">
            <?php foreach ($related_events as $event_item): ?>
            <div class="etn-related-event-card-style-2">
                <div class="etn-event-item">
                    <div class="etn-event-thumb">
                        <a href="<?php echo esc_url(get_the_permalink($event_item->id)); ?>"
                            aria-label="<?php echo esc_attr($event_item->get_title()); ?>">
                            <?php
                                $thumbnail_id = get_post_thumbnail_id($event_item->id);
                                if ($thumbnail_id) {
                                    echo wp_get_attachment_image($thumbnail_id, 'medium', false, [
                                        'class'    => 'attachment-medium size-medium wp-post-image',
                                        'alt'      => esc_attr($event_item->get_title()),
                                        'decoding' => 'async',
                                    ]);
                                } else {
                                    $default_image = Wpeventin::assets_url() . 'images/event-placeholder.jpg';
                                    echo '<img src="' . esc_url($default_image) . '" alt="' . esc_attr($event_item->get_title()) . '" class="attachment-medium size-medium wp-post-image" decoding="async" />';
                                }
                            ?>
                        </a>
                    </div>
                    <div class="etn-event-content">
                        <h3 class="etn-title etn-event-title">
                            <a href="<?php echo esc_url(get_the_permalink($event_item->id)); ?>">
                                <?php echo esc_html($event_item->get_title()); ?>
                            </a>
                        </h3>
                        <div class="etn-event-date">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="14" viewBox="0 0 13 14" fill="none">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M3.93316 0C4.33203 0 4.65538 0.316922 4.65538 0.707865V2.59551C4.65538 2.98645 4.33203 3.30337 3.93316 3.30337C3.53429 3.30337 3.21094 2.98645 3.21094 2.59551V0.707865C3.21094 0.316922 3.53429 0 3.93316 0Z" fill="#6D7D8C"/>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M9.06597 0C9.46484 0 9.78819 0.316922 9.78819 0.707865V2.59551C9.78819 2.98645 9.46484 3.30337 9.06597 3.30337C8.6671 3.30337 8.34375 2.98645 8.34375 2.59551V0.707865C8.34375 0.316922 8.6671 0 9.06597 0Z" fill="#6D7D8C"/>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M0.320312 5.1688C0.320312 4.77786 0.643662 4.46094 1.04253 4.46094H11.9561C12.355 4.46094 12.6783 4.77786 12.6783 5.1688C12.6783 5.55975 12.355 5.87667 11.9561 5.87667H1.04253C0.643662 5.87667 0.320312 5.55975 0.320312 5.1688Z" fill="#6D7D8C"/>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M2.01381 2.99855C1.65761 3.37452 1.44444 3.96247 1.44444 4.79778V10.1461C1.44444 10.9814 1.65761 11.5694 2.01381 11.9453C2.36101 12.3118 2.9463 12.5843 3.9321 12.5843H9.0679C10.0537 12.5843 10.639 12.3118 10.9862 11.9453C11.3424 11.5694 11.5556 10.9814 11.5556 10.1461V4.79778C11.5556 3.96247 11.3424 3.37452 10.9862 2.99855C10.639 2.63208 10.0537 2.35958 9.0679 2.35958H3.9321C2.9463 2.35958 2.36101 2.63208 2.01381 2.99855ZM0.955328 2.03521C1.65134 1.30056 2.67099 0.943848 3.9321 0.943848H9.0679C10.329 0.943848 11.3487 1.30056 12.0447 2.03521C12.7317 2.76037 13 3.74545 13 4.79778V10.1461C13 11.1984 12.7317 12.1835 12.0447 12.9087C11.3487 13.6433 10.329 14 9.0679 14H3.9321C2.67099 14 1.65134 13.6433 0.955328 12.9087C0.268314 12.1835 0 11.1984 0 10.1461V4.79778C0 3.74545 0.268314 2.76037 0.955328 2.03521Z" fill="#6D7D8C"/>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M8.14844 8.06919C8.14844 7.67825 8.47179 7.36133 8.87066 7.36133H8.87643C9.2753 7.36133 9.59865 7.67825 9.59865 8.06919C9.59865 8.46014 9.2753 8.77706 8.87643 8.77706H8.87066C8.47179 8.77706 8.14844 8.46014 8.14844 8.06919Z" fill="#6D7D8C"/>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M8.14844 9.95689C8.14844 9.56595 8.47179 9.24902 8.87066 9.24902H8.87643C9.2753 9.24902 9.59865 9.56595 9.59865 9.95689C9.59865 10.3478 9.2753 10.6648 8.87643 10.6648H8.87066C8.47179 10.6648 8.14844 10.3478 8.14844 9.95689Z" fill="#6D7D8C"/>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M5.77344 8.06919C5.77344 7.67825 6.09679 7.36133 6.49566 7.36133H6.50143C6.9003 7.36133 7.22365 7.67825 7.22365 8.06919C7.22365 8.46014 6.9003 8.77706 6.50143 8.77706H6.49566C6.09679 8.77706 5.77344 8.46014 5.77344 8.06919Z" fill="#6D7D8C"/>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M5.77344 9.95689C5.77344 9.56595 6.09679 9.24902 6.49566 9.24902H6.50143C6.9003 9.24902 7.22365 9.56595 7.22365 9.95689C7.22365 10.3478 6.9003 10.6648 6.50143 10.6648H6.49566C6.09679 10.6648 5.77344 10.3478 5.77344 9.95689Z" fill="#6D7D8C"/>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M3.39844 8.06919C3.39844 7.67825 3.72179 7.36133 4.12066 7.36133H4.12643C4.5253 7.36133 4.84865 7.67825 4.84865 8.06919C4.84865 8.46014 4.5253 8.77706 4.12643 8.77706H4.12066C3.72179 8.77706 3.39844 8.46014 3.39844 8.06919Z" fill="#6D7D8C"/>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M3.39844 9.95689C3.39844 9.56595 3.72179 9.24902 4.12066 9.24902H4.12643C4.5253 9.24902 4.84865 9.56595 4.84865 9.95689C4.84865 10.3478 4.5253 10.6648 4.12643 10.6648H4.12066C3.72179 10.6648 3.39844 10.3478 3.39844 9.95689Z" fill="#6D7D8C"/>
                            </svg>
                            <?php
                                $start_date = $event_item->get_start_date('D, d');
                                $end_date   = $event_item->get_end_date('D, d M Y');
                                if ($end_date && $start_date !== $end_date) {
                                    echo esc_html($start_date . ' - ' . $end_date);
                                } else {
                                    echo esc_html($event_item->get_start_date('D, d M Y'));
                                }
                            ?>
                        </div>
                        <div class="etn-event-footer">
                            <div class="etn-event-location">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="14" viewBox="0 0 13 14" fill="none">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.49797 4.68216C5.76343 4.68216 5.16797 5.2443 5.16797 5.93772C5.16797 6.63115 5.76343 7.19329 6.49797 7.19329C7.23251 7.19329 7.82797 6.63115 7.82797 5.93772C7.82797 5.2443 7.23251 4.68216 6.49797 4.68216ZM3.66797 5.93772C3.66797 4.46223 4.935 3.26611 6.49797 3.26611C8.06094 3.26611 9.32797 4.46223 9.32797 5.93772C9.32797 7.41321 8.06093 8.60934 6.49797 8.60934C4.935 8.60934 3.66797 7.41321 3.66797 5.93772Z" fill="#6D7D8C"/>
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.50148 1.41605C4.31762 1.41466 2.20905 2.60587 1.64421 4.94929C0.9706 7.75938 2.81076 10.2263 4.61275 11.866C5.66991 12.8241 7.33009 12.8222 8.37864 11.8668L8.38001 11.8656C10.1886 10.226 12.0285 7.76507 11.3547 4.95516L11.3547 4.95512C10.7928 2.61155 8.68551 1.41744 6.50148 1.41605ZM12.8176 4.64251C12.0728 1.53589 9.25012 0.00175473 6.50249 1.50459e-06C3.75478 -0.00175183 0.930097 1.52889 0.181502 4.63549L0.181328 4.63621C-0.678041 8.21992 1.69461 11.1776 3.57236 12.8861L3.57331 12.8869C5.20905 14.37 7.78758 14.3721 9.41897 12.887C11.3037 11.1784 13.6772 8.22684 12.8176 4.64251Z" fill="#6D7D8C"/>
                                </svg>
                                <?php echo esc_html($event_item->get_address()); ?>
                            </div>
                            <div class="etn-atend-btn">
                                <a href="<?php echo esc_url(get_the_permalink($event_item->id)); ?>"
                                    class="etn-btn etn-btn-primary"
                                    title="<?php echo esc_attr($event_item->get_title()); ?>">
                                    <?php esc_html_e('Attend', 'eventin'); ?>
                                </a>
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
