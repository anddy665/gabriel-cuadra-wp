<?php

use Etn\Core\Event\Event_Model;
use Etn\Utils\Helper;
 
$tickets_variations = get_post_meta($single_event_id,'etn_ticket_variations', true); 
$style = isset($style_variant) ? $style_variant : 'style-1';

if(!empty($tickets_variations)){
?>
<?php do_action( 'etn_before_add_to_cart_widget_block', $single_event_id ); ?>

<div class="etn-purchase-ticket-root" data-post_id="<?php echo esc_attr($single_event_id); ?>" data-style="<?php echo esc_attr($style); ?>" ></div>

<?php 
}
do_action( 'etn_after_add_to_cart_widget_block', $single_event_id ); ?>