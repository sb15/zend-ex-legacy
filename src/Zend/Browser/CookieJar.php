<?php

require_once 'Zend/Http/CookieJar.php';

class Zend_Browser_CookieJar extends Zend_Http_CookieJar
{
    protected $_encodeCookie = true;
    
    public function setUnencodeCookie()
    {
        $this->_encodeCookie = false;
    }
    
    public function addCookie($cookie, $ref_uri = null)
    {
        if (is_string($cookie)) {
            if ($this->_encodeCookie) {
                $cookie = Zend_Http_Cookie::fromString($cookie, $ref_uri);
            } else {
                $cookie = Zend_Http_Cookie::fromString($cookie, $ref_uri, false);
            }            
        }

        if ($cookie instanceof Zend_Http_Cookie) {
            $domain = $cookie->getDomain();
            $path = $cookie->getPath();
            if (! isset($this->cookies[$domain])) $this->cookies[$domain] = array();
            if (! isset($this->cookies[$domain][$path])) $this->cookies[$domain][$path] = array();
            $this->cookies[$domain][$path][$cookie->getName()] = $cookie;
            $this->_rawCookies[] = $cookie;
        } else {
            require_once 'Zend/Http/Exception.php';
            throw new Zend_Http_Exception('Supplient argument is not a valid cookie string or object');
        }
    }
}
