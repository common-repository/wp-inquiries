<?php
function inquiries_render() {
	global $inquiries_list_table;
	?>
    <div class="wrap">
        <h1><?php _e( 'Inquiries', 'wp-inquiries' ); ?></h1>
        <p><?php _e( 'Please add this shortcode to any page (eg: Contact Us) or widget that you want:', 'wp-inquiries' ); ?>
            <code>[wp-inquiries]</code></p>
        <form>
            <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>"/>
			<?php $inquiries_list_table->prepare_items(); ?>
			<?php $inquiries_list_table->search_box( __( 'Search Inquiries' ), 'inquiries' ); ?>
			<?php $inquiries_list_table->display(); ?>
        </form>
    </div>
	<?php
}

function inquiries_menu() {
	$hook = add_menu_page(
		__( 'Inquiries', 'wp-inquiries' ),
		__( 'Inquiries', 'wp-inquiries' ),
		'manage_options',
		'wp-inquiries',
		'inquiries_render',
		'dashicons-email',
		26
	);

	add_action( "load-$hook", 'inquiries_options' );
}

function inquiries_options() {
	global $inquiries_list_table;

	$option = 'per_page';
	$args   = array(
		'label'   => __( 'Inquiries', 'wp-inquiries' ),
		'default' => 10,
		'option'  => 'inquiries_per_page'
	);

	add_screen_option( $option, $args );

	$inquiries_list_table = new Inquiries_List_Table();
}

add_action( 'admin_menu', 'inquiries_menu' );

function inquiries_screen_options( $status, $option, $value ) {
	return $value;
}

add_filter( 'set-screen-option', 'inquiries_screen_options', 10, 3 );

add_action( 'rest_api_init', array( 'Inquiries_REST_API', 'register_routes' ) );