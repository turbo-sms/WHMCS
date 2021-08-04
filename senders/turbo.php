<?php
class turbo extends turbosms {

    function __construct($message,$gsmnumber,$countryCode){
        $this->message = $this->utilmessage($message);
        $this->gsmnumber = $this->utilgsmnumber($gsmnumber);
        $this->countryCode = $countryCode;
    }
	function file_get_contents_curl($url) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);       

        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

	function send(){
        if($this->gsmnumber == "numbererror"){
            $log[] = ("Number format error.".$this->gsmnumber);
            $error[] = ("Number format error.".$this->gsmnumber);
            return null;
        }
        $params = $this->getParams();

		//Your authentication key (Go to https://turbosms.top/Developers/)
		$authKey = $params->authkey;
		
		//Base URL
		//Composed of initial common portion of URL of SMS Gateway Provider
		$baseurl = "http://turbosms.top";
	
		//Sender ID, While using route 4 sender id should be 6 characters long.
		$senderId = trim($params->senderid);
		
		$senderId = substr($senderId, 0, 13);
		
		$smsRoute = 4;			//Using Default route 4 if undefined in settings


        $text = urlencode($this->message);
        $to = $this->gsmnumber;

        

       if (empty($authKey)) {
		$authK = invalid;
}

else {
    		$authK = $params->authkey;
}
		// Validation of connection to SMS Gateway Server
        $url = "http://turbosms.top/miscapi/$authK/getBalance"; //verify connection to gateway server
        $ret = $this->file_get_contents_curl($url);
        $log[] = ("Response returned from the server: ".$ret);

        $sess = explode(",", $ret);
        if ($sess[0] != "Error: 1003") {
		
            $url = "http://turbosms.top/smsapi?api_key=$authK&type=text&contacts=$to&senderid=$senderId&msg=$text";
            $ret = $this->file_get_contents_curl($url);
            $send = array_map('trim',explode(":", $ret));

          $log[] = ("URL : ".$url);
            if ($send[0] != "CODE" && $send[0] != "Please") {
                $log[] = ("Message sent!");
            } else {
                $log[] = ("Message could not be sent. Error: $ret");
                $error[] = ("An error occurred while sending the message. Error: $ret");
            }
        } else {
            $log[] = ("Message could not be sent. Authentication Error: $ret");
            $error[] = ("Authentication failed. $ret");
        }

        return array(
            'log' => $log,
            'error' => $error,
            'msgid' => $send[0],
        );
    }

    function balance(){
		$params = $this->getParams();
        if($params->authkey && $params->route){
			$baseurl = "http://turbosms.top";
			$url = 	"http://turbosms.top/$authKey/getBalance";
            $result = $this->file_get_contents_curl($url);
            $result = array_map('trim',explode(":",$result));
            $cvp = $result[1];
			if ($cvp == 001 || $cvp == 002){
				return null;
			}else{
				return $result[0];
			}
        }else{
            return null;
        }
    }

    function report($msgid){
		$params = $this->getParams();
        if($params->authkey && $msgid){
			$baseurl = "http://vtermination.com";
			$url = "$baseurl/api/check_delivery.php?authkey=$params->authkey&requestid=$msgid";
			$result = $this->file_get_contents_curl($url);
			$result = array_map('trim',explode(":",$result));
			$cvp = $result[1];
            if ($cvp == 001 || $cvp == 002){
                return "error";
            }else{
                return "success";
            }
        }else{
            return null;
        }
    }

    //You can specifically convert your gsm number. See netgsm for example
    function utilgsmnumber($number){
        return $number;
    }
    //You can spesifically convert your message
    function utilmessage($message){
        return $message;
    }
}

return array(
    'value' => 'turbo',
    'label' => 'turbosms.net',
    'fields' => array(
        'authkey'
    )
);
