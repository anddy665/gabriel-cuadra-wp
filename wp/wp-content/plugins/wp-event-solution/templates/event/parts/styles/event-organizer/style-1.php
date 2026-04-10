<div class="etn-event-organizers etn-organizer-style-1
<?php echo esc_attr($container_class); ?>">
    <?php if ($event_organizers): ?>
    <div class="etn-organizer-wrap etn-organizer-grid">
        <?php foreach ($event_organizers as $organizer): ?>
            <div class="etn-organaizer-item">
                <div class="etn-organizer-logo">
                    <?php
                        // Try to get company logo first, fallback to profile image
                        $company_logo = $organizer->get_speaker_company_logo();
                        $image        = $company_logo ? $company_logo : $organizer->get_image();

                        if (! $image) {
                            $image = Wpeventin::assets_url() . 'images/avatar.jpg';
                        }

                        $organizer_email = $organizer->get_speaker_email();
                        if ($organizer_email):
                    ?>
                    <img src="<?php echo esc_url($image); ?>"
                        alt="<?php echo esc_attr($organizer->get_speaker_title()); ?>">
                </div>
		                <?php endif; ?>
                <div class="etn-organizer-content">
                    <h4 class="etn-organizer-name"><?php echo esc_html($organizer->get_speaker_title()); ?></h4>
                    <p class="etn-organizer-email"><?php echo esc_html($organizer_email); ?></p>
                    <?php if ($organizer->get_speaker_socials()): ?>
                    <div class="etn-social etn-social-style-1">
                        <?php foreach ($organizer->get_speaker_socials() as $social): ?>
                        <?php
                            $icon  = ! empty($social['icon']) ? $social['icon'] : '';
                            $url   = ! empty($social['etn_social_url']) ? $social['etn_social_url'] : '';
                            $title = ! empty($social['etn_social_title']) ? $social['etn_social_title'] : '';
                        ?>
                        <a href="<?php echo esc_url($url); ?>" target="_blank" title="<?php echo esc_attr($title); ?>" aria-label="<?php echo esc_attr($title); ?>">
                            <i class="etn-icon <?php echo esc_attr($icon); ?>"></i>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p><?php esc_html_e('No organizer found', 'eventin'); ?></p>
    <?php endif; ?>
</div>
