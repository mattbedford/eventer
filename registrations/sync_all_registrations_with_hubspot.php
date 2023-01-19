<?php
$path = preg_replace('/wp-content.*$/','',__DIR__);
include($path.'wp-load.php'); 
require_once plugin_dir_path( __FILE__ ) . "add_new_registrant.php";
require_once plugin_dir_path( __FILE__ ) . "add_registrant_to_hubspot.php";

//Testing only
$x = do_site_to_hubspot_sync();

function get_missing_registrations_from_hs() {
	global $wpdb;
	$reg_table = $wpdb->prefix . 'registrations';
	$result = $wpdb->get_results ( "SELECT * FROM $reg_table" );
	$db_entries = array();
	foreach($result as $row) {
		$db_entries[] = $row->email;
	}

	$offset = null;
	$list_id = '376';//get_option('hubspot_list');
	$url = 'https://api.hubapi.com/contacts/v1/lists/' . $list_id . '/contacts/all?count=100&&property=firstname&property=lastname&property=phone&property=website&property=email&property=company_type&property=company&property=address&property=zip&property=country&property=city&property=jobtitle&vidOffset=' . $offset;

	//Make first call
	$x = grab_hs_list($url);

	//Grab decoded info from call: has-more, next contact for offset, contacts list
	$init_call = (array)json_decode($x);
	$more_flag = $init_call['has-more'];
	$offset = $init_call['vid-offset'];
	$all_contacts = $init_call['contacts'];

	//Loop for additional contacts. Push all into all_contacts array.
	while ($more_flag == true) {
		$y = grab_hs_list($url);
		$more = (array)json_decode($y);
		array_merge($all_contacts, $more['contacts']);
		$more_flag = $more['has_more'];
		$offset = $more['vid-offset'];
	}

    //Create new array, $results, with anyone who is listed in hubspot registrations but is not in DB table.
	$results = array();
	$n = 0;
	foreach($all_contacts as $single_contact) {
        $mail = $single_contact->properties->email->value;
        if(!empty($mail) && !in_array($mail, $db_entries)) {
		
    foreach($single_contact->properties as $key => $val) {
        $keyval_arr[$n][$key] = validate_hubspot_return_vals($key, $val->value);
    }
       
    $results[$n]['title'] = validate_hubspot_return_vals('title', $keyval_arr[$n]['title']);
    $results[$n]['fname'] = validate_hubspot_return_vals('name', $keyval_arr[$n]['firstname']);
    $results[$n]['lname'] = validate_hubspot_return_vals('surname', $keyval_arr[$n]['lastname']);
    $results[$n]['email'] = validate_hubspot_return_vals('email', $keyval_arr[$n]['email']);
    $results[$n]['company'] = validate_hubspot_return_vals('company', $keyval_arr[$n]['company']);
    $results[$n]['my_company_is'] = validate_hubspot_return_vals('company_type', $keyval_arr[$n]['company_type']);
    $results[$n]['role'] = validate_hubspot_return_vals('role', $keyval_arr[$n]['jobtitle']);
    $results[$n]['office_phone'] = validate_hubspot_return_vals('office_phone', $keyval_arr[$n]['phone']);
    $results[$n]['website'] = validate_hubspot_return_vals('website', $keyval_arr[$n]['website']);
    $results[$n]['street_address'] = validate_hubspot_return_vals('address', $keyval_arr[$n]['address']);
    $results[$n]['postcode'] = validate_hubspot_return_vals('zip', $keyval_arr[$n]['zip']);
    $results[$n]['city'] = validate_hubspot_return_vals('city', $keyval_arr[$n]['city']);
    $results[$n]['country'] = validate_hubspot_return_vals('country', $keyval_arr[$n]['country']);
    $results[$n]['mobile_phone'] = validate_hubspot_return_vals('mobile', $keyval_arr[$n]['mobile']);
    $results[$n]['hs_synched'] = 1;
    $results[$n]['mkt'] = 1;
    $results[$n]['printed'] = 0;
    $results[$n]['paid'] = 0;
    $results[$n]['payment_status'] = "Free entry";
    
    $n++;  
   }
}

	foreach($results as $single_reg) {
       register_new_attendee($single_reg, $single_reg['paid'], $single_reg['payment_status'] );
    }
  
  	//Then... time to push any unsynched users from our site into hs.
  	do_site_to_hubspot_sync();
  
}



//API interrogator tool
function grab_hs_list($url) {
	$hubspot_token = get_option('hubspot_key');
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array(
		'Content-Type:application/json',
		'authorization: Bearer ' . $hubspot_token
	));

	$data = curl_exec($curl);
	curl_close($curl);
	return $data;
}

function validate_hubspot_return_vals($key, $val) {
    if(!isset($val) || $val == NULL || empty($val) || $val == "" || $val == " " ) {
        $val = "Unknown";
        if ($key == "company_type") $val = "Other";
        if ($key == "gender" || $key == "title") $val = NULL;
    }
    return $val;
}

function do_site_to_hubspot_sync() {
 	global $wpdb;
	$reg_table = $wpdb->prefix . 'registrations';
	$result = $wpdb->get_results ( "SELECT * FROM $reg_table WHERE 'hs_synched' = 0" );
	$unsycnhed_db_entries = (array) $result;
	foreach($unsycnhed_db_entries as $row) {
	  $res = set_up_and_send_new_contact($row);
	}
}
