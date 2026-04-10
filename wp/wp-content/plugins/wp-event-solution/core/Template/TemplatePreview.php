<?php

defined( 'ABSPATH' ) || exit;

    // Add meta tag for responsive design in the head

use Etn\Core\Event\Event_Model;
use Eventin\Template\TemplateModel;

    function etn_viewport_meta() {
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0"/>';
    }
    add_action('wp_head', 'etn_viewport_meta', '1');

    show_admin_bar( false );
    $attendee_name    = 'John Doe';
    $ticket_file_name = sanitize_title_with_dashes($attendee_name);
    $payment_status   =  'Success';

    $all_payment_status = [
        'success' => esc_html__('Success', 'eventin'),
        'failed'  => esc_html__('Failed', 'eventin')
    ];

    // Load ticket layout style
    wp_enqueue_style( 'etn-ticket-markup' );
    wp_enqueue_script( 'etn-pdf-gen' );
    wp_enqueue_script( 'etn-html-2-canvas' );
    wp_enqueue_script( 'etn-dom-purify-pdf' );
    wp_enqueue_script( 'html-to-image' );

    // Include QR Code related scripts when pro plugin is activated
    if(class_exists('Wpeventin_Pro')) {
        wp_enqueue_script('etn-qr-code');
        wp_enqueue_script('etn-qr-code-scanner');
        wp_enqueue_script('etn-qr-code-custom');
    }

    $template_id = ! empty( $_GET['template_id'] ) ? intval( $_GET['template_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- admin template preview; template_id is cast to integer; capability check is performed by the calling hook.

    if ( ! $template_id ) {
        wp_head();
        printf( '<p>%s</p>', esc_html__( 'No template found. Please select one first.', 'eventin' ) );
        wp_footer();
        return;
    }

    $template = new TemplateModel( $template_id );
    $post     = get_post( $template_id );

    // Enqueue Elementor styles if template is built with Elementor.
    if ( did_action( 'elementor/loaded' ) && $post && $post->post_type === 'etn-template' ) {
        $document = \Elementor\Plugin::$instance->documents->get( $template_id );
        if ( $document && $document->is_built_with_elementor() ) {
            \Elementor\Plugin::$instance->frontend->enqueue_styles();
            $css_file = \Elementor\Core\Files\CSS\Post::create( $template_id );
            $css_file->enqueue();
        }
    }

    wp_head();

    if ( $post && $post->post_type == 'etn-template' ) {
        $template_html = $template->get_demo_content();
    }
?>
<meta name="viewport" content="width=device-width, initial-scale=1">
<div class="etn-ticket-download-wrapper">
    <div class="etn-ticket-wrap" id="etn_attendee_details_to_print" >
      <div class="etn-ticket-wrapper">
            <div class="etn-ticket-main-wrapper">
                <div class="etn-ticket">
                    <?php echo wp_kses_post( $template_html ); ?>
                </div>
                <!-- <div class="etn-ticket-action"></div> -->
            </div>
      </div>
    </div>
</div>

<?php if ( 'event' !== $template->type ): ?>
<div class="etn-download-ticket">
    <button class="etn-btn button etn-print-ticket-btn" id="etn_ticket_print_btn" data-ticketname="<?php echo esc_html( $ticket_file_name )?>" ><?php echo esc_html__( "Print", "eventin" ); ?></button>

    <button class="etn-btn button etn-download-ticket-btn" id="etn_ticket_download_btn" data-ticketname="<?php echo esc_html( $ticket_file_name )?>" ><?php echo esc_html__( "Download", "eventin" ); ?></button>
</div>
<?php endif; ?>

<?php wp_footer(); ?>
