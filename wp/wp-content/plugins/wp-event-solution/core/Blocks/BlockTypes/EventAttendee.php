<?php

namespace Eventin\Blocks\BlockTypes;

defined( 'ABSPATH' ) || exit;
    use Etn\Core\Event\Event_Model;
    use Eventin\Blocks\BlockTypes\AbstractBlock;
    use Wpeventin;

    /**
     * Event Attendee Gutenberg block
     */
    class EventAttendee extends AbstractBlock
    {
        /**
         * Block namespace.
         *
         * @var string
         */
        protected $namespace = 'eventin-pro';

        /**
         * Block name.
         *
         * @var string
         */
        protected $block_name = 'event-attendee';

        /**
         * Include and render the block
         *
         * @param   array  $attributes  Block attributes. Default empty array
         * @param   string  $content     Block content. Default empty string
         * @param   WP_Block  $block       Block instance
         *
         * @return  string Rendered block type output
         */
        protected function render($attributes, $content, $block)
        {
            $container_class  = ! empty($attributes['containerClassName']) ? $attributes['containerClassName'] : '';
            $items_per_row    = ! empty($attributes['itemsPerRow']) ? intval($attributes['itemsPerRow']) : 3;
            $styles           = ! empty($attributes['styles']) ? $attributes['styles'] : [];
            $style_variant    = ! empty($attributes['styleVariant']) ? sanitize_key($attributes['styleVariant']) : 'style-1';
            $allowed_variants = ['style-1', 'style-2'];

            if (! in_array($style_variant, $allowed_variants, true)) {
                $style_variant = 'style-1';
            }

            if ($this->is_editor()) {
                $event_id = ! empty($attributes['eventId']) ? intval($attributes['eventId']) : 0;

                if ($event_id == 0) {
                    $template = new \Eventin\Template\TemplateModel(get_the_ID());
                    $event_id = $template->get_preview_event_id();
                }
            } else if ('etn-template' == get_post_type(get_the_ID())) {
                $template = new \Eventin\Template\TemplateModel(get_the_ID());
                $event_id = $template->get_preview_event_id();
            } else {
                $event_id = get_the_ID();
            }

            $event           = new Event_Model($event_id);
            $event_attendees = $event->get_attendees( ['publish', 'trash'], apply_filters( 'etn_block_attendees_limit', 200 ) );

            $attendee_page_url = get_post_meta($event_id, 'attende_page_link', true);
            ob_start();
        ?>
        <?php
            // Generate CSS with !important for attendee block to override SCSS
                    $frontend_css = $this->generate_frontend_css($styles, $container_class);
                    if (! empty($frontend_css)) {
                        // Add !important to common properties that need to override SCSS
                        $important_properties = ['width', 'height', 'font-size', 'color', 'font-weight', 'line-height', 'margin', 'padding', 'border-radius'];
                        foreach ($important_properties as $prop) {
                            $frontend_css = preg_replace(
                                "/({$prop}):\s*([^;!]+?)(?!\s*!important)\s*;/im",
                                "$1: $2 !important;",
                                $frontend_css
                            );
                        }

                        // Ensure img always uses 100% width/height to fill container, overriding any saved styles
                        $avatar_img_selector = ".{$container_class} .etn-attendee-item .etn-attendee-avatar img";
                        $frontend_css .= "\n{$avatar_img_selector} {\n";
                        $frontend_css .= "  width: 100% !important;\n";
                        $frontend_css .= "  height: 100% !important;\n";
                        $frontend_css .= "}\n";
                        
                        echo '<style>' . $frontend_css . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS generated from block editor attributes; HTML escaping would break styles.
                    }
                ?>
        <?php
            $items_per_row  = $items_per_row; // Make available to template
                    $style_variant  = $style_variant;
                    $style_template = Wpeventin::templates_dir() . 'event/parts/styles/event-attendee/' . $style_variant . '.php';
                    require $style_template;
                    return ob_get_clean();
                }
        }
