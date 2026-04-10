<?php

namespace Eventin\Blocks\BlockTypes;

defined( 'ABSPATH' ) || exit;
use Wpeventin;

/**
 * QR Code block
 */
class QRCodeBlock extends AbstractBlock {
    /**
     * Block name within this namespace
     *
     * @var string
     */
    protected $block_name = 'ticket-qrcode';

    /**
     * Block Namespace (overriding parent to use pro namespace)
     *
     * @var string
     */
    protected $namespace = 'eventin-pro';

    /**
     * Get the frontend script handle for this block type.
     *
     * @param string $key Data to get, or default to everything.
     * @return array|string|null
     */
    protected function get_block_type_script( $key = null ) {
        // Return null if Pro plugin is not active
        if ( ! class_exists('Wpeventin_Pro') || ! defined('ETN_PRO_ASSETS') ) {
            return null;
        }

        $script = [
            'handle'       => 'etn-qr-code-custom-block',
            'path'         => ETN_PRO_ASSETS . 'js/qr-code-custom.js',
            'dependencies' => ['jquery', 'etn-qr-code-block'],
        ];
        return $key ? $script[ $key ] : $script;
    }

    /**
     * Get the editor style handle for this block type.
     *
     * @return string[]|null
     */
    protected function get_block_type_editor_style() {
        return ['etn-blocks-style', 'etn-blocks-style-custom'];
    }

    /**
     * Get the frontend style handle for this block type.
     *
     * @return string[]|null
     */
    protected function get_block_type_style() {
        return ['etn-blocks-style', 'etn-blocks-style-custom'];
    }

    /**
     * Register script and style assets for the block type before it is registered.
     *
     * This registers the scripts; it does not enqueue them.
     */
    protected function register_block_type_assets() {
        parent::register_block_type_assets();

        // Register QR code dependencies if pro version exists
        if ( class_exists('Wpeventin_Pro') && defined('ETN_PRO_ASSETS') ) {
            wp_register_script('etn-qr-code-block', ETN_PRO_ASSETS . 'js/qr-code.js', array('jquery'), Wpeventin::version(), false);
        }
    }

    /**
     * Render the QR code block
     *
     * @param array    $attributes Block attributes.
     * @param string   $content    Block content.
     * @param WP_Block $block      Block instance.
     * @return string Rendered block type output.
     */
    protected function render( $attributes, $content, $block ) {
        // Only render if pro version exists
        if ( ! class_exists('Wpeventin_Pro') ) {
            return '';
        }
        $container_class = ! empty( $attributes['containerClassName'] ) ? $attributes['containerClassName'] : '';
        $styles = ! empty( $attributes['styles'] ) ? $attributes['styles'] : [];
        
        // Get content settings for styling
        $content_settings = ! empty( $attributes['contentSettings'] ) ? $attributes['contentSettings'] : [];
        $padding = ! empty( $content_settings['padding'] ) ? $content_settings['padding'] : '0px 0px 0px 0px';
        $margin = ! empty( $content_settings['margin'] ) ? $content_settings['margin'] : '10px 0px 0px 0px';
        $alignment = ! empty( $content_settings['alignment'] ) ? $content_settings['alignment'] : 'center';

        $attendee_id       = ! empty( $_GET['attendee_id'] ) ? intval( $_GET['attendee_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- QR code block render; attendee_id is cast to integer via intval().
        $unique_id         = get_post_meta( $attendee_id, 'etn_unique_ticket_id', true );
        $ticket_verify_url = admin_url( '/edit.php?post_type=etn-attendee&etn_action=ticket_scanner' );
        $ticket_verify_url .= "&attendee_id=$attendee_id&ticket_id=$unique_id";
        
        // Build inline styles for content settings
        $content_styles = sprintf(
            'padding: %s; margin: %s; text-align: %s;',
            esc_attr( $padding ),
            esc_attr( $margin ),
            esc_attr( $alignment )
        );
        
        ob_start();
        ?>
        <?php echo $this->render_frontend_css( $styles, esc_attr( $container_class ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS rendered by render_frontend_css(); script and style tags stripped by generate_frontend_css(). ?>
        <div class="eventin-ticket-qrcode" style="<?php echo esc_attr( $content_styles ); ?>">
            <p class="etn-ticket-id" id="ticketUnqId" data-ticketverifyurl="<?php echo esc_url( $ticket_verify_url ) ?>"></p>
            <img class="etn-qrImage" src="" alt="" id="qrImage" />
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Register/enqueue scripts used for this block on the frontend, during render.
     *
     * @param array $attributes Any attributes that currently are available from the block.
     */
    protected function enqueue_scripts( array $attributes = [] ) {
        parent::enqueue_scripts( $attributes );
        
        // Enqueue QR code specific scripts
        if ( class_exists('Wpeventin_Pro') ) {
            wp_enqueue_script( 'etn-qr-code-block' );
        }
    }
}