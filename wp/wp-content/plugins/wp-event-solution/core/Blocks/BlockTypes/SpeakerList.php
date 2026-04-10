<?php

namespace Eventin\Blocks\BlockTypes;

defined( 'ABSPATH' ) || exit;
use Wpeventin;

/**
 * Speaker List Block
 */
class SpeakerList extends AbstractBlock {
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
    protected $block_name = 'speaker-list';

    /**
     * Get block attributes
     *
     * @return array
     */
    protected function get_block_type_attributes() {
        return [
            'speaker_style' => [
                'type'    => 'string',
                'default' => 'speaker-2',
            ],
            'speaker_id' => [
                'type'    => 'string',
                'default' => '',
            ],
            'speakers_category' => [
                'type'    => 'array',
                'default' => []
            ],
            'etn_speaker_count' => [
                'type'    => 'integer',
                'default' => 20,
            ],
            'etn_speaker_col' => [
                'type'    => 'string',
                'default' => '4',
            ],
            'etn_speaker_order' => [
                'type'    => 'string',
                'default' => 'DESC',
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
        $style              = $attributes['speaker_style'];
        $sanitize_filename  = sanitize_file_name($style);
        $style              = !empty($sanitize_filename) ? $sanitize_filename : 'speaker-2';

        $speaker_id         = $attributes['speaker_id'];
        $etn_speaker_count  = $attributes['etn_speaker_count'];
        $etn_speaker_col    = $attributes['etn_speaker_col'];
        $etn_speaker_order  = $attributes['etn_speaker_order'];
        $speakers_category  = $attributes['speakers_category'];

        $post_attributes    = ['title', 'ID', 'name', 'post_date'];
        $orderby            = !empty($attributes['orderby']) ? $attributes['orderby'] : 'title';
        $orderby_meta       = in_array($orderby, $post_attributes) ? false : 'meta_value';
        ob_start();
        ?>
        <div class="guten-speaker-blocks">
            <?php
            $template_path = Wpeventin::plugin_dir() . "widgets/speakers/style/{$style}.php";
            if ( file_exists( $template_path ) ) {
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