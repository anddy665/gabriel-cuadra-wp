<?php
/**
 * Eventin Event Pagination Template
 * 
 * This template can be included to display pagination for event listings.
 * 
 * @package Eventin
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Extract variables
$paged         = (int) $args['paged'];
$total_pages   = (int) $args['total_pages'];
$prev_text     = $args['prev_text'];
$next_text     = $args['next_text'];
$base_class    = $args['base_class'];
$current_class = $args['current_class'];
$query_param   = isset($args['param']) ? $args['param'] : 'paged';
?>

<?php
// Get current URL without pagination parameters
$current_url = remove_query_arg( $query_param );
?>

<div class="<?php echo esc_attr( $base_class . '__pagination' ); ?> etn-pagination-wrapper">
    <?php if ( $paged > 1 ) : ?>
        <a href="<?php echo esc_url( add_query_arg( $query_param, $paged - 1, $current_url ) ); ?>" 
           class="etn-pagination-link etn-pagination-prev">
            <?php echo esc_html( $prev_text ); ?>
        </a>
    <?php endif; ?>

    <?php
    // Calculate page numbers to show
    $start = max( 1, $paged - 2 );
    $end = min( $total_pages, $paged + 2 );

    // Show first page if needed
    if ( $start > 1 ) {
        echo '<a href="' . esc_url( add_query_arg( $query_param, 1, $current_url ) ) . '" class="etn-pagination-link">1</a>';
        if ( $start > 2 ) {
            echo '<span class="etn-pagination-dots">...</span>';
        }
    }

    // Show page numbers
    for ( $i = $start; $i <= $end; $i++ ) {
        $class = ( $i == $paged ) ? 'etn-pagination-link ' . $current_class : 'etn-pagination-link';
        echo '<a href="' . esc_url( add_query_arg( $query_param, $i, $current_url ) ) . '" class="' . esc_attr( $class ) . '">' . esc_html( $i ) . '</a>';
    }

    // Show last page if needed
    if ( $end < $total_pages ) {
        if ( $end < $total_pages - 1 ) {
            echo '<span class="etn-pagination-dots">...</span>';
        }
        echo '<a href="' . esc_url( add_query_arg( $query_param, $total_pages, $current_url ) ) . '" class="etn-pagination-link">' . esc_html( $total_pages ) . '</a>';
    }
    ?>

    <?php if ( $paged < $total_pages ) : ?>
        <a href="<?php echo esc_url( add_query_arg( $query_param, $paged + 1, $current_url ) ); ?>" 
           class="etn-pagination-link etn-pagination-next">
            <?php echo esc_html( $next_text ); ?>
        </a>
    <?php endif; ?>
</div>
