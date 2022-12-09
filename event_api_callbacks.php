<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function all_event_options() {
    $all_options = array(
        "event_name",
        "event_payoff",
        "event_date",
        "event_start",
        "event_end",
        "alt_stripe_key",
        "stripe_webhook",
        "event_tag",
        "hubspot_list",
        "hubspot_key",
        "venue_name",
        "venue_address",
        "venue_city",
        "venue_country",
        "max_attendees",
        "badge_template",
        "ticket_price",
        "badge_x",
        "badge_y",
        "badge_x_p2",
        "badge_y_p2",
    );
    $return_array = array();

    foreach($all_options as $single_opt) {
        $existing_option = get_option($single_opt);
        if(!empty($existing_option) && $existing_option !== FALSE) {
            $return_array[] = array($single_opt, $existing_option);
        }
    }

    echo json_encode($return_array);
    die();
}


function set_the_options($args) {
    $data = $args->get_json_params();

    $permitted = array(
            "event_name",
            "event_payoff",
            "event_date",
            "event_start",
            "event_end",
            "alt_stripe_key",
            "stripe_webhook",
            "event_tag",
            "hubspot_list",
            "hubspot_key",
            "venue_name",
            "venue_address",
            "venue_city",
            "venue_country",
            "max_attendees",
            "badge_template",
            "ticket_price",
            "badge_x",
            "badge_y",
            "badge_x_p2",
            "badge_y_p2",
    );
    $errors = array();

    foreach($data as $key => $val) {
        $new_option = strval($key);
        $new_value = stripslashes(strip_tags(trim($val)));

        // Make sure options are legit
        if(!in_array($new_option, $permitted)) return "Sorry, you submitted a forbidden option";

        // Catch any empty fileds in form submission
        if($new_value == null || empty($new_value)) continue;

        update_option($new_option, $new_value);
        $option_check = get_option($new_option);

        if($option_check !== $new_value) {
            $errors[] = $new_option;
        }
    }

    if(empty($errors)) {
        $return_status = "Success";
        $return_string = ("All options saved correctly.");
    }
    else {
        $return_status = "Watch out";
        $return_string = "Options saved but we found errors in the following fields: ";
        foreach($errors as $err) {
            $return_string .= " " . $err;
        }
        $return_string .= ". Please review and try again";
    }

    echo json_encode(array($return_status, $return_string));
    die();
}


function all_event_registrations() {
    global $wpdb;
    $table = $wpdb->prefix . 'registrations';
    $result = $wpdb->get_results ( "SELECT * FROM $table" );

    echo json_encode($result);
    die();
}


function edit_registration($data) {
    $x = $data->get_json_params();
    $cleaned_data = array_map('sanitize_it', $x);
    
    $reg_id = $cleaned_data['id'];
    $reg_action = $cleaned_data['command'];

    // Based on command given, we either channel the request into one of 3 different actions: to create, edit, or delete a registration.
    switch ($reg_action) {
    case 'create':
        $res = create_new_registration($cleaned_data);
        break;
    case 'edit':
        $res = edit_existing_registration($cleaned_data);
        break;
    case 'delete':
        $res = delete_existing_registration($cleaned_data);
        break;
    default: 
        //If we can't figure it out, bail and return error message.
        echo json_encode(array('Sorry!', 'Could not identify the correct registration record to update.'));
        die();
    }

    //Res will be a two-part array with STATUS and a user-friendly message.
    echo json_encode($res);
    die();
}

function create_new_registration($data) {
    global $wpdb;
	$table_name = $wpdb->prefix . 'registrations';

	$email = $data['email'] ? $data['email'] : "unknown";
	$name = $data['name'] ? $data['name'] : "unknown";
	$surname = $data['surname'] ? $data['surname'] : "unknown";
	$company = $data['company'] ? $data['company'] : "unknown";
	$role = $data['role'] ? $data['role'] : "unknown";
    $city = $data['city'] ? $data['city'] : "unknown";
    $country = $data['country'] ? $data['country'] : "unknown";
    $mobile_phone = $data['mobile_phone'] ? $data['mobile_phone'] : "unknown";
    $office_phone = $data['office_phone'] ? $data['office_phone'] : "unknown";
    $postcode = $data['postcode'] ? $data['postcode'] : "unknown";
    $street_address = $data['street_address'] ? $data['street_address'] : "unknown";
    $website = $data['website'] ? $data['website'] : "https://unknown.com";
    $coupon_code = "none";
    $sign_up_date = date("Y-m-d H:i:s");
    $t_and_c = "1";
    $paid = "0";
    $payment_status = "Free entry";
		
	$create = $wpdb->insert( 
		$table_name, 
		array(  
            'name' => $name, 
            'surname' => $surname, 
            'email' => $email, 
            'company' => $company, 
            'role' => $role, 
            'city' => $city, 
            'country' => $country, 
            'mobile_phone' => $mobile_phone, 
            'office_phone' => $office_phone, 
            'postcode' => $postcode,
            'street_address' => $street_address,
            'website' => $website,
            'sign_up_date' => $sign_up_date,
            't_and_c' => $t_and_c,
            'coupon_code' => $coupon_code,
            'paid' => $paid,
            'payment_status' => $payment_status

        ) );

    if($create !== false) {
		$val = array("Success", "New registration successfully created");
		return $val;
	}
	$val = array("Uh oh", "Registration was not created. We don't know any more than that, sorry.");
    return $val;
}

function edit_existing_registration($data) {
    global $wpdb;
	$my_table = $wpdb->prefix . 'registrations';
	$id = intval($data['id']);

	$email = $data['email'];
	$name = $data['name'];
	$surname = $data['surname'];
	$company = $data['company'];
	$role = $data['role'];
    $city = $data['city'];
    $country = $data['country'];
    $mobile_phone = $data['mobile_phone'];
    $office_phone = $data['office_phone'];
    $postcode = $data['postcode'];
    $street_address = $data['street_address'];
    $website = $data['website'];
		
	$chk = $wpdb->query( $wpdb->prepare( 
		"
			UPDATE $my_table 
			SET name = %s, 
            surname = %s, 
            email = %s, 
            company = %s, 
            role = %s, 
            city = %s, 
            country = %s, 
            mobile_phone = %s, 
            office_phone = %s, 
            postcode = %s,
            street_address = %s,
            website = %s
			WHERE id = %d
		",
	$name, $surname, $email, $company, $role, $city, $country, $mobile_phone, $office_phone, $postcode, $street_address, $website, $id
	) );

    if($chk !== false) {
		$val = array("Success", "Registration details updated correctly");
		return $val;
	}
	$val = array("Uh oh", "Registration update failed. We don't know any more than that, sorry.");
    return $val;
}

function delete_existing_registration($data) {
    $id = intval($data['id']);
	global $wpdb;
	$reg_table = $wpdb->prefix . 'registrations';
	$res = $wpdb->delete( $reg_table, array( 'id' => $id ) );
    if($res !== false) {
        return array("Success", "Registration deleted successfully.");
    }
    return array("Uh oh", "Registration could not be deleted, sorry.");
}

function sanitize_it($el) {
    $sanitized_el = htmlspecialchars(strip_tags(trim($el)));
    return $sanitized_el;
}