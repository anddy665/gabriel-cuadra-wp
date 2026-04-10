<?php

namespace Etn\Templates\Event\Parts;

use DateTimeZone;
use Etn\Core\Event\Event_Model;
use Etn\Utils\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Event details class.
 *
 * @since 3.3.9
 */
class EventDetailsParts {

	/**
	 * Process event single tag lists
	 *
	 * @param [type] $event_id
	 *
	 * @return void
	 */
	public static function event_single_tag_list( $single_event_id ) {
		?>
        <div class="etn-event-tag-list">
			<?php
			global $post;
			$etn_terms = wp_get_post_terms( $single_event_id, 'etn_tags' );
			if ( $etn_terms ) {
				?>
                <h4 class="etn-tags-title">
					<?php
					$tag_title = apply_filters( 'etn_event_tag_list_title', esc_html__( 'Tags', "eventin" ) );
					echo esc_html( $tag_title );
					?>
                </h4>
				<?php
				$output = array();

				if ( is_array( $etn_terms ) ) {
					foreach ( $etn_terms as $term ) {
						$term_link = get_term_link( $term->slug, 'etn_tags' );
						$output[]  = '<a href="' . $term_link . '">' . $term->name . '</a>';
					}
				}
				echo Helper::kses( join( ' ', $output ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Helper::kses() wraps wp_kses() with an allowed tags list.
			}
			?>
        </div>
		<?php
	}

	/**
	 * Process event single tag lists
	 *
	 * @param [type] $event_id
	 *
	 * @return void
	 */
	public static function event_single_organizers( $etn_organizer_events ) {
		$args = [
			'include' => $etn_organizer_events
		];
		$event_options        = get_option( "etn_event_options" );
		$email_show 		  =  $event_options["hide_oragnizer_email_from_events"] ?? '';
	 
		$data = get_users( $args );
        
		if ( $data && $etn_organizer_events ) :
			?>
            <div class="etn-widget etn-event-organizers">
                <h4 class="etn-widget-title etn-title">
					<?php
					$event_organizers_title = apply_filters( 'etn_event_organizers_title', esc_html__( "Organizers", "eventin" ) );
					echo esc_html( $event_organizers_title );
					?>
                </h4>
				<?php
				foreach ( $data as $value ) {
					$social                   = get_user_meta( $value->ID, 'etn_speaker_social', true );
					$email                    = get_user_meta( $value->ID, 'etn_speaker_website_email', true );
					$etn_speaker_company_logo = get_user_meta( $value->ID, 'image_id', true );
					$etn_company_logo         = get_user_meta( $value->ID, 'etn_speaker_company_logo', true );

					$logo                     = wp_get_attachment_image_src( $etn_speaker_company_logo, 'full' );
					$etn_author_url           = get_author_posts_url( $value->ID, $value->user_nicename, true );
					?>
                    <div class="etn-organaizer-item">
						<?php if ( isset( $logo[0] ) ) { ?>
                            <div class="etn-organizer-logo">
								<?php echo wp_get_attachment_image( $etn_speaker_company_logo, 'full' ); ?>
                            </div>
						<?php } else {
							?>
							<div class="etn-organizer-logo">
								<img src="<?php echo esc_url( $etn_company_logo); ?>">
							</div>
						<?php
						} 
						?>
                        <h4 class="etn-organizer-name">
							<a href='<?php echo esc_url($etn_author_url); ?>'> <?php echo esc_html( get_user_meta( $value->ID, 'first_name', true ) ); ?> </a>
                        </h4>

						<?php if ( $email  && $email_show ) { ?>
                            <div class="etn-organizer-email">
                                <span class="etn-label-name"><?php echo esc_html__( 'Email :', "eventin" ); ?></span>
                                <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
                            </div>
						<?php } ?>
						<?php if ( is_array( $social ) && ! empty( $social ) ) { ?>
                            <div class="etn-social">
                                <!-- <span class="etn-label-name"><?php echo esc_html__( 'Social :', "eventin" ); ?></span> -->
								<?php 
                                foreach ($social as $social_value) {
									// Validate and sanitize each value
									$icon = isset($social_value['icon']) && is_string($social_value['icon']) ? $social_value['icon'] : '';
									$etn_social_url = isset($social_value['etn_social_url']) ? $social_value['etn_social_url'] : '#';
									$etn_social_title = isset($social_value['etn_social_title']) ? $social_value['etn_social_title'] : '';
							
									// Generate the social class
									$etn_social_class = 'etn-' . str_replace('fab fa-', '', $icon);
									?>
									<a href="<?php echo esc_url($etn_social_url); ?>" 
										target="_blank" 
										class="<?php echo esc_attr($etn_social_class); ?>" 
										title="<?php echo esc_attr($etn_social_title); ?>" 
										aria-label="<?php echo esc_attr($etn_social_title); ?>">
										<i class="etn-icon <?php echo esc_attr($icon); ?>"></i>
									</a>
                            	<?php } ?>
                            </div>
						<?php } ?>
                    </div>
				<?php }
				?>
            </div>
		<?php endif;
	}

	/**
	 * Process event single category list
	 *
	 * @param [type] $single_event_id
	 *
	 * @return void
	 */
	public static function event_single_category( $single_event_id ) {
		global $post;
		$data              = Helper::single_template_options( $single_event_id );
		$etn_event_socials = isset( $data['etn_event_socials'] ) ? $data['etn_event_socials'] : [];
		$etn_cat_terms     = wp_get_post_terms( $single_event_id, 'etn_category' );
		$is_hide_social    = etn_get_option( 'hide_social_from_details' );
		?>

		<?php if( !empty( $etn_event_socials ) || !empty( $etn_cat_terms ) ) : ?>
			<div class="etn-event-meta">
				<?php if( !empty( $etn_cat_terms ) ) : ?>
					<div class="etn-event-category">
						<?php
							$output = array();
							if ( is_array( $etn_cat_terms ) ) {
								foreach ( $etn_cat_terms as $term ) {
									$term_link = get_term_link( $term->slug, 'etn_category' );
									$output[]  = '<a  href="' . $term_link . '">' . $term->name . '</a>';
								}
							}
							echo "<span>" . Helper::kses( join( ' ', $output ) ) . "</span>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Helper::kses() wraps wp_kses(); span wrapper is hardcoded.
						?>
					</div>
				<?php endif ;?>
				<?php if( !empty( $etn_event_socials ) && 'on' === $is_hide_social ) : ?>
					<div class="etn-event-social-wrap">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18">
							<path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
						</svg>
						<div class="etn-social">
							<?php if ( is_array( $etn_event_socials ) ) : ?>
								<?php foreach ( $etn_event_socials as $social ) : ?>
									<?php $etn_social_class = 'etn-' . str_replace( 'etn-icon fa-', '', $social['icon'] ); ?>
									<a
											href="<?php echo esc_url( $social['etn_social_url'] ); ?>"
											target="_blank"
											rel="noopener"
											aria-label="<?php echo esc_attr( $social["etn_social_title"] ); ?>"
									>
										<i class="etn-icon <?php echo esc_attr( $social['icon'] ); ?>"></i>
									</a>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
					</div>
				<?php endif ;?>
			</div>
		<?php endif; ?>
		<?php
	}

	/**
	 * Process event single category list
	 *
	 * @param [type] $single_event_id
	 *
	 * @return void
	 */
	public static function event_single_sidebar_meta( $single_event_id ) {
		$event_options = get_option( "etn_event_options" );
		$hide_location = ! empty( $event_options["etn_hide_location_from_details"] ) ? $event_options["etn_hide_location_from_details"] : '';
		$data          = Helper::single_template_options( $single_event_id );

		$separate = ! empty( $data['event_end_date'] ) ? ' - ' : '';

		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		$event = new Event_Model( $single_event_id );
		$start_date_time = $event->etn_start_date . ' ' . $event->etn_start_time;
		
		$start_date   = date_i18n( $date_format, strtotime( $event->etn_start_date ) );
		$end_date     = date_i18n( $date_format, strtotime( $event->etn_end_date ) );


		$end_date_time = $event->etn_end_date . ' ' . $event->etn_end_time;

		$start_time	  = gmdate( $time_format, strtotime( $start_date_time ) );
		$end_time	  = gmdate( $time_format, strtotime( $end_date_time ) );

		if ( ! empty( $data['event_start_date'] ) || ! empty( $data['event_start_time'] ) || ! empty( $data['etn_event_location'] )) :
			?>
            <div class="etn-event-meta-info etn-widget">
                <ul>
					<?php
					// event date
					if ( empty( $event_options["etn_hide_date_from_details"] ) && ! empty( $data['event_start_date'] ) ) {
					
						?>
                        <li>
							<?php if ( $data['event_start_date'] !== $data['event_end_date']): ?>
                            <span> <?php echo esc_html__( 'Date :', "eventin" ); ?></span>
							<?php echo esc_html( $start_date . $separate . $end_date ); ?>
							

							<?php else: ?>
								<span> <?php echo esc_html__( 'Date : ', "eventin" ); ?></span>
							<?php echo esc_html( $start_date ); ?>
							<?php endif; ?>
                        </li>
						<?php
					}
					?>
					<?php
					// event time
					if ( empty( $event_options["etn_hide_time_from_details"] ) && ( ! empty( $data['event_start_time'] ) || ! empty( $data['event_end_time'] ) ) ) {
						$separate = ! empty( $data['event_end_time'] ) ? ' - ' : '';
						?>
                        <li>
                            <span><?php echo esc_html__( 'Time : ', "eventin" ); ?></span>
							<?php echo esc_html( $start_time . $separate . $end_time ); ?>
                            <span class="etn-event-timezone">
                            <?php
                            if ( ! empty( $data['event_timezone'] ) && ! isset( $event_options["etn_hide_timezone_from_details"] ) ) {
	                            ?>
                                (<?php echo esc_html( $data['event_timezone'] ); ?>)
	                            <?php
                            }
                            ?>
                        </span>
                        </li>
						<?php
					}
					?>
					
					<?php
					$location = \Etn\Core\Event\Helper::instance()->display_event_location( $single_event_id );
					$location = etn_prepare_address( $location );
					$event_location_type = $data['etn_event_location_type'];
					if ( empty( $event_options["etn_hide_location_from_details"] ) && ! empty( $location) ) {
						?>
                        <li>
                            <span><?php echo esc_html__( 'Venue : ', "eventin" ) ?></span>
							<?php  
								echo esc_html( $location ); 
							?>
                        </li>
						<?php
					}

					?>
					<?php if ( $event_location_type === 'new_location' && class_exists( 'Wpeventin_Pro' ) && empty( $hide_location ) ): ?>
						<?php
							$event_terms = ! empty( get_the_terms( $single_event_id, 'etn_location' ) ) ? get_the_terms( $single_event_id, 'etn_location' ) : [];
							if( ! empty( $event_terms ) ) :
						?>
							<li>
								<span><?php echo esc_html__( 'Venue : ', "eventin" ) ?></span>
								<?php foreach ( $event_terms as $term ) : ?>
									<span class="etn-location-name"><?php echo esc_html( $term->name ); ?></span>
								<?php endforeach; ?>
							</li>
						<?php endif; ?>
					<?php endif; ?>
                </ul>
				<?php
				?>
            </div>
		<?php endif; ?>
		<?php
	}

	public static function event_single_faq( $single_event_id ) {
		$etn_faqs = get_post_meta( $single_event_id, 'etn_event_faq', true );

		if ( ! empty( $etn_faqs ) && is_array( $etn_faqs ) ) :

			$has_enabled_faq = false;

			foreach ( $etn_faqs as $faq ) {
				if(!array_key_exists('etn_faq_enabled', $faq)) {
					$has_enabled_faq = true;
					break;
				} elseif ( ! empty( $faq['etn_faq_enabled'] ) ) {
					$has_enabled_faq = true;
					break;
				}
			}
			?>

			<div class="etn-accordion-wrap etn-event-single-content-wrap">
				<h3 class="etn-faq-heading">
					<?php
					echo esc_html(
						apply_filters( 'etn_event_faq_title', __( 'Frequently Asked Questions', 'eventin' ) )
					);
					?>
				</h3>

				<?php if ( $has_enabled_faq ) : ?>

					<?php foreach ( $etn_faqs as $key => $faq ) :
						if ( array_key_exists('etn_faq_enabled', $faq) && empty( $faq['etn_faq_enabled'] ) ) {
							continue;
						}

						$acc_class = ( $key === 0 ) ? 'active' : '';
						?>
						<div class="etn-content-item">
							<h4 class="etn-accordion-heading <?php echo esc_attr( $acc_class ); ?>">
								<?php echo esc_html( $faq['etn_faq_title'] ); ?>
								<i class="etn-icon <?php echo $acc_class ? 'etn-minus' : 'etn-plus'; ?>"></i>
							</h4>

							<p class="etn-acccordion-contents <?php echo esc_attr( $acc_class ); ?>">
								<?php
								if ( has_blocks( $faq['etn_faq_content'] ) ) {
									echo do_blocks( $faq['etn_faq_content'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- do_blocks() is a trusted WP core function processing Gutenberg block content.
								} else {
									echo esc_html( $faq['etn_faq_content'] );
								}
								?>
							</p>
						</div>
					<?php endforeach; ?>

				<?php else : ?>
					<div class="etn-event-faq-body">
						<?php echo esc_html__( 'No FAQ found!', 'eventin' ); ?>
					</div>
				<?php endif; ?>
			</div>

		<?php endif;
	}


}