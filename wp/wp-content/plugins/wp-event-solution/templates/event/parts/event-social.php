<div class="etn-event-social-wrap <?php echo esc_attr( $container_class ); ?>">
    <div class="eventin-block-container">
        <div class="etn-social">
            <div class="share-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                </svg>
            </div>
            <?php if ( is_array( $event_socials ) ) : ?>
                <?php foreach ( $event_socials as $social ) : ?>
                    <?php 
                        $icon   = ! empty( $social['icon'] ) ? $social['icon'] : '';
                        $title  = ! empty( $social["etn_social_title"] ) ? $social["etn_social_title"] : '';
                        $url    = ! empty( $social['etn_social_url'] ) ? $social['etn_social_url'] : '';
                        $etn_social_class = 'etn-' . str_replace( 'etn-icon fa-', '', $icon ); 

                    ?>
                    <a
                        href="<?php echo esc_url( $url ); ?>"
                        target="_blank"
                        rel="noopener"
                        aria-label="<?php echo esc_attr( $title ); ?>"
                        class="etn-social-link"
                    >
                        <i class="etn-icon <?php echo esc_attr( $icon ); ?>"></i>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </div>
</div>
