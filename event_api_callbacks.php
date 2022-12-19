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
    $company_is = $data['my_company_is'] ? $data['my_company_is'] : "unknown";
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
            'my_company_is' => $company_is,
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
    $company_is = $data['my_company_is'];
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
            my_company_is = %s,
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
	$name, $surname, $email, $company, $company_is, $role, $city, $country, $mobile_phone, $office_phone, $postcode, $street_address, $website, $id
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

function all_event_coupons() {
    $args = array(
        'post_type' => 'invitation',
        'numberposts' => -1,
        'fields' => 'ids'
    );
    $invitations = get_posts($args);
    $invitation_array = array();
    $n = 0;

    foreach($invitations as $sing_inv) {
        //Use $related to check if we have a registered post of this recipient or if it's a random person
        $related = get_post_meta($sing_inv, 'related_post', true);
        if(empty($related) || $related == false) {
            //If no related and registered user...
            $related_name = get_post_meta($sing_inv, 'recipient', true);
            $related_id = "other";
        } else {
            //But if we do have one...
            $related_name = get_the_title($related);
            $related_id = $related;
        }

        $headliners_ids = get_post_meta($sing_inv, 'custom_headliners', false);
        
        $invitation_array[$n]['with_headliners'] = "standard";
        $headliners = array();

        if(!empty($headliners_ids)) {
            $invitation_array[$n]['with_headliners'] = "custom";
            foreach($headliners_ids as $sing_id) {
                $headliners[] = intval($sing_id);
                //$headliners[] = array('id' => $sing_id, 'name' => get_the_title($sing_id));
            }
        } 


        $invitation_array[$n]['invitation_post_id'] = $sing_inv;
        $invitation_array[$n]['coupon_title'] = get_the_title($sing_inv);
        $invitation_array[$n]['invitation_type'] = get_post_meta($sing_inv, 'invitation_type', true);
        $invitation_array[$n]['headliners'] = $headliners;
        $invitation_array[$n]['recipient_name'] = $related_name;
        $invitation_array[$n]['recipient_id'] = $related_id;
        $invitation_array[$n]['max_uses'] = get_post_meta($sing_inv, 'max_uses', true);
        $invitation_array[$n]['actual_uses'] = get_post_meta($sing_inv, 'actual_uses', true);
        $invitation_array[$n]['discount'] = get_post_meta($sing_inv, 'percentage_value', true);
        $invitation_array[$n]['guest_status'] = get_post_meta($sing_inv, 'for_guests', true);
        $n++;
    }
    
    echo json_encode($invitation_array);
    die();
}

function all_people_and_orgs() {
    $args = array(
        'post_type' => array('speaker', 'company'),
        'numberposts' => -1,
    );
    $res = get_posts($args);

    $n = 0;
    $res_array = array();
    foreach($res as $peep) {
        $res_array[$n]['id'] = $peep->ID;
        $res_array[$n]['name'] = $peep->post_title;
        $n++;
    }
    echo json_encode($res_array);
    die();
}

function just_the_people() {
    $args = array(
        'post_type' => 'speaker',
        'numberposts' => -1,
    );
    $res = get_posts($args);

    $n = 0;
    $res_array = array();
    foreach($res as $peep) {
        $res_array[$n]['id'] = $peep->ID;
        $res_array[$n]['name'] = $peep->post_title;
        $n++;
    }
    echo json_encode($res_array);
    die();
}

