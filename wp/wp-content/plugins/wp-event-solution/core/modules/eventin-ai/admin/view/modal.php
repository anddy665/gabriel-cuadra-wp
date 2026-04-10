<?php  defined( 'ABSPATH' ) || exit; ?>
<div class="etn-ai-modal-backdrop">
    <div class="etn-ai-modal" id="etn-ai-modal">
        <img src="<?php echo esc_attr( Wpeventin::plugin_url( 'assets/images/ai-modal-bg-gradient.png' )) ?>" alt="Eventin AI" class="etn-overlay-image">
        <div class="etn-ai-modal-content">
            <div class="etn-ai-icon">
            <svg width="60" height="60" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="30" cy="30" r="30" fill="url(#paint0_linear_298_1743)"/>
                <path d="M28.4639 15.6468C28.5308 15.2884 28.721 14.9647 29.0016 14.7317C29.2821 14.4987 29.6352 14.3712 29.9998 14.3712C30.3645 14.3712 30.7176 14.4987 30.9981 14.7317C31.2786 14.9647 31.4688 15.2884 31.5358 15.6468L33.178 24.3312C33.2946 24.9486 33.5946 25.5165 34.0389 25.9608C34.4832 26.4052 35.0512 26.7052 35.6686 26.8218L44.353 28.464C44.7114 28.531 45.0351 28.7212 45.2681 29.0017C45.501 29.2822 45.6286 29.6353 45.6286 30C45.6286 30.3646 45.501 30.7177 45.2681 30.9982C45.0351 31.2787 44.7114 31.4689 44.353 31.5359L35.6686 33.1781C35.0512 33.2947 34.4832 33.5948 34.0389 34.0391C33.5946 34.4834 33.2946 35.0513 33.178 35.6687L31.5358 44.3531C31.4688 44.7115 31.2786 45.0352 30.9981 45.2682C30.7176 45.5012 30.3645 45.6287 29.9998 45.6287C29.6352 45.6287 29.2821 45.5012 29.0016 45.2682C28.721 45.0352 28.5308 44.7115 28.4639 44.3531L26.8217 35.6687C26.7051 35.0513 26.405 34.4834 25.9607 34.0391C25.5164 33.5948 24.9485 33.2947 24.3311 33.1781L15.6467 31.5359C15.2883 31.4689 14.9645 31.2787 14.7316 30.9982C14.4986 30.7177 14.3711 30.3646 14.3711 30C14.3711 29.6353 14.4986 29.2822 14.7316 29.0017C14.9645 28.7212 15.2883 28.531 15.6467 28.464L24.3311 26.8218C24.9485 26.7052 25.5164 26.4052 25.9607 25.9608C26.405 25.5165 26.7051 24.9486 26.8217 24.3312L28.4639 15.6468Z" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M42.5 14.375V20.625" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M45.625 17.5H39.375" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M17.5 45.625C19.2259 45.625 20.625 44.2259 20.625 42.5C20.625 40.7741 19.2259 39.375 17.5 39.375C15.7741 39.375 14.375 40.7741 14.375 42.5C14.375 44.2259 15.7741 45.625 17.5 45.625Z" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                <defs>
                    <linearGradient id="paint0_linear_298_1743" x1="0" y1="30" x2="60" y2="30" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#FF36E6"/>
                    <stop offset="0.25" stop-color="#D439EC"/>
                    <stop offset="0.5" stop-color="#A93DF3"/>
                    <stop offset="0.75" stop-color="#933FF6"/>
                    <stop offset="1" stop-color="#5243FE"/>
                    </linearGradient>
                </defs>
            </svg>

            </div>
            <div class="etn-ai-modal-body">
                <h2><?php esc_html_e( 'Please install and active both Eventin Pro and Eventin AI plugin.', 'eventin' ); ?></h2>
                <p><?php esc_html_e( 'Eventin AI is only available for purchase on our paid plans. Upgrade and add AI now!', 'eventin' ); ?></p>
            </div>
            <div class="etn-ai-modal-footer">
                <a href="<?php echo esc_url( 'https://themewinter.com/eventin/#ts-pricing-list' ); ?>" class="etn-ai-buy-button" target="_blank"><?php esc_html_e( 'Upgrade Your Plan', 'eventin' ); ?></a>
            </div>
        </div>
    </div>
</div>