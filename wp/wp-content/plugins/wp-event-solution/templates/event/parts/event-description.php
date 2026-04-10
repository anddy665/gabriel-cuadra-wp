<div class="<?php echo esc_attr( $container_class ); ?>">
    <div class="eventin-block-container">
        <div class="etn-event-content-body">
            <?php 
            $etn_desc = $event->get_description();
            // Remove any embedded <style> or <script> blocks entirely to avoid CSS/JS appearing as text
            $etn_desc = preg_replace( '#<style[^>]*>.*?</style>#is', '', $etn_desc );
            $etn_desc = preg_replace( '#<script[^>]*>.*?</script>#is', '', $etn_desc );
            echo wp_kses_post( $etn_desc );
        ?>
        </div>
    </div>
</div>