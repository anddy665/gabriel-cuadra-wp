<?php

namespace Eventin\Enqueue;

/**
 * Frontend class
 */
class Frontend
{
    /**
     * Initialize the class
     *
     * @return  void
     */
    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    /**
     * Enqueue scripts and styles
     *
     * @return  void
     */
    public function enqueue_scripts($top)
    {
        wp_enqueue_script('eventin-i18n');
        wp_enqueue_script('etn-public');
        wp_enqueue_style('etn-icon');
        wp_enqueue_style('etn-public-css');
        //set translations

        wp_set_script_translations('etn-public', 'eventin');
        // wp_enqueue_script( 'html-to-image' ); // Don't need this. Without this file it's working fine

        //set frontend translation
        wp_set_script_translations(
            'etn-module-purchase',  // The script handle
            'eventin',              // Text domain
            plugin_dir_path(__FILE__) . 'languages' //path to language folder
        );
        wp_set_script_translations(
            'etn-app-index',  // The script handle
            'eventin',              // Text domain
            plugin_dir_path(__FILE__) . 'languages' //path to language folder
        );
    }
}