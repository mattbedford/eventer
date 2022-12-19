<?php
$path = preg_replace('/wp-content.*$/','',__DIR__);
include($path.'wp-load.php'); 
if( ! defined( 'ABSPATH' ) ) exit; 

/*
{
    "id": "4",
    "title": null,
    "name": "Stevie",
    "surname": "Wonder",
    "email": "stevie@gmail.com",
    "company": "Sarcazzo SA",
    "role": "Singer",
    "mobile_phone": null,
    "office_phone": "+41 11111111111",
    "website": "https://sarcazzo.ch",
    "street_address": "53 via roma",
    "postcode": "20900",
    "city": "Monza",
    "country": "Italia",
    "t_and_c": "0",
    "interests": "e-commerce platform",
    "paid": "1200",
    "coupon_code": "none",
    "sign_up_date": "2022-12-06",
    "printed": "0",
    "payment_status": "Pending",
    "my_company_is": null,
    "hs_synched": "0"
}
*/
function set_up_and_send_new_contact($clean_form_data) {
	
	$t = time();
	//Log to file
	file_put_contents('hubspot_log.txt', "TIME: " . $t . "\n", FILE_APPEND);
	file_put_contents('hubspot_log.txt', print_r($clean_form_data, true . "\n"), FILE_APPEND);
	
	$contact_email = $clean_form_data['email'];
	$url = "https://api.hubapi.com/contacts/v1/contact/createOrUpdate/email/" . $contact_email;
	
    if (isset($clean_form_data['my_company_is']) && $clean_form_data['my_company_is'] == "Media & Press" 
        || $clean_form_data['my_company_is'] == "Media / Press"
        || $clean_form_data['my_company_is'] == "Media / Press / Journalism"
        || $clean_form_data['my_company_is'] == "Media &amp; Press") {
        $clean_form_data['my_company_is'] = "Media & Press / Journalism";
    }

	$fields = array(
        'properties' => array(
            array(
                'property' => 'email',
                'value'	=> $clean_form_data['email']
            ),
            array(
                'property' => 'company',
                'value'	=> $clean_form_data['company']
            ),
            array(
                'property' => 'jobtitle',
                'value'	=> $clean_form_data['role']
            ),
            array(
                'property' => 'firstname',
                'value'	=> $clean_form_data['name']
            ),
            array(
                'property' => 'lastname',
                'value'	=> $clean_form_data['surname']
            ),
            array(
                'property' => 'phone',
                'value'	=> $clean_form_data['office_phone']
            ),
            array(
                'property' => 'website',
                'value'	=> $clean_form_data['website']
            ),
            array(
                'property' => 'address',
                'value'	=> $clean_form_data['street_address']
            ),
            array(
                'property' => 'zip',
                'value'	=> $clean_form_data['postcode']
            ),
            array(
                'property' => 'city',
                'value'	=> $clean_form_data['city']
            ),
            array(
                'property' => 'country',
                'value'	=> $clean_form_data['country']
            ),
        ),
    );

	//Pushing optional fields into submitted data
	if(!empty($clean_form_data['title'])) {
		$fields['properties'][] = array(
			'property' => 'gender',
            'value'	=> $clean_form_data['title']
		);
	} 
	
	if(!empty($clean_form_data['mobile'])) {
		$fields['properties'][] = array(
			'property' => 'mobilephone',
				'value'	=> $clean_form_data['mobile']
			);
	}
	
	if(!empty($clean_form_data['tags'])) {
		$fields['properties'][] = array(
				'property' => 'description',
				'value'	=> $clean_form_data['tags']
			);
	}
	if(!empty($clean_form_data['my_company_is'])) {
        $fields['properties'][] = array(
            'property' => 'company_type',
            'value' => $clean_form_data['my_company_is']
        );
    }
	
	$fields_string = json_encode($fields);
	$response = trigger_hubspot_curl($url, $fields, $fields_string);
    file_put_contents('hubspot_log.txt', print_r($response, true . "\n") . "\n", FILE_APPEND);
    if(isset($response->vid)) {
	    file_put_contents('hubspot_log.txt', "CONTACT ID: " . $response->vid . "\n", FILE_APPEND);
        set_up_and_send_list_add($response->vid);
        return array("Success", "Contact added to Hubspot.");
    } else {
        return array("Dammit", "We could not communicate with Hubspot. Please try again later.");
    }

}

function set_up_and_send_list_add($vid) {
	$list_id = get_option('hubspot_list');
	$new_contact = $vid;
	$url = "https://api.hubapi.com/contacts/v1/lists/" . $list_id . "/add";

	$fields = array(
		'vids' => array(
			$new_contact
		)
	);

	$fields_string = json_encode($fields);
	
	$response = trigger_hubspot_curl($url, $fields, $fields_string);
	return $response;
}



function trigger_hubspot_curl($url, $fields, $fields_string) {
	$hubspot_token = get_option('hubspot_key');
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_POST, TRUE);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array(
		'Content-Type:application/json',
		'authorization: Bearer ' . $hubspot_token
	));
	curl_setopt($curl, CURLOPT_POSTFIELDS, $fields_string);

	$data = curl_exec($curl);
	curl_close($curl);
	return json_decode($data);
}