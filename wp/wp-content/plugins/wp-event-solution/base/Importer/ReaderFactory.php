<?php
/**
 * File Reader Factory
 * 
 * @package Eventin
 */
namespace Eventin\Importer;

class ReaderFactory {
    /**
     * Get reader depends on file type
     *
     * @return Reader_Interface
     */
    public static function get_reader( $file ) {
        $file_name = ! empty( $file['tmp_name'] ) ? $file['tmp_name'] : '';

        $finfo     = class_exists( 'finfo' ) ? new \finfo( FILEINFO_MIME_TYPE ) : null;
        $real_mime = ( $finfo instanceof \finfo ) ? $finfo->file( $file_name ) : '';

        if ( ! $real_mime ) {
            $ext_map   = [ 'csv' => 'text/csv', 'json' => 'application/json' ];
            $ext       = strtolower( pathinfo( $file['name'] ?? '', PATHINFO_EXTENSION ) );
            $real_mime = $ext_map[ $ext ] ?? '';
        }

        $allowed   = [ 'text/plain', 'text/csv', 'application/json' ];

        if ( ! in_array( $real_mime, $allowed, true ) ) {
            return new \WP_Error( 'invalid_file_type', esc_html__( 'Please upload a csv or json file', 'eventin' ), [ 'status' => 422 ] );
        }

        switch( $real_mime ) {
            case 'application/json':
                return new JSONReader( $file_name );
            default:
                return new CSVReader( $file_name );
        }
    }
}
