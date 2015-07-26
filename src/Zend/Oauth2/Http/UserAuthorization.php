<?php

class Zend_Oauth2_Http_UserAuthorization extends Zend_Oauth_Http_UserAuthorization 
{
    public function assembleParams()
    {
        Zend_Debug::dump( $this->_parameters);
        die;
    }
}