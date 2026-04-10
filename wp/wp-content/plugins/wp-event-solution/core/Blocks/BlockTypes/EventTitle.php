<?php

namespace Eventin\Blocks\BlockTypes;

defined( 'ABSPATH' ) || exit;
use Etn\Core\Event\Event_Model;
use Eventin\Blocks\BlockTypes\AbstractBlock;
use Eventin\Template\TemplateModel;
use Wpeventin;

/**
 * Event Title Gutenberg block
 */
class EventTitle extends AbstractBlock {
    /**
     * Block name.
     *
     * @var string
     */
    protected $block_name = 'event-title';

    /**
     * Include and render the block
     *
     * @param   array  $attributes  Block attributes. Default empty array
     * @param   string  $content     Block content. Default empty string
     * @param   WP_Block  $block       Block instance
     *
     * @return  string Rendered block type output
     */
    protected function render( $attributes, $content, $block ) {
        $container_class = ! empty( $attributes['containerClassName'] ) ? $attributes['containerClassName'] : '';
        $tag = ! empty( $attributes['htmlTag'] ) ? $attributes['htmlTag'] : 'h2';
        $styles = ! empty( $attributes['styles'] ) ? $attributes['styles'] : [];

        if ( $this->is_editor() ) {
            $event_id = ! empty( $attributes['eventId'] ) ? intval( $attributes['eventId'] ) : 0;

            if ( $event_id == 0 ) {
                $template = new \Eventin\Template\TemplateModel( get_the_ID() );
                $event_id = $template->get_preview_event_id();
            }
        } else if ( 'etn-template' == get_post_type( get_the_ID() ) ) {
            $template = new \Eventin\Template\TemplateModel( get_the_ID() );
            $event_id = $template->get_preview_event_id();
        } else {
            $event_id = get_the_ID();
        }

        $event = new Event_Model( $event_id );

        ob_start();
        ?>
        <?php echo $this->render_frontend_css( $styles, esc_attr( $container_class ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS rendered by render_frontend_css(); script and style tags stripped by generate_frontend_css(). ?>

        <div class="<?php echo esc_attr( $container_class ); ?>">
            <div class="eventin-block-container">
                <<?php echo esc_attr($tag); ?> class="etn-event-entry-title"><?php echo esc_html( $event->get_title() ); ?>
                </<?php echo esc_attr($tag); ?>>
            </div>
        </div>
        <?php

        return ob_get_clean();
    }
}