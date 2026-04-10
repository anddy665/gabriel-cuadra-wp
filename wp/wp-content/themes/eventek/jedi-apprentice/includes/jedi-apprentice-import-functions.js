/**
 * Easy Demo Import
 *
 * @package   jedi-master
 * @author    Jerry Simmons <jerry@ferventsolutions.com>
 * @copyright 2021 Jerry Simmons
 * @license   GPL-2.0+
 */

jQuery(document).ready(function ($) {
	let importStep = 0;
	let importOptions = jedi_ajax_vars.jedi_import_options;
	const importStats = jedi_ajax_vars.import_stats;
	const importBatchSize = jedi_ajax_vars.jedi_import_options.import_batch_size;
	const importMediaBatchSize = jedi_ajax_vars.jedi_import_options.import_media_batch_size;

	let installPlugins = 0;
	let activatePlugins = 0;

	let importedMediaCount = 0;
	let importedPostCount = 0;

	let importErrors = 0;

	const jediImportSettingsNonce = jedi_ajax_vars.jswj_import_settings_nonce;
	const jediImportProcessNonce = jedi_ajax_vars.jswj_import_process_nonce;

	let ajaxTimeout;
	const phpMaxExecutionTime = parseInt( jedi_ajax_vars.max_execution_time );
	if( phpMaxExecutionTime <= 30 ) { 
		ajaxTimeout = 30000; 
	} else {
		ajaxTimeout = phpMaxExecutionTime * 1000;
	}


	/**
	 * Get Import Options & Then Initialize Import Report
	 */
	$('.jedi_import_report_container').ready(function () {
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'jswj_get_import_options',
				jedi_ajax_nonce: jediImportSettingsNonce,
			},
			success(response) {
				importOptions = getAjaxResponse(response);
			},
		})
		.then(function () {
			prepareImportReport();
		});
	});

	/**
	 * Initialize Import Report
	 */
	function prepareImportReport() {
		installPlugins = $('#jedi_plugins_install').attr('data-install');
		activatePlugins = $('#jedi_plugins_activate').attr('data-activate');

		if (installPlugins > 0) {
			setupImportLabel('#jedi_plugins_install', 'Plugins To Install: ', installPlugins);
		}
		if (activatePlugins > 0) {
			setupImportLabel('#jedi_plugins_activate', 'Plugins To Activate: ', activatePlugins);
		}

		if (importOptions.include_media === '1') {
			setupImportLabel('#jedi_media_import', 'Media Files To Import: ', importStats.media.total);
		}

		if (importOptions.include_categories === '1' && importOptions.include_posts === '1') {
			setupImportLabel('#jedi_categories_import', 'Categories & Taxonomies To Import: ', importStats.categories.total);
		}

		if (importOptions.include_posts === '1') {
			setupImportLabel('#jedi_posts_import', 'Pages & Posts To Import: ', importStats.posts.total);
		}

		if (importOptions.include_homepage === '1' && importOptions.include_posts === '1') {
			setupImportLabel('#jedi_homepage_import', 'Homepage Setting Queued', '');
		}

		if (importOptions.include_options === '1') {
			setupImportLabel('#jedi_divioptions_import', 'Divi Theme Settings & Customizer Options Queued For Import', '');
		}

		if (importOptions.include_divi_theme_builder === '1') {
			setupImportLabel('#jedi_divi_theme_builder_import', 'Divi Theme Builder Data Queued For Import', '');
		}

		if (importOptions.elementor.include_options === '1') {
			setupImportLabel('#jedi_elementor_options_import', 'Elementor Options & Settings Queued For Import', '');
		}

		if (importOptions.include_css === '1') {
			setupImportLabel('#jedi_css_import', 'Customizer CSS Queued For Import', '');
		}

		if (importOptions.include_menus === '1') {
			setupImportLabel('#jedi_menus_import', 'Menus To Import: ', importStats.menus.total);
		}

		if (importOptions.include_widgets === '1') {
			setupImportLabel('#jedi_widgets_import', 'Widgets Queued For Import', '');
		}

		if (importOptions.include_wp_options === '1') {
			setupImportLabel('#jedi_wp_options_import', 'WP Options Queued For Import', '');
		}

		doImport();
	} // END prepareImportReport ready()

	/**
	 * AJAX Call To Validate Import
	 */
	function afterImportChecks() {
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: { action: 'jswj_after_import_checks', jedi_ajax_nonce: jediImportProcessNonce },
			success(rawResponse) {
				const response = getAjaxResponse(rawResponse);
				if (response[0] === 1) {		// Increment Count If Successful Import
					continueImport();
				} else {
					getResumeImportForm();
				}
			},
		});
	} // END afterImportChecks()

	/**
	 * AJAX Call To Get Resume Import Form
	 */
	function getResumeImportForm() {
		$.post(
			ajaxurl,
			{ action: 'jswj_trigger_resume_import_form', jedi_ajax_nonce: jediImportProcessNonce },
			function (rawResponse) { // Show Continue Form
				const response = rawResponse.substring(0, rawResponse.length - 1);
				$('.jedi_import_report_container').append(response);
			},
		);
	}

	/**
	 * Run Through Import Process
	 *
	 * @requires continueImport() to increment through steps
	 */
	function doImport() {
		switch (importStep) {
			case 0: jediDoImportHook('jedi_before_import'); break;

			case 1:
				if (installPlugins > 0) {
					processingImportLabel('#jedi_plugins_install', 'Installing Plugins... ');
					jediInstallPlugins();
				} else {
					continueImport();
				}
				break;
			case 2:
				if (activatePlugins > 0) {
					processingImportLabel('#jedi_plugins_activate', 'Activating Plugins... ');
					jediActivatePlugins();
				} else {
					continueImport();
				}
				break;
			case 3: jediDoImportHook('jedi_before_media_import'); break;
			case 4:
				if (importOptions.include_media === '1') {
					processingImportLabel('#jedi_media_import', 'Importing Media Files... ');
					importErrors = 0;
					mediaImportDriver();
				} else {
					continueImport();
				}
				break;
			case 5: jediDoImportHook('jedi_after_media_import'); break;
			case 6:
				if (importOptions.include_categories === '1' && importOptions.include_posts === '1') {
					processingImportLabel('#jedi_categories_import', 'Importing Categories & Taxonomies... ');
					jediCategoriesImport();
				} else {
					continueImport();
				}
				break;
			case 7: jediDoImportHook('jedi_before_post_import'); break;
			case 8:
				if (importOptions.include_posts === '1') {
					processingImportLabel('#jedi_posts_import', 'Importing Pages & Posts... ');
					importErrors = 0;
					postImportDriver();
				} else {
					continueImport();
				}
				break;
			case 9: 
				processingImportLabel('#jedi_posts_import', 'Additional Post Processing...');
				jediDoImportHook('jedi_after_post_import')
					.then( function() {
						processedImportLabel('#jedi_posts_import', 'Pages & Posts Imported: ');
					});
				break;
			case 10:
				if (importOptions.include_homepage === '1') {
					processingImportLabel('#jedi_homepage_import', 'Setting Homepage... ');
					jediSetHomepage();
				} else {
					continueImport();
				}
				break;
			case 11:
				if (importOptions.include_options === '1') {
					processingImportLabel('#jedi_divioptions_import', 'Importing Divi Theme Options... ');
					jediImportDiviOptions();
				} else {
					continueImport();
				}
				break;

			case 12:
				if (importOptions.include_divi_theme_builder === '1') {
					processingImportLabel('#jedi_divi_theme_builder_import', 'Importing Divi Theme Builder Data... ');
					jediImportDiviThemeBuilderData();
				} else {
					continueImport();
				}
				break;

			case 13:
				if (importOptions.elementor.include_options === '1') {
					processingImportLabel('#jedi_elementor_options_import', 'Importing Elementor Options & Settings... ');
					jediImportElementorOptions();
				} else {
					continueImport();
				}
				break;

			case 14:
				if (importOptions.include_css === '1') {
					processingImportLabel('#jedi_css_import', 'Importing Customizer CSS... ');
					jediImportCSS();
				} else {
					continueImport();
				}
				break;
			case 15:
				if (importOptions.include_menus === '1') {
					processingImportLabel('#jedi_menus_import', 'Importing Menus... ');
					jediImportMenus();
				} else {
					continueImport();
				}
				break;
			case 16:
				if (importOptions.include_widgets === '1') {
					processingImportLabel('#jedi_widgets_import', 'Importing Widget Data... ');
					jediImportWidgets();
				} else {
					continueImport();
				}
				break;
			case 17:
				if (importOptions.include_wp_options === '1') {
					processingImportLabel('#jedi_wp_options_import', 'Importing WP Options... ');
					jediImportWPOptions();
				} else {
					continueImport();
				}
				break;
			case 18:
				jediDoImportHook('jedi_after_import');
				continueImport();
				break;
			case 19: // After Import Checks
				afterImportChecks();
				break;
			case 20: // Finish Import Report
				completeImport();
				break;

			default:
				break;
		} // END switch(importStep)
	} // END doImport()

	/**
	 * Increment Import Step & Continue Import Process
	 */
	function continueImport() {
		importStep += 1;
		doImport();
	} // END continueImport()

	/**
	 * Functions For Styling Import Labels & Status
	 */
	function setupImportLabel(labelSelector, labelText, statText) {
		$(`${labelSelector} .jedi_label`).text(labelText);
		$(`${labelSelector} .jedi_stat`).text(statText);
	}
	function processingImportLabel(labelSelector, labelText) {
		$(labelSelector).addClass('importing_now');
		$(`${labelSelector} .jedi_label`).text(labelText);
	}
	function processingImportStatus(labelSelector, statusPercent) {
		const statusWidth = statusPercent * 300;
		$(`${labelSelector} .jedi_status`).css('width', `${statusWidth}px`);
	}
	function processedImportLabel(labelSelector, labelText) {
		setTimeout(function () {
			$(`${labelSelector} .jedi_import_icon`).html('&check;');
			$(`${labelSelector} .jedi_import_icon`).css('position', 'relative');
			$(`${labelSelector} .jedi_import_icon`).css('display', 'inline-block');
			$(`${labelSelector} .jedi_import_icon`).css('font-size', '2em');
			$(`${labelSelector} .jedi_import_icon`).css('margin-left', '0');
			$(`${labelSelector} .jedi_label`).text(labelText);
			$(labelSelector).removeClass('importing_now');
		}, 250);
	}

	/**
	 * AJAX Call To Do Import Hook
	 *
	 * Continues Import Process On Success
	 */
	function jediDoImportHook(hookName) {
		return $.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'jswj_do_import_hooks',
				hook_name: hookName,
				jedi_ajax_nonce: jediImportProcessNonce,
			},
			success(rawResponse) {
				const response = getAjaxResponse(rawResponse);
				if (response[0] === 1) {		// Increment Count If Successful Import
				}
			},
		})
		.then(
			function () { continueImport(); },
		);
	} // END jediDoImportHook()

	/**
	 * AJAX Calls To Install & Activate Plugins
	 *
	 * Continues Import Process On Success
	 */
	function jediInstallPlugins() {
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'jswj_jedi_install_plugins',
				jedi_ajax_nonce: jediImportProcessNonce,
			},
			success(rawResponse) {
				processingImportStatus('#jedi_plugins_install', 1);
				const response = getAjaxResponse(rawResponse);
				if (response[0] === 1) {		// Increment Count If Successful Import
					$('#jedi_plugins_install .jedi_stat').text(response[1]);
					processedImportLabel('#jedi_plugins_install', '');
				} else {
					$('#jedi_plugins_install .jedi_stat').text('Completed with errors. Check log for details.');
					processedImportLabel('#jedi_plugins_install', '');
				}
				continueImport();
			},
		});
	} // END jediInstallActivatePluginsDriver()

	/**
	 * AJAX Call To Activate Plugins
	 */
	function jediActivatePlugins() {
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'jswj_jedi_activate_plugins',
				jedi_ajax_nonce: jediImportProcessNonce,
			},
			success(rawResponse) {
				processingImportStatus('#jedi_plugins_activate', 1);

				const response = getAjaxResponse(rawResponse);
				if (response[0] === 1) {		// Increment Count If Successful Import
					$('#jedi_plugins_activate .jedi_stat').text(response[1]);
					processedImportLabel('#jedi_plugins_activate', '');
				} else {
					$('#jedi_plugins_activate .jedi_stat').text('Completed with errors. Check log for details.');
					processedImportLabel('#jedi_plugins_activate', '');
				}

				continueImport();
			},
		});
	} // jediActivatePlugins()

	/**
	 * Media Import Driver
	 */
	function mediaImportDriver() {
		if (importedMediaCount > importStats.media.total) {
			processingImportStatus('#jedi_media_import', 1);
			$('#jedi_media_import .jedi_stat').text(`${importStats.media.total} of ${importStats.media.total}`);
			processedImportLabel('#jedi_media_import', 'Media Files Imported: ');
			continueImport();
			return;
		}

		$('#jedi_media_import .jedi_stat').text(`${importedMediaCount + 1} of ${importStats.media.total}`);
		processingImportStatus('#jedi_media_import',
			((importedMediaCount + 1) / importStats.media.total));

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			timeout: ajaxTimeout,
			data: {
				action: 'jswj_import_media_batch',
				importedMediaCount,
				failedBatchCount: importErrors,
				jedi_ajax_nonce: jediImportProcessNonce,
			},
			success() {
				$('#jedi_import_alerts_container').empty();
			},
			error(xhr, ajaxOptions, thrownError) {
				console.error(`Error: ${thrownError}`);
				importErrors += 1;
				console.error(`Error Count: ${importErrors}`);

				if (importErrors < 10) {
					$('#jedi_import_alerts_container').empty();
					$('#jedi_import_alerts_container').append(`<div><p><strong>Uh Oh, Looks Like The Server Struggled With That Last Batch Of Media Files</strong><br>Let's Try That Again, Attempt ${importErrors + 1} of 10</p></div>`);
					setTimeout(function () { mediaImportDriver(); }, 500 * importErrors);
				} else {
					$('#jedi_import_alerts_container').append('<div><h1>Import Failed</h1><p>Server Timeout Importing Media Files, Reduce The Batch Size And Try Again</p></div>');
				}
			},
		})
		.then(function () {
				continueMediaImport();
		});
	} // END mediaImportDriver()

	/**
	 * Continue Media Import
	 */
	function continueMediaImport() {
		importedMediaCount += importMediaBatchSize;
		mediaImportDriver();
	} // END jedimediaImportDriver()

	/**
	 * Post Import Driver
	 */
	function postImportDriver() {
		if (importedPostCount > importStats.posts.total) {
			processingImportStatus('#jedi_posts_import', 1);
			$('#jedi_posts_import .jedi_stat').text(`${importStats.posts.total} of ${importStats.posts.total}`);
			processedImportLabel('#jedi_posts_import', 'Pages & Posts Imported: ');
			continueImport();
			return;
		}

		$('#jedi_posts_import .jedi_stat').text(`${importedPostCount + 1} of ${importStats.posts.total}`);
		processingImportStatus('#jedi_posts_import',
			((importedPostCount + 1) / importStats.posts.total));

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			timeout: ajaxTimeout,
			data: {
				action: 'jswj_import_posts_batch',
				importedPostCount,
				jedi_ajax_nonce: jediImportProcessNonce,
			},
			success() { continuePostImport(); },
			error(xhr, ajaxOptions, thrownError) {
				console.error(xhr.status);
				console.error(thrownError);

				importErrors += 1;
				if (importErrors < 10) {
					// Add a little delay to let the server catch up
					setTimeout(function () { postImportDriver(); }, 500 * importErrors);
				} else {
					$('#jedi_import_alerts_container').append('<div><h1>Import Failed</h1><p>Server Timeout Importing Posts, Reduce The Batch Size And Try Again</p></div>');
				}
			},
		});
	} // END postImportDriver()

	/**
	 * Continue Post Import
	 */
	function continuePostImport() {
		importedPostCount += importBatchSize;
		postImportDriver();
	} // END continuePostImport()

	/**
	 * Categories Import
	 *
	 * Continues Import Process On Success
	 */
	function jediCategoriesImport() {
		$('#jedi_categories_import .jedi_stat').text(`1 of ${importStats.categories.total}`);

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'jswj_import_categories',
				jedi_ajax_nonce: jediImportProcessNonce,
			},
			success() {},
		})
		.then(function () {
			processingImportStatus('#jedi_categories_import', 1);
		})
		.then(function () {
				$('#jedi_categories_import .jedi_stat').text(`${importStats.categories.total} of ${importStats.categories.total}`);
				processedImportLabel('#jedi_categories_import', 'Categories & Taxonomies Imported: ');
				continueImport();
		});
	} // END jediCategoriesImport()

	/**
	 * AJAX Call To Set Homepage
	 *
	 * Continues Import Process On Success
	 */
	function jediSetHomepage() {
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'jswj_jedi_set_homepage',
				jedi_ajax_nonce: jediImportProcessNonce,
			},
			success(rawResponse) {
				processingImportStatus('#jedi_homepage_import', 1);
				const response = getAjaxResponse(rawResponse);
				if (response[0] === 1) {		// Increment Count If Successful Import
					$('#jedi_homepage_import .jedi_stat').text(response[1]);
					processedImportLabel('#jedi_homepage_import', '');
				} else {
					$('#jedi_homepage_import .jedi_stat').text('Completed with errors. Check log for details.');
					processedImportLabel('#jedi_homepage_import', '');
				}
			},
		})
		.then(function () {
			continueImport();
		});
	} // END jediSetHomepage()

	/**
	 * AJAX Call To Import Divi's Theme Options
	 *
	 * Continues Import Process On Success
	 */
	function jediImportDiviOptions() {
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'jswj_jedi_import_divi_options',
				jedi_ajax_nonce: jediImportProcessNonce,
			},
			success(rawResponse) {
				processingImportStatus('#jedi_divioptions_import', 1);
				const response = getAjaxResponse(rawResponse);
				if (response[0] === 1) {		// Increment Count If Successful Import
					$('#jedi_divioptions_import .jedi_stat').text(response[1]);
					processedImportLabel('#jedi_divioptions_import', '');
				} else {
					$('#jedi_divioptions_import .jedi_stat').text('Completed with errors. Check log for details.');
					processedImportLabel('#jedi_divioptions_import', '');
				}
			},
		})
		.then(function () {
			continueImport();
		});
	} // END jediImportDiviOptions()

	/**
	 * AJAX Call To Import Divi's Theme Builder Data
	 *
	 * Continues Import Process On Success
	 */
	function jediImportDiviThemeBuilderData() {
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'jswj_jedi_import_divi_theme_builder_data',
				jedi_ajax_nonce: jediImportProcessNonce,
			},
			success(rawResponse) {
				processingImportStatus('#jedi_divi_theme_builder_import', 1);
				const response = getAjaxResponse(rawResponse);
				if (response[0] === 1) {		// Increment Count If Successful Import
					$('#jedi_divi_theme_builder_import .jedi_stat').text(response[1]);
					processedImportLabel('#jedi_divi_theme_builder_import', '');
				} else {
					$('#jedi_divi_theme_builder_import .jedi_stat').text('Completed with errors. Check log for details.');
					processedImportLabel('#jedi_divi_theme_builder_import', '');
				}
			},
		})
		.then(function () {
			continueImport();
		});
	} // END jediImportDiviThemeBuilderData()

	/**
	 * AJAX Call To Import Elementor's Options & Settings
	 *
	 * Continues Import Process On Success
	 */
	function jediImportElementorOptions() {
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'jswj_jedi_import_elementor_options',
				jedi_ajax_nonce: jediImportProcessNonce,
			},
			success(rawResponse) {
				processingImportStatus('#jedi_elementor_options_import', 1);
				const response = getAjaxResponse(rawResponse);
				if (response[0] === 1) {		// Increment Count If Successful Import
					$('#jedi_elementor_options_import .jedi_stat').text(response[1]);
					processedImportLabel('#jedi_elementor_options_import', '');
				} else {
					$('#jedi_elementor_options_import .jedi_stat').text('Completed with errors. Check log for details.');
					processedImportLabel('#jedi_elementor_options_import', '');
				}
			},
		})
		.then(function () {
			continueImport();
		});
	} // END jediImportElementorOptions()

	/**
	 * AJAX Call To Import CSS
	 *
	 * Continues Import Process On Success
	 */
	function jediImportCSS() {
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'jswj_jedi_import_css',
				jedi_ajax_nonce: jediImportProcessNonce,
			},
			success(rawResponse) {
				processingImportStatus('#jedi_css_import', 1);
				const response = getAjaxResponse(rawResponse);
				if (response[0] === 1) {		// Increment Count If Successful Import
					$('#jedi_css_import .jedi_stat').text(response[1]);
					processedImportLabel('#jedi_css_import', '');
				} else {
					$('#jedi_css_import .jedi_stat').text('Completed with errors. Check log for details.');
					processedImportLabel('#jedi_css_import', '');
				}
			},
		})
		.then(function () {
			continueImport();
		});
	} // END jediImportCSS()

	/**
	 * AJAX Call To Import Menus
	 *
	 * Continues Import Process On Success
	 */
	function jediImportMenus() {
		$('#jedi_menus_import .jedi_stat').text(`1 of ${importStats.menus.total}`);

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'jswj_jedi_import_menus',
				jedi_ajax_nonce: jediImportProcessNonce,
			},
			success(rawResponse) {
				processingImportStatus('#jedi_menus_import', 1);
				const response = getAjaxResponse(rawResponse);
				if (response[0] === 1) {		// Increment Count If Successful Import
					$('#jedi_menus_import .jedi_stat').text(response[1]);
					processedImportLabel('#jedi_menus_import', '');
				} else {
					$('#jedi_menus_import .jedi_stat').text('Completed with errors. Check log for details.');
					processedImportLabel('#jedi_menus_import', '');
				}
			},
		})
		.then(function () {
			continueImport();
		});
	} // END jediImportMenus()

	/**
	 * AJAX Call To Import Widgets
	 *
	 * Continues Import Process On Success
	 */
	function jediImportWidgets() {
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'jswj_jedi_import_widgets',
				jedi_ajax_nonce: jediImportProcessNonce,
			},
			success(rawResponse) {
				const response = getAjaxResponse(rawResponse);
				if (response[0] === 1) {		// Increment Count If Successful Import
					$('#jedi_widgets_import .jedi_stat').text(response[1]);
				}
			},
		})
		.then(function () {
			processingImportStatus('#jedi_widgets_import', 1);
		})
		.then(function () {
				processedImportLabel('#jedi_widgets_import', '');
		})
		.then(function () {
			continueImport();
		});
	} // END jediImportWidgets()

	/**
	 * AJAX Call To Import WP Options
	 *
	 * Continues Import Process On Success
	 */
	function jediImportWPOptions() {
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'jswj_jedi_import_wp_options',
				jedi_ajax_nonce: jediImportProcessNonce,
			},
			success(rawResponse) {
				const response = getAjaxResponse(rawResponse);
				if (response[0] === 1) {		// Increment Count If Successful Import
					$('#jedi_wp_options_import .jedi_stat').text(response[1]);
				}
			},
		})
		.then(function () {
			processingImportStatus('#jedi_wp_options_import', 1);
		})
		.then(function () {
			processedImportLabel('#jedi_wp_options_import', '');
		})
		.then(function () {
			continueImport();
		});
	} // END jediImportWPOptions()

	/**
	 * AJAX Call To Complete Import Process
	 *
	 */
	function completeImport() {
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'jswj_jedi_import_complete',
				jedi_ajax_nonce: jediImportProcessNonce,
			},
			success(response) {
				$('#jedi_import_complete').text('Import Process Completed');
				$(response).insertAfter('.jedi_import_report_container');
			},
		})
		.then(function () {
			continueImport();
		});
	} // END completeImport()

	/**
	 * AJAX Call To Update JEDI Log
	 */
	function jediLog(logText) {
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'jswj_ajax_log',
				logText,
				jedi_ajax_nonce: jediImportProcessNonce,
			},
		});
	}

	/**
	 * Parse AJAX JSON Response
	 * Tries with start & end markers first
	 *
	 * @return JSON object
	 */
	function getAjaxResponse(ajaxResponse) {
		const responseStart = ajaxResponse.indexOf('jswj_response_start');
		const responseEnd = ajaxResponse.indexOf('jswj_response_end');

		if (responseStart > -1 && responseEnd > -1) {
			const jswjResponse = ajaxResponse.substring(responseStart + 19, responseEnd);

			try {
				const jsonResponse = $.parseJSON(jswjResponse);
				return jsonResponse;
			} catch (err) {
				jediLog(err);
				jediLog(ajaxResponse);
			}
		} else {
			try {
				const jsonResponse = $.parseJSON(ajaxResponse);
				return jsonResponse;
			} catch (err) {
				jediLog('AJAX Response Markers Not Found');
				jediLog(err);
				jediLog(ajaxResponse);
			}
		}

		const emptyResponse = { success: '0', message: 'Unexpected AJAX Response' };
		return emptyResponse;
	} // END getAjaxResponse()
}); // END jQuery
