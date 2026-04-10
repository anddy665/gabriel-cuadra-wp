<?php
// Ensure this file is not accessed directly
if (!defined('ABSPATH')) {
    exit;
}
// Check if the request is an AJAX call
if (!wp_doing_ajax()) {
    get_header();
}
?>

<style>
    .etn-no-events-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 20px;
    }


    .etn-no-events-icon svg {
        width: 160px;
        height: 160px;
    }

    .etn-no-events-title {
        font-size: 28px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 12px;
        line-height: 1.3;
        margin-top: -20px;
    }

    .etn-no-events-title span {
        color: #ef4444;
    }

    .etn-no-events-desc {
        font-size: 15px;
        color: #6b7280;
        margin: 0 0 32px;
        max-width: 420px;
        line-height: 1.6;
    }

    .etn-no-events-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #4f46e5;
        color: #fff;
        font-size: 15px;
        font-weight: 600;
        padding: 12px 28px;
        border-radius: 50px;
        text-decoration: none;
        transition: background 0.2s;
    }

    .etn-no-events-btn:hover {
        background: #4338ca;
        color: #fff;
        text-decoration: none;
    }

    .etn-no-events-btn svg {
        width: 18px;
        height: 18px;
    }
</style>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <section class="error-404 not-found">
            <div class="etn-no-events-wrap">

                <div class="etn-no-events-icon">
                    <svg width="160" height="160" viewBox="0 0 320 320" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g filter="url(#filter0_f_3030_5761)">
                            <path d="M80 160C80 115.817 115.817 80 160 80V80C204.183 80 240 115.817 240 160V160C240 204.183 204.183 240 160 240V240C115.817 240 80 204.183 80 160V160Z" fill="url(#paint0_linear_3030_5761)" />
                        </g>
                        <g filter="url(#filter1_dd_3030_5761)">
                            <path d="M195.302 109.94C196.91 102.377 204.344 97.5493 211.907 99.1569L231.47 103.315C239.033 104.923 243.861 112.357 242.253 119.92L238.095 139.483C236.487 147.046 229.053 151.874 221.49 150.266L201.927 146.108C194.364 144.5 189.536 137.066 191.144 129.503L195.302 109.94Z" fill="white" shape-rendering="crispEdges" />
                            <g clip-path="url(#clip0_3030_5761)">
                                <path d="M215.129 116.434L214.297 120.346" stroke="#FF6467" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M221.498 117.789L220.666 121.702" stroke="#FF6467" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M223.471 120.255L212.325 117.885C211.445 117.698 210.546 118.423 210.317 119.503L207.406 133.197C207.176 134.278 207.703 135.305 208.582 135.492L219.728 137.861C220.608 138.048 221.507 137.324 221.737 136.243L224.647 122.549C224.877 121.469 224.35 120.441 223.471 120.255Z" stroke="#FF6467" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M209.485 123.414L223.816 126.46" stroke="#FF6467" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </g>
                        </g>
                        <g filter="url(#filter2_dd_3030_5761)">
                            <path d="M76.4176 173.671C74.81 166.108 79.6379 158.673 87.2009 157.066L106.764 152.908C114.327 151.3 121.761 156.128 123.369 163.691L127.527 183.254C129.134 190.817 124.307 198.251 116.744 199.859L97.1807 204.017C89.6176 205.624 82.1834 200.797 80.5758 193.234L76.4176 173.671Z" fill="white" shape-rendering="crispEdges" />
                            <g clip-path="url(#clip1_3030_5761)">
                                <path d="M93.8512 179.401C94.4846 179.266 95.1579 179.446 95.7228 179.901C96.2877 180.356 96.6979 181.049 96.8634 181.828C97.0288 182.606 96.9358 183.406 96.6049 184.051C96.2739 184.697 95.7321 185.135 95.0986 185.27L95.5145 187.226C95.6248 187.745 95.8983 188.207 96.2749 188.51C96.6515 188.814 97.1003 188.934 97.5226 188.844L110.261 186.136C110.683 186.046 111.045 185.754 111.265 185.324C111.486 184.894 111.548 184.36 111.438 183.842L111.022 181.885C110.388 182.02 109.715 181.84 109.15 181.385C108.585 180.93 108.175 180.237 108.01 179.458C107.844 178.68 107.937 177.88 108.268 177.235C108.599 176.589 109.141 176.151 109.774 176.016L109.358 174.06C109.248 173.541 108.975 173.079 108.598 172.776C108.221 172.472 107.773 172.352 107.35 172.442L94.6118 175.15C94.1895 175.24 93.8283 175.532 93.6077 175.962C93.3871 176.392 93.3251 176.926 93.4354 177.445L93.8512 179.401Z" stroke="#FF8904" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M101.777 173.629L102.193 175.585" stroke="#FF8904" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M104.272 185.367L104.688 187.323" stroke="#FF8904" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M103.024 179.496L103.44 181.452" stroke="#FF8904" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </g>
                        </g>
                        <g clip-path="url(#clip2_3030_5761)">
                            <path d="M96.2809 96.9455C96.2065 96.6571 96.0561 96.3939 95.8455 96.1833C95.6349 95.9727 95.3718 95.8224 95.0834 95.748L89.9709 94.4297C89.8836 94.4049 89.8069 94.3524 89.7522 94.2801C89.6975 94.2077 89.668 94.1195 89.668 94.0289C89.668 93.9382 89.6975 93.85 89.7522 93.7776C89.8069 93.7053 89.8836 93.6528 89.9709 93.628L95.0834 92.3089C95.3717 92.2345 95.6348 92.0843 95.8454 91.8739C96.056 91.6634 96.2063 91.4004 96.2809 91.1122L97.5992 85.9997C97.6237 85.9121 97.6762 85.835 97.7486 85.78C97.8211 85.7251 97.9095 85.6953 98.0004 85.6953C98.0914 85.6953 98.1798 85.7251 98.2523 85.78C98.3247 85.835 98.3772 85.9121 98.4017 85.9997L99.7192 91.1122C99.7936 91.4006 99.9439 91.6638 100.155 91.8744C100.365 92.085 100.628 92.2353 100.917 92.3097L106.029 93.6272C106.117 93.6514 106.195 93.7039 106.25 93.7764C106.305 93.849 106.335 93.9377 106.335 94.0289C106.335 94.1201 106.305 94.2087 106.25 94.2813C106.195 94.3538 106.117 94.4063 106.029 94.4305L100.917 95.748C100.628 95.8224 100.365 95.9727 100.155 96.1833C99.9439 96.3939 99.7936 96.6571 99.7192 96.9455L98.4009 102.058C98.3764 102.146 98.3239 102.223 98.2514 102.278C98.179 102.333 98.0905 102.362 97.9996 102.362C97.9087 102.362 97.8202 102.333 97.7478 102.278C97.6754 102.223 97.6229 102.146 97.5984 102.058L96.2809 96.9455Z" stroke="#FFA2A2" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M104.667 86.5273V89.8607" stroke="#FFA2A2" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M106.333 88.1953H103" stroke="#FFA2A2" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M91.333 98.1953V99.862" stroke="#FFA2A2" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M92.1667 99.0273H90.5" stroke="#FFA2A2" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
                        </g>
                        <g filter="url(#filter3_dd_3030_5761)">
                            <path d="M112 128C112 119.163 119.163 112 128 112H192C200.837 112 208 119.163 208 128V192C208 200.837 200.837 208 192 208H128C119.163 208 112 200.837 112 192V128Z" fill="url(#paint1_linear_3030_5761)" shape-rendering="crispEdges" />
                            <path d="M158 174C166.837 174 174 166.837 174 158C174 149.163 166.837 142 158 142C149.163 142 142 149.163 142 158C142 166.837 149.163 174 158 174Z" stroke="#99A1AF" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M178 177.998L169.4 169.398" stroke="#99A1AF" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                        </g>
                        <defs>
                            <filter id="filter0_f_3030_5761" x="0" y="0" width="320" height="320" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                <feFlood flood-opacity="0" result="BackgroundImageFix" />
                                <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape" />
                                <feGaussianBlur stdDeviation="40" result="effect1_foregroundBlur_3030_5761" />
                            </filter>
                            <filter id="filter1_dd_3030_5761" x="183.233" y="95.2461" width="66.9307" height="66.9297" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                <feFlood flood-opacity="0" result="BackgroundImageFix" />
                                <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                <feMorphology radius="2" operator="erode" in="SourceAlpha" result="effect1_dropShadow_3030_5761" />
                                <feOffset dy="2" />
                                <feGaussianBlur stdDeviation="2" />
                                <feComposite in2="hardAlpha" operator="out" />
                                <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.1 0" />
                                <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_3030_5761" />
                                <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                <feMorphology radius="1" operator="erode" in="SourceAlpha" result="effect2_dropShadow_3030_5761" />
                                <feOffset dy="4" />
                                <feGaussianBlur stdDeviation="3" />
                                <feComposite in2="hardAlpha" operator="out" />
                                <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.1 0" />
                                <feBlend mode="normal" in2="effect1_dropShadow_3030_5761" result="effect2_dropShadow_3030_5761" />
                                <feBlend mode="normal" in="SourceGraphic" in2="effect2_dropShadow_3030_5761" result="shape" />
                            </filter>
                            <filter id="filter2_dd_3030_5761" x="68.5068" y="148.996" width="66.9307" height="66.9297" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                <feFlood flood-opacity="0" result="BackgroundImageFix" />
                                <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                <feMorphology radius="2" operator="erode" in="SourceAlpha" result="effect1_dropShadow_3030_5761" />
                                <feOffset dy="2" />
                                <feGaussianBlur stdDeviation="2" />
                                <feComposite in2="hardAlpha" operator="out" />
                                <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.1 0" />
                                <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_3030_5761" />
                                <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                <feMorphology radius="1" operator="erode" in="SourceAlpha" result="effect2_dropShadow_3030_5761" />
                                <feOffset dy="4" />
                                <feGaussianBlur stdDeviation="3" />
                                <feComposite in2="hardAlpha" operator="out" />
                                <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.1 0" />
                                <feBlend mode="normal" in2="effect1_dropShadow_3030_5761" result="effect2_dropShadow_3030_5761" />
                                <feBlend mode="normal" in="SourceGraphic" in2="effect2_dropShadow_3030_5761" result="shape" />
                            </filter>
                            <filter id="filter3_dd_3030_5761" x="100" y="110" width="120" height="120" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                <feFlood flood-opacity="0" result="BackgroundImageFix" />
                                <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                <feMorphology radius="4" operator="erode" in="SourceAlpha" result="effect1_dropShadow_3030_5761" />
                                <feOffset dy="4" />
                                <feGaussianBlur stdDeviation="3" />
                                <feComposite in2="hardAlpha" operator="out" />
                                <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.1 0" />
                                <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_3030_5761" />
                                <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                <feMorphology radius="3" operator="erode" in="SourceAlpha" result="effect2_dropShadow_3030_5761" />
                                <feOffset dy="10" />
                                <feGaussianBlur stdDeviation="7.5" />
                                <feComposite in2="hardAlpha" operator="out" />
                                <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.1 0" />
                                <feBlend mode="normal" in2="effect1_dropShadow_3030_5761" result="effect2_dropShadow_3030_5761" />
                                <feBlend mode="normal" in="SourceGraphic" in2="effect2_dropShadow_3030_5761" result="shape" />
                            </filter>
                            <linearGradient id="paint0_linear_3030_5761" x1="80" y1="80" x2="240" y2="240" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#FFE2E2" stop-opacity="0.6" />
                                <stop offset="0.5" stop-color="#FFEDD4" stop-opacity="0.4" />
                                <stop offset="1" stop-color="#F3F4F6" stop-opacity="0.6" />
                            </linearGradient>
                            <linearGradient id="paint1_linear_3030_5761" x1="112" y1="112" x2="208" y2="208" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#FEF2F2" />
                                <stop offset="1" stop-color="#FFF7ED" />
                            </linearGradient>
                            <clipPath id="clip0_3030_5761">
                                <rect width="19.5345" height="24" fill="white" transform="translate(209.176 113.125) rotate(12)" />
                            </clipPath>
                            <clipPath id="clip1_3030_5761">
                                <rect width="19.5345" height="24" fill="white" transform="translate(90.3877 170.938) rotate(-12)" />
                            </clipPath>
                            <clipPath id="clip2_3030_5761">
                                <rect width="20" height="20" fill="white" transform="translate(88 84.0273)" />
                            </clipPath>
                        </defs>
                    </svg>

                </div>

                <h2 class="etn-no-events-title">
                    <?php esc_html_e('No Events', 'eventin'); ?>
                    <span><?php esc_html_e('Found', 'eventin'); ?></span>
                </h2>

                <p class="etn-no-events-desc">
                    <?php esc_html_e("We couldn't find any events matching your current search. Try changing filters or explore upcoming events.", 'eventin'); ?>
                </p>

                <a href="<?php echo esc_url(get_post_type_archive_link('etn')); ?>" class="etn-no-events-btn">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_3025_5726)">
                            <path d="M6.6243 10.3322C6.56478 10.1015 6.44453 9.89093 6.27605 9.72245C6.10757 9.55397 5.89702 9.43372 5.6663 9.3742L1.5763 8.31953C1.50652 8.29972 1.44511 8.2577 1.40138 8.19983C1.35765 8.14196 1.33398 8.0714 1.33398 7.99886C1.33398 7.92633 1.35765 7.85577 1.40138 7.7979C1.44511 7.74003 1.50652 7.698 1.5763 7.6782L5.6663 6.62286C5.89693 6.5634 6.10743 6.44325 6.2759 6.27489C6.44438 6.10653 6.56468 5.89612 6.6243 5.66553L7.67897 1.57553C7.69857 1.50548 7.74056 1.44376 7.79851 1.39979C7.85647 1.35583 7.92722 1.33203 7.99997 1.33203C8.07271 1.33203 8.14346 1.35583 8.20142 1.39979C8.25938 1.44376 8.30136 1.50548 8.32097 1.57553L9.37497 5.66553C9.43449 5.89625 9.55474 6.1068 9.72322 6.27528C9.8917 6.44376 10.1023 6.56401 10.333 6.62353L14.423 7.67753C14.4933 7.69693 14.5553 7.73887 14.5995 7.79692C14.6437 7.85496 14.6677 7.9259 14.6677 7.99886C14.6677 8.07182 14.6437 8.14277 14.5995 8.20081C14.5553 8.25886 14.4933 8.3008 14.423 8.3202L10.333 9.3742C10.1023 9.43372 9.8917 9.55397 9.72322 9.72245C9.55474 9.89093 9.43449 10.1015 9.37497 10.3322L8.3203 14.4222C8.3007 14.4923 8.25871 14.554 8.20075 14.5979C8.1428 14.6419 8.07205 14.6657 7.9993 14.6657C7.92656 14.6657 7.85581 14.6419 7.79785 14.5979C7.73989 14.554 7.69791 14.4923 7.6783 14.4222L6.6243 10.3322Z" stroke="white" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M13.333 2V4.66667" stroke="white" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M14.6667 3.33203H12" stroke="white" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M2.66699 11.332V12.6654" stroke="white" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M3.33333 12H2" stroke="white" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round" />
                        </g>
                        <defs>
                            <clipPath id="clip0_3025_5726">
                                <rect width="16" height="16" fill="white" />
                            </clipPath>
                        </defs>
                    </svg>

                    <?php esc_html_e('Browse All Events', 'eventin'); ?>
                </a>

            </div>
        </section><!-- .error-404 -->

    </main><!-- #main -->
</div><!-- #primary -->

<?php
// Check if the request is an AJAX call
if (!wp_doing_ajax()) {
    get_footer();
}
