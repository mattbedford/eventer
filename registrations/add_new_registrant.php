<?php
$path = preg_replace('/wp-content.*$/','',__DIR__);
include($path.'wp-load.php'); 

//Registering a new user to the event. Status is either: "Pending", "Free entry" or "Paid".
// Should return an ID from our DB only for "pending" users.
function register_new_attendee($user_data, $amount_paid, $status) {
    //Log incoming data
    $t = date("Y-m-d h:i A", time());
    file_put_contents('registration-log.txt', "NEW REGISTRATION" . "\n", FILE_APPEND);
    file_put_contents('registration-log.txt', "Billed: " . $amount_paid . " | Status: " . $status . " TIME: " . $t . "\n", FILE_APPEND);
	file_put_contents('registration-log.txt', print_r($user_data, true . "\n \n"), FILE_APPEND);

    global $wpdb;
	
	$table_name = $wpdb->prefix . 'registrations';
	$sign_up_date = date("Y-m-d H:i:s");

	if(!isset($user_data['title']) || empty($user_data['title'])) {
        $user_data['title'] = null;
    }

    if(!isset($user_data['interests']) || empty($user_data['interests'])) {
        $user_data['interests'] = null;
    }

    if(!isset($user_data['mobile']) || empty($user_data['mobile'])) {
        $user_data['mobile'] = null;
    }

    if(!isset($user_data['hs_synched']) || empty($user_data['hs_synched'])) {
        $user_data['hs_synched'] = 0;
    }
	
	$wpdb->insert( 
		$table_name, 
		array( 
            'title' => $user_data['title'],
			'name' => $user_data['fname'], 
			'surname' => $user_data['lname'],
			'email' => $user_data['email'], 
			'company' => $user_data['company'],
            'my_company_is' => $user_data['my_company_is'],
            'street_address' => $user_data['address'],
            'city' => $user_data['city'],
            'country' => $user_data['country'],
            't_and_c' => $user_data['mkt'],
            'interests' => $user_data['tags'],
            'mobile_phone' => $user_data['mobile'],
            'office_phone' => $user_data['office'],
            'website' => $user_data['website'],
            'postcode' => $user_data['postcode'],
			'role' => $user_data['role'],
			'paid' => $amount_paid,
            'payment_status' => $status,
			'coupon_code' => $user_data['coupon'],
			'sign_up_date' => $sign_up_date,
			'printed' => '0',
            'hs_synched' => $user_data['hs_synched']
		) 
	);

    if($status == "pending" || $status == "Pending") {
        if($wpdb->insert_id !== false) {
            $lastid = $wpdb->insert_id;
            return $lastid;
        } else {
            return "unknown";
        }
    }
}