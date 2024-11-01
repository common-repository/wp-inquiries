<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Inquiries_List_Table extends WP_List_Table {
	public function __construct() {
		parent::__construct( [
			'singular' => __( 'Inquiry', 'wp-inquiries' ),
			'plural'   => __( 'Inquiries', 'wp-inquiries' ),
			'ajax'     => false
		] );
	}

	public static function get_inquiries( $per_page = 10, $page_number = 1 ) {
		global $wpdb;

		$search  = ! empty( $_REQUEST['s'] ) ? $wpdb->esc_like( $_REQUEST['s'] ) : '';
		$orderby = ! empty( $_REQUEST['orderby'] ) ? esc_sql( $_REQUEST['orderby'] ) : '';
		$order   = ! empty( $_REQUEST['order'] ) ? esc_sql( $_REQUEST['order'] ) : '';
		$limit   = esc_sql( $per_page );
		$offset  = ( esc_sql( $page_number ) - 1 ) * $limit;

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

		return $wpdb->get_results( $sql, 'ARRAY_A' );
	}

	public static function delete_inquiry( $id ) {
		global $wpdb;

		$wpdb->update(
			"{$wpdb->prefix}inquiries",
			array( 'deleted_at' => date_i18n( 'Y-m-d H:i:s' ) ),
			array( 'ID' => esc_sql( $id ) ),
			array( '%s' ),
			array( '%d' )
		);
	}

	public static function total_inquiries() {
		global $wpdb;

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}inquiries WHERE deleted_at IS NULL";

		return $wpdb->get_var( $sql );
	}

	function get_columns() {
		return array(
			'cb'         => '<input type="checkbox">',
			'_title'     => __( 'Email', 'wp-inquiries' ),
			'sender'     => __( 'Name', 'wp-inquiries' ),
			'comment'    => __( 'Message', 'wp-inquiries' ),
			'created_at' => __( 'Date', 'wp-inquiries' )
		);
	}

	function get_sortable_columns() {
		return array(
			'_title'     => array(
				'email',
				false
			),
			'sender'     => array(
				'name',
				false
			),
			'created_at' => array(
				'created_at',
				false
			)
		);
	}

	public function process_bulk_action() {
		if (
			'delete' === $this->current_action() &&
			wp_verify_nonce( esc_attr( $_REQUEST['nonce'] ), 'inquiries_delete_inquiry' )
		) {
			$this->delete_inquiry( absint( $_GET['inquiry'] ) );
			wp_redirect( esc_url_raw( add_query_arg() ) );
		}

		if (
			(
				( ! empty( $_REQUEST['action'] ) && $_REQUEST['action'] == 'delete' ) ||
				( ! empty( $_REQUEST['action2'] ) && $_REQUEST['action2'] == 'delete' )
			) &&
			! empty( $_REQUEST['_wpnonce'] ) &&
			wp_verify_nonce( esc_attr( $_REQUEST['_wpnonce'] ), 'bulk-' . $this->_args['plural'] )
		) {
			$delete_ids = esc_sql( $_REQUEST['inquiry'] );

			foreach ( $delete_ids as $id ) {
				$this->delete_inquiry( absint( $id ) );
			}

			wp_redirect( esc_url_raw( add_query_arg() ) );
		}
	}

	function prepare_items() {
		$this->_column_headers = $this->get_column_info();
		$this->process_bulk_action();

		$per_page     = $this->get_items_per_page( 'inquiries_per_page', 10 );
		$current_page = $this->get_pagenum();
		$total_items  = $this->total_inquiries();

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page
		) );

		$this->items = $this->get_inquiries( $per_page, $current_page );
	}

	function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case '_title':
				$delete_nonce = wp_create_nonce( 'inquiries_delete_inquiry' );

				$actions = array(
					'delete' => sprintf(
						'<a href="?page=%s&action=%s&inquiry=%s&nonce=%s">%s</a>',
						$_REQUEST['page'],
						'delete',
						$item['id'],
						$delete_nonce,
						__( 'Delete', 'wp-inquiries' )
					)
				);

				$email = '<a href="mailto:' . esc_html( $item['email'] ) . '">' . esc_html( $item['email'] ) . '</a>';

				return sprintf(
					'%1$s %2$s',
					$email,
					$this->row_actions( $actions )
				);
			case 'created_at':
				return date_i18n( 'F j, Y, g:i a', strtotime( $item['created_at'] ) );
			case 'sender':
				return esc_html( $item['name'] );
			case 'comment':
				return esc_html( $item['message'] );
		}

		return '';
	}

	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="inquiry[]" value="%s">',
			$item['id']
		);
	}

	function usort_reorder( $a, $b ) {
		$orderby = ( ! empty( $_GET['orderby'] ) ) ? esc_sql( $_GET['orderby'] ) : 'date';
		$order   = ( ! empty( $_GET['order'] ) ) ? esc_sql( $_GET['order'] ) : 'desc';
		$result  = strcmp( $a[ $orderby ], $b[ $orderby ] );

		return ( $order === 'asc' ) ? $result : - $result;
	}

	function get_bulk_actions() {
		return array(
			'delete' => __( 'Delete', 'wp-inquiries' )
		);
	}

	function no_items() {
		_e( 'There are no inquiries at this moment.', 'wp-inquiries' );
	}
}