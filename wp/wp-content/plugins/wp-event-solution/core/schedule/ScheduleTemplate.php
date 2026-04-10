<?php

namespace Eventin\Schedule;

defined( 'ABSPATH' ) || exit;

use Eventin\Interfaces\HookableInterface;

/**
 * Schedule Template Class
 */
class ScheduleTemplate implements HookableInterface {
    /**
     * Register all hooks
     *
     * @return  void
     */
    public function register_hooks(): void {
        add_action( 'single_template', [ $this, 'schedule' ] );
    }

    /**
     * Single include single template
     *
     * @param   string  $single
     *
     * @return  string
     */
    public function schedule( $single ) {

        global $post;

        if ( $post->post_type == 'etn-schedule' ) {
            if ( file_exists( \Wpeventin::templates_dir() . 'schedule/schedule-single-page.php' ) ) {
                return \Wpeventin::templates_dir() . 'schedule/schedule-single-page.php';
            }
        }

        return $single;
    }
}
