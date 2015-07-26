<?

require_once 'Zend/Service/Amazon.php';

class Zend_Service_AmazonEx extends Zend_Service_Amazon {

	public function browseNodeLookup(array $options)
    {
        $client = $this->getRestClient();
        $client->setUri($this->_baseUri);

        $defaultOptions = array();
        $options = $this->_prepareOptions('BrowseNodeLookup', $options, $defaultOptions);
        $client->getHttpClient()->resetParameters();
        $response = $client->restGet('/onca/xml', $options);

        if ($response->isError()) {
            /**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception('An error occurred sending request. Status code: '
                                           . $response->getStatus());
        }

        $dom = new DOMDocument();
        $r = $dom->loadXML($response->getBody());

        self::_checkErrors($dom);

		$xpath = new DOMXPath($dom);
        $xpath->registerNamespace('az', 'http://webservices.amazon.com/AWSECommerceService/2005-10-05');
        $nodes = $xpath->query('//az:BrowseNodes');

		//Zend_Debug::dump($nodes);//die;
		//print_r($dom);
		//print_r($response->getBody());
        /**
         * @see Zend_Service_Amazon_ResultSet
         */
        require_once 'Zend/Service/Amazon/NodeSet.php';
        return new Zend_Service_Amazon_NodeSet($dom);
    }

	public function itemSearch(array $options)
	{
		$client = $this->getRestClient();
        $client->setUri($this->_baseUri);

        $defaultOptions = array('ResponseGroup' => 'Small');
        $options = $this->_prepareOptions('ItemSearch', $options, $defaultOptions);
        $client->getHttpClient()->resetParameters();
        $response = $client->restGet('/onca/xml', $options);

        if ($response->isError()) {
            /**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception('An error occurred sending request. Status code: '
                                           . $response->getStatus());
        }

        $dom = new DOMDocument();
        $dom->loadXML($response->getBody());
        self::_checkErrors($dom);
        
        /**
         * @see Zend_Service_Amazon_ResultSet
         */
        require_once 'Zend/Service/Amazon/ResultSetEx.php';
        return new Zend_Service_Amazon_ResultSetEx($dom);
	}

	public function itemLookup($asin, array $options = array())
	{
		$client = $this->getRestClient();
        $client->setUri($this->_baseUri);

        $defaultOptions = array('ResponseGroup' => 'Small',
								'ItemId' => $asin);
        $options = $this->_prepareOptions('ItemLookup', $options, $defaultOptions);
        $client->getHttpClient()->resetParameters();
        $response = $client->restGet('/onca/xml', $options);

        if ($response->isError()) {
            /**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception('An error occurred sending request. Status code: '
                                           . $response->getStatus());
        }

        $dom = new DOMDocument();
        $dom->loadXML($response->getBody());
        self::_checkErrors($dom);

        /**
         * @see Zend_Service_Amazon_ResultSet
         */
        require_once 'Zend/Service/Amazon/ResultSetEx.php';
        return new Zend_Service_Amazon_ResultSetEx($dom);
	}

}