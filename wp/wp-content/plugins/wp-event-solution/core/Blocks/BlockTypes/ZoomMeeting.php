<?php

namespace Eventin\Blocks\BlockTypes;

defined( 'ABSPATH' ) || exit;
/**
 * Zoom Meeting Block
 */
class ZoomMeeting extends AbstractBlock {
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
    protected $block_name = 'zoom-meeting';

    /**
     * Get block attributes
     *
     * @return array
     */
    protected function get_block_type_attributes() {
        return [
            'zoom_id' => [
                'type' => 'string',
            ],
            'link_only' => [
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
        $zoom_id = !empty($attributes['zoom_id']) ? $attributes['zoom_id'] : '';
        $link_only = !empty($attributes['link_only']) ? $attributes['link_only'] : '';
        $meeting_id = get_post_meta($zoom_id, 'zoom_meeting_id', true);

        ob_start();
        ?>
        <div class="guten-zoom-blocks">
            <?php echo do_shortcode("[etn_zoom_api_link meeting_id ={$meeting_id} link_only={$link_only}]"); ?>
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