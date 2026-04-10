<?php
defined( 'ABSPATH' ) || exit;

$author_id = get_queried_object_id();

$etn_speaker_summary = !empty(get_user_meta( $author_id, 'etn_speaker_summery', true) ) ? get_user_meta( $author_id, 'etn_speaker_summery', true) : get_user_meta( $author_id, 'organizer_bio', true);
?>
<div class="etn-speaker-summery">
    <?php echo wp_kses_post( wpautop($etn_speaker_summary) ); ?>
</div>
