<?php
if (!defined('ABSPATH')) exit;

use \Etn\Utils\Helper as Helper;

// Ensure variables are defined with defaults
$posts_to_show = isset($posts_to_show) ? $posts_to_show : -1;
$etn_paged = isset($etn_paged) ? $etn_paged : 1;
$enable_pagination = isset($enable_pagination) ? $enable_pagination : 'no';
$post_parent = isset($post_parent) ? $post_parent : 0;

// Use posts_to_show and paged for pagination
$data           = Helper::post_data_query('etn', $posts_to_show, $order, $event_cat, 'etn_category',
null, null, $event_tag, $orderby_meta, $orderby, $filter_with_status, $post_parent, '', $etn_paged);

?>
<div class='etn-row etn-event-wrapper'>
    <?php
	if ( !empty( $data ) ) {
		foreach ( $data as $value ) {
            $event = new \Etn\Core\Event\Event_Model($value->ID);
			$total_tickets = $event->get_total_ticket();
			$total_sold_tickets = $event->get_total_sold_ticket();


			$social             = get_post_meta($value->ID, 'etn_event_socials', true);
			$etn_event_location = get_post_meta($value->ID, 'etn_event_location', true);
			$category           = Helper::cate_with_link($value->ID, 'etn_category');
			$existing_location  = Helper::cate_with_link($value->ID, 'etn_location');

            $etn_event_location_type = get_post_meta($value->ID, 'etn_event_location_type', true);
            $banner_image_url       = get_post_meta( $value->ID, 'event_banner', true );

		?>
    <div class="etn-col-md-6 etn-col-lg-<?php echo esc_attr($etn_event_col); ?>">
        <div class="etn-event-item">
            <!-- thumbnail -->
            <div class="etn-event-thumb">
                <?php if ( $banner_image_url ): ?>
                    <a
                        href="<?php echo esc_url(get_the_permalink($value->ID)); ?>"
                        aria-label="<?php echo esc_attr(get_the_title()); ?>"
                    >
                        <img src="<?php echo esc_url($banner_image_url); ?>" alt="Image">
                    </a>
                <?php elseif ( get_the_post_thumbnail_url($value->ID) ): ?>
                    <a
                        href="<?php echo esc_url(get_the_permalink($value->ID)); ?>"
                        aria-label="<?php echo esc_attr(get_the_title()); ?>"
                    >
                        <?php echo get_the_post_thumbnail($value->ID, 'large');  ?>
                    </a>
                <?php endif; ?>

                <div class="etn-event-category">
                    <?php echo  Helper::kses($category); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Helper::kses() wraps wp_kses() with an allowed tags list. ?>
                </div>
                <?php Helper::event_recurring_status($value); ?>
            </div>
            <!-- thumbnail start-->

            <!-- content start-->
            <div class="etn-event-content">
                <?php if($show_event_location == 'yes'): 
                    $location = \Etn\Core\Event\Helper::instance()->display_event_location($value->ID);
                    ?>
                    <?php if (!empty($location)) { ?>
                        <div class="etn-event-location">
                            <i class="etn-icon etn-location"></i>
                            <?php echo esc_html($location); ?>
                        </div>
                    <?php } ?>

                <?php endif; ?>

                <h3 class="etn-title etn-event-title"><a href="<?php echo esc_url(get_the_permalink($value->ID)); ?>">
                    <?php echo esc_html(get_the_title($value->ID)); ?></a>
                </h3>
                <?php
                    if($etn_desc_show =='yes'):
                ?>
                    <p>
                        <?php echo esc_html(Helper::trim_words(get_the_excerpt($value->ID), $etn_desc_limit)); ?>
                    </p>
                <?php endif; ?>

                <div class="etn-event-footer">
                    <div class="etn-event-date">
                        <?php
                            $show_end_date = !empty($show_end_date) ? $show_end_date : 'no';
                            echo esc_html(Helper::etn_display_date($value->ID, 'yes', $show_end_date)); 
                        ?>
                    </div>
                    <div class="etn-atend-btn">
                        <?php
                    $show_form_button = apply_filters("etn_form_submit_visibility", true, $value->ID);
                    if ($show_form_button === false) {
                            ?>
                        <a href="#"  class="etn-btn etn-btn-border etn-event-expired"><?php echo esc_html__('Expired!', "eventin"); ?> </a>
                        <?php
                        } else {
                                ?>
                        <a href="<?php echo esc_url(get_the_permalink($value->ID)); ?>" class="etn-btn etn-btn-border"
                            title="<?php echo esc_attr(get_the_title($value->ID)); ?>"><?php echo esc_html__('Attend', 'eventin') ?>
                            <i class="etn-icon etm-arrow-right"></i></a>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php 
                if( isset( $show_remaining_tickets ) && $show_remaining_tickets =='yes'):
            ?>
            <div class="etn-mt-1 etn-remaining-tickets">
                <?php if ( $total_tickets === -1 ) : ?>
                    <small class="etn-ticket-count-lot"><?php echo esc_html__( 'Unlimited tickets remaining', 'eventin' ); ?></small>
                <?php else : ?>
                    <small class="<?php echo $total_tickets > 5 ? 'etn-ticket-count-lot' : 'etn-ticket-count-few' ;?>"><?php echo esc_html( etn_humanize_number( $total_tickets ) ); ?> <?php echo $total_tickets > 1 ? esc_html__( 'tickets', 'eventin' ) : esc_html__( 'ticket', 'eventin' ); ?> <?php esc_html_e( 'remaining', 'eventin' ); ?></small>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <!-- content end-->
        </div>
        <!-- etn event item end-->
    </div>
    <?php
			}
		}else{
			?>
    <p class="etn-not-found-post"><?php echo esc_html__('No Post Found', 'eventin'); ?></p>
    <?php
		} ?>
</div>

<?php
// Add pagination if enabled
if ($enable_pagination === 'yes' && !empty($data)) {
    // Use custom pagination parameter if provided (for tabs), otherwise use default
    $pagination_param = isset($pagination_param) ? $pagination_param : 'etn_paged';

    // Get current page from URL parameter
    $current_page = isset($_GET[$pagination_param]) ? absint($_GET[$pagination_param]) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public event pagination; value cast to integer via absint().
    $current_page = max(1, $current_page);

    // Get total posts for pagination using WP_Query
    $args = [
        'post_type' => 'etn',
        'post_status' => 'publish',
        'posts_per_page' => -1, // Get all posts to count
        'meta_query' => [], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
        'tax_query' => [], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
        'post_parent' => $post_parent
    ];

    // Add category filter
    if (!empty($event_cat)) {
        $args['tax_query'][] = [
            'taxonomy' => 'etn_category',
            'field' => 'term_id',
            'terms' => $event_cat,
        ];
    }

    // Add tag filter
    if (!empty($event_tag)) {
        $args['tax_query'][] = [
            'taxonomy' => 'etn_tag',
            'field' => 'term_id',
            'terms' => $event_tag,
        ];
    }

    // Add status filter
    if (!empty($filter_with_status)) {
        if ($filter_with_status === 'upcoming') {
            $args['meta_query'][] = [
                'key' => 'etn_start_date',
                'value' => current_time('mysql'),
                'compare' => '>=',
                'type' => 'DATETIME',
            ];
        } elseif ($filter_with_status === 'expire') {
            $args['meta_query'][] = [
                'key' => 'etn_end_date',
                'value' => current_time('mysql'),
                'compare' => '<',
                'type' => 'DATETIME',
            ];
        }
    }

    $count_query = new WP_Query($args);

    $total_posts = $count_query->found_posts;
    $posts_per_page_int = max(1, intval($posts_per_page)); // Ensure it's an integer and at least 1
    $total_pages = ceil($total_posts / $posts_per_page_int);

    if ($total_pages > 1) {
        // Include the pagination template
        $args = [
            'paged'         => $current_page,
            'total_pages'   => $total_pages,
            'prev_text'     => esc_html__('Previous', 'eventin'),
            'next_text'     => esc_html__('Next', 'eventin'),
            'base_class'    => 'etn',
            'current_class' => 'etn-pagination-current',
            'param'         => $pagination_param // Use custom parameter
        ];

        // Include the template
        include \Wpeventin::plugin_dir() . 'templates/parts/pagination.php';
    }

    wp_reset_postdata();
}
?>