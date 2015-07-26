<?php

class Zend_Auth_Adapter_Consumer_Mailru implements Zend_Auth_Adapter_Interface
{

    protected $_consumerId = 'mailru';

    protected $_config = array(
        'consumerId'            => '',
        'consumerSecret'        => '',
        'callbackUrl'           => '',

        'userAuthorizationUrl'  => 'https://connect.mail.ru/oauth/authorize',
        'accessTokenUrl'        => 'https://connect.mail.ru/oauth/token',
        'requestDataUrl'        => 'http://www.appsmail.ru/platform/api',
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
                    'redirect_uri'  => $this->_config['callbackUrl'],
                    'client_secret' => $this->_config['consumerSecret'],
                    'code'          => trim($_GET['code']),
                    'grant_type'    => 'authorization_code',
                );
                $accessTokenInfo = $consumer->getAccessToken($options);

                Zend_Debug::dump($accessTokenInfo);

                $accessToken = $accessTokenInfo['access_token'];

                $options = array(
                    'app_id' => $this->_config['consumerId'],
                    'method' => 'users.getInfo',
                    'secure' => 1,
                    'session_key' => $accessToken,
                );

                $sign = $this->getSign($options, $accessToken);
                $options['sig'] = $sign;

                $identity = $consumer->getIdentity($options, 'POST');
                $identity = reset($identity);

                $identity['CONSUMER_ID'] = $this->_consumerId;
                return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, (object) $identity);

            } elseif (!isset($_GET['error'])) {

                $consumer->redirect(array(
                    'client_id'     => $this->_config['consumerId'],
                    'redirect_uri'  => $this->_config['callbackUrl'],
                    'response_type' => 'code',
                ));

            } else {
                throw new Exception($_GET['error']);
            }

        } catch (Exception $e) {
            return new Zend_Auth_Result(Zend_Auth_Result::FAILURE, false, array($e->getMessage()));
        }

    }

    public function getSign(array $requestParams, $accessToken)
    {
        $consumerSecret = $this->_config['consumerSecret'];
        ksort($requestParams);

        $params = '';
        foreach ($requestParams as $key => $value) {
            $params .= $key . '=' . $value;
        }
        return md5($params . $consumerSecret);
    }

}

