<?php
    namespace Eventin\Blocks\BlockTypes;

    use Etn\Core\Event\Event_Model;
    use Eventin\Blocks\BlockTypes\AbstractBlock;
    use Wpeventin;

    /**
     * Buy Event Ticket Gutenberg block
     */
    class BuyTicket extends AbstractBlock
    {
        /**
         * Block name.
         *
         * @var string
         */
        protected $block_name = 'buy-ticket';

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
            $style_variant = ! empty($attributes['styleVariant']) ? $attributes['styleVariant'] : 'style-1';

            $container_class = ! empty($attributes['containerClassName']) ? $attributes['containerClassName'] : '';
            $styles          = ! empty($attributes['styles']) ? $attributes['styles'] : [];
            // Check if we're in editor/admin
            if ($this->is_editor()) {
                return '<div style="border: 2px dashed #ccc;
                       border-radius: 8px;
                       padding: 20px;
                       text-align: center;
                       background: #f8f9fa;
                       color: #666;
                       margin: 10px 0;">
                <svg style="width: 24px; height: 24px; margin-bottom: 8px;" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M13 13h-2V7h2v6zm0 4h-2v-2h2v2zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
                </svg>
                <p style="margin: 0; font-size: 14px; font-weight: 500;">' .
                esc_html__("This Block (Buy Ticket) is not available for preview in Edit Mode. However, it will render correctly on the frontend for customers.", 'eventin') .
                    '</p>
            </div>';
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

            ob_start();

        ?>
        <?php echo $this->render_frontend_css($styles, esc_attr($container_class)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <?php
            require Wpeventin::templates_dir() . 'event/parts/buy-ticket.php';
                ?>

        <?php

                    return ob_get_clean();
            }
        }