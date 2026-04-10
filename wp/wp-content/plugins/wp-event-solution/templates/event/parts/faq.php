<?php
$etn_faqs = get_post_meta( $event_id, 'etn_event_faq', true );
if(!empty($etn_faqs)):
    ?>
<div class="etn-accordion-wrap etn-event-single-content-wrap <?php echo esc_attr( $container_class ); ?>">
    <?php
            if ( is_array( $etn_faqs ) && !empty( $etn_faqs ) ) {
                foreach ( $etn_faqs as $key => $faq ) {
                    $acc_class = ( $key == 0 ) ? 'active' : '';  ?>
    <div class="etn-content-item eventin-block-container">
        <h4 class="etn-accordion-heading <?php echo esc_attr( $acc_class ); ?>">
            <?php echo esc_html( $faq["etn_faq_title"] ); ?>
            <?php 
                                if($acc_class){
                                    echo '<i class="etn-icon etn-minus"></i>';
                                } else {
                                    echo '<i class="etn-icon etn-plus"></i>';
                                }
                            ?>
        </h4>
        <p class="etn-acccordion-contents <?php echo esc_attr( $acc_class ); ?>">
            <?php
                                if ( has_blocks( $faq["etn_faq_content"] ) ) {
                                    echo wp_kses_post( do_blocks( $faq["etn_faq_content"] ) );
                                } else {
                                    echo esc_html( $faq["etn_faq_content"] );
                                }
                            ?>
        </p>
    </div>
    <?php }
            } else { ?>
    <div class="etn-event-faq-body">
        <?php echo esc_html__( "No FAQ found!", "eventin" ); ?>
    </div>
    <?php } ?>
</div>
<?php endif; 