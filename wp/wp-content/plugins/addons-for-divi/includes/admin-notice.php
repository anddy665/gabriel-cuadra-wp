<?php

namespace DiviTorqueLite;

defined('ABSPATH') || exit;

class Admin_Notice
{
    private $notice_key = 'dtl_black_friday_notice_dismissed';
    private $bf_launch_date;
    private $bf_end_date;
    private $cm_start_date;
    private $cm_end_date;

    public function __construct()
    {
        // Black Friday: November 27 - November 30, 2025
        $this->bf_launch_date = strtotime('2025-11-27');
        $this->bf_end_date = strtotime('2025-11-30 23:59:59');

        // Cyber Monday / Holiday Sale: December 1 - December 31, 2025
        $this->cm_start_date = strtotime('2025-12-01');
        $this->cm_end_date = strtotime('2025-12-31 23:59:59');

        add_action('admin_notices', [$this, 'display_sale_notice']);
        add_action('wp_ajax_dtl_dismiss_notice', [$this, 'dismiss_notice']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_notice_styles']);
    }

    public function should_show_notice()
    {
        // Don't show if user dismissed it
        if (get_user_meta(get_current_user_id(), $this->notice_key, true)) {
            return false;
        }

        // Only show during sale periods
        $now = current_time('timestamp');
        $in_black_friday = ($now >= $this->bf_launch_date && $now <= $this->bf_end_date);
        $in_cyber_monday = ($now >= $this->cm_start_date && $now <= $this->cm_end_date);

        if (!$in_black_friday && !$in_cyber_monday) {
            return false;
        }

        // Get current screen
        $screen = get_current_screen();
        if (!$screen) {
            return false;
        }

        // Don't show on Divi Torque dashboard pages (they have their own banner)
        if ($screen->base === 'toplevel_page_divitorque' || strpos($screen->id, 'divitorque') !== false) {
            return false;
        }

        // Show on WordPress dashboard and plugin pages
        $allowed_base = ['dashboard', 'plugins'];
        if (in_array($screen->base, $allowed_base)) {
            return true;
        }

        return false;
    }

    public function get_notice_config()
    {
        $now = current_time('timestamp');

        // Black Friday period
        if ($now >= $this->bf_launch_date && $now <= $this->bf_end_date) {
            $days_left = max(0, ceil(($this->bf_end_date - $now) / DAY_IN_SECONDS));
            return [
                'badge' => '🎉 BLACK FRIDAY - 50% OFF',
                'title' => 'Divi Torque Pro Lifetime',
                'original_price' => '$179.00',
                'price' => '$89.50',
                'sites' => '20 sites',
                'features' => 'Lifetime updates & support',
                'days_left' => $days_left,
                'cta_url' => 'https://divitorque.com/black-friday/',
            ];
        }

        // Cyber Monday / Holiday Sale period
        if ($now >= $this->cm_start_date && $now <= $this->cm_end_date) {
            $days_left = max(0, ceil(($this->cm_end_date - $now) / DAY_IN_SECONDS));
            return [
                'badge' => '🎄 HOLIDAY SALE - 50% OFF',
                'title' => 'Divi Torque Pro Lifetime',
                'original_price' => '$179.00',
                'price' => '$89.50',
                'sites' => '20 sites',
                'features' => 'Lifetime updates & support',
                'days_left' => $days_left,
                'cta_url' => 'https://divitorque.com/black-friday/',
            ];
        }

        return null;
    }

    public function display_sale_notice()
    {
        $config = $this->get_notice_config();

        if (!$config || !$this->should_show_notice()) {
            return;
        }

        $days_left = $config['days_left'];
?>
        <div class="notice dtl-bf-notice is-dismissible" data-notice="<?php echo esc_attr($this->notice_key); ?>">
            <div class="dtl-bf-content">
                <span class="dtl-bf-badge">🎉 BLACK FRIDAY</span>
                <div class="dtl-bf-info">
                    <strong>Divi Torque Pro Lifetime</strong>
                    <span>20 sites • Lifetime updates & support</span>
                </div>
                <div class="dtl-bf-divider"></div>
                <div class="dtl-bf-price">
                    <div>
                        <span class="dtl-bf-new">$89.50</span>
                        <span class="dtl-bf-old">$179</span>
                    </div>
                    <span class="dtl-bf-save">Save 50%</span>
                </div>
                <?php if ($days_left > 0) : ?>
                    <span class="dtl-bf-timer"><?php echo $days_left; ?> <?php echo $days_left > 1 ? 'days' : 'day'; ?> left</span>
                <?php else : ?>
                    <span class="dtl-bf-timer">Ends today</span>
                <?php endif; ?>
                <a href="<?php echo esc_url($config['cta_url']); ?>" target="_blank" class="dtl-bf-btn">Claim Discount</a>
            </div>
        </div>
        <script>
            jQuery(document).ready(function($) {
                $(document).on('click', '.dtl-bf-notice .notice-dismiss', function() {
                    var noticeKey = $(this).closest('.dtl-bf-notice').data('notice');
                    $.post(ajaxurl, {
                        action: 'dtl_dismiss_notice',
                        notice_key: noticeKey,
                        nonce: '<?php echo wp_create_nonce('dtl_dismiss_notice'); ?>'
                    });
                });
            });
        </script>
<?php
    }

    public function dismiss_notice()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to dismiss this notice.', 'addons-for-divi'));
        }

