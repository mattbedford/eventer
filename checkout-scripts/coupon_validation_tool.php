<?php


function live_check_my_coupon($code) {

	//Only bother this lot if coupon has been supplied
	if(!isset($code) || $code == "") {
        return "no";
    }
	$code = strtoupper($code);
    $invitation_object = get_page_by_title( $code, OBJECT, 'invitation' );
    $amount_to_pay = get_option('ticket_price');
		
	//BAIL if coupon present in form but NOT in DB
	if($invitation_object === null || empty($invitation_object) || $invitation_object === false) {
		return "no";
	}

	//Grab meta about invitation
	$invitation_post_id = $invitation_object->ID;
	$max_uses = get_post_meta($invitation_post_id, 'max_uses', true);
	$actual_uses = get_post_meta($invitation_post_id, 'actual_uses', true);
	$discount = get_post_meta($invitation_post_id, 'percentage_value', true);
	$guest_status = get_post_meta($invitation_post_id, 'for_guests', true);

	//Handling cases where data is not set in coupon
	if( empty($max_uses) ) {
		$max_uses = 999;
	}

	if( empty($actual_uses) ) {
		$actual_uses = 0;
	}

	if( empty($discount) ) {
		$discount = 0;
	}

	// Bail if coupon max uses is reached, but only for NON GUEST coupons 
	// (assume nobody is a guest unless explicitly set as guest).
	if($guest_status === false && $actual_uses >= $max_uses) {
		return "max";
	}

	//Bail if no discount amount set, thus we have a dud coupon.
	if(!isset($discount) || $discount === false) {
		return "no";
	}
	//Figure out amount to pay
	$disc_perc = $amount_to_pay / 100; //Full price as a percentage
	$amount_to_discount = $disc_perc * $discount; //value to knock off main price
	$amount_to_pay = $amount_to_pay - $amount_to_discount;//Final price to pay on checkout

	//Bail from stripe session if coupon present and correct and if price to pay is zero
	if($amount_to_pay <= 0) {
		return "free";
	}
	return $amount_to_pay;
}
