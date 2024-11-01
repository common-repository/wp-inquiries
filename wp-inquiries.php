<?php
/**
 * Plugin Name: WP Inquiries
 * Plugin URI: https://aristorinjuang.com/wp-inquiries.html
 * Description: A simple contact form plugin that record inquiries.
 * Version: 0.2.1
 * Author: Aristo Rinjuang
 * Author URI: https://aristorinjuang.com
 * Text Domain: wp-inquiries
 * Domain Path: /languages
 * License: GNU General Public License version 2
 *
 * WP Inquiries is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * WP Inquiries is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WP Inquiries. If not, see https://opensource.org/licenses/GPL-2.0.
 */


define( 'INQUIRIES_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'INQUIRIES_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'INQUIRIES_VERSION', '0.2.1' );

if ( ! class_exists( 'Inquiries_List_Table' ) ) {
	require_once( INQUIRIES_PLUGIN_DIR . 'classes/inquiries-list-table.php' );
}

if ( ! class_exists( 'Inquiries_REST_API' ) ) {
	require_once( INQUIRIES_PLUGIN_DIR . 'classes/inquiries-rest-api.php' );
}

require_once( INQUIRIES_PLUGIN_DIR . 'includes/database.php' );
require_once( INQUIRIES_PLUGIN_DIR . 'includes/settings.php' );
require_once( INQUIRIES_PLUGIN_DIR . 'includes/shortcodes.php' );
require_once( INQUIRIES_PLUGIN_DIR . 'includes/ajax.php' );