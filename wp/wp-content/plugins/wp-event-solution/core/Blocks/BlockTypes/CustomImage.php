<?php

namespace Eventin\Blocks\BlockTypes;

defined( 'ABSPATH' ) || exit;
/**
 * CustomImage Block
 */
class CustomImage extends AbstractBlock {
    /**
     * Block name.
     *
     * @var string
     */
    protected $block_name = 'custom-image';

    /**
     * Block namespace
     *
     * @var string
     */
    protected $namespace = 'eventin-pro';

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
        return $content;
    }
}