        $notice_key = isset($_POST['notice_key']) ? sanitize_text_field($_POST['notice_key']) : '';
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

        if (!wp_verify_nonce($nonce, 'dtl_dismiss_notice')) {
            wp_die(__('Nonce verification failed.', 'addons-for-divi'));
        }

        if ($notice_key === $this->notice_key) {
            update_user_meta(get_current_user_id(), $this->notice_key, true);
        }

        wp_die();
    }

    public function enqueue_notice_styles()
    {
        if (!$this->should_show_notice()) {
            return;
        }

        wp_add_inline_style('wp-admin', '
            .dtl-bf-notice {
                position: relative;
                background: linear-gradient(to right, #fefefe, #fcfcff);
                border: 1px solid #e8e5ff;
                border-left: 3px solid #4C3FFF;
                padding: 0 !important;
                margin: 16px 0;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            }
            
            .dtl-bf-content {
                display: flex;
                align-items: center;
                gap: 20px;
                padding: 16px 50px 16px 20px;
            }
            
            .dtl-bf-badge {
                flex-shrink: 0;
                font-size: 11px;
                font-weight: 600;
                color: #4C3FFF;
                letter-spacing: 0.3px;
            }
            
            .dtl-bf-info {
                flex: 1;
                min-width: 0;
            }
            
            .dtl-bf-info strong {
                display: block;
                font-size: 14px;
                font-weight: 600;
                color: #0f172a;
                margin-bottom: 3px;
                line-height: 1.3;
            }
            
            .dtl-bf-info span {
                font-size: 12px;
                color: #64748b;
                line-height: 1.4;
            }
            
            .dtl-bf-divider {
                width: 1px;
                height: 32px;
                background: #e2e8f0;
                flex-shrink: 0;
            }
            
            .dtl-bf-price {
                flex-shrink: 0;
                text-align: center;
            }
            
            .dtl-bf-price > div {
                display: flex;
                align-items: baseline;
                gap: 6px;
                margin-bottom: 2px;
            }
            
            .dtl-bf-new {
                font-size: 22px;
                font-weight: 700;
                color: #4C3FFF;
                line-height: 1;
            }
            
            .dtl-bf-old {
                font-size: 13px;
                color: #94a3b8;
                text-decoration: line-through;
                line-height: 1;
            }
            
            .dtl-bf-save {
                display: block;
                font-size: 10px;
                font-weight: 600;
                color: #10b981;
                text-transform: uppercase;
                letter-spacing: 0.3px;
            }
            
            .dtl-bf-timer {
                flex-shrink: 0;
                padding: 6px 12px;
                background: #fff7ed;
                border: 1px solid #fed7aa;
                color: #c2410c;
                font-size: 11px;
                font-weight: 600;
                border-radius: 4px;
                white-space: nowrap;
            }
            
            .dtl-bf-btn {
                flex-shrink: 0;
                padding: 10px 24px;
                background: #4C3FFF;
                color: #fff;
                text-decoration: none;
                font-weight: 600;
                font-size: 13px;
                border-radius: 6px;
                transition: all 0.15s ease;
                white-space: nowrap;
            }
            
            .dtl-bf-btn:hover {
                background: #3d32cc;
                color: #fff;
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(76, 63, 255, 0.25);
            }
            
            .dtl-bf-notice .notice-dismiss {
                top: 50%;
                transform: translateY(-50%);
                color: #cbd5e1;
            }
            
            .dtl-bf-notice .notice-dismiss:hover {
                color: #94a3b8;
            }
            
            .dtl-bf-notice .notice-dismiss:before {
                color: currentColor;
            }
            
            @media screen and (max-width: 1280px) {
                .dtl-bf-content {
                    gap: 16px;
                }
                
                .dtl-bf-divider {
                    display: none;
                }
            }
            
            @media screen and (max-width: 960px) {
                .dtl-bf-content {
                    flex-wrap: wrap;
                    gap: 12px;
                }
                
                .dtl-bf-badge {
                    width: 100%;
                }
                
                .dtl-bf-btn {
                    order: 10;
                }
            }
            
            @media screen and (max-width: 782px) {
                .dtl-bf-content {
                    padding: 14px 14px 14px 16px;
                    gap: 10px;
                }
                
                .dtl-bf-info {
                    width: 100%;
                }
                
                .dtl-bf-btn {
                    width: 100%;
                    text-align: center;
                    padding: 12px 24px;
                }
                
                .dtl-bf-notice .notice-dismiss {
                    top: 10px;
                    transform: none;
                }
            }
        ');
    }
}
