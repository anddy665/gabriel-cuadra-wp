<?php

namespace Etn\Core\Event\Pages;

defined( 'ABSPATH' ) || exit;

class Event_single_post {

    use \Etn\Traits\Singleton;
    public function __construct() {
        add_filter('template_include', [$this, 'event_single_page'], 99);
        add_filter('template_include', [$this, 'event_archive_template'], 99);
    }

    public function event_archive_template($template) {
        if (is_post_type_archive('etn')) {
            $default_file = \Wpeventin::plugin_dir() . 'core/event/views/event-archive-page.php';
            if (file_exists($default_file)) {
                 return $default_file;
            } 
        }
        return $template;
    }

    public function event_single_page($template) {
        global $post, $wp_query;
    
        // Check if we are on a singular page for the post type 'etn'
        if ($post && $post->post_type === 'etn' && is_singular('etn')) {
            // Define the default file path
            $default_file = \Wpeventin::plugin_dir() . 'core/event/views/event-single-page.php';

            $build_with_elementor = false;
    
            // Check if Elementor is active and the current post is built with Elementor
            if (class_exists('\Elementor\Plugin') && isset(\Elementor\Plugin::$instance)) {
                $elementor_instance = \Elementor\Plugin::$instance;
            
                if (isset($elementor_instance->documents)) {
                    $document = $elementor_instance->documents->get($post->ID);
            
                    if ($document && method_exists($document, 'is_built_with_elementor')) {
                        $build_with_elementor = $document->is_built_with_elementor();
                    }
                }
            }
            
            // Get the template slug
            $template_slug = get_page_template_slug($post->ID);

            // If the template slug is empty and the default file exists, set the template slug to 'default'
            if(($template_slug == '') && file_exists($default_file)) {
                $template_slug = 'default';
                $build_with_elementor = false;
                return $default_file;
            }
    
            // If the file exists and the post is not built with Elementor, return the default file
            if (file_exists($default_file) && !$build_with_elementor) {
                return $default_file;
            }
        }
    
        // Return the original template if conditions are not met
        return $template;
    }
    

}
