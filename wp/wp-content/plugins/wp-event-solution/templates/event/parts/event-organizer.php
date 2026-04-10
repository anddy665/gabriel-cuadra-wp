<div class="etn-event-organizers etn-organizer-style-1 <?php echo esc_attr( $container_class ); ?>">
    <div class="eventin-block-container">
        <h4 class="etn-title"><?php esc_html_e( 'Organizers', 'eventin' ); ?></h4>
        <?php if ( $event_organizers ): ?>
        <div class="etn-organizer-wrap">
            <?php foreach( $event_organizers as $organizer ): ?>
            <div class="etn-organaizer-item">
                <div class="etn-organizer-logo">
                    <?php
                    $image = $organizer->get_image();

                    if ( ! $image ) {
                        $image = Wpeventin::assets_url() . 'images/avatar.jpg';
                    }
                ?>
                    <img src="<?php echo esc_url( $image ); ?>"
                        alt="<?php echo esc_attr( $organizer->get_speaker_title() ); ?>" width="150" height="150"
                        style="object-fit: cover;">
                </div>
                <h4 class="etn-organizer-name"><?php echo esc_html( $organizer->get_speaker_title() ); ?></h4>

                <?php if ( $organizer->get_speaker_socials() ): ?>
                <div class="etn-social etn-social-style-1">
                    <?php foreach( $organizer->get_speaker_socials() as $social ): ?>
                    <?php
                        $icon  = ! empty( $social['icon'] ) ? $social['icon'] : '';
                        $url   = ! empty( $social['etn_social_url'] ) ? $social['etn_social_url'] : '';
                        $title = ! empty( $social['etn_social_title'] ) ? $social['etn_social_title'] : '';   
                    ?>
                    <a href="<?php echo esc_url( $url ); ?>" title="<?php echo esc_attr( $title ); ?> ">
                        <i class="etn-icon <?php echo esc_attr( $icon ); ?>"></i>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p><?php esc_html_e( 'No organizer found', 'eventin' ); ?></p>
        <?php endif; ?>
    </div>
</div>