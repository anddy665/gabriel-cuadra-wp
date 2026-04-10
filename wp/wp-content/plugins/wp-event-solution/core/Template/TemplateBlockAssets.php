<?php

/**
 * Template Block Assets
 *
 * @package Eventin
 */
namespace Eventin\Template;

defined( 'ABSPATH' ) || exit;

use Eventin\Interfaces\HookableInterface;
use Wpeventin;

/**
 * TemplateBlockAssets class
 *
 * Handles template builder specific scripts and assets
 */
class TemplateBlockAssets implements HookableInterface
{
    /**
     * Register hooks
     *
     * @return  void
     */
    public function register_hooks(): void
    {
        add_action('enqueue_block_assets', [$this, 'blocks_assets']);
        add_action('enqueue_block_assets', [$this, 'register_event_selection_button']);
        add_action('wp_enqueue_scripts', [$this, 'frontend_blocks_assets']);
        add_action('elementor/editor/before_enqueue_scripts', [$this, 'register_template_save_elementor']);
    }

    /**
     * Register block assets
     *
     * @return  void
     */
    public function blocks_assets()
    {
        $screen = get_current_screen();
        if (is_admin() && $screen && $screen->is_block_editor()) {
            wp_enqueue_script('etn-blocks', Wpeventin::plugin_url() . 'build/js/gutenberg-blocks.js', ['wp-blocks', 'wp-element', 'wp-editor', "etn-dashboard"], Wpeventin::version(), true);
            wp_set_script_translations('etn-blocks', 'eventin');
            wp_enqueue_style('eventin-blocks-editor-style', Wpeventin::plugin_url() . 'build/css/gutenberg-blocks.css', [], Wpeventin::version(), 'all');
        }
        // Load blocks-style.css on both editor and frontend
        // Note: After editing src/blocks/blocks-style.scss, run: npm run build
        wp_enqueue_style('etn-blocks-style', Wpeventin::plugin_url() . 'build/css/etn-block-styles.css', [], Wpeventin::version(), 'all');

        if (class_exists('Wpeventin_Pro')) {
            wp_register_script('etn-qr-code-block', ETN_PRO_ASSETS . 'js/qr-code.js', ['jquery'], Wpeventin::version(), false);
            wp_register_script('etn-qr-code-custom-block', ETN_PRO_ASSETS . 'js/qr-code-custom.js', ['jquery', 'etn-qr-code-block'], Wpeventin::version(), false);
        }
    }

    /**
     * Register block assets for frontend
     *
     * @return  void
     */
    public function frontend_blocks_assets()
    {
        // Ensure blocks-style.css loads on frontend
        wp_enqueue_style('etn-blocks-style', Wpeventin::plugin_url() . 'build/css/etn-block-styles.css', [], Wpeventin::version(), 'all');
    }

    /**
     * Register event selection button script for gutenberg editor
     *
     * @return  void
     */
    public function register_event_selection_button()
    {
        $screen = get_current_screen();

        if (is_admin() && $screen && $screen->post_type === 'etn-template' && $screen->is_block_editor()) {
            wp_enqueue_script('etn-header-toolbar', Wpeventin::plugin_url() . 'build/js/template-builder-header-toolbar.js', ['wp-blocks', 'wp-element', 'wp-editor'], Wpeventin::version(), true);
        }
    }

    /**
     * Register template save elementor script for elementor editor
     *
     * @return  void
     */
    public function register_template_save_elementor()
    {
        $screen = get_current_screen();

        // Check if we're in Elementor editor
        if (is_admin() && $screen && $screen->post_type === 'etn-template' && isset($_GET['action']) && $_GET['action'] === 'elementor') { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- action is compared to a hardcoded string 'elementor' only; no data stored.
            // Register the html2canvas script if not already registered
            if (! wp_script_is('etn-html-2-canvas', 'registered')) {
                wp_register_script('etn-html-2-canvas', Wpeventin::plugin_url() . 'assets/lib/js/html2canvas.min.js', ['jquery'], Wpeventin::version(), false);
            }
            wp_enqueue_script('etn-template-save-elementor', Wpeventin::plugin_url() . 'build/js/elementor-scripts.js', ['etn-html-2-canvas'], Wpeventin::version(), true);
            wp_enqueue_script('etn-html-2-canvas');
        }
    }
}
