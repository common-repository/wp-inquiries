<?php

class Inquiries_REST_API extends WP_REST_Controller {
	public function register_routes() {
		$version   = 0;
		$namespace = 'wp-inquiries/v' . $version;

		register_rest_route( $namespace, '/inquiries', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( 'Inquiries_REST_API', 'get_items' ),
				'permission_callback' => array( 'Inquiries_REST_API', 'permissions_check' ),
				'args'                => array(
					'page_number' => array(
						'description'       => __( 'Current page of the collection.', 'wp-inquiries' ),
						'type'              => 'integer',
						'default'           => 1,
						'sanitize_callback' => 'absint'
					),
					'per_page'    => array(
						'description'       => __( 'Maximum number of items to be returned in result set.', 'wp-inquiries' ),
						'type'              => 'integer',
						'default'           => 10,
						'sanitize_callback' => 'absint'
					),
					's'           => array(
						'description'       => __( 'Limit results to those matching a string.', 'wp-inquiries' ),
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field'
					),
					'orderby'     => array(
						'description'       => __( 'Order by.', 'wp-inquiries' ),
						'type'              => 'string',
						'default'           => 'created_at',
						'sanitize_callback' => 'sanitize_text_field'
					),
					'order'       => array(
						'description'       => __( 'Ascending or Descending.', 'wp-inquiries' ),
						'type'              => 'string',
						'default'           => 'ASC',
						'sanitize_callback' => 'sanitize_text_field'
					)
				)
			),
			array(
				'methods'  => WP_REST_Server::CREATABLE,
				'callback' => array( 'Inquiries_REST_API', 'create_item' ),
				'args'     => array(
					'email'   => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_email',
						'description'       => __( 'Your Email', 'wp-inquiries' )
					),
					'name'    => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'description'       => __( 'Your Name', 'wp-inquiries' )
					),
					'message' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_textarea_field',
						'description'       => __( 'Your Message', 'wp-inquiries' )
					),
				)
			)
		) );

		register_rest_route( $namespace, '/inquiry/(?P<id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( 'Inquiries_REST_API', 'update_item' ),
				'permission_callback' => array( 'Inquiries_REST_API', 'permissions_check' ),
				'args'                => array(
					'id' => array(
						'required'          => true,
						'type'              => 'int',
						'sanitize_callback' => 'absint',
						'description'       => __( 'The ID of the inquiry.', 'wp-inquiries' )
					)
				)
			)
		) );
	}

	public function get_items( $request ) {
		global $wpdb;

		$search  = $wpdb->esc_like( $request['s'] );
		$orderby = esc_sql( $request['orderby'] );
		$order   = esc_sql( $request['order'] );
		$limit   = esc_sql( $request['per_page'] );
		$offset  = ( esc_sql( $request['page_number'] ) - 1 ) * $limit;

		$sql = "SELECT * FROM {$wpdb->prefix}inquiries WHERE deleted_at IS NULL";

		if ( ! empty( $search ) ) {
			$sql .= " AND message LIKE '%" . $search . "%'";
		}

		if ( ! empty( $orderby ) ) {
			$sql .= ' ORDER BY ' . $orderby;
			$sql .= ! empty( $order ) ? ' ' . $order : ' ASC';
		} else {
			$sql .= ' ORDER BY created_at DESC';
		}

		$sql .= " LIMIT $limit OFFSET $offset";

		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

		return new WP_REST_Response( $result, 200 );
	}

	public function create_item( $request ) {
		global $wpdb;

		$email   = $request['email'];
		$name    = $request['name'];
		$message = $request['message'];

		if ( empty( $email ) ) {
			return new WP_Error( 'cant-create', __( 'Please input your email.', 'wp-inquiries' ), array( 'status' => 500 ) );
		} else {
			if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
				return new WP_Error( 'cant-create', __( 'Please input a valid email format.', 'wp-inquiries' ), array( 'status' => 500 ) );
			}
		}

		if ( empty( $name ) ) {
			return new WP_Error( 'cant-create', __( 'Please input your name.', 'wp-inquiries' ), array( 'status' => 500 ) );
		}

		if ( empty( $message ) ) {
			return new WP_Error( 'cant-create', __( 'Please input your message.', 'wp-inquiries' ), array( 'status' => 500 ) );
		}

		$value  = array(
			'email'      => $email,
			'name'       => $name,
			'message'    => $message,
			'created_at' => date_i18n( 'Y-m-d H:i:s' )
		);
		$format = array( '%s', '%s', '%s', '%s' );

		if ( ! $wpdb->insert( $wpdb->prefix . 'inquiries', $value, $format ) ) {
			return new WP_Error( 'cant-create', __( 'Sorry, we failed to store your message.', 'wp-inquiries' ), array( 'status' => 500 ) );
		}

		$to      = get_bloginfo( 'admin_email' );
		$subject = __( 'Inquiry from', 'wp-inquiries' ) . ' ' . get_bloginfo( 'name' ) . ' <' . get_bloginfo( 'url' ) . '>';
		$headers = __( 'From', 'wp-inquiries' ) . ': ' . $name . ' <' . $email . '>';

		if ( ! wp_mail( $to, $subject, $message, $headers ) ) {
			return new WP_Error( 'cant-create', __( 'Sorry, we failed to send your message.', 'wp-inquiries' ), array( 'status' => 500 ) );
		}

		$response['type']    = 'success';
		$response['message'] = __( 'Thank you for your message. We will contact you soon.', 'wp-inquiries' );

		return new WP_REST_Response( $response, 200 );
	}

	public function update_item( $request ) {
		global $wpdb;

		if (
		$wpdb->update(
			"{$wpdb->prefix}inquiries",
			array( 'deleted_at' => date_i18n( 'Y-m-d H:i:s' ) ),
			array( 'ID' => esc_sql( $request['id'] ) ),
			array( '%s' ),
			array( '%d' )
		)
		) {
			$response['type']    = 'success';
			$response['message'] = __( 'The inquiry has been successfully deleted.', 'wp-inquiries' );

			return new WP_REST_Response( $response, 200 );
		}

		return new WP_Error( 'cant-update', __( 'Sorry, we can not update an inquiry at this moment.', 'wp-inquiries' ), array( 'status' => 500 ) );
	}

	public static function permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}
}