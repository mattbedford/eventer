<?php

/**
 * HubspotTool Class. Manages interactions with Hubspot.
 * 
 * Static methods include:
 * hubspotExistsHandler -> to return HS id (or create new HS contact and then return id)
 * addRegistrantToHubspotList -> takes a VID and pushes it into HS event list
 * 
 */

class HubspotTool {

    public static function hubspotExistsHandler($data) {

        // Return array of HS user id and sync status.

        $existing_user = $this->checkEmailInHubspot($data['email']);

        if($existing_user->total == 1) {
            
            $hs_contact_id = $existing_user->results[0]->id;
            return array($hs_contact_id, false);

        } else {

            $hs_contact_id = $this->createNewHubspotPerson($data);

            if($hs_contact_id === null) {
                return array('Error', false);
            }

            return array($hs_contact_id, true);
        }

    }

    public static function addRegistrantToHubspotList($vid) {
        $list_id = get_option('hubspot_list');
	    $url = "https://api.hubapi.com/contacts/v1/lists/" . $list_id . "/add";

        $fields = array(
            'vids' => array(
                $vid
            )
        );

	    $fields_string = json_encode($fields);
	
	    $response = $this->triggerHubspotCurl($url, $fields_string);

    }


    
    private function checkEmailInHubspot($email) {

        $url = "https://api.hubapi.com/crm/v3/objects/contacts/search";
        $data = array(
            "limit" => 1,
            "filterGroups" => array(
                array( 
                    "filters" => array(
                        array(
                            "value" => $email,
                            "propertyName" => "email",
                            "operator" => "EQ"
                        )
                    )
                )
            ),
        );
    
        $existing = $this->triggerHubspotCurl($url, json_encode($data));

        return $existing;

    }
    

    private function createNewHubspotPerson($data) {
        $url = "https://api.hubapi.com/contacts/v1/contact/createOrUpdate/email/" . $data['email'];
    
        if (isset($data['my_company_is']) && strpos($data['my_company_is'], 'Media') !== false) {
            $data['my_company_is'] = "Media & Press";
        }

        $fields = array(
            'properties' => array(
                array(
                    'property' => 'email',
                    'value'	=> $data['email']
                ),
                array(
                    'property' => 'company',
                    'value'	=> $data['company']
                ),
                array(
                    'property' => 'jobtitle',
                    'value'	=> $data['role']
                ),
                array(
                    'property' => 'firstname',
                    'value'	=> $data['name']
                ),
                array(
                    'property' => 'lastname',
                    'value'	=> $data['surname']
                ),
                array(
                    'property' => 'phone',
                    'value'	=> $data['office_phone']
                ),
                array(
                    'property' => 'website',
                    'value'	=> $data['website']
                ),
                array(
                    'property' => 'address',
                    'value'	=> $data['street_address']
                ),
                array(
                    'property' => 'zip',
                    'value'	=> $data['postcode']
                ),
                array(
                    'property' => 'city',
                    'value'	=> $data['city']
                ),
                array(
                    'property' => 'country',
                    'value'	=> $data['country']
                ),
            ),
        );

        //Pushing optional fields into submitted data
        if(!empty($data['title'])) {
            $fields['properties'][] = array(
                'property' => 'gender',
                'value'	=> $data['title']
            );
        } 
        
        if(!empty($data['mobile'])) {
            $fields['properties'][] = array(
                'property' => 'mobilephone',
                    'value'	=> $data['mobile']
                );
        }
        
        if(!empty($data['tags'])) {
            $fields['properties'][] = array(
                    'property' => 'description',
                    'value'	=> $data['tags']
                );
        }
        if(!empty($data['my_company_is'])) {
            $fields['properties'][] = array(
                'property' => 'company_type',
                'value' => $data['my_company_is']
            );
        }
        
        $fields_string = json_encode($fields);

        $response = $this->triggerHubspotCurl($url, $fields_string);

        if(isset($response->vid)) {
            return $response->vid;
        } else {
            return null;
        }
    }


    private function triggerHubspotCurl($url, $fields_string) {
        $hubspot_token = get_option('hubspot_key');
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type:application/json',
            'authorization: Bearer ' . $hubspot_token
        ));
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $fields_string);

        $data = curl_exec($curl);
        curl_close($curl);
        return json_decode($data);
    }


}