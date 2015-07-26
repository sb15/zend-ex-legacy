<?

require_once 'Zend/Http/Client.php';

class Zend_Service_Google_Translate {
	
	public function translate($message, $from, $to) 
	{
		$url = "http://ajax.googleapis.com/ajax/services/language/translate";
		$client = new Zend_Http_Client($url, 
										array('maxredirects' => 0, 
											  'timeout' => 30 ));
		$langpair = $from . '|' . $to;
		$params = array ('v' => '1.1', 
						 'q' => $message, 
						 'langpair' => $langpair,
						 'key' => 'ABQIAAAAMtXAc56OizxVFR_fG__ZZRSrxD5q6_ZpfA55q8xveFjTjZJnShSvPHZq2PGkhSBZ0_OObHUNyy0smw');
		/**
		 * Zend_Http_Client
		 */
		$client->setParameterPost($params);
		$client->setHeaders('Referer', 'http://sb6.ru');
		$response = $client->request("POST");
		
		//print_r ($response);
		
		$data = $response->getBody();
		
		$serverResult = json_decode($data);
		
		$status = $serverResult->responseStatus; // should be 200
		
		$result = '';
		if ($status == 200 ) {
			$result = $serverResult->responseData->translatedText;			
		} else {
			echo "retry\n";
			print_r($client->getLastRequest());
			print_r($client->getLastResponse());
			die;
			return $this->translate($message, $from, $to);
		}
		return $result;
	}
	
}