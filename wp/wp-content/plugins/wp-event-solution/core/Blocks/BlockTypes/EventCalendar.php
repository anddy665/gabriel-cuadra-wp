<?php

namespace Eventin\Blocks\BlockTypes;

defined( 'ABSPATH' ) || exit;
/**
 * Event Calendar Block
 */
class EventCalendar extends AbstractBlock {
    /**
     * Block Namespace - Override to use 'etn' to maintain compatibility
     *
     * @var string
     */
    protected $namespace = 'etn';

    /**
     * Block name.
     *
     * @var string
     */
    protected $block_name = 'event-calendar';

    /**
     * Get block attributes
     *
     * @return array
     */
    protected function get_block_type_attributes() {
        return [
            'etn_event_style' => [
                'type'    => 'string',
                'default' => 'style-1',
            ],
            'etn_event_cat' => [
                'type'    => 'array',
                'default' => []
            ],
            'etn_event_count' => [
                'type'    => 'integer',
                'default' => 20,
            ],
            'display_calendar_view' => [
                'type'    => 'string',
                'default' => 'full_width',
            ],
            'show_desc' => [
                'type'    => 'string',
                'default' => 'no',
            ],
        ];
    }

    /**
     * Include and render the block
     *
     * @param   array     $attributes  Block attributes.
     * @param   string    $content     Block content.
     * @param   WP_Block  $block       Block instance.
     * @return  string Rendered block output
     */
    protected function render( $attributes, $content, $block ) {
        // Set up variables like the original function
        $style          = sanitize_key( $attributes['etn_event_style'] );
        $event_cat      = array_map( 'absint', (array) $attributes['etn_event_cat'] );
        $event_count    = absint( $attributes['etn_event_count'] );
        $calendar_view  = sanitize_key( $attributes['display_calendar_view'] );
        $show_desc      = sanitize_key( $attributes['show_desc'] );
        $event_cats     = join(", ", $event_cat);

        ob_start();
        ?>
        <div class="guten-event-calendar-blocks">
            <?php
            echo do_shortcode("[events_calendar style ={$style} event_cat_ids='{$event_cats}'  calendar_show={$calendar_view} show_desc={$show_desc} limit = {$event_count}]");
            ?>
        </div>
        <?php

        return ob_get_clean();
    }

    /**
     * Override the register_block_type method to match original registration
     *
     * @return void
     */
    protected function register_block_type() {
        // Skip if block is already registered
        if ( \WP_Block_Type_Registry::get_instance()->is_registered( $this->get_block_type() ) ) {
            return;
        }

        $block_settings = [
            'style'           => 'eventin-block-style-css',
            'editor_style'    => 'eventin-calendar-block-editor-style',
            'editor_script'   => 'eventin-calender-block-js',
            'render_callback' => [$this, 'render_callback'],
            'attributes'      => $this->get_block_type_attributes(),
        ];

        register_block_type($this->get_block_type(), $block_settings);
    }

    /**
     * Override asset registration to prevent script issues
     *
     * @return void
     */
    protected function register_block_type_assets() {
        // Don't register assets here since we're using existing handles
        // This prevents the l10n.php path errors
    }

    /**
     * Get the editor style handle for this block type.
     *
     * @return string
     */
    protected function get_block_type_editor_style() {
        return 'eventin-calendar-block-editor-style';
    }

    /**
     * Get the frontend style handle for this block type.
     *
     * @return string
     */
    protected function get_block_type_style() {
        return 'eventin-block-style-css';
    }
}