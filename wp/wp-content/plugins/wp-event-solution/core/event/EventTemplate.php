<?php

namespace Eventin\Event;

defined( 'ABSPATH' ) || exit;

use Eventin\Interfaces\HookableInterface;
use Etn\Core\Event\Event_Model;

/**
 * Manage event templates
 */
class EventTemplate implements HookableInterface {
    /**
     * Register all hooks
     *
     * @return  void 
     */
    public function register_hooks(): void {
        add_filter('template_include', [$this, 'event_single_page'], 99);
        add_filter('template_include', [$this, 'event_archive_template'], 99);
    }

    /**
     * Set archive events template
     *
     * @param   string  $template
     *
     * @return  string
     */
    // eventin archive page template redirection
    public function event_archive_template($template) {
        if ( ! is_post_type_archive('etn') ) {
            return $template;
        }

        // redirect to elementor pro archive page if any archive template is assigned
        if ($this->is_elementor_pro_archive_page('etn_archive')) {
            echo wp_kses_post( \Elementor\Plugin::$instance->frontend->get_builder_content_for_display($template) );
            return $template;
        }else{
            $enable_event_template_builder = etn_get_option( 'enable_event_template_builder' );
            
            if ( $enable_event_template_builder ) {
                \Wpeventin::templates_dir() . 'blocks/event/block-archive-template.php';
            } else {
                $template = \Wpeventin::templates_dir() . 'event/event-archive-page.php';
            }
        }


        return $template;
    }

    /**
     * Set event single template
     *
     * @param   string  $template
     *
     * @return  string
     */
    // Event single page template redirection
    public function event_single_page( $template ) {
        global $post;

        if ( ! $post ) {
            return $template;
        }

        if ( $post->post_type !== 'etn' || ! is_singular( 'etn' ) ) {
            return $template;
        }

        $current_post_id = get_the_ID();
        $is_elementor_editor = get_post_meta($current_post_id, '_elementor_edit_mode', true) === 'builder';

        if (class_exists('Elementor\Plugin') && $is_elementor_editor ) {

            $page_settings_manager = \Elementor\Plugin::$instance->documents->get($current_post_id);

            if ( $page_settings_manager ) {
                $page_settings_manager = $page_settings_manager->get_settings();
            }

            if (isset($page_settings_manager['template']) && ( 'elementor_canvas' == $page_settings_manager['template'] || 'elementor_header_footer' == $page_settings_manager['template']) ) {
                return $template;
            }else{
                $template = \Wpeventin::templates_dir() . 'event/event-single-page.php';
                return $template;
            }
        }

        $event = new Event_Model( $post->ID );
        $enable_event_template_builder = etn_get_option( 'enable_event_template_builder', true );

        if ( 'etn-template' === get_post_type( $event->event_layout ) ) {
            $template = \Wpeventin::templates_dir() . 'template-parts/event/block-single-template.php';
        } else {
            $template = \Wpeventin::templates_dir() . 'event/event-single-page.php';
        }

        return $template;
    }

    // check if the archive page is build with Elementor theme builder - archive template
    public function is_elementor_pro_archive_page($post_type) {
        if (class_exists('ElementorPro\Modules\ThemeBuilder\Module')) {
            $theme_builder = \ElementorPro\Modules\ThemeBuilder\Module::instance();
            $documents = $theme_builder->get_conditions_manager()->get_documents_for_location('archive');

            if (!empty($documents)) {
                foreach ($documents as $document) {
                    $template_id = $document->get_main_id();
                    $template_document = \ElementorPro\Plugin::elementor()->documents->get( $template_id );
                    $template_conditions = $theme_builder->get_conditions_manager()->get_document_conditions( $template_document );

                    if (!empty($template_conditions) && is_array($template_conditions)) {
                        foreach ($template_conditions as $rule) {
                            if ( isset($rule['sub_name']) && $rule['name'] === 'archive' && $rule['sub_name'] === $post_type ) {
                                return true; // Found an archive template specific to 'etn'
                            }
                        }
                    }
                }
            }
        }

        return false; // No matching archive template found for 'etn'
    }

}
