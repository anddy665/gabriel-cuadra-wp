<div class="etn-event-tag-list <?php echo esc_attr( $container_class ); ?>">
    <div class="eventin-block-container">
        <h4 class="etn-tags-title"><?php esc_html_e( 'Tags', 'eventin' ); ?></h4>
        <?php if ( $event_tags ): ?>
        <?php foreach( $event_tags as $tag ): ?>
        <a class="etn-tags-link"
            href="<?php echo esc_url( get_term_link( $tag->slug, 'etn_tags' ) ); ?>"><?php echo esc_html( $tag->name ); ?></a>
        <?php endforeach;?>
        <?php else: ?>
        <p><?php esc_attr_e( 'No tags found', 'eventin' ); ?></p>
        <?php endif; ?>
    </div>
</div>