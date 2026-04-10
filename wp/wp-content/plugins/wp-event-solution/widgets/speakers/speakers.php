<?php

namespace Elementor;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use \Etn\Utils\Helper;

defined( 'ABSPATH' ) || exit;

class Etn_Speakers extends Widget_Base {

    /**
     * Retrieve the widget name.
     * @return string Widget name.
     */
    public function get_name() {
        return 'etn-speaker';
    }

    /**
     * Retrieve the widget title.
     * @return string Widget title.
     */
    public function get_title() {
        return esc_html__( 'Eventin speaker', 'eventin' );
    }

    /**
     * Retrieve the widget icon.
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-user-circle-o';
    }

    /**
     * Retrieve the widget category.
     * @return string Widget category.
     */
    public function get_categories() {
        return ['etn-event'];
    }

    protected function register_controls() {
        // Start of speaker section
        $this->start_controls_section(
            'section_tab',
            [
                'label' => esc_html__( 'Eventin Speakers', 'eventin' ),
            ]
        );

        $this->add_control(
            'speaker_style',
            [
                'label'   => esc_html__( 'Speaker Style', 'eventin' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'speaker-1',
                'options' => [
                    'speaker-1' => esc_html__( 'Speaker 1', 'eventin' ),
                    'speaker-2' => esc_html__( 'Speaker 2', 'eventin' ),
                    'speaker-3' => esc_html__( 'Speaker 3', 'eventin' ),
                    'speaker-4' => esc_html__( 'Speaker 4', 'eventin' ),

                ],
            ]
        );

        $this->add_control(
            'speaker_id',
            [
                'label'     => esc_html__( 'Speaker', 'eventin' ),
                'type'      => Controls_Manager::SELECT2,
                'options'   => $this->get_speakers(),
                'condition' => ['speaker_style' => ['speaker-1']],
            ]
        );

        $this->add_control(
            'speakers_category',
            [
                'label'     => esc_html__( 'Speaker Category', 'eventin' ),
                'type'      => Controls_Manager::SELECT2,
                'multiple'  => true,
                'options'   => $this->get_speakers_category(),
                'condition' => ['speaker_style' => ['speaker-2','speaker-3','speaker-4']],
            ]
        );

        $this->add_control(
            'etn_speaker_count',
            [
                'label'     => esc_html__( 'Speaker Count', 'eventin' ),
                'type'      => Controls_Manager::NUMBER,
                'default'   => '6',
                'condition' => ['speaker_style' => ['speaker-2','speaker-3','speaker-4']],
            ]
        );

        $this->add_control(
            'etn_speaker_col',
            [
                'label'     => esc_html__( 'Speaker Column', 'eventin' ),
                'type'      => Controls_Manager::SELECT,
                'default'   => '4',
                'options'   => [
                    '3' => esc_html__( '4 Column ', 'eventin' ),
                    '4' => esc_html__( '3 Column', 'eventin' ),
                    '6' => esc_html__( '2 Column', 'eventin' ),

                ],
                'condition' => ['speaker_style' => ['speaker-2','speaker-3','speaker-4']],
            ]
        );

        $this->add_control(
            'orderby',
            [
                'label'     => esc_html__( 'Order Speaker By', 'eventin' ),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'post_date',
                'options'   => [
                    'ID'        => esc_html__( 'Id', 'eventin' ),
                    'title'     => esc_html__( 'Title', 'eventin' ),
                    'post_date' => esc_html__( 'Post Date', 'eventin' ),
                ],
                'condition' => ['speaker_style' => ['speaker-2','speaker-3','speaker-4']],
            ]
        );

        $this->add_control(
            'etn_speaker_order',
            [
                'label'     => esc_html__( 'Speaker Order', 'eventin' ),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'DESC',
                'options'   => [
                    'DESC' => esc_html__( 'Descending', 'eventin' ),
                    'ASC'  => esc_html__( 'Ascending', 'eventin' ),
                ],
                'condition' => ['speaker_style' => ['speaker-2','speaker-3','speaker-4']],
            ]
        );

        $this->add_control(
            'enable_pagination',
            [
                'label'   => esc_html__( 'Enable Pagination', 'eventin' ),
                'type'    => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'eventin' ),
                'label_off' => esc_html__( 'No', 'eventin' ),
                'return_value' => 'yes',
                'default' => 'no',
                'condition' => ['speaker_style' => ['speaker-2','speaker-3','speaker-4']],
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label'   => esc_html__( 'Speakers Per Page', 'eventin' ),
                'type'    => Controls_Manager::NUMBER,
                'default' => 6,
                'min'     => 1,
                'max'     => 50,
                'condition' => [
                    'speaker_style' => ['speaker-2','speaker-3','speaker-4'],
                    'enable_pagination' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'show_designation_style_4',
            [
                'label' => esc_html__('Show Designation', 'eventin'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'eventin'),
                'label_off' => esc_html__('Hide', 'eventin'),
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => ['speaker_style' => ['speaker-4']],
            ]
        );

        $this->end_controls_section();

        // End of speaker section

         // Start of Speaker Item Wrapper Style section (first in style tab)
         $this->start_controls_section(
            'etn_speaker_item_wrapper_section',
            [
                'label' => esc_html__( 'Speaker Item Wrapper', 'eventin' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_responsive_control(
            'etn_speaker_item_padding',
            [
                'label'      => esc_html__( 'Padding', 'eventin' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .etn-speaker-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name'     => 'etn_speaker_item_border',
                'label'    => esc_html__( 'Border', 'eventin' ),
                'selector' => '{{WRAPPER}} .etn-speaker-item',
            ]
        );
        $this->add_responsive_control(
            'etn_speaker_item_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'eventin' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .etn-speaker-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'etn_speaker_item_box_shadow',
                'label'    => esc_html__( 'Box Shadow', 'eventin' ),
                'selector' => '{{WRAPPER}} .etn-speaker-item',
            ]
        );
        $this->add_control(
            'etn_speaker_item_bg_color',
            [
                'label'     => esc_html__( 'Background Color', 'eventin' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .etn-speaker-item' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        $this->end_controls_section();

        // Start of title section
        $this->start_controls_section(
            'title_section',
            [
                'label' => esc_html__( 'Title Section', 'eventin' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        //control for title typography
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'ent_title_typography',
                'label'    => esc_html__( 'Title Typography', 'eventin' ),
                'selector' => '{{WRAPPER}} .etn-speaker-content .etn-title',
            ]
        );

        //start of title color tabs (normal and hover)
        $this->start_controls_tabs(
            'etn_title_tabs'
        );

        //start of title normal color tab
        $this->start_controls_tab(
            'etn_title_normal_tab',
            [
                'label' => esc_html__( 'Normal', 'eventin' ),
            ]
        );
        $this->add_control(
            'etn_title_color',
            [
                'label'     => esc_html__( 'Title color', 'eventin' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .etn-speaker-content .etn-title'   => 'color: {{VALUE}};',
                    '{{WRAPPER}} .etn-speaker-content .etn-title a' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->end_controls_tab();

        // end of title normal color tab

        //start of title hover color tab
        $this->start_controls_tab(
            'etn_title_hover_tab',
            [
                'label' => esc_html__( 'Hover', 'eventin' ),
            ]
        );
        $this->add_control(
            'etn_title_hover_color',
            [
                'label'     => esc_html__( 'Title Hover color', 'eventin' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .etn-speaker-item:hover .etn-speaker-content .etn-title:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .etn-speaker-item:hover .etn-speaker-content .etn-title a'     => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->end_controls_tab();
        //end of title hover color tab

        $this->end_controls_tabs();

        //end of title color tabs (normal and hover)

        //start of title margin control
        $this->add_responsive_control(
            'title_margin',
            [
                'label'      => esc_html__( 'Title margin', 'eventin' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .etn-speaker-content .etn-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        //end of title margin control

        $this->end_controls_section();

        // end of title section

        // Start of designation section
        $this->start_controls_section(
            'desginnation_section',
            [
                'label' => esc_html__( 'Designation Section', 'eventin' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        //control for designation typography
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'etn_designation_typography',
                'label'    => esc_html__( 'Designation Typography', 'eventin' ),
                'selector' => '{{WRAPPER}} .etn-speaker-content p',
            ]
        );

        //start of designation color tabs (normal and hover)
        $this->start_controls_tabs(
            'etn_desc_tabs'
        );

        //start of designation normal color tab
        $this->start_controls_tab(
            'etn_desc_normal_tab',
            [
                'label' => esc_html__( 'Normal', 'eventin' ),
            ]
        );
        $this->add_control(
            'etn_desc_color',
            [
                'label'     => esc_html__( 'Designation color', 'eventin' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .etn-speaker-content p' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->end_controls_tab();

        //end of designation normal color tab

        //start of designation hover color tab
        $this->start_controls_tab(
            'etn_desc_hover_tab',
            [
                'label' => esc_html__( 'Hover', 'eventin' ),
            ]
        );
        $this->add_control(
            'etn_desc_hover_color',
            [
                'label'     => esc_html__( 'Designation Hover color', 'eventin' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .etn-speaker-item:hover .etn-speaker-content p' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->end_controls_tab();
        //end of designation hover color tab

        $this->end_controls_tabs();

        //end of designation color tabs (normal and hover)

        //control for designation margin
        $this->add_responsive_control(
            'etn_desc_margin',
            [
                'label'      => esc_html__( 'Description margin', 'eventin' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .etn-speaker-content p' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // End of designation section

        // Start of email section (only for style 4)
        $this->start_controls_section(
            'email_section',
            [
                'label' => esc_html__( 'Email Section', 'eventin' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => ['speaker_style' => ['speaker-4']],
            ]
        );
        //control for email typography
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'etn_email_typography',
                'label'    => esc_html__( 'Email Typography', 'eventin' ),
                'selector' => '{{WRAPPER}} .etn-speaker-content .etn-email',
            ]
        );
        //start of email color tabs (normal and hover)
        $this->start_controls_tabs(
            'etn_email_tabs'
        );
        //start of email normal color tab
        $this->start_controls_tab(
            'etn_email_normal_tab',
            [
                'label' => esc_html__( 'Normal', 'eventin' ),
            ]
        );
        $this->add_control(
            'etn_email_color',
            [
                'label'     => esc_html__( 'Email color', 'eventin' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .etn-speaker-content .etn-email' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->end_controls_tab();
        //start of email hover color tab
        $this->start_controls_tab(
            'etn_email_hover_tab',
            [
                'label' => esc_html__( 'Hover', 'eventin' ),
            ]
        );
        $this->add_control(
            'etn_email_hover_color',
            [
                'label'     => esc_html__( 'Email Hover color', 'eventin' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .etn-speaker-item:hover .etn-speaker-content .etn-email' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();
        //end of email color tabs (normal and hover)
        //control for email margin
        $this->add_responsive_control(
            'etn_email_margin',
            [
                'label'      => esc_html__( 'Email margin', 'eventin' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .etn-speaker-content .etn-email' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->end_controls_section();

        // Start of social section
        $this->start_controls_section(
            'social_section',
            [
                'label' => esc_html__( 'Social Section', 'eventin' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Start of social-bg color tabs (Normal and Hover)
        $this->start_controls_tabs(
            'etn_social_tabs'
        );

        //start of social-bg normal color tab
        $this->start_controls_tab(
            'etn_social_normal_tab',
            [
                'label' => esc_html__( 'Normal', 'eventin' ),
            ]
        );
        $this->add_control(
            'etn_social_color',
            [
                'label'     => esc_html__( 'Social BG color', 'eventin' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .etn-speaker-item .etn-speaker-thumb .etn-speakers-social a' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .etn-speaker-item.style-3 .etn-speaker-content .etn-speakers-social a' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        $this->end_controls_tab();

        //end of social-bg normal color tab

        //start of social-bg hover color tab
        $this->start_controls_tab(
            'etn_social_hover_tab',
            [
                'label' => esc_html__( 'Hover', 'eventin' ),
            ]
        );
        $this->add_control(
            'etn_social_hover_color',
            [
                'label'     => esc_html__( 'Social Hover BG color', 'eventin' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .etn-speaker-item .etn-speaker-thumb .etn-speakers-social a:hover' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .etn-speaker-item.style-3 .etn-speaker-content .etn-speakers-social a:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        $this->end_controls_tab();
        //end of social-bg hover color tab

        $this->end_controls_tabs();
        // End of social-bg color tabs (Normal and Hover)

        $this->end_controls_section();

        // End of social section

        // Pagination style section
        $this->start_controls_section(
            'pagination_section',
            [
                'label' => esc_html__( 'Pagination Style', 'eventin' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => ['enable_pagination' => 'yes'],
            ]
        );

        $this->add_control(
            'pagination_alignment',
            [
                'label' => esc_html__( 'Alignment', 'eventin' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__( 'Left', 'eventin' ),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'eventin' ),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__( 'Right', 'eventin' ),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .etn-pagination-wrapper' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'pagination_spacing',
            [
                'label' => esc_html__( 'Spacing', 'eventin' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 20,
                ],
                'selectors' => [
                    '{{WRAPPER}} .etn-pagination-wrapper' => 'margin-top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'pagination_typography',
                'label' => esc_html__( 'Typography', 'eventin' ),
                'selector' => '{{WRAPPER}} .etn-pagination-link',
            ]
        );

        $this->add_control(
            'pagination_color',
            [
                'label' => esc_html__( 'Text Color', 'eventin' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .etn-pagination-link' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'pagination_bg_color',
            [
                'label' => esc_html__( 'Background Color', 'eventin' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .etn-pagination-link' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'pagination_current_color',
            [
                'label' => esc_html__( 'Current Page Color', 'eventin' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .etn-pagination-current' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'pagination_current_bg',
            [
                'label' => esc_html__( 'Current Page Background', 'eventin' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .etn-pagination-current' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Start of advance style section
        $this->start_controls_section(
            'advance_section',
            [
                'label' => esc_html__( 'Advance Section', 'eventin' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        // tab controls start
        $this->start_controls_tabs(
            'etn_content_box_tabs'
        );

        $this->start_controls_tab(
            'etn_content_box_normal_tab',
            [
                'label' => esc_html__( 'Normal', 'eventin' ),
            ]
        );
        $this->add_control(
            'etn_content_box_color',
            [
                'label'     => esc_html__( 'Content box BG color', 'eventin' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .etn-speaker-item .etn-speaker-content' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'content_box_border',
                'label'    => esc_html__( 'Border', 'eventin' ),
                'selector' => '{{WRAPPER}} .etn-speaker-item .etn-speaker-content',
            ]
        );
        $this->end_controls_tab();

        $this->start_controls_tab(
            'etn_content_box_hover_tab',
            [
                'label' => esc_html__( 'Hover', 'eventin' ),
            ]
        );
        $this->add_control(
            'etn_content_box_hover_color',
            [
                'label'     => esc_html__( 'Box Hover BG color', 'eventin' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .etn-speaker-item:hover .etn-speaker-content' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'content_box_hover_border',
                'label'    => esc_html__( 'BorderHover', 'eventin' ),
                'selector' => '{{WRAPPER}} .etn-speaker-item:hover .etn-speaker-content',
            ]
        );
        $this->end_controls_tab();

        $this->end_controls_tabs();
        // tabs control end

        $this->add_responsive_control(
            'etn_content_box_padding',
            [
                'label'      => esc_html__( 'Content Box Padding', 'eventin' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .etn-speaker-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
        // End of advance style section

    }

    protected function render() {
        $settings   = $this->get_settings();
        $style      = $settings["speaker_style"];
        $speaker_id = $settings["speaker_id"];

        $etn_speaker_count  = $settings["etn_speaker_count"];
        $etn_speaker_col    = $settings["etn_speaker_col"];
        $speakers_category  = $settings["speakers_category"];
        $etn_speaker_order  = $settings["etn_speaker_order"];
        $orderby            = $settings["orderby"];
        $enable_pagination  = (isset($settings["enable_pagination"]) ? $settings["enable_pagination"] : 'no');
        $posts_per_page     = (isset($settings["posts_per_page"]) ? $settings["posts_per_page"] : 6);
        $orderby_meta      = null;

        // Handle pagination
        $speaker_paged = 1;
        if ($enable_pagination === 'yes') {
            // Register the custom query var
            add_filter('query_vars', [$this, 'register_query_vars']);
            
            // Get the page number from URL parameter
            $speaker_paged = isset($_GET['speaker_paged']) ? absint($_GET['speaker_paged']) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public speaker pagination param; value cast to integer via absint().
            $speaker_paged = max(1, $speaker_paged);
            
            // Set the query var for WordPress
            set_query_var('speaker_paged', $speaker_paged);
        }

        
        // Use posts_per_page for pagination, otherwise use etn_speaker_count
        $speakers_to_show = ($enable_pagination === 'yes') ? $posts_per_page : $etn_speaker_count;

        $sanitize_filename = sanitize_file_name($style);
        $style             = !empty($sanitize_filename) ? $sanitize_filename : 'speaker-1';

        if ( file_exists( \Wpeventin::plugin_dir() . "widgets/speakers/style/{$style}.php") ) {
            include \Wpeventin::plugin_dir() . "widgets/speakers/style/{$style}.php";
        }
    }

    protected function get_speakers() {
        return Helper::get_speakers();
    }
    
    /**
     * Register custom query vars for pagination
     */
    public function register_query_vars($vars) {
        $vars[] = 'speaker_paged';
        return $vars;
    }

    protected function get_speakers_category() {
        return Helper::get_speakers_category();
    }

}
