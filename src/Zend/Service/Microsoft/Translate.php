<?

require_once 'Zend/Http/Client.php';

class Zend_Service_Microsoft_Translate {
	
	private $_appId = 'ECC00C04E884A49685031FE13E75E8BE7CB2D908';
	
	public function translate($message, $from, $to) 
	{
		$url = "http://api.microsofttranslator.com/v2/Http.svc/Translate";
		
		$client = new Zend_Http_Client($url, array(
			'maxredirects' => 0, 
			'timeout'      => 30
		));
		
		$langpair = $from . '|' . $to;
		$params = array ('appId' => $this->_appId, 
						 'text' => $message,
						 'from' => $from,
						 'to' => $to,
						 'category' => 'general');
		/**
		 * Zend_Http_Client
		 */
		$client->setParameterGet($params);
		$response = $client->request("GET");
		
		$data = $response->getBody();
		
		$result = '';
		if ($client->getLastResponse()->getStatus() == 200 ) {
			$result = strip_tags(urldecode($data));
		} else {
			$result = $message;
		}
		return $result;
	}
	
}