<?php

$hook = array(
    'hook' => 'ClientAreaRegister',
    'function' => 'ClientAreaRegister_clientarea',
	'description' => array(
        'english' => 'After Client Registration (OTP) Mobile Verification'
    ),
    'type' => 'client',
    'extra' => '',
    'defaultmessage' => 'Dear {firstname} {lastname}, OTP generated for your mobile phone verification is {otp} for Request # {request}.',
    'variables' => '{firstname},{lastname},{otp},{request}'
);

if(!function_exists('ClientAreaRegister_clientarea')) {
	
	function ClientAreaRegister_clientarea($args){
		
		$class = new turbosms();
        $template = $class->getTemplateDetails(__FUNCTION__);
		
		//Set User
		$class->setUserid( $args['userid'] );
		$client_query = $class->getClientDetailsBy( $class->userid );
		$client = mysql_fetch_array( $client_query , MYSQL_ASSOC);
		
		$otp = $class->randomString(6 , true);
		$request = $class->randomString(20);

		$message = str_replace(['{firstname}' , '{lastname}' , '{otp}' , '{request}'] , [$client['firstname'], $client['lastname'] , $otp , $request] , $template['template']);

		//Send the Otp VIA SMS
		$class->setGsmnumber( $client['gsmnumber'] );
		$class->setMessage( $message );
		$class->setCountryCode( $client['country'] );
		
		//Insert the OTP
        $values = array(
            "otp" => $otp,
            "type" => 'client',
            "relid" => $client['id'],
            "request" => $request,
            "text" => $message,
            "status" => 0,
            "datetime" => date("Y-m-d H:i:s"),
			"phonenumber" => $client['gsmnumber']
        );
		
        $otp_id = insert_query('turbosms_otp', $values);
        $class->addLog("OTP saved to the database");		

		//Lets SMS The Hook.
		//Lets Update the OTP
		
		$class->send();
		
		/*
		status => 0 //Default	
		status => 1 //Sent
		status => 2 //confirmed
		status => 3 //EMPTY
		
		*/
		update_query( "turbosms_otp", array( "status" => "1"), array( "id" => $otp_id ) );
        $class->addLog("OTP sent.");		

		
	}
}



return $hook;