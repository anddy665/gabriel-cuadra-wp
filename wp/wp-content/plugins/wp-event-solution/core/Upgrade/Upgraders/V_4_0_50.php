<?php
/**
 * Updater for version 4.0.10
 *
 * @package Eventin\Upgrade
 */

namespace Eventin\Upgrade\Upgraders;

defined( 'ABSPATH' ) || exit;
 
    use Eventin\Extensions\ImportAutomation;
use Eventin\Settings;

    /**
     * Updater class for v4.0.50
     *
     * @since 4.0.9
     */
    class V_4_0_50 implements UpdateInterface {
        /**
         * Run the updater
         *
         * @return  void
         */
        public function run() {
			$etn_is_migrated = Settings::get("etn_is_migrated");
            
            if ( $etn_is_migrated ) {
				return;
			}
            ImportAutomation::update_automation_flows();
        }
    }