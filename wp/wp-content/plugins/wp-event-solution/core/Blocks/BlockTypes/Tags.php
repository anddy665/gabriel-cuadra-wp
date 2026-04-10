<?php

namespace Eventin\Blocks\BlockTypes;

defined( 'ABSPATH' ) || exit;
/**
 * Tags class.
 */
class Tags extends AbstractBlock {
    /**
     * Block name.
     *
     * @var string
     */
    protected $block_name = 'tags';

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
        return '<div class="etn-block-components tags">Tags Blocks</div>';
    }
}
