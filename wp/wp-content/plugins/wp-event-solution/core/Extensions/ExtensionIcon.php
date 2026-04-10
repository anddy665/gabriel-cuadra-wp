<?php

/**
 * Extentions class
 * 
 * @package Eventin
 */
namespace Eventin\Extensions;

defined( 'ABSPATH' ) || exit;

use Wpeventin;

class ExtensionIcon {
    /**
     * Get icon
     *
     * @param   string  $extension_name  [$extension_name description]
     *
     * @return  string
     */
    public static function get( $extension_name ) {
        return self::get_svg( $extension_name );
    }

    /**
     * Get svg icon
     *
     * @param   string  $file_name  [$file_name description]
     *
     * @return  string
     */
    public static function get_svg( $file_name ) {
        $extensions = ['svg', 'jpg', 'png', 'webp'];
        $base_path  = Wpeventin::assets_dir() . 'images/addons/';
        $base_url   = Wpeventin::assets_url() . 'images/addons/';

        foreach ( $extensions as $ext ) {
            $file = $base_path . $file_name . '.' . $ext;
            if ( file_exists( $file ) ) {
                return $ext === 'svg' ? file_get_contents( $file ) : $base_url . $file_name . '.' . $ext;
            }
        }
    }
}