<?php

class Zend_Auth_Adapter_Consumer_Vk implements Zend_Auth_Adapter_Interface
{

    protected $_consumerId = 'vk';

    protected $_config = array(
        'consumerId'            => '',
        'consumerSecret'        => '',
        'callbackUrl'           => '',

        'userAuthorizationUrl'  => 'http://api.vkontakte.ru/oauth/authorize',
        'accessTokenUrl'        => 'https://api.vkontakte.ru/oauth/access_token',
        'requestDataUrl'        => 'https://api.vk.com/method/getProfiles',
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
                    'code'          => trim($_GET['code'])
                );
                $accessTokenInfo = $consumer->getAccessToken($options);
                $accessToken = $accessTokenInfo['access_token'];

                $options = array(
                    'uid'          => $accessTokenInfo['user_id'],
                    'fields'       => implode(",", $this->_config['fields']),
                    'access_token' => $accessToken
                );

                $identity = $consumer->getIdentity($options);
                $identity = reset($identity['response']);

                $identity['CONSUMER_ID'] = $this->_consumerId;
                return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, (object) $identity);

            } elseif (!isset($_GET['error'])) {

                $consumer->redirect(array(
                    'client_id'     => $this->_config['consumerId'],
                    'redirect_uri'  => $this->_config['callbackUrl'],
                    'scope'  => implode(",", $this->_config['scope']),
                    'response_type' => 'code',
                ));

            } else {
                throw new Exception($_GET['error']);
            }

        } catch (Exception $e) {
            return new Zend_Auth_Result(Zend_Auth_Result::FAILURE, false, array($e->getMessage()));
        }

    }

}
