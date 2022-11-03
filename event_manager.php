<?php


/*
Plugin Name: Dagora event manager
Description: Simplified interface to help set up perfect events.
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

// On activate, trigger new menu class
//register_activation_hook( __FILE__, 'do_event_start_up' ); 

//function do_event_start_up() {
	
//}

