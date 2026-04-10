<?php

    use \Etn\Utils\Helper as Helper;

    // Ensure variables are defined with defaults
    $speakers_to_show  = isset($speakers_to_show) ? $speakers_to_show : 6;
    $enable_pagination = isset($enable_pagination) ? $enable_pagination : 'no';

    // Get current page number, default to 1 if not set
    $current_page = ! empty($speaker_paged) ? max(1, intval($speaker_paged)) : 1;

    // Get speakers for current page
    $data = Helper::user_data_query(
        $speakers_to_show,
        $etn_speaker_order,
        $speakers_category,
        $orderby,
        $current_page
    );

    if (! empty($data)) {
    ?>
    <div class='etn-row etn-speaker-wrapper'>
        <?php
            foreach ($data as $value) {
                    $etn_speaker_designation = get_user_meta($value->data->ID, 'etn_speaker_designation', true);
                    $etn_speaker_image       = get_user_meta($value->data->ID, 'image', true);
                    $social                  = get_user_meta($value->data->ID, 'etn_speaker_social', true);
                    $author_id               = get_the_author_meta($value->data->ID);
                ?>
            <div class="etn-col-lg-<?php echo esc_attr($etn_speaker_col); ?> etn-col-md-6">
                <div class="etn-speaker-item">
                    <div class="etn-speaker-thumb">
                        <a href="<?php echo esc_url(get_the_permalink($value->data->ID)); ?>" class="etn-img-link" aria-label="<?php echo esc_html($value->data->display_name); ?>">
                            <img src="<?php echo esc_url($etn_speaker_image); ?>" alt="">
                        </a>
                        <div class="etn-speakers-social">
                            <?php
                                if (is_array($social) && ! empty($social)) {
                                            foreach ($social as $social_value) {
                                                if (! empty($social_value)) {
                                                ?>
                                        <a href="<?php echo esc_url($social_value['etn_social_url']); ?>" title="<?php echo ! empty($social_value['etn_social_title']) ? esc_attr($social_value['etn_social_title']) : ''; ?>">
                                            <i class="etn-icon                                                                                                                                                                                           <?php echo esc_attr($social_value["icon"]); ?>"></i>
                                        </a>
                                        <?php
                                            }
                                                        }
                                                    }
                                                ?>
                        </div>
                    </div>
                    <div class="etn-speaker-content">
                        <h3 class="etn-title etn-speaker-title"><a href="<?php echo esc_url( Helper::get_author_page_url_by_id($value->data->ID) ); ?>"><?php echo esc_html($value->data->display_name); ?></a> </h3>
                        <p>
                            <?php echo Helper::kses($etn_speaker_designation); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Helper::kses() wraps wp_kses() with an allowed tags list. ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php
            }
            ?>
    </div>
<?php
    } else {
    ?>
    <p class="etn-not-found-post"><?php echo esc_html__('No Post Found', 'eventin'); ?></p>
    <?php
        }
    ?>

<?php
    // Add pagination if enabled
    if ($enable_pagination === 'yes' && ! empty($data)) {
        // Get the total number of speakers
        $total_speakers = count(get_users([
            'role__in'    => ['etn-speaker', 'etn-organizer'],
            'fields'      => 'ID',
            'count_total' => true,
        ]));

        // Get posts per page setting or use default 6
        $posts_per_page = ! empty($speakers_to_show) ? intval($speakers_to_show) : 6;

        // Calculate total pages
        $total_pages = ceil($total_speakers / $posts_per_page);

        // Only show pagination if there are multiple pages
        if ($total_pages > 1) {
            // Get current URL and remove existing pagination parameter
            $current_url = remove_query_arg('speaker_paged');

            // Include the pagination template
            $args = [
                'paged'         => max(1, intval($speaker_paged)),
                'total_pages'   => $total_pages,
                'prev_text'     => esc_html__('Previous', 'eventin'),
                'next_text'     => esc_html__('Next', 'eventin'),
                'base_class'    => 'etn',
                'current_class' => 'etn-pagination-current',
                'param'         => 'speaker_paged',
            ];

            // Include the template
            include \Wpeventin::plugin_dir() . 'templates/parts/pagination.php';
        }

        wp_reset_postdata();
    }
?>
