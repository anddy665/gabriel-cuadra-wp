<?php

namespace Eventin\Blocks;

defined( 'ABSPATH' ) || exit;

use Eventin\Blocks\BlockTypes\EventImage;
use Eventin\Blocks\BlockTypes\EventTitle;
use Eventin\Interfaces\HookableInterface;
use Wpeventin;
use Etn\Core\Event\Event_Model;
use Eventin\Template\TemplateModel;

/**
 * Block Controller Types
 */
class BlockTypesController implements HookableInterface {
    /**
     * Store Block Types
     *
     * @var array
     */
    private $blocks = [
        EventImage::class,
        EventTitle::class,
    ];

    /**
     * Register hooks
     *
     * @return  void
     */
    public function register_hooks(): void {
        add_action( 'init', [ $this, 'register_blocks' ], 99 );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_block_global_assets' ] );
    }

    /**
     * Register blocks
     *
     * @return  void
     */
    public function register_blocks() {
        $block_types = $this->get_block_types();

        foreach ( $block_types as $block_type ) {
            new $block_type();
        }
    }

    /**
     * Get all register blocks
     *
     * @return  array
     */
    private function get_block_types() {
        return apply_filters( 'eventin_gutenberg_blocks', $this->blocks );
    }

    /**
     * Enqueue block global assets
     *
     * @return  void
     */
    public function enqueue_block_global_assets() {
        $event_id    = get_the_ID();
        $event       = new Event_Model( $event_id );
        $template_id = $event->event_layout;

        if ( 'etn-template' == get_post_type( $template_id ) ) {
            $template    = new TemplateModel( $template_id );
        
            $template_css = $template->template_css;


            //wp_add_inline_style( 'etn-blocks-style',  $template_css );
        }
    }
}