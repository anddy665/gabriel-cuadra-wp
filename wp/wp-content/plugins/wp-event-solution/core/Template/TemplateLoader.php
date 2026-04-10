<?php

namespace Eventin\Template;

defined( 'ABSPATH' ) || exit;

use Eventin\Interfaces\HookableInterface;

/**
 * Manage etn-template post type template loading
 *
 * @package Eventin
 * @since 4.0.43
 */
class TemplateLoader implements HookableInterface {
    /**
     * Register all hooks
     *
     * @return  void
     */
    public function register_hooks(): void {
        add_filter( 'template_include', [ $this, 'etn_template_single_page' ], 99 );
    }

    /**
     * Set etn-template single template
     *
     * @param   string  $template
     *
     * @return  string
     */
    public function etn_template_single_page( $template ) {
        global $post;

        if ( ! $post ) {
            return $template;
        }

        if ( $post->post_type !== 'etn-template' || ! is_singular( 'etn-template' ) ) {
            return $template;
        }

        // Use custom template for etn-template single view
        $custom_template = \Wpeventin::templates_dir() . 'single-etn-template.php';

        if ( file_exists( $custom_template ) ) {
            return $custom_template;
        }

        return $template;
    }
}
