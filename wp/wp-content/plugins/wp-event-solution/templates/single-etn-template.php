<?php
/**
 * Single Template for etn-template post type
 *
 * @package Eventin
 * @since 4.0.43
 */

use Eventin\Template\TemplateModel;

if ( wp_is_block_theme() ) {
    block_header_area();
    wp_head();
} else {
    get_header();
}

// Load ticket layout style
wp_enqueue_style( 'etn-ticket-markup' );
wp_enqueue_style( 'etn-blocks-style' );

// Include QR Code related scripts when pro plugin is activated
if ( class_exists( 'Wpeventin_Pro' ) ) {
    wp_enqueue_script( 'etn-qr-code' );
    wp_enqueue_script( 'etn-qr-code-scanner' );
    wp_enqueue_script( 'etn-qr-code-custom' );
}

$template_id = get_the_ID();

if ( ! $template_id ) {
    printf( '<p>%s</p>', esc_html__( 'No template found.', 'eventin' ) );
    return;
}

$template = new TemplateModel( $template_id );
$post     = get_post( $template_id );

if ( ! $post || 'etn-template' !== $post->post_type ) {
    printf( '<p>%s</p>', esc_html__( 'Invalid template.', 'eventin' ) );
    return;
}

// Check if this template is built with Elementor
$is_elementor_template = false;
if ( did_action( 'elementor/loaded' ) ) {
    $document = \Elementor\Plugin::$instance->documents->get( $template_id );
    $is_elementor_template = $document && $document->is_built_with_elementor();
}

// For Elementor templates, render using Elementor's content method
// For other templates, get demo content
if ( $is_elementor_template ) {
    // Set up the WordPress loop for Elementor
    while ( have_posts() ) {
        the_post();
        ?>
        <style>
            .ast-container .etn-template-single-wrapper {
                flex-basis: 100%;
            }
        </style>
        <div class="etn-template-single-wrapper">
            <div class="etn-template-content">
                <?php the_content(); ?>
            </div>
        </div>
        <?php
    }
} else {
    $template_html = $template->get_demo_content();
    ?>
    <style>
        .ast-container .etn-template-single-wrapper {
            flex-basis: 100%;
        }
    </style>
    <div class="etn-template-single-wrapper">
        <div class="etn-template-content">
            <?php echo $template_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted rendered block/Elementor HTML content. ?>
        </div>
    </div>
    <?php
}
?>

<?php
if ( wp_is_block_theme() ) {
    block_footer_area();
    wp_footer();
} else {
    get_footer();
}
