<?php

namespace Eventin\Blocks\BlockTypes;

defined( 'ABSPATH' ) || exit;
use Etn\Utils\Helper;
use Wpeventin;

/**
 * Event List Block
 */
class EventList extends AbstractBlock {

    protected $namespace = 'etn';

    /**
     * Block name.
     *
     * @var string
     */
    protected $block_name = 'event-list';

    /**
     * Get block attributes
     *
     * @return array
     */
    protected function get_block_type_attributes() {
        return [
            'etn_event_style' => [
                'type'    => 'string',
                'default' => 'event-1',
            ],
            'etn_event_cat' => [
                'type'    => 'array',
                'default' => []
            ],
            'etn_event_tag' => [
                'type'    => 'array',
                'default' => []
            ],
            'etn_event_count' => [
                'type'    => 'integer',
                'default' => 20,
            ],
            'etn_desc_limit' => [
                'type'    => 'integer',
                'default' => 20,
            ],
            'etn_desc_status' => [
                'type'    => 'string',
                'default' => 'yes',
            ],
            'etn_event_location_status' => [
                'type'    => 'string',
                'default' => 'yes',
            ],
            'etn_event_col' => [
                'type'    => 'string',
                'default' => '4',
            ],
            'filter_with_status' => [
                'type'    => 'string',
                'default' => '',
            ],
            'order' => [
                'type'    => 'string',
                'default' => 'DESC',
            ],
            'orderby' => [
                'type'    => 'string',
                'default' => 'ID',
            ],
            'show_end_date' => [
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
        // Sanitize and set up variables like the original function
        $style = !empty($attributes['etn_event_style']) ? 
                sanitize_file_name($attributes['etn_event_style']) : 'event-1';

        $event_cat = $attributes['etn_event_cat'] ?? [];
        $event_tag = $attributes['etn_event_tag'] ?? [];
        $event_count = $attributes['etn_event_count'] ?? 20;
        $etn_event_col = $attributes['etn_event_col'] ?? '4';
        $etn_desc_limit = $attributes['etn_desc_limit'] ?? 20;
        $etn_desc_show = $attributes['etn_desc_status'] ?? 'yes';
        $show_event_location = $attributes['etn_event_location_status'] ?? 'yes';
        $filter_with_status = $attributes['filter_with_status'] ?? '';
        $order = $attributes['order'] ?? 'DESC';
        $orderby = $attributes['orderby'] ?? 'ID';
        $show_end_date = $attributes['show_end_date'] ?? 'no';
        $post_parent = Helper::show_parent_child('yes', 'yes');

        // Handle meta ordering like the original
        if ($orderby == "etn_start_date" || $orderby == "etn_end_date") {
            $orderby_meta = "meta_value";
        } else {
            $orderby_meta = null;
        }

        ob_start();
        ?>
        <div class="guten-event-blocks">
            <?php
            $template_path = Wpeventin::plugin_dir() . "widgets/events/style/{$style}.php";
            if (file_exists($template_path)) {
                include $template_path;
            }
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
            'editor_style'    => 'eventin-block-editor-style-css', 
            'editor_script'   => 'eventin-block-js',
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