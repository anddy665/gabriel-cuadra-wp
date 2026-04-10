<?php
defined( 'ABSPATH' ) || exit;

/**
 * event term select
 *
 * @since 1.0.0
 *
 * @return $all_terms
 * @return $term_includes
 */
function divi_event_selected_terms( $all_terms, $term_includes ) {
    // filter only selected term keys
    $includes_keys = array_filter(
        $term_includes,
        function( $cat ) {
            if ( $cat === 'on' ) {
                return $cat;
            }
        }
    );
    // available terms
    $available_terms = array_keys( $all_terms );
    $selected_terms  = array();

    // push event selected terms
    foreach ( $includes_keys as $key => $value ) {
        array_push( $selected_terms, $available_terms[ $key ] );
    }

    $selected_terms = implode(',', $selected_terms);

    return $selected_terms;

}