function edit_coupon_or_invitation($data) {
    $x = $data->get_json_params();
    $y = $x;
    unset($y['headliners']);
    $cleaned_data = array_map('sanitize_it', $y); 
    $cleaned_headliners = array();

    //Sanitize, validate headliners separately then return to cleaned array as they get killed by 'sanitize_it' function.
    if(!empty($x['headliners'])) {
        foreach($x['headliners'] as $sing_headliner) {
            if('publish' === get_post_status( $sing_headliner )) {
                $cleaned_headliners[] = $sing_headliner;
            }
        }
    }
    $cleaned_data['headliners'] = $cleaned_headliners;
    if(!empty($cleaned_data['invitation_post_id'])) {
        $reg_id = $cleaned_data['invitation_post_id'];
    }
    $reg_action = $cleaned_data['command'];
    
    switch ($reg_action) {
        case 'create':
            $res = create_new_coupon($cleaned_data);
            break;
        case 'edit':
            $res = edit_existing_coupon($cleaned_data);
            break;
        case 'delete':
            $res = delete_existing_coupon($reg_id);
            break;
        default: 
            //If we can't figure it out, bail and return error message.
            echo json_encode(array('Sorry!', 'Could not identify the correct coupon record to update.'));
            die();
        }

    print_r(json_encode($res));
    die();
}

function create_new_coupon($data) {
    $for_guests = $data['guest_status'];

    //If non-named coupon/invitation...
    if($data['recipient_id'] === "other") {
        $related_post = null;
        $given_recipient = $data['recipient_name'];
    } else {
    //But if we have a related post instead, then...
        $related_post = $data['recipient_id'];
        $given_recipient = null;
    }

    if(!empty($data['headliners']) && $data['with_headliners'] == "custom") {
        $new_headliners = $data['headliners'];
    } else {
        $new_headliners = null;
    }

    $args = array(
        'title' => $data['coupon_title'],
        'invitation_type' => $data['invitation_type'],
        'custom_headliners' => $new_headliners,
        'for_guests' => $for_guests,
        'related_post' => $related_post,
        'recipient' => $given_recipient,
        'percentage_value' => $data['discount'],
        'max_uses' => $data['max_uses'],
    );

    $pod = pods('invitation');
    $new_invitation = $pod->add( $args );
    if('publish' === get_post_status( $new_invitation )) {
        return array('Great!', 'New coupon successfully created.');
    }
    return array('Uh oh.', 'Something went wrong and we could not save this coupon/invitation. Sorry.');
}
function edit_existing_coupon($data) {
    $id = intval($data['invitation_post_id']);
    $for_guests = $data['guest_status'];

    //If non-named coupon/invitation...
    if($data['recipient_id'] === "other") {
        $related_post = null;
        $given_recipient = $data['recipient_name'];
    } else {
    //But if we have a related post instead, then...
        $related_post = $data['recipient_id'];
        $given_recipient = null;
    }

    if(!empty($data['headliners']) && $data['with_headliners'] == "custom") {
        $new_headliners = $data['headliners'];
    } else {
        $new_headliners = null;
    }

    $args = array(
        'title' => $data['coupon_title'],
        'invitation_type' => $data['invitation_type'],
        'custom_headliners' => $new_headliners,
        'for_guests' => $for_guests,
        'related_post' => $related_post,
        'recipient' => $given_recipient,
        'percentage_value' => $data['discount'],
        'max_uses' => $data['max_uses'],
    );

    $pod = pods('invitation', $id);
    $new_invitation = $pod->save( $args );
    if('publish' === get_post_status( $new_invitation )) {
        return array('Great!', 'Coupon/invitation ' . $data['coupon_title'] . ' (ID ' . $id . ') was successfully updated.');
    }
    return array('Uh oh.', 'Something went wrong and we could not update this coupon/invitation. Sorry.');
}

function delete_existing_coupon($id_to_delete) {
    if('publish' === get_post_status( $id_to_delete )) {
        wp_delete_post($id_to_delete);
        return array("Success", "Invitation/coupon " . $id_to_delete . " was successfully deleted from the system.");
    }
    return array("Hmm...", "Something went wrong and we couldn't find the invitation/coupon you tried to delete.");
}

function hubspot_sync($new_data) {
    $data = $new_data->get_json_params();

    require_once plugin_dir_path( __DIR__ ) . 'eventer/registrations/add_registrant_to_hubspot.php';
    $res = set_up_and_send_new_contact($data);

    if($res[0] == "Success") {
        $data['hs_synched'] = "1";
        edit_existing_registration($data);
    }
    
    return $res;
    die();
}