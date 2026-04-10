<?php

namespace Eventin\Blocks\BlockTypes;

defined( 'ABSPATH' ) || exit;
/**
 * RelatedEvents Class.
 */
class RelatedEvents extends AbstractBlock
{
    /**
     * Block name.
     *
     * @var string
     */
    protected $block_name = 'related-events';

    /**
     * Include and render the block
     *
     * @param   array  $attributes  Block attributes. Default empty array
     * @param   string  $content     Block content. Default empty string
     * @param   WP_Block  $block       Block instance
     *
     * @return  string Rendered block type output
     */
    protected function render($attributes, $content, $block)
    {
        $data = [
            'attributes' => $attributes,
            'content'    => $content,
            'block'      => $block,
        ];

        return $this->include_template('event/related-events', $data);
    }
}
