<?php
    $etn_faqs = get_post_meta($event_id, 'etn_event_faq', true);
    if (! empty($etn_faqs)):
?>
<div class="etn-accordion-wrap etn-event-single-content-wrap etn-faq-style-2
<?php echo esc_attr($container_class); ?>">
    <?php
        if (is_array($etn_faqs) && ! empty($etn_faqs)) {
            foreach ($etn_faqs as $key => $faq) {
                // 2nd item (index 1) should be expanded by default
                $is_expanded = ($key === 1);
                $acc_class   = $is_expanded ? 'active' : '';
            ?>
            <div class="etn-faq-item
            <?php echo esc_attr($acc_class); ?>">
                <div class="etn-faq-header">
                    <h4 class="etn-faq-title"><?php echo esc_html($faq["etn_faq_title"]); ?></h4>
                    <div class="etn-faq-icon">
                        <i class="etn-icon etn-angle-down"></i>
                    </div>
                </div>
                <div class="etn-faq-content">
                    <p class="etn-faq-content-text">
                        <?php
                            if (has_blocks($faq["etn_faq_content"])) {
                                        echo do_blocks($faq["etn_faq_content"]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                    } else {
                                        echo esc_html($faq["etn_faq_content"]);
                                    }
                                ?>
                    </p>
                </div>
            </div>
            <?php
                }
                } else {
                ?>
        <div class="etn-event-faq-body">
            <?php echo esc_html__("No FAQ found!", "eventin"); ?>
        </div>
        <?php
            }
        ?>
</div>
<script>
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        const faqItems = document.querySelectorAll('.etn-faq-style-2 .etn-faq-item');

        // Initialize expanded items on page load with smooth animation
        faqItems.forEach(function(item) {
            const content = item.querySelector('.etn-faq-content');
            const isInitiallyExpanded = item.classList.contains('active');

            if (content) {
                if (isInitiallyExpanded) {
                    // Set initial state
                    content.style.maxHeight = '0px';
                    content.style.paddingTop = '0px';
                    content.style.opacity = '0';

                    // Use requestAnimationFrame for smooth animation
                    requestAnimationFrame(function() {
                        const height = content.scrollHeight;
                        content.style.maxHeight = height + 'px';
                        content.style.paddingTop = '15px';
                        content.style.opacity = '1';
                    });
                } else {
                    content.style.maxHeight = '0px';
                    content.style.paddingTop = '0px';
                    content.style.opacity = '0';
                }
            }
        });

        // Handle click events
        const faqHeaders = document.querySelectorAll('.etn-faq-style-2 .etn-faq-header');
        faqHeaders.forEach(function(header) {
            header.addEventListener('click', function() {
                const item = this.closest('.etn-faq-item');
                const content = item.querySelector('.etn-faq-content');
                const isActive = item.classList.contains('active');

                // Close all items with smooth animation
                document.querySelectorAll('.etn-faq-style-2 .etn-faq-item').forEach(function(el) {
                    const otherContent = el.querySelector('.etn-faq-content');
                    if (otherContent && el !== item) {
                        el.classList.remove('active');
                        // Smooth collapse
                        requestAnimationFrame(function() {
                            otherContent.style.maxHeight = '0px';
                            otherContent.style.paddingTop = '0px';
                            otherContent.style.opacity = '0';
                        });
                    }
                });

                // Open clicked item if it wasn't active with smooth animation
                if (!isActive && content) {
                    item.classList.add('active');

                    // Get actual height first
                    content.style.maxHeight = 'none';
                    const height = content.scrollHeight;
                    content.style.maxHeight = '0px';
                    content.style.opacity = '0';

                    // Smooth expand
                    requestAnimationFrame(function() {
                        requestAnimationFrame(function() {
                            content.style.maxHeight = height + 'px';
                            content.style.paddingTop = '15px';
                            content.style.opacity = '1';
                        });
                    });
                } else if (isActive && content) {
                    // Close if clicking on active item
                    item.classList.remove('active');
                    requestAnimationFrame(function() {
                        content.style.maxHeight = '0px';
                        content.style.paddingTop = '0px';
                        content.style.opacity = '0';
                    });
                }
            });
        });
    });
})();
</script>
<?php endif;
