<?php

namespace Etn\Core\Schedule;

use \Etn\Core\Schedule\Pages\Schedule_single_post;

defined( 'ABSPATH' ) || exit;

class Hooks {

    use \Etn\Traits\Singleton;

    public $cpt;
    public $action;
    public $base;
    public $schedule;
    public $settings;
    public $schedule_action;

    public $actionPost_type = ['etn-schedule'];

    public function Init() {
        $this->cpt      = new Cpt();
    }
}
