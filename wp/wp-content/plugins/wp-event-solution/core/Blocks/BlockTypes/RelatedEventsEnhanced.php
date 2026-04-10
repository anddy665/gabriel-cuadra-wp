<?php

namespace Eventin\Blocks\BlockTypes;

defined( 'ABSPATH' ) || exit;
    use Etn\Core\Event\Event_Model;
    use Eventin\Blocks\BlockTypes\AbstractBlock;
    use Wpeventin;

    /**
 * Related Events Gutenberg block
 */
    class RelatedEventsEnhanced extends AbstractBlock
    {
    /**
     * Block name.
     *
     * @var string
     */
    protected $block_name = 'related-events';

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
        $container_class = ! empty($attributes['containerClassName']) ? $attributes['containerClassName'] : '';
        $styles          = ! empty($attributes['styles']) ? $attributes['styles'] : [];
        $style_variant   = ! empty($attributes['styleVariant']) ? sanitize_key($attributes['styleVariant']) : 'style-1';

        $allowed_variants = ['style-1', 'style-2', 'style-3'];
        if (! in_array($style_variant, $allowed_variants, true)) {
            $style_variant = 'style-1';
        }

        if ($this->is_editor()) {
            $event_id = ! empty($attributes['eventId']) ? intval($attributes['eventId']) : 0;
        } else if ('etn-template' == get_post_type(get_the_ID())) {
            $template = new \Eventin\Template\TemplateModel(get_the_ID());
            $event_id = $template->get_preview_event_id();
        } else {
            $event_id = get_the_ID();
        }

        $event = new Event_Model($event_id);

        $related_events = $event->get_related_events();

        ob_start();
        ?>
        <?php
            // Generate CSS with !important to override SCSS
                    $frontend_css = $this->generate_frontend_css($styles, $container_class);
                    if (! empty($frontend_css)) {
                        // Add !important to common properties that need to override SCSS
                        // Include typography properties: word-spacing, text-transform, text-decoration
                        $important_properties = ['width', 'height', 'font-size', 'color', 'font-weight', 'line-height', 'letter-spacing', 'word-spacing', 'text-transform', 'text-decoration', 'margin', 'padding', 'text-align', 'font-family', 'border-width', 'border-color', 'border-style', 'border-radius', 'z-index', 'box-shadow', 'left', 'right', 'top', 'bottom', 'position'];
                        foreach ($important_properties as $prop) {
                            $frontend_css = preg_replace(
                                "/({$prop})\s*:\s*([^;!]+?)(?!\s*!important)\s*;/im",
                                "$1: $2 !important;",
                                $frontend_css
                            );
                        }
                        echo '<style>' . $frontend_css . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS generated from block editor attributes; HTML escaping would break styles.
                    }
                ?>
        <?php
            // Load the style template
                    $style_template = Wpeventin::templates_dir() . 'event/parts/styles/related-events/' . $style_variant . '.php';
                    if (file_exists($style_template)) {
                        require $style_template;
                    } else {
                        // Fallback to default template if style template doesn't exist
                        require_once Wpeventin::templates_dir() . 'event/parts/related-events.php';
                    }
                ?>
        <?php

                    return ob_get_clean();
                }
        }
