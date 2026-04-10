<?php

/**
 * Default templates class
 * 
 * @package Eventin
 */
namespace Eventin\Template;

defined( 'ABSPATH' ) || exit;

use Wpeventin;

class DefaultTemplate {
    /**
     * Get default template
     *
     * @param   string  $template_name
     *
     * @return  array
     */
    public static function get_template( $template_name ) {
        $templates = self::get_templates();

        if ( ! empty( $templates[$template_name] ) ) {
            return $templates[$template_name];
        }
        
        return null;
    }

    /**
     * Get default ticket template
     *
     * @param   string  $template_name
     *
     * @return  array
     */
    public static function get_ticket_template( $template_name = 'ticket-template-one' ) {
        $templates = self::get_templates();

        return $templates[$template_name];
    }

    /**
     * Get default certificate template
     *
     * @param   string  $template_name
     *
     * @return  array
     */
    public static function get_certificate_template( $template_name = 'certificate-template-one' ) {
        $templates = self::get_templates();

        return $templates[$template_name];
    }

    /**
     * Get default templates
     *
     * @return  array
     */
    public static function get_templates() {
        return self::templates();
    }

    /**
     * Define default templates
     *
     * @return  array
     */
    public static function templates() {
        return [
            'ticket-template-one' => [
                'name'        => __( 'Ticket Template One', 'eventin' ),
                'type'        => 'ticket',
                'orientation' => 'landscape',
                'status'      => 'publish',
                'is_pro'      => false,
                'content'     => self::get_content( 'ticket-template-one' ),
            ],
            'ticket-template-two' => [
                'name'       => __('Ticket Template Two', 'eventin'),
                'type'        => 'ticket',
                'orientation' => 'landscape',
                'status'      => 'publish',
                'is_pro'      => false,
                'content'     => self::get_content( 'ticket-template-two' ),
            ],
            'certificate-template-one' => [
                'name'        => __('Certificate Template One', 'eventin'),
                'type'        => 'certificate',
                'orientation' => 'landscape',
                'status'      => 'publish',
                'is_pro'      => false,
                'content'     => self::get_content( 'certificate-template-one' ),
            ],
            'event-template-one' => [
                'name'        => __('Event Template One', 'eventin'),
                'type'        => 'event',
                'orientation' => 'landscape',
                'status'      => 'publish',
                'is_pro'      => false,
                'content'     => self::get_content( 'event-template-one' ),
            ],
            'event-template-two' => [
                'name'        => __('Event Template Two', 'eventin'),
                'type'        => 'event',
                'orientation' => 'landscape',
                'status'      => 'publish',
                'is_pro'      => false,
                'content'     => self::get_content( 'event-template-two' ),
            ],
            'event-template-three' => [
                'name'        => __('Event Template Three', 'eventin'),
                'type'        => 'event',
                'orientation' => 'landscape',
                'status'      => 'publish',
                'is_pro'      => false,
                'content'     => self::get_content( 'event-template-three' ),
            ],
        ];
    }

    /**
     * Get default template content
     *
     * @param   string  $template_name  Template Content
     *
     * @return  string
     */
    public static function get_content( $template_name ) {
        $base_dir = realpath( Wpeventin::core_dir() . '/Template/DefaultContents' );
        $file     = realpath( $base_dir . '/' . $template_name . '.php' );

        if ( false === $file || strpos( $file, $base_dir . DIRECTORY_SEPARATOR ) !== 0 ) {
            return null;
        }

        if ( file_exists( $file ) ) {
            return file_get_contents( $file );
        }

        return null;
    }

    /**
     * Create free templates
     *
     * @return  void
     */
    public static function create_free_template() {
        $template_model = new TemplateModel();

        $templates = self::get_templates();

        if ( $templates ) {
            foreach( $templates as $template ) {
                $template_model->create( [
                    'post_title'    => $template['name'],
                    'post_content'  => $template['content'],
                    'post_status'   => $template['status'],
                    'type'          => $template['type'],
                    'orientation'   => $template['orientation'],
                    'is_pro'        => $template['is_pro'],
                ] );
            }
        }

    }
}
