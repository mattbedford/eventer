<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


function run_database_set_up() {
    
	//Badges & registrations table
	global $wpdb;

	$table_name = $wpdb->prefix . 'registrations';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		name text NOT NULL,
		surname text NOT NULL,
		email text NOT NULL,
		company text NOT NULL,
		role text,
		paid int(9),
		coupon_code text NOT NULL,
		sign_up_date DATE NOT NULL,
		printed tinyint(1) DEFAULT '0' NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}