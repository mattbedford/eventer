<?php

//Validation runtime. Returns an array of error type and afflicted fields ELSE an OK message with sanitized array of post data


//MAIN RUNTIMES
//validate all with: stripslashes(strip_tags(trim($code)));
function validate_the_form($post_object) {
	//Step 1: check hidden fields aren't filled.
	$hiddens = check_hidden_fields($post_object);
	if ($hiddens === "bad") {
		$res = send_return_array("error", "honeypot", array("honeypot"));
		return $res;
	}
	
	//Step 2: validate text fields; new array of $sanitized
	unset($post_object['tags']);
	$sanitized = array_map('sanitize_fields', $post_object);
	
	//Step 3: now the individual field checker, but on sanitized data
	//Pass control of the return to a new function
	$final_result = full_field_check($sanitized);	
	return $final_result;
}

function full_field_check($data) {
	$error_fields = array();
	$req_fields = array();
	unset($data['first_name']);
	unset($data['required']);
	unset($data['coupon']);
	
	//Take out optional fields from sanitized array
	$title_field = null;
	$mobile_field = null;
	if(!empty($data['title'])) $title_field = $data['title'];
	if(!empty($data['mobile']))	$mobile_field = $data['mobile'];
	unset($data['mobile']);
	unset($data['title']);
	
	foreach($data as $field_name => $value) {
		if($value == "" || $value == " " || empty($value)) {
			$error_fields[] = $field_name;
		}
	}
	if(!empty($error_fields)) {
		$res = send_return_array('error', 'missing fields', $error_fields);
		return $res;
	}
	
	if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
  		$res = send_return_array('error', 'invalid email', array("email"));
		return $res;
	}

	global $wpdb;
	$reg_table = $wpdb->prefix . 'registrations';
	$result = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $reg_table WHERE email = %s", $data['email'] ) );
	if($result >= 1) {
		$res = send_return_array('error', 'usedemail', array("email"));
		return $res;
	}


	if(!empty($title_field)) $data['title'] = $title_field;
	if(!empty($mobile_field)) $data['mobile'] = $mobile_field;
	
	//Else, all looks good
	$res = send_return_array('success', 'all fields valid', $data);
	return $res;
}


//AUXILIARY FUNCTIONS
function check_hidden_fields($post_object) {
	if(isset($post_object['required']) && $post_object['required'] !== "") {
		return "bad";
	}
	if(isset($post_object['name']) && $post_object['name'] !== "") {
		return "bad";
	}
	return "good";
}

function sanitize_fields($field) {
	return stripslashes(strip_tags(trim($field)));
}


function send_return_array($status, $problem, $fields) {
	return array($status, $problem, $fields);
}

function getTrimmedUrl($link) {
    $str = str_replace(["www.","https://","http://"],[''],$link);
    $link = explode("/",$str);
    return strtolower($link[0]);                
}