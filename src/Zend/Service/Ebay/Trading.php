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
class Zend_Service_Ebay_Trading extends Zend_Service_Ebay_Abstract
{
    const SERVICE_NAME         = 'TradingService';
    const SERVICE_VERSION      = '1.0.0';
    const RESPONSE_DATA_FORMAT = 'XML';

    const ENDPOINT_URI  = 'https://api.ebay.com';
    const ENDPOINT_PATH = '/ws/api.dll';

    const XMLNS_TRADING = 'e';
    const XMLNS_MS       = 'ms';

    const OPTION_AUTH_TOKEN    = 'auth_token';
    const OPTION_SITE_ID       = 'site_id';    
    const OPTION_COMPATIBILITY_LEVEL = 'compatibility_level';
    const OPTION_DEV_NAME = 'dev_name';
    const OPTION_APP_NAME = 'app_name';
    const OPTION_CERT_NAME = 'cert_name';
    
     /**
     * @var array
     */
    protected static $_xmlNamespaces = array(
        self::XMLNS_TRADING => 'http://www.w3.org/2001/XMLSchema'
    );

    /**
     *
     * @var array
     */
    protected $_options = array(
        self::OPTION_SITE_ID => '0',
        self::OPTION_COMPATIBILITY_LEVEL => 705
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
		
		if (! array_key_exists ( self::OPTION_AUTH_TOKEN, $options )) {
			/**
			 * @see Zend_Service_Ebay_Trading_Exception
			 */
			require_once 'Zend/Service/Ebay/Trading/Exception.php';
			throw new Zend_Service_Ebay_Trading_Exception ( 'Auth token is missing.' );
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
    
    public function getCategories($parentId = -1, $levelLimit = 1)
    {
    	$options = array();
    	
    	$options['WarningLevel'] = 'High';
    	$options['CategorySiteID'] = $this->getOption(self::OPTION_SITE_ID);
    	$options['DetailLevel'] = 'ReturnAll';
    	$options['CategoryParent'] = $parentId;
    	$options['LevelLimit'] = $levelLimit;
    	 // do request
        $xml = $this->_request('GetCategories', $options);
        
        $result = array();
        foreach ($xml->CategoryArray->Category as $category) {
        	//if ($category->CategoryID != $parentId) {
        		$result[] = (array) $category;
        	//}
        }
        return $result;
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
        
        $client->setHeaders('X-EBAY-API-COMPATIBILITY-LEVEL', $this->getOption(self::OPTION_COMPATIBILITY_LEVEL));                   
        $client->setHeaders('X-EBAY-API-DEV-NAME', $this->getOption(self::OPTION_DEV_NAME));                   
        $client->setHeaders('X-EBAY-API-APP-NAME', $this->getOption(self::OPTION_APP_NAME));                   
        $client->setHeaders('X-EBAY-API-CERT-NAME', $this->getOption(self::OPTION_CERT_NAME));                   
        $client->setHeaders('X-EBAY-API-SITEID', $this->getOption(self::OPTION_SITE_ID));                   
        $client->setHeaders('X-EBAY-API-CALL-NAME', $operation);                   

        $xmlBody = '';
        foreach ($options as $key => $option) {
        	$xmlBody .= "<{$key}>{$option}</{$key}>";
        }
        
        $xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
				<{$operation}Request xmlns=\"urn:ebay:apis:eBLBaseComponents\">
					<RequesterCredentials>
						<eBayAuthToken>".$this->getOption(self::OPTION_AUTH_TOKEN)."</eBayAuthToken>
					</RequesterCredentials>
						{$xmlBody}
				</{$operation}Request>";

        $client->setRawData($xml);
        
        $response = $client->request("POST");
        
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

        // first trying, loading XML
        $xml = simplexml_load_string($response->getBody());
        if (!$xml) {
            $message = 'It was not possible to load XML returned.';
        }

        if ($xml->Ack != 'Success') {
        	$message = 'Ack not success.';
        }
        
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
            require_once 'Zend/Service/Ebay/Trading/Exception.php';
            throw new Zend_Service_Ebay_Trading_Exception($message);
        }

        return $xml;
    }
}
