<div class="<?php echo esc_attr($container_class); ?>">
    <div class="eventin-block-container">
        <div class="etn-social etn-event-social-style-1">
            <h2 class="etn-social-title">
                <?php echo esc_html__('Share:', 'eventin'); ?>
            </h2>

             <?php if (is_array($event_socials)): ?>
                <?php foreach ($event_socials as $social): ?>
                    <?php
                        $icon             = ! empty($social['icon']) ? $social['icon'] : '';
                        $title            = ! empty($social["etn_social_title"]) ? $social["etn_social_title"] : '';
                        $url              = ! empty($social['etn_social_url']) ? $social['etn_social_url'] : '';
                        $etn_social_class = 'etn-' . str_replace('etn-icon fa-', '', $icon);

                    ?>
                    <a
                        href="<?php echo esc_url($url); ?>"
                        target="_blank"
                        rel="noopener"
                        aria-label="<?php echo esc_attr($title); ?>"
                        class="etn-social-link"
                    >
                        <i class="etn-icon <?php echo esc_attr($icon); ?>"></i>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>


<style>
    .etn-event-social-style-1 {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .etn-social a {
        border-style: solid;
    }
</style>