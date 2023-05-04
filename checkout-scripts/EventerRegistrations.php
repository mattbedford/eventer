<?php

class EventerRegistration {

    public $data;
    public $hubspot_id;
    public $hubspot_sync_status;
    public $event_status;
    public $registration_id;


    function __construct($form_data) { 
        $this->event_status = "pending";
        $this->registration_id = false;
        $this->data = $form_data;

        $hubspot_values = $this->checkHubspotId();

        $this->hubspot_id = $hubspot_values[0];
        $this->hubspot_sync_status = $hubspot_values[1];
        $this->addUserToDb();
    }


    private function checkHubspotId() {

        // Return two-part array: hubspot ID and whether-synced
        $res = HubspotTool::hubspotExistsHandler($this->data);
        return $res;
    
    }


    private function addUserToDb() {
        global $wpdb;
	
        $presumed_payment = 0;
        $table_name = $wpdb->prefix . 'registrations';
        $sign_up_date = date("Y-m-d H:i:s");

        if(!isset($this->data['coupon']) || empty($this->data['coupon'])) {
            $p = get_option('ticket_price');
            $presumed_payment = intval(preg_replace("/[^0-9.]/", "", $p));
            $this->data['coupon'] = 'none';
        }

        if(!isset($this->data['title']) || empty($this->data['title'])) {
            $this->data['title'] = null;
        }

        if(!isset($this->data['interests']) || empty($this->data['interests'])) {
            $this->data['interests'] = null;
        }

        if(!isset($this->data['mobile']) || empty($this->data['mobile'])) {
            $this->data['mobile'] = null;
        }
        
        $wpdb->insert( 
            $table_name, 
            array( 
                'title' => $this->data['title'],
                'name' => $this->data['fname'], 
                'surname' => $this->data['lname'],
                'email' => $this->data['email'], 
                'company' => $this->data['company'],
                'my_company_is' => $this->data['my_company_is'],
                'street_address' => $this->data['address'],
                'city' => $this->data['city'],
                'country' => $this->data['country'],
                't_and_c' => $this->data['mkt'],
                'interests' => $this->data['tags'],
                'mobile_phone' => $this->data['mobile'],
                'office_phone' => $this->data['office'],
                'website' => $this->data['website'],
                'postcode' => $this->data['postcode'],
                'role' => $this->data['role'],
                'paid' => $presumed_payment,
                'hubspot_id' => $this->hubspot_id,
                'payment_status' => $this->event_status,
                'coupon_code' => $this->data['coupon'],
                'sign_up_date' => $sign_up_date,
                'printed' => '0',
                'hs_synched' => $this->hubspot_sync_status
            ) 
        );
        
        if($wpdb->insert_id !== false) {
           $this->registration_id = $wpdb->insert_id;
        } 
    }




    // Two ways into the same update/confirm function.
    // First one is only called on valid coupon entry or manual data entry.
    // Second is called after someone goes through Stripe
    public function confirmFreeUser() {
        $this->confirmAnyUser(0, "Free entry", $this->registration_id);

    }

    public static function confirmPaidUser($price_paid, $payment_status, $registration_id) {
        $this->confirmAnyUser($price_paid, $payment_status, $registration_id);
    }



    private function confirmAnyUser($price_paid, $payment_status, $registration_id) {
        // - Updates user payment status in database
        // - Sends a welcome mail and updates in database TO DO: Merge these DB queries into one.
        // - Adds user to confirmed list in HS.

        global $wpdb;
        $my_table = $wpdb->prefix . 'registrations';
         
        $user_data = $wpdb->query( $wpdb->prepare( 
            "
                SELECT * 
                FROM $my_table 
                WHERE id = %d
            ",
               $price_paid, $payment_status, intval($registration_id)
        ));

        $welcome_mail = new MailFunction($user_data->email, $user_data->name, $user_data->surname, "welcome");


        $wpdb->query( $wpdb->prepare( 
         "
             UPDATE $my_table 
             SET paid = %d,
             payment_status = %s,
             welcome_email_sent = 1,
             WHERE id = %d
         ",
            $price_paid, $payment_status, intval($registration_id)
        ) );

        HubspotTool::addRegistrantToHubspotList($user_data->hubspot_id);

    }

}