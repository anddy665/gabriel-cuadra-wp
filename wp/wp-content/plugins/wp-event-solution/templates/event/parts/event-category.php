<div class="<?php echo esc_attr( $container_class ); ?> etn-event-meta ">
    <div class="eventin-block-container">
        <div class="etn-event-category">
            <?php if ( $event_categories ): ?>
            <?php foreach( $event_categories as $category ): ?>
                <span>
                <a href="<?php echo esc_url( get_term_link( $category->slug, 'etn_category' ) ); ?>"><?php echo esc_html( $category->name ); ?></a> 
                </span>
            <?php endforeach;?> 
            <?php else: ?>
                <p><?php esc_attr_e( 'No category found', 'eventin' ); ?></p>
            <?php endif; ?>				
        </div>
    </div>
</div>
