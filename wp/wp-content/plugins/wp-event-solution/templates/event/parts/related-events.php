<?php

    use Etn\Utils\Helper;

    $desc_limit = 15;

?>

<div class="<?php echo esc_attr($container_class); ?> eventin-block-container">

    <div class="etn-event-related-post eventin-block-container">
        <h3 class="related-post-title"><?php esc_html_e('Related Events', 'eventin'); ?></h3>
        <?php if ($related_events): ?>
        <div class="etn-row">
            <?php foreach ($related_events as $event_item): ?>
            <div class="etn-col-md-6">
                <div class="etn-event-item">
                    <div class="etn-event-thumb">
                        <a href="https://product.themewinter.com/eventin/event/symposiums-chemotherapy-foundation-symposiums/"
                            aria-label="Machine Learning Night Fastaily Part 2 Influential">
                            <img width="300" height="225"
                                src="https://product.themewinter.com/eventin/wp-content/uploads/sites/2/2020/03/event3-300x225.jpg"
                                class="attachment-medium size-medium wp-post-image" alt="Event Feature Image"
                                decoding="async"
                                srcset="https://product.themewinter.com/eventin/wp-content/uploads/sites/2/2020/03/event3-300x225.jpg 300w, https://product.themewinter.com/eventin/wp-content/uploads/sites/2/2020/03/event3-768x576.jpg 768w, https://product.themewinter.com/eventin/wp-content/uploads/sites/2/2020/03/event3-600x450.jpg 600w, https://product.themewinter.com/eventin/wp-content/uploads/sites/2/2020/03/event3.jpg 780w"
                                sizes="(max-width: 300px) 100vw, 300px"> </a>
                        <div class="etn-event-category">
                            <span>event</span> <span>sports</span>
                        </div>
                    </div>
                    <div class="etn-event-content">
                        <div class="etn-event-location">
                            <i class="etn-icon etn-location"></i>
                            <?php echo esc_html($event_item->get_address()); ?>
                        </div>
                        <h3 class="etn-title etn-event-title">
                            <a
                                href="<?php echo esc_url(get_the_permalink($event_item->id)); ?>"><?php echo esc_html($event_item->get_title()); ?></a>
                        </h3>
                        <p>
                            <?php echo esc_html(Helper::trim_words($event_item->get_description(), $desc_limit)); ?>
                        </p>
                        <div class="etn-event-footer">
                            <div class="etn-event-date">
                                <i class="etn-icon etn-calendar"></i>
                                <?php echo esc_html($event_item->get_start_date('F j, Y')); ?>
                            </div>
                            <div class="etn-atend-btn">
                                <a href="<?php echo esc_url(get_the_permalink($event_item->id)); ?>"
                                    class="etn-btn etn-btn-border"
                                    title="<?php echo esc_attr($event_item->get_title()); ?>"><?php echo esc_html_e('attend', 'eventin'); ?>
                                    <i class="etn-icon etn-arrow-right"></i></a>
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