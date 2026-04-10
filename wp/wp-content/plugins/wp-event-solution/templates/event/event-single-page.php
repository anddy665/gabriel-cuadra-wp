<?php

$etn_show_event = true;
if( isset( $_POST['ticket_purchase_next_step'] ) && in_array( sanitize_text_field( wp_unslash( $_POST['ticket_purchase_next_step'] ) ), [ 'two', 'three' ], true ) ){ // phpcs:ignore WordPress.Security.NonceVerification.Missing -- display flag only; nonce is verified in the ticket purchase handler that processes the full form submission.
	$etn_show_event = false;
}
?>

<?php
//check if it's called from form submission or from single page
if ( $etn_show_event === true ) {
	?>

	<?php 
        if( wp_is_block_theme() ){
            block_header_area();
            wp_head();
        }else{
            get_header();
        }
	?>

	<?php
	if ( file_exists( get_stylesheet_directory() . \Wpeventin::theme_templates_dir() . 'single-event.php' ) ) {
		require_once get_stylesheet_directory() . \Wpeventin::theme_templates_dir() . 'single-event.php';
	} else if ( file_exists( get_template_directory() . \Wpeventin::theme_templates_dir() . 'single-event.php' ) ) {
		require_once  get_template_directory() . \Wpeventin::theme_templates_dir() . 'single-event.php';
	} else if( file_exists( \Wpeventin::templates_dir() . 'single-event.php' ) ){
		include_once \Wpeventin::templates_dir() . 'single-event.php';
	}
	?>
	
	<?php 
		if( wp_is_block_theme() ){
		    block_footer_area();
		    wp_footer();
		}else{
		    get_footer();
		}
	?>
	
	<?php
}
?>

