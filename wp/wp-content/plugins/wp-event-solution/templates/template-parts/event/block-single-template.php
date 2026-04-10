<?php
use Etn\Core\Event\Event_Model;
use Eventin\Template\TemplateModel;

if ( wp_is_block_theme() ) {
    block_header_area();
    wp_head();
} else {
    get_header();
}

    $default_template_name = [
        'event-one'     => 'event-template-one',
        'event-two'     => 'event-template-two',
        'event-three'   => 'event-template-three',
    ];

    $event_id = get_the_ID();

    // Check if password is required
    if ( post_password_required( $event_id ) ) {
        echo get_the_password_form( $event_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_the_password_form() is a trusted WP core function.
    } else {
        $event = new Event_Model( $event_id );

        $template_id = $event->event_layout;

    if ( ! $template_id ) {
        $template_id = etn_get_option( 'event_template', 'event-one' );
    }

        $template = new TemplateModel( $template_id );

        if ( $template && get_post_type( $template_id ) == 'etn-template' ) {
            $template->render_content( '', $event_id );
        } else {
            $template->render_content( $default_template_name[$template_id], $event_id );
        }
    }

    if ( wp_is_block_theme() ) {
        block_footer_area();
        wp_footer();
    } else {
        get_footer();
    }
?>
