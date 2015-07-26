<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Ebay
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Finding.php 22824 2010-08-09 18:59:54Z renanbr $
 */

/**
 * @see Zend_Service_Ebay_Abstract
 */
require_once 'Zend/Service/Ebay/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Ebay
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @uses       Zend_Service_Ebay_Abstract
 */
class Zend_Service_Ebay_Shopping extends Zend_Service_Ebay_Abstract
{
    const SERVICE_NAME         = 'TradingService';
    const SERVICE_VERSION      = '1.0.0';
    const RESPONSE_DATA_FORMAT = 'XML';

    const ENDPOINT_URI  = 'http://open.api.ebay.com';
    const ENDPOINT_PATH = '/shopping?';

    const XMLNS_FINDING = 'e';
    const XMLNS_MS      = 'ms';

	const OPTION_APP_ID = 'app_id';
	const OPTION_VERSION = 'version';
	const OPTION_SITE_ID       = 'site_id'; 
    const OPTION_REQUEST_ENCODING = 'request_encoding';
    
	
     /**
     * @var array
     */
    protected static $_xmlNamespaces = array(
        self::XMLNS_FINDING => 'http://www.ebay.com/marketplace/search/v1/services',
        self::XMLNS_MS      => 'http://www.ebay.com/marketplace/services'
    );

    /**
     *
     * @var array
     */
    protected $_options = array(
        self::OPTION_SITE_ID => '0',
        self::OPTION_REQUEST_ENCODING => 'XML',
        self::OPTION_VERSION => '759'
    );

    /**
     * @return array
     */
    public static function getXmlNamespaces()
    {
        return self::$_xmlNamespaces;
    }

    /**
     * @param  Zend_Config|array|string $options Application Id or array of options
     * @throws Zend_Service_Ebay_Finding_Exception When application id is missing
     * @return void
     */
    public function __construct($options)
    {
		// prepare options
		// check application id
		$options = parent::optionsToArray ( $options );
		if (! array_key_exists ( self::OPTION_APP_ID, $options )) {
			/**
			 * @see Zend_Service_Ebay_Finding_Exception
			 */
			require_once 'Zend/Service/Ebay/Finding/Exception.php';
			throw new Zend_Service_Ebay_Finding_Exception ( 'Application Id is missing.' );
		}
		    
        // load options
        parent::setOption($options);
    }

    /**
     * @param  Zend_Rest_Client $client
     * @return Zend_Service_Ebay_Finding Provides a fluent interface
     */
    public function setClient($client)
    {
        if (!$client instanceof Zend_Rest_Client) {
            /**
             * @see Zend_Service_Ebay_Finding_Exception
             */
            require_once 'Zend/Service/Ebay/Trading/Exception.php';
            throw new Zend_Service_Ebay_Trading_Exception(
                'Client object must extend Zend_Rest_Client.');
        }
        $this->_client = $client;

        return $this;
    }

    /**
     * @return Zend_Rest_Client
     */
    public function getClient()
    {
        if (!$this->_client instanceof Zend_Http_Client) {
            /**
             * @see Zend_Http_Client
             */
            require_once 'Zend/Http/Client.php';
            $this->_client = new Zend_Http_Client();
        }
        return $this->_client;
    }
    
    public function getSingleItem($ePID, $options = null)
    {
    	$options = array();
    	
    	$options['ItemID'] = $ePID;
    	$options['IncludeSelector'] = 'Details,Description,ShippingCosts';
		//Details,Description,ShippingCosts,ItemSpecifics,Variations
    	
		// do request
        $xml = $this->_request('GetSingleItem', $options);
        //print_r($xml);        
        $item = new Zend_Service_Ebay_Shopping_Item($xml->Item);
        
        //Zend_Debug::dump($ob);
        
        return $item;
    }

    public function getShippingCost($ePID, $options = null)
    {
        $options = array();
    	$options['ItemID'] = $ePID;
    	$options['DestinationCountryCode'] = 'US';
    	$options['DestinationPostalCode'] = '02035';
    	$options['QuantitySold'] = '1';

        $xml = $this->_request('GetShippingCosts', $options);
        return (string) $xml->ShippingCostSummary->ShippingServiceCost;
    }

    
    /**
     * @param  string $operation
     * @param  array  $options
     * @return SimpleXMLElement
     */
    protected function _request($operation, array $options = null)
    {
        // do request
        $client = $this->getClient();
        $client->resetParameters();
        
        $client->setUri(self::ENDPOINT_URI . self::ENDPOINT_PATH);
        
        
		/*$client->setHeaders('X-EBAY-API-APP-ID', $this->getOption(self::OPTION_APP_ID));                   
		$client->setHeaders('X-EBAY-API-VERSION', $this->getOption(self::OPTION_VERSION));                   
		$client->setHeaders('X-EBAY-API-SITEID', $this->getOption(self::OPTION_SITE_ID));                   
        $client->setHeaders('X-EBAY-API-CALL-NAME', $operation);                   
        $client->setHeaders('X-EBAY-API-REQUEST-ENCODING', $this->getOption(self::OPTION_REQUEST_ENCODING));*/                   


        $client->setParameterGet('callname', $operation);
        $client->setParameterGet('responseencoding', $this->getOption(self::OPTION_REQUEST_ENCODING));
        $client->setParameterGet('appid', $this->getOption(self::OPTION_APP_ID));
        $client->setParameterGet('siteid', $this->getOption(self::OPTION_SITE_ID));
        $client->setParameterGet('version', $this->getOption(self::OPTION_VERSION));
        
        foreach ($options as $key => $option) {
            $client->setParameterGet($key, $option);
        }
        
        $response = $client->request("GET");
        
        //print_r($client->getLastRequest());
        //print_r($client->getLastResponse());
        //print_r($response);
        
        return $this->_parseResponse($response);
    }

    /**
     * Search for error from request.
     *
     * If any error is found a DOMDocument is returned, this object contains a
     * DOMXPath object as "ebayFindingXPath" attribute.
     *
     * @param  Zend_Http_Response $response
     * @throws Zend_Service_Ebay_Trading_Exception When any error occurrs during request
     * @return SimpleXMLElement
     */
    protected function _parseResponse(Zend_Http_Response $response)
    {
        // error message
        $message = '';

        $xml = simplexml_load_string($response->getBody());
		if (!$xml) {
			$message = 'It was not possible to load XML returned.';
		}
		
        if ($xml->Ack != 'Success' && $xml->Ack != 'Warning') {
        	$message = "Ack not success: " . print_r($xml, true);
		}
		//var_dump($dom->Ack);die;
        
        // second trying, check request status
        if ($response->isError()) {
            $message = $response->getMessage()
                     . ' (HTTP status code #' . $response->getStatus() . ')';
        }
		
        // throw exception when an error was detected
        if (strlen($message) > 0) {
            /**
             * @see Zend_Service_Ebay_Finding_Exception
             */
            require_once 'Zend/Service/Ebay/Shopping/Exception.php';
            throw new Zend_Service_Ebay_Shopping_Exception($message);
        }
		
        /*
        $dom = new DOMDocument();
        $dom->loadXML($response->getBody());
        Zend_Debug::dump($dom->saveXML());

        Zend_Debug::dump($xml);
        */
        
        return $xml;
    }
}
