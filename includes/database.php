<?php
global $inquiries_db_version;

$inquiries_db_version = '0.1';

function inquiries_install() {
	global $wpdb;
	global $inquiries_db_version;

	$table_name = $wpdb->prefix . 'inquiries';

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		email tinytext NOT NULL,
		name tinytext NOT NULL,
		message text NOT NULL,
		created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		deleted_at datetime,
		PRIMARY KEY (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'inquiries_db_version', $inquiries_db_version );
}

register_activation_hook( __FILE__, 'inquiries_install' );

function inquiries_db_check() {
	global $inquiries_db_version;

	if ( get_site_option( 'inquiries_db_version' ) != $inquiries_db_version ) {
		inquiries_install();
	}
}

add_action( 'plugins_loaded', 'inquiries_db_check' );