<?php 
$path = preg_replace('/wp-content.*$/','',__DIR__);
include($path.'wp-load.php'); 
if( ! defined( 'ABSPATH' ) ) exit; 
require_once("vendor/autoload.php"); 
require_once('add_registrant_to_hubspot.php');

//First grab everything off user's form submission.
//validate all with: stripslashes(strip_tags(trim($code)));
require_once('form_server_validation.php');
$form_is_valid = validate_the_form($_POST);

//form_is_valid is an array with 3 elements: [0] = status, [1] = problem, [2] = afflicted fields 
//OR, if [0] is not "error" then [2] has sanitized fields (BUT NO COUPON CODE... watch out. That's handled separately below.)
if($form_is_valid[0] === "error") {
	$errs = implode(",", $form_is_valid[2]);
	$msg = $form_is_valid[1];
	header("Location: " . site_url() . "/checkout/?status=error&msg=" . $msg . "&fields=" . $errs);
	exit;
}
$cleaned_form_data = $form_is_valid[2];

//Squash tags into a string for sending
$tag_string = "";
if(!empty($_POST['tags'])) {
	foreach($_POST['tags'] as $tag_item) {
		$tag_string .= stripslashes(strip_tags(trim($tag_item))) . ",";
	}
}
$cleaned_form_data['tags'] = preg_replace("/,$/", '', $tag_string);
	
//OK. Form is valid. Let's move on.
//Send this user into hubspot.
$hs_response = set_up_and_send_new_contact($cleaned_form_data); //hs id is now $hs_response->vid;

$price_calc = get_option('ticket_price');
$amount_to_pay = preg_replace("/[^0-9.]/", "", $price_calc);

//Only bother this lot if coupon has been supplied
if(isset($_POST['coupon']) && !empty($_POST['coupon'])) {
	//Set up coupon vars
	$coupons_list = array();
	$coupons_lookup = array();
	$coupon_discount = 0;
	$coupon_valid = false;

	//Grab existing coupons from DB if any.
	global $wpdb;
	$db_coupons = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}coupons WHERE coupon_status LIKE 'live'" );
	foreach ( $db_coupons as $row ) {
		$single_code = strtoupper($row->code);
		if($single_code != "") {
			$coupons_list[] = $row->code;
			$coupons_lookup[strtoupper($row->code)] = $row->percent_discount;
		}
	}

	//Check if coupon is present and valid
	//First off though, validate it
	if(isset($_POST['coupon']) && $_POST['coupon'] != "") {
		$sanitized_coupon = stripslashes(strip_tags(trim($_POST['coupon'])));
	}

	if(in_array(strtoupper($sanitized_coupon), $coupons_list)) {
		$coupon_valid = true;
	}

	//BAIL if coupon present but NOT correct
	if($coupon_valid != true) {
		header("Location: " . site_url() . "/checkout/?status=error&msg=badcoupon&errs=coupon");
		exit;
	}

	//max uses check routine: Bail if coupon max uses is reached, but only for NON GUEST coupons.
	//Note that coupon guest status could have been mutated by invites under same name, so check those too.
	$code_check = strtoupper($sanitized_coupon);
	$assoc_invitations = get_posts(array('post_type' => 'invitation', 'title' => $code_check));
	$coupon_db_row = null;
	foreach($db_coupons as $item => $val) {
		if($val->code == $code_check) {
			$coupon_db_row = $val;
		}
	}
	$our_coupon_type = $coupon_db_row->is_guest;
	$our_coupon_limit = $coupon_db_row->max_uses;
	$our_coupon_uses = $coupon_db_row->actual_uses;
	$guest_status = false;
	foreach($assoc_invitations as $single_invite) {
		$x = get_post_meta($single_invite->ID, 'for_guests', true);
		if($x === true || $x === 1 || $x === "1" ) {
			$guest_status = true;
		}
	}
	if($our_coupon_type === 1 || $our_coupon_type === "1" || $our_coupon_type === true) {
		$guest_status = true;
	}
	if($guest_status === false && $our_coupon_uses >= $our_coupon_limit) {
		header("Location: " . site_url() . "/checkout/?status=error&msg=couponlimit&errs=coupon");
		exit();
	}
	//End coupon max uses check routine
	

	//Check discount amount of supplied coupon. If no discount set we have a dud coupon: bail.
	$supplied_coupon = strtoupper($sanitized_coupon);
	$coupon_discount = $coupons_lookup[$supplied_coupon];
	if(!isset($coupon_discount)) {
		header("Location: " . site_url() . "/checkout/?status=error&msg=couponnotexist&errs=coupon");
		exit;
	}
	
	
	//Figure out amount to apy
	$filtered_ticket_price = $amount_to_pay; //750

	$disc_perc = $filtered_ticket_price / 100; //Full price as a percentage
	$amount_to_discount = $disc_perc * $coupon_discount; //value to knock off main price
	$amount_to_pay = $filtered_ticket_price - $amount_to_discount;//Final price to pay on checkout

	//Bail from stripe session if coupon present and correct and if price to pay is non-zero
	if(isset($supplied_coupon) && in_array($supplied_coupon, $coupons_list) && $amount_to_pay <= 0) {
		//Look up coupon and adjust uses to +1
		//Add user to hubspot registration list
		require_once('update_coupon_count.php');
		add_one_coupon_usage($supplied_coupon);
		
		//Add user to registered attendees list
		require_once 'user_registration_script.php';
		$registration_data = array();
		$registration_data['fname'] = $form_is_valid[2]['fname'];
		$registration_data['lname'] = $form_is_valid[2]['lname'];
		$registration_data['email'] = $form_is_valid[2]['email'];
		$registration_data['role'] = $form_is_valid[2]['role'];
		$registration_data['company'] = $form_is_valid[2]['company'];
		$registration_data['coupon'] = $supplied_coupon;
		add_new_registration($registration_data);
		
		//connect newly-registered user to the hs registration list 
		set_up_and_send_list_add($hs_response->vid);
		header("Location: " . site_url() . "/success?coupon=" . $supplied_coupon);
		exit;
	}
} else {
	$supplied_coupon = "none";
}

//Either no coupon was provided, or one was provided but total left to pay is non-zero, so we go to Stripe.
$customer_mail = $form_is_valid[2]['email'];

$stripe_api_access = get_option('alt_stripe_key');
\Stripe\Stripe::setApiKey($stripe_api_access);

header('Content-Type: application/json');
$YOUR_DOMAIN = site_url();

$event_name = get_option('event_name');
$real_amount = $amount_to_pay;
$amount = intval($real_amount*100);

$checkout_session = \Stripe\Checkout\Session::create([
  'customer_email' => $customer_mail,
  'client_reference_id' => $hs_response->vid,
  'line_items' => [[
      'price_data' => [
        'currency' => 'chf',
        'product_data' => [
          'name' => "Entrance to Dagorà " . $event_name,
        ],
        'unit_amount' => $amount,
      ],
      'quantity' => 1,
   ]],
  'mode' => 'payment',
  'success_url' => $YOUR_DOMAIN . '/success?coupon=' . $supplied_coupon . '&session_id={CHECKOUT_SESSION_ID}',
  'cancel_url' => $YOUR_DOMAIN . '/checkout',
]);

header("HTTP/1.1 303 See Other");
header("Location: " . $checkout_session->url);

