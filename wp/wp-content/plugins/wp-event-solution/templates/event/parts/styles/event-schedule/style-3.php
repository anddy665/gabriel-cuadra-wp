<?php

    use \Etn\Utils\Helper;

    defined('ABSPATH') || exit;

    $etn_event_schedule = $event->etn_event_schedule;
    date_default_timezone_set('UTC'); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.timezone_change_date_default_timezone_set -- intentional UTC context for schedule sorting.

    if (is_array($etn_event_schedule) && ! empty($etn_event_schedule)) {
    $args = [
        'post__in'         => $etn_event_schedule,
        'orderby'          => 'post_date',
        'order'            => 'asc',
        'post_type'        => 'etn-schedule',
        'post_status'      => 'publish',
        'suppress_filters' => false,
    ];

    $schedule_query = get_posts($args);
    ?>
<!-- schedule tab start -->
<div class="schedule-tab-wrapper etn-tab-wrapper schedule-style-1 schedule-style-three
<?php echo esc_attr($container_class); ?>">
    <div class="eventin-block-container schedule-style-two">
        <div class="schedule-block-wrapper">
            <ul class='etn-nav'>
                <?php
                    $i = -1;
                        if (is_array($schedule_query)) {
                            foreach ($schedule_query as $post):
                                $single_schedule_id = $post->ID;
                                $i++;
                                $schedule_meta = get_post_meta($single_schedule_id);
                                $schedule_date = ! empty($schedule_meta['etn_schedule_date'][0]) ? date_i18n("d M", strtotime($schedule_meta['etn_schedule_date'][0])) : "";
                                $active_class  = (($i == 0) ? 'etn-active' : ' ');
                        ?>
		                        <li>
		                            <a href='#' class='etn-tab-a		                                                        	                                                        															                                                                														                                                                													                                                                												                                                                											                                                                										                                                                									                                                                								                                                                							                                                                						                                                                					                                                                				                                                                			                                                                		                                                                	                                                                					                                                                    				                                                                    			                                                                    		                                                                    	                                                                     <?php echo esc_attr($active_class); ?>' data-id='tab<?php echo esc_attr($i); ?>'>
		                                <span class=etn-day><?php echo esc_html($post->post_title); ?></span>
		                                <span class='etn-date'><?php echo esc_html($schedule_date); ?></span>
		                            </a>
		                        </li>
		                    <?php
                                    endforeach;
                                    }
                                ?>
            </ul>
            <div class='etn-tab-content clearfix etn-schedule-wrap'>
                <?php
                    $j = -1;
                        if (is_array($schedule_query)) {
                            foreach ($schedule_query as $post):
                                $single_schedule_id = $post->ID;
                                $j++;
                                $schedule_meta   = get_post_meta($single_schedule_id);
                                $schedule_topics_raw = etn_safe_decode($schedule_meta['etn_schedule_topics'][0] ?? '');
                                $schedule_topics = is_array($schedule_topics_raw) ? $schedule_topics_raw : [];
                                $schedule_date   = ! empty($schedule_meta['etn_schedule_date'][0]) ? date_i18n("d M", strtotime($schedule_meta['etn_schedule_date'][0])) : "";
                                $active_class    = (($j == 0) ? 'tab-active' : ' ');
                        ?>
		                            <!-- start repeatable item -->
		                            <div class='etn-tab		                                               	                                               				                                               			                                               		                                               	                                               				                                               			                                               		                                               	                                               					                                               				                                               			                                               		                                               	                                               		                                               	                                               												                                                                                                                                               											                                                                                                                                               										                                                                                                                                               									                                                                                                                                               								                                                                                                                                               							                                                                                                                                               						                                                                                                                                               					                                                                                                                                               				                                                                                                                                               			                                                                                                                                               		                                                                                                                                               	                                                                                                                                               																														                   																													                   																												                   																											                   																										                   																									                   																								                   																							                   																						                   																					                   																				                   																			                   																		                   																	                                    																                                    															                                    														                                    													                                    												                                    											                                    										                                    									                                    								                                    							                                    						                                    					                                    				                                    			                                    		                                     <?php echo esc_attr($active_class); ?>' data-id='tab<?php echo esc_attr($j); ?>'>
		                                <?php $etn_tab_time_format = (isset($event_options["time_format"]) && $event_options["time_format"] == '24') ? "H:i" : get_option('time_format');
                                                        if (is_array($schedule_topics) && ! empty($schedule_topics)) {
                                                            $topic_index = -1;
                                                            foreach ($schedule_topics as $topic):
                                                                $topic_index++;
                                                                $etn_schedule_topic      = (isset($topic['etn_schedule_topic']) ? $topic['etn_schedule_topic'] : '');
                                                                $etn_schedule_start_time = ! empty($topic['etn_shedule_start_time']) ? date_i18n($etn_tab_time_format, strtotime($topic['etn_shedule_start_time'])) : '';
                                                                $etn_schedule_end_time   = ! empty($topic['etn_shedule_end_time']) ? date_i18n($etn_tab_time_format, strtotime($topic['etn_shedule_end_time'])) : '';
                                                                $etn_schedule_room       = (isset($topic['etn_shedule_room']) ? $topic['etn_shedule_room'] : '');
                                                                $etn_schedule_objective  = (isset($topic['etn_shedule_objective']) ? $topic['etn_shedule_objective'] : '');
                                                                $etn_schedule_speaker    = (isset($topic['speakers']) ? $topic['speakers'] : []);
                                                                $dash_sign               = (! empty($etn_schedule_start_time) && ! empty($etn_schedule_end_time)) ? " - " : " ";
                                                                // 2nd item (index 1) should be expanded by default
                                                                $is_expanded   = ($topic_index === 1);
                                                                $aria_expanded = $is_expanded ? 'true' : 'false';
                                                    ?>
				                                    <div class='etn-single-schedule-item etn-row'>
				                                        <div class='etn-schedule-info etn-col-sm-3'>
				                                            <?php
                                                            if (! empty($etn_schedule_start_time) || ! empty($etn_schedule_end_time)) {?>
				                                        <span class='etn-schedule-time'>
				                                            <?php echo esc_html($etn_schedule_start_time) . $dash_sign . esc_html($etn_schedule_end_time); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				                                        </span>
				                                        <?php
                                                            }
                                                                    ?>
				                                    </div>
				                                    <div class='etn-schedule-content etn-col-sm-9'>
				                                        <div class='etn-schedule-item-header'>
				                                            <h4 class='etn-title' style='margin: 0; flex: 1;'><?php echo esc_html($etn_schedule_topic); ?></h4>
				                                            <button class='etn-schedule-toggle' type='button' aria-expanded='<?php echo esc_attr($aria_expanded); ?>' aria-label='<?php echo esc_attr__('Toggle schedule details', 'eventin'); ?>'>
				                                                <svg width='20' height='20' viewBox='0 0 20 20' fill='none' xmlns='http://www.w3.org/2000/svg'>
				                                                    <path d='M5 7.5L10 12.5L15 7.5' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/>
				                                                </svg>
				                                            </button>
				                                        </div>
				                                            <div class='etn-schedule-item-content<?php echo $is_expanded ? ' expanded' : ''; ?>'>
				                                            <p><?php echo Helper::kses($etn_schedule_objective); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
				                                            <?php
                                                                        $etn_show_speaker_with_schedule = get_post_meta($event_id, 'etn_select_speaker_schedule_type', true);
                                                                                    $etn_show_speaker_with_schedule = ! empty($etn_show_speaker_with_schedule) ? $etn_show_speaker_with_schedule : 'schedule_with_speaker';
                                                                        ?>
				                                            <?php if ($etn_show_speaker_with_schedule === 'schedule_with_speaker'): ?>
				                                            <!-- Show speaker block if it's selected from event meta -->
				                                            <div class='etn-schedule-content'>
				                                                <div class='etn-schedule-speaker'>
				                                                <?php
                                                                    $speaker_avatar = apply_filters("etn/speakers/avatar", \Wpeventin::assets_url() . "images/avatar.jpg");
                                                                                        if (is_array($etn_schedule_speaker) && ! empty($etn_schedule_speaker)) {
                                                                                            foreach ($etn_schedule_speaker as $key => $value) {
                                                                                                $etn_speaker_permalink = Helper::get_author_page_url_by_id($value);
                                                                                                $etn_speaker_image     = get_user_meta($value, 'image', true);
                                                                                                $speaker_title         = get_the_author_meta('display_name', $value);
                                                                                                $speaker_designation   = get_user_meta($value, 'etn_speaker_designation', true);
                                                                                    ?>
				                                        <div class='etn-schedule-single-speaker'>
				                                            <a href='<?php echo esc_url($etn_speaker_permalink); ?>'
				                                            aria-label="<?php echo esc_html($speaker_title); ?>">
				                                            <?php if ($etn_speaker_image): ?>
				                                                <img src="<?php echo esc_url($etn_speaker_image); ?>" alt="<?php echo esc_attr($speaker_title); ?>" height="50" width="50">
				                                                        <?php endif; ?>
		                                        </a>
		                                        <div class='schedule-speaker-info'>
		                                            <p class='schedule-speaker-title'><?php echo esc_html($speaker_title); ?></p>
		                                            <p class='schedule-speaker-designation'><?php echo esc_html($speaker_designation); ?></p>
		                                        </div>
		                                    </div>
		                                    <?php
                                                    }
                                                                }
                                                        ?>
		                                </div>
		                            </div>
		                            <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                        endforeach;
                                }
                            ?>
                </div>
                <!-- end repeatable item -->
                <?php
                    endforeach;
                        }
                    wp_reset_postdata(); ?>
            </div>
        </div>
    </div>
</div>
<!-- schedule tab end -->
<script>
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        const EXTRA_HEIGHT = 20; // extra px to add to expanded content
        // Get all schedule items within each tab
        const scheduleTabs = document.querySelectorAll('.schedule-style-1 .etn-tab');

        scheduleTabs.forEach(function(tab) {
            const scheduleItems = tab.querySelectorAll('.etn-single-schedule-item');

            scheduleItems.forEach(function(item) {
                const toggleBtn = item.querySelector('.etn-schedule-toggle');
                const content = item.querySelector('.etn-schedule-item-content');

                if (toggleBtn && content) {
                    // Initialize expanded items on page load
                    const isInitiallyExpanded = toggleBtn.getAttribute('aria-expanded') === 'true';
                    if (isInitiallyExpanded) {
                        content.style.height = 'auto';
                        const height = content.scrollHeight;
                        content.style.height = (height + EXTRA_HEIGHT) + 'px';
                        content.style.opacity = '1';
                        content.style.paddingTop = '16px';
                        content.style.paddingBottom = '16px';
                    } else {
                        content.style.height = '0px';
                        content.style.opacity = '0';
                        content.style.paddingTop = '0px';
                        content.style.paddingBottom = '0px';
                    }
                    toggleBtn.addEventListener('click', function() {
                        const isExpanded = this.getAttribute('aria-expanded') === 'true';
                        const newExpanded = !isExpanded;

                        // If clicking to expand, collapse all other items in the same tab
                        if (newExpanded) {
                            scheduleItems.forEach(function(otherItem) {
                                if (otherItem !== item) {
                                    const otherToggleBtn = otherItem.querySelector('.etn-schedule-toggle');
                                    const otherContent = otherItem.querySelector('.etn-schedule-item-content');

                                    if (otherToggleBtn && otherContent) {
                                        otherToggleBtn.setAttribute('aria-expanded', 'false');
                                        // Collapse other items smoothly
                                        otherContent.style.height = '0px';
                                        otherContent.style.opacity = '0';
                                        otherContent.style.paddingTop = '0px';
                                        otherContent.style.paddingBottom = '0px';
                                    }
                                }
                            });
                        }

                        // Toggle current item with smooth animation
                        this.setAttribute('aria-expanded', newExpanded);

                        if (newExpanded) {
                            // Expand: Set to auto to get actual height, then set specific height (+ extra)
                            content.style.height = 'auto';
                            const height = content.scrollHeight;
                            const targetHeight = height + EXTRA_HEIGHT; // add extra height
                            content.style.height = '0px';
                            content.style.opacity = '0';
                            content.style.paddingTop = '0px';
                            content.style.paddingBottom = '0px';

                            // Force reflow
                            content.offsetHeight;

                            // Animate to full height
                            requestAnimationFrame(function() {
                                content.style.height = targetHeight + 'px';
                                content.style.opacity = '1';
                                content.style.paddingTop = '16px';
                                content.style.paddingBottom = '16px';
                            });
                        } else {
                            // Collapse: start from the current computed height (includes extra) and animate to 0
                            const computedHeight = parseFloat(getComputedStyle(content).height) || (content.scrollHeight + EXTRA_HEIGHT);
                            content.style.height = computedHeight + 'px';

                            // Force reflow
                            content.offsetHeight;

                            // Animate to 0
                            requestAnimationFrame(function() {
                                content.style.height = '0px';
                                content.style.opacity = '0';
                                content.style.paddingTop = '0px';
                                content.style.paddingBottom = '0px';
                            });
                        }
                    });
                }
            });
        });
    });
})();
</script>
<?php
}
