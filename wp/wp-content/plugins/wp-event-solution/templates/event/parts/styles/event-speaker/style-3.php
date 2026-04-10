<div class="etn-event-speakers etn-single-event-speaker-block eventin-block-container etn-speaker-style-3 etn-speaker-style-2
<?php echo esc_attr($container_class); ?>">
    <?php if ($event_speakers): ?>
    <div class="etn-single-event-speaker-grid-wrapper">
        <?php foreach ($event_speakers as $speaker): ?>
        <div class="etn-se-speaker-item">
            <div class="etn-speaker-image">
                <a href="<?php echo esc_url($speaker->get_author_url()); ?>"
                    aria-label="<?php echo esc_attr($speaker->get_speaker_title()); ?>">
                    <?php
                        $image = $speaker->get_image();

                        if (! $image) {
                            $image = Wpeventin::assets_url() . 'images/avatar.jpg';
                        }
                    ?>
                    <img src="<?php echo esc_url($image); ?>"
                        alt="<?php echo esc_attr($speaker->get_speaker_title()); ?>"
                        style="object-fit: cover;">
                </a>
            </div>
            <div class="etn-speaker-info-overlay">
                <div class="etn-speaker-info-header">
                    <h4 class="etn-speaker-name">
                        <a href="<?php echo esc_url($speaker->get_author_url()); ?>">
                            <?php echo esc_html($speaker->get_speaker_title()); ?>
                        </a>
                    </h4>
                    <div class="etn-speaker-designation">
                        <p class="eventin-speaker-designation"><?php echo esc_html($speaker->get_speaker_designation()); ?></p>
                    </div>
                </div>
                <div class="etn-speaker-social-wrapper">
                    <?php if ($speaker->get_speaker_socials()): ?> <div class="etn-social">
                    <?php foreach ($speaker->get_speaker_socials() as $social): ?>
                    <?php
                        $icon  = ! empty($social['icon']) ? $social['icon'] : '';
                        $url   = ! empty($social['etn_social_url']) ? $social['etn_social_url'] : '';
                        $title = ! empty($social['etn_social_title']) ? $social['etn_social_title'] : '';
                    ?>

                    <a href="<?php echo esc_url($url); ?>" target="_blank" class="etn-etn-icon fa-facebook-f"
                        title="<?php echo esc_attr($title) ?>" aria-label="<?php echo esc_attr($title) ?>">
                        <i class="etn-icon                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   <?php echo esc_attr($icon); ?>"></i>
                    </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p><?php esc_html_e('No speakers found', 'eventin'); ?></p>
    <?php endif; ?>
</div>