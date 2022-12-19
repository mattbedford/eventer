<?php


/*
Plugin Name: Eventer
Description: An event management interface to help set up perfect events.
Plugin URI:  https://dagora.ch
Author:      Matt Bedford
Version:     2.0
License:     GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.txt

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 
2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
with this program. If not, visit: https://www.gnu.org/licenses/

*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// include dependencies
require plugin_dir_path( __FILE__ ) . 'event_globals.php';
require plugin_dir_path( __FILE__ ) . 'api_routes.php';
require plugin_dir_path( __FILE__ ) . 'event_api_callbacks.php';

// On activate, trigger new menu class
register_activation_hook( __FILE__, 'do_event_start_up' ); 
function do_event_start_up() {
	require plugin_dir_path( __FILE__ ) . '/set-up/database_set_up.php';
	require plugin_dir_path( __FILE__ ) . '/set-up/create_pages.php';
	run_database_set_up();
	run_page_creation();
}

//Create menu page
function do_eventer() {
	add_menu_page( 
		'Eventer',
		'Mission control',
		'manage_options',
		'eventer',
		'event_setup',
		'dashicons-admin-site-alt3', 
		1
	); 
}
add_action( 'admin_menu', 'do_eventer' );


//Callback for menu page adds only the div for vue instantiation
function event_setup() { 
	echo "<div id='app'></div>";
}

//Load up scripts and styles for our admin "mission control" page
add_action( 'admin_enqueue_scripts', 'load_vue_core' );
function load_vue_core($hook) {
	if($hook !== 'toplevel_page_eventer') return;

	wp_localize_script( 'wp-api', 'wpApiSettings', array(
		'nonce' => wp_create_nonce( 'wp_rest' )
	) );
	
	wp_register_script( // the app build script generated by Webpack.
		'vue_app',
		//'http://localhost:8080/js/app.js',
		site_url() . '/wp-content/plugins/eventer/core/dist/js/app.e106c47c.js',
		array(),
		false,
		true
	); 
	wp_register_script( // the app build script generated by Webpack.
		'vue_chunks',
		//'http://localhost:8080/js/chunk-vendors.js',
		site_url() . '/wp-content/plugins/eventer/core/dist/js/chunk-vendors.a2ab937e.js',
		array(),
		false,
		true
	);
	wp_enqueue_style('good-table-styles', site_url() . '/wp-content/plugins/eventer/core/dist/css/app.b90c420f.css');
	wp_enqueue_style( 'vue_styles', plugins_url('assets/styles.css', __FILE__), array(), '');
	wp_enqueue_script('wp-api');
	wp_enqueue_script('vue_app');
	wp_enqueue_script('vue_chunks');
}
