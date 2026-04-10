<?php
$event_logo             = get_post_meta($event_id, 'etn_event_logo', true);
?>

<?php if ( $event_logo ){ ?>
    <div class="etn-event-logo <?php echo esc_attr($container_class); ?>">
        <div class="eventin-block-container">
            <img class="etn-event-logo-img" src="<?php echo esc_url( $event_logo ); ?>" alt="<?php the_title_attribute(); ?>">
        </div>
    </div>
<?php }else{
    echo esc_html('Event logo not set yet', 'eventin');
} ?>