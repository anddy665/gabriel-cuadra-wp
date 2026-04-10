<?php 
  //echo do_shortcode("[etn_pro_ticket_form id='" . $event_id . "' show_title='no']"); 
?>
<div class="etn-single-event-ticket-wrap" data-preview="<?php echo esc_attr( is_preview() ); ?>" >
    <?php if ( isset( $show_title ) && $show_title === "yes" ) : ?>
    <h3 class="etn-event-form-widget-title" ?>>
        <?php echo esc_html( get_the_title( $event_id ) ); ?>
    </h3>
    <?php endif; ?>

    <?php
    \Etn\Utils\Helper::eventin_ticket_widget( $event_id, $styles, null, $style_variant );  
    ?>
</div>
