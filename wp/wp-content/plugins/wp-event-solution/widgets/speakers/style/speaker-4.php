<?php

use \Etn\Utils\Helper as Helper;

$data = Helper::user_data_query( $etn_speaker_count, $etn_speaker_order, $speakers_category, $orderby );

if ( !empty( $data ) ) { 
    ?>
    <div class='etn-row etn-speaker-wrapper etn-speaker-style-4'>
        <?php
        foreach( $data as $value ) {
            $etn_speaker_designation = get_user_meta( $value->data->ID , 'etn_speaker_designation', true);
            $etn_speaker_image = get_user_meta( $value->data->ID, 'image', true);
            $email = get_user_meta( $value->data->ID, 'user_login', true);
            $author_id = get_the_author_meta($value->data->ID);
            ?>
            <div class="etn-col-lg-<?php echo esc_attr($etn_speaker_col); ?> etn-col-md-6">
                <div class="etn-speaker-item etn-speaker-card-flex">
                    <div class="etn-speaker-img-circle">
                        <img src="<?php echo esc_url($etn_speaker_image); ?>" alt="">
                    </div>
                    <div class="etn-speaker-content">
                        <h3 class="etn-title etn-speaker-title">
                            <a href="<?php echo esc_url( Helper::get_author_page_url_by_id($value->data->ID) ); ?>">
                                <?php echo esc_html($value->data->display_name); ?>
                            </a>
                        </h3>
                        <?php if (!empty($settings['show_designation_style_4']) && $settings['show_designation_style_4'] === 'yes') : ?>
                        <p>
                            <?php echo Helper::kses($etn_speaker_designation); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Helper::kses() wraps wp_kses() with an allowed tags list. ?>
                        </p>
                        <?php endif; ?>
                        <p class="etn-email">
                            <?php echo Helper::kses($email); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Helper::kses() wraps wp_kses() with an allowed tags list. ?>
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