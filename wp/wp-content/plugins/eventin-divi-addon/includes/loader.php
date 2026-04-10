<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if (! class_exists('ET_Builder_Element') || ! class_exists('Wpeventin')) {
	return;
}

/**
 * Get input modules name
 *
 * @return array
 */
//phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
function eventin_divi_addon_get_input_modules_name()
{
	$modules = array(
		'AdvancedSearch',
		'EtnEvents',
		'EtnSpeaker',
		'EventsCalendar',
		'EventsTabs',
		'ScheduleLists',
		'ScheduleTabs',
	);

	if (class_exists('Wpeventin_Pro')) {
		$pro_module = array(
			'SpeakerPro',
			'ScheduleTabsPro',
			'ScheduleListsPro',
			'EventsTabspro',
			'EventsTicketFormPro',
			'OrganizerPro',
			'RecurringEventPro',
			'RelatedEventPro',
			'EventsCalendarpro',
			'EventsCountdownPro',
			'EventsPro',
			'EventsSliderpro',
			'EventsCalendarlist',
			'EventLocationPro',
			'SpeakerSlider',
			'EventsAttendeeListPro',
			'EventsClassicPro',
			'EventsOnelinePro'
		);
		$modules = array_merge($modules, $pro_module);
	}

	return $modules;
}

/**
 * Load custom Divi Builder modules
 *
 * @return void
 */
//phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
function eventin_divi_addon_load_modules() {
	foreach ((array) eventin_divi_addon_get_input_modules_name() as $module_file) {
		$file_path = plugin_dir_path(__FILE__) . 'modules/' . $module_file . '/' . $module_file . '.php';
		if (file_exists($file_path)) {
			require_once $file_path;
		}
	}
}

// Load custom Divi Builder modules
eventin_divi_addon_load_modules();