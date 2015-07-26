<?php

class Zend_Auth_Adapter_Consumer_Google implements Zend_Auth_Adapter_Interface
{

    protected $_consumerId = 'google';

    protected $_config = array(
        'consumerId'            => '',
        'consumerSecret'        => '',
        'callbackUrl'           => '',

        'userAuthorizationUrl'  => 'https://accounts.google.com/o/oauth2/auth',
        'accessTokenUrl'        => 'https://accounts.google.com/o/oauth2/token',
        'requestDataUrl'       => 'https://www.googleapis.com/oauth2/v1/userinfo',
        'responseType'          => 'code',
        'scope'                 => null
    );

    public function __construct($options)
    {
        $this->_config = array_merge($this->_config, $options);
    }

    public function authenticate()
    {
        $consumer = new Zend_Oauth2_Consumer($this->_config);

        try {
            if (isset($_GET['code']) && !empty($_GET['code'])) {

                $options = array(
                    'client_id'     => $this->_config['consumerId'],
                    'client_secret' => $this->_config['consumerSecret'],
                    'redirect_uri'  => $this->_config['callbackUrl'],
                    'code'          => trim($_GET['code']),
                    'grant_type'    => 'authorization_code'
                );
                $accessTokenInfo = $consumer->getAccessToken($options);
                Zend_Debug::dump($accessTokenInfo);

                $accessToken = $accessTokenInfo['access_token'];

                $options = array(
                   'access_token' => $accessToken
                );

                $identity = $consumer->getIdentity($options);
                $identity['CONSUMER_ID'] = $this->_consumerId;
                return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, (object) $identity);

            } elseif (!isset($_GET['error'])) {

                $consumer->redirect(array(
                    'client_id' => $this->_config['consumerId'],
                    'redirect_uri' => $this->_config['callbackUrl'],
                    'response_type' => 'code',
                    'state'         => 'profile',
                    'access_type'   => 'offline',
                    'scope'         => implode($this->_config['scope'], ' ')
                ));

            } else {
                throw new Exception($_GET['error']);
            }

        } catch (Exception $e) {
            return new Zend_Auth_Result(Zend_Auth_Result::FAILURE, false, array($e->getMessage()));
        }

    }

}