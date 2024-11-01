<?php
function inquiries_ajax_action() {
	wp_verify_nonce( $_POST['nonce'], 'inquiries_ajax_nonce' ) || wp_die();

	global $wpdb;

	$email   = sanitize_email( $_POST['email'] );
	$name    = sanitize_text_field( $_POST['name'] );
	$message = sanitize_textarea_field( $_POST['message'] );

	if ( empty( $email ) ) {
		$response['type']    = 'error';
		$response['message'] = __( 'Please input your email.', 'wp-inquiries' );

		wp_send_json( $response );

		wp_die();
	} else {
		if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			$response['type']    = 'error';
			$response['message'] = __( 'Please input a valid email format.', 'wp-inquiries' );

			wp_send_json( $response );

			wp_die();
		}
	}

	if ( empty( $name ) ) {
		$response['type']    = 'error';
		$response['message'] = __( 'Please input your name.', 'wp-inquiries' );

		wp_send_json( $response );

		wp_die();
	}

	if ( empty( $message ) ) {
		$response['type']    = 'error';
		$response['message'] = __( 'Please input your message.', 'wp-inquiries' );

		wp_send_json( $response );

		wp_die();
	}

	$value  = array(
		'email'      => $email,
		'name'       => $name,
		'message'    => $message,
		'created_at' => date_i18n( 'Y-m-d H:i:s' )
	);
	$format = array( '%s', '%s', '%s', '%s' );

	if ( ! $wpdb->insert( $wpdb->prefix . 'inquiries', $value, $format ) ) {
		$response['type']    = 'error';
		$response['message'] = __( 'Sorry, we failed to store your message.', 'wp-inquiries' );

		wp_send_json( $response );

		wp_die();
	}

	$to      = get_bloginfo( 'admin_email' );
	$subject = __( 'Inquiry from', 'wp-inquiries' ) . ' ' . get_bloginfo( 'name' ) . ' <' . get_bloginfo( 'url' ) . '>';
	$headers = __( 'From', 'wp-inquiries' ) . ': ' . $name . ' <' . $email . '>';

	if ( ! wp_mail( $to, $subject, $message, $headers ) ) {
		$response['type']    = 'error';
		$response['message'] = __( 'Sorry, we failed to send your message.', 'wp-inquiries' );

		wp_send_json( $response );

		wp_die();
	}

	$response['type']    = 'success';
	$response['message'] = __( 'Thank you for your message. We will contact you soon.', 'wp-inquiries' );

	wp_send_json( $response );

	wp_die();
}

add_action( 'wp_ajax_inquiries_ajax_action', 'inquiries_ajax_action' );
add_action( 'wp_ajax_nopriv_inquiries_ajax_action', 'inquiries_ajax_action' );