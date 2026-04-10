<?php

namespace Eventin\Blocks\BlockTypes;

defined( 'ABSPATH' ) || exit;
use Wpeventin;

/**
 * Schedule Tab Block
 */
class ScheduleTab extends AbstractBlock {

    /**
     * Block Namespace - Override to use 'etn' to maintain compatibility
     *
     * @var string
     */
    protected $namespace = 'etn';

    /**
     * Block name (without namespace).
     *
     * @var string
     */
    protected $block_name = 'schedule-tab';

    /**
     * Get block attributes (matches the procedural registration).
     *
     * @return array
     */
    protected function get_block_type_attributes() {
        return [
            'schedule_style' => [
                'type'    => 'string',
                'default' => 'schedule-1',
            ],
            'schedule_id' => [
                'type'    => 'array',
                'default' => [],
            ],
            'etn_schedule_order' => [
                'type'    => 'string',
                'default' => 'DESC',
            ],
        ];
    }

    /**
     * Render the block (moved logic from etn_schedule_tab_callback()).
     *
     * @param array    $attributes Block attributes.
     * @param string   $content    Block content.
     * @param \WP_Block $block     Block instance.
     * @return string Rendered block output.
     */
    protected function render( $attributes, $content, $block ) {
        // Preserve original variable names to keep included template compatibility
        $style              = isset( $attributes['schedule_style'] ) ? $attributes['schedule_style'] : '';
        $sanitize_filename  = sanitize_file_name( $style );
        $style              = ! empty( $sanitize_filename ) ? $sanitize_filename : 'schedule-1';

        $etn_schedule_order = isset( $attributes['etn_schedule_order'] ) ? $attributes['etn_schedule_order'] : 'ASC';
        $etn_schedule_ids   = isset( $attributes['schedule_id'] ) ? (array) $attributes['schedule_id'] : [];
        $order              = $etn_schedule_order ? $etn_schedule_order : 'ASC';

        ob_start();
        ?>
        <div class="guten-schedule-blocks">
            <?php
            $template_path = Wpeventin::plugin_dir() . "widgets/schedule/style/{$style}.php";
            if ( file_exists( $template_path ) ) {
                // Keep variables ($style, $order, $etn_schedule_ids) in scope for the template.
                include $template_path;
            }
            ?>
        </div>
        <?php

        return ob_get_clean();
    }

    /**
     * Override the register_block_type method to match ZoomMeeting behavior
     * and the original procedural registration (explicit attributes + handles).
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
            'editor_style'    => 'eventin-block-editor-style-css',
            'editor_script'   => 'eventin-block-js',
            'render_callback' => [ $this, 'render_callback' ], // AbstractBlock provides render_callback()
            'attributes'      => $this->get_block_type_attributes(),
        ];

        register_block_type( $this->get_block_type(), $block_settings );
    }

    /**
     * Prevent the parent from registering assets (we're using global handles),
     * same as ZoomMeeting to avoid l10n path issues.
     *
     * @return void
     */
    protected function register_block_type_assets() {
        // Intentionally empty.
    }

    /**
     * Get the editor style handle for this block type.
     *
     * @return string
     */
    protected function get_block_type_editor_style() {
        return 'eventin-block-editor-style-css';
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
