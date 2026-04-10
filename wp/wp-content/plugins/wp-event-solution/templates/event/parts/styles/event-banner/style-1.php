<div class="eventin-block-container
<?php echo esc_attr($container_class); ?>">
    <div class="event-banner">
        <?php if ($event_banner): ?>
        <img class="event-banner-image" src="<?php echo esc_url($event_banner) ?>" alt="event-banner">
        <?php else: ?>
        <p><?php esc_html_e('No event banner image found.', 'eventin'); ?></p>
        <?php endif; ?>
    </div>
</div>