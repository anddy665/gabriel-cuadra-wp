<?php
defined( 'ABSPATH' ) || exit;

use \Etn\Utils\Helper;

if ( !empty( $objective ) ) {
    ?>
    <p><?php echo wp_kses_post( Helper::render(trim( $objective )) ); ?></p>
    <?php
}