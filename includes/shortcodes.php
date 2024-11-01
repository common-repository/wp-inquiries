<?php
function inquiries_shortcode() {
	$html = '<form method="post" class="inquiries-form">';
	$html .= '<p><label><span class="screen-reader-text">' . __( 'Your Name', 'wp-inquiries' ) . '</span></label>';
	$html .= '<input type="text" name="inquiry_name" placeholder="' . __( 'Your Name', 'wp-inquiries' ) . '" required></p>';
	$html .= '<p><label><span class="screen-reader-text">' . __( 'Your Email', 'wp-inquiries' ) . '</span></label>';
	$html .= '<input type="email" name="inquiry_email" placeholder="' . __( 'Your Email', 'wp-inquiries' ) . '" required></p>';
	$html .= '<p><label><span class="screen-reader-text">' . __( 'Your Message', 'wp-inquiries' ) . '</span></label>';
	$html .= '<textarea name="inquiry_message" rows="5" placeholder="' . __( 'Your Message', 'wp-inquiries' ) . '" required></textarea></p>';
	$html .= '<p><input type="submit" value="' . __( 'Send', 'wp-inquiries' ) . '" data-sending="' . __( 'Sending...', 'wp-inquiries' ) . '"></p>';
	$html .= '</form>';

	return $html;
}

add_shortcode( 'wp-inquiries', 'inquiries_shortcode' );

function inquiries_enqueue_scripts() {
	wp_enqueue_style( 'wp-inquiries', INQUIRIES_PLUGIN_URL . '/css/wp-inquiries.css', array(), INQUIRIES_VERSION );
	wp_enqueue_script( 'wp-inquiries', INQUIRIES_PLUGIN_URL . '/js/wp-inquiries.js', array( 'jquery' ), INQUIRIES_VERSION, true );
	wp_localize_script( 'wp-inquiries', 'inquiries_ajax_object', array(
		'ajax_nonce' => wp_create_nonce( 'inquiries_ajax_nonce' ),
		'ajax_url'   => admin_url( 'admin-ajax.php' )
	) );
}

add_action( 'wp_enqueue_scripts', 'inquiries_enqueue_scripts' );