<?php


/*
Plugin Name: Eventer
Description: An event management interface to help set up perfect events.
Plugin URI:  https://dagora.ch
Author:      Matt Bedford
Version:     6.3
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

// NEW VERSION 6
//Load up scripts and styles for our admin "mission control" page
add_action( 'admin_enqueue_scripts', 'load_vue_core' );
function load_vue_core($hook) {
	if($hook !== 'toplevel_page_eventer') return;

	$domain = site_url();
	$on_staging = strpos($domain, '.local');

	switch ($on_staging !== false) {
		case true:
			$app_root = "http://localhost:8080/js/";
			$app = "app.js";
			$chunks = "chunk-vendors.js";
			$css = "";
			break;
		
		case false:
			$app_root = site_url() . "/wp-content/plugins/eventer/dist/js/";
			$app = "app/eventerWebpack.js";
			$chunks = "chunk-vendors/eventerWebpack.js";
			wp_enqueue_style('good-table-styles', site_url() . "/wp-content/plugins/eventer/dist/css/app.b90c420f.css");
			break;

		default:
			$app_root = site_url() . "/wp-content/plugins/eventer/dist/js/";
			$app = "app/eventerWebpack.js";
			$chunks = "chunk-vendors/eventerWebpack.js";
			wp_enqueue_style('good-table-styles', site_url() . "/wp-content/plugins/eventer/dist/css/app.b90c420f.css");
			break;
	}

	wp_register_script( 'wp-api-mine', false );
	wp_localize_script( 'wp-api-mine', 'wpApiSettings', array(
		'nonce' => wp_create_nonce( 'wp_rest' )
	) );
	
	wp_register_script( // the app build script generated by Webpack.
		'vue_app',
		$app_root . $app,
		array(),
		false,
		true
	); 
	wp_register_script( // the app build script generated by Webpack.
		'vue_chunks',
		$app_root . $chunks,
		array(),
		false,
		true
	);
	
	wp_enqueue_style( 'vue_styles', plugins_url('assets/styles.css', __FILE__), array(), '');
	wp_enqueue_script('wp-api-mine');
	wp_enqueue_script('vue_app');
	wp_enqueue_script('vue_chunks');

}

