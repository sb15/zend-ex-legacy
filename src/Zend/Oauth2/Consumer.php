<?php

/** Zend_Oauth */
require_once 'Zend/Oauth.php';

/** Zend_Uri */
require_once 'Zend/Uri.php';

/** Zend_Oauth_Http_RequestToken */
require_once 'Zend/Oauth/Http/RequestToken.php';

/** Zend_Oauth_Http_UserAuthorization */
require_once 'Zend/Oauth/Http/UserAuthorization.php';

/** Zend_Oauth_Http_AccessToken */
require_once 'Zend/Oauth/Http/AccessToken.php';

/** Zend_Oauth_Token_AuthorizedRequest */
require_once 'Zend/Oauth/Token/AuthorizedRequest.php';

/** Zend_Oauth_Config */
require_once 'Zend/Oauth/Config.php';

class Zend_Oauth2_Consumer extends Zend_Oauth
{
     /**
     * @var array
     */
    protected $_config = null;

    public function __construct($options = null)
    {
        $this->_config = $options;
    }

    public function redirect(
        array $customServiceParameters = null        
    ) {
        
        $common = array();        
        $params = array_merge($common, $customServiceParameters);
        
        $url = $this->_config['userAuthorizationUrl'] . '?';
        $url .= http_build_query($params, null, '&');        
        header('Location: ' . $url);        
        exit(1);
    }


    public function getAccessToken(array $customServiceParameters = null) 
    {
        $client = self::getHttpClient();
        $client->setUri($this->_config['accessTokenUrl']);        
        foreach ($customServiceParameters as $paramName => $paramValue) {
            $client->setParameterPost($paramName, $paramValue);
        }
        
        $response = $client->request('POST');
        
        if ($response->isError()) {
            $error = 'Service unavailable';
            throw new Exception('Service unavailable');            
        } elseif ($response->isSuccessful()) {
            return (array) Zend_Json::decode($response->getBody());
        }        
    }
   
    public function getIdentity(array $customServiceParameters = null, $method = 'GET')
    {
        $client = self::getHttpClient();
        $client->setUri($this->_config['requestDataUrl']);
        foreach ($customServiceParameters as $paramName => $paramValue) {
            if ($method == 'GET') {
                $client->setParameterGet($paramName, $paramValue);
            } else {
                $client->setParameterPost($paramName, $paramValue);
            }
        }
        
        $response = $client->request($method);
        
        if ($response->isError()) {
            $error = 'Service unavailable';
            throw new Exception('Service unavailable');
        } elseif ($response->isSuccessful()) {
            return (array) Zend_Json::decode($response->getBody());
        }
    }
    
}
