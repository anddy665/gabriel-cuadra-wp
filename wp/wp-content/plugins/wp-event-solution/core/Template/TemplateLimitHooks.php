<?php

namespace Eventin\Template;

defined( 'ABSPATH' ) || exit;

use Eventin\Interfaces\HookableInterface;

/**
 * Class TemplateLimitHooks
 * Handles limiting the number of etn-templates posts
 */
class TemplateLimitHooks implements HookableInterface {
    /**
     * Maximum number of templates allowed without eventin pro
     */
    private const MAX_TEMPLATES_FOR_EVENTIN_FREE = 1;

    /**
     * Register all hooks for the class
     *
     * @return void
     */
    public function register_hooks(): void {
        add_filter('wp_insert_post_data', [$this, 'limit_etn_templates_posts'], 10, 2);

        add_action( 'enqueue_block_editor_assets', function() {
            global $post;
            if ( $post && $post->post_type === 'etn-template' ) {
                $template_type= get_post_meta( $post->ID, 'type', true);

                if ( 'ticket' == $template_type || 'certificate' == $template_type ) {
                    wp_add_inline_style(
                        'wp-block-library',
                        "
                        /* Make the editor background gray */
                        body.post-type-etn-template .edit-post-visual-editor {
                            background-color: #F3F5F7 !important;
                        }
            
                        /* Reset default Gutenberg white background */
                        body.post-type-etn-template .block-editor-writing-flow {
                            background: transparent !important;
                            padding: 0;
                        }
            
                        "
                    );
                }
            }
        });
    }

    /**
     * Limit the number of etn-templates posts when eventin pro is not active
     *
     * @param array $data An array of slashed post data
     * @param array $postarr An array of sanitized, but otherwise unmodified post data
     * @return array Modified post data
     */
    public function limit_etn_templates_posts( $data, $postarr ) {
        if ( class_exists( 'Wpeventin_Pro' ) ) {
            return $data;
        }

        if ( isset( $data['post_type'] ) && 'etn-template' === $data['post_type'] ) {
            $count = wp_count_posts('etn-template');
            $total = $count->publish + $count->draft + $count->pending;

            if ( $total >= self::MAX_TEMPLATES_FOR_EVENTIN_FREE && empty( $postarr['ID'] ) ) {
                wp_die(
                    esc_html__('You cannot create more than 1 template for landing page without eventin-pro.', 'eventin'),
                    esc_html__('Template Limit Reached', 'eventin'),
                    ['back_link' => true]
                );
            }
        }

        return $data;
    }
}
