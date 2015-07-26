<?php

require_once 'Zend/Browser/Zend/Browser/Console.php';

class Zend_Browser_Script_SearchSubmit_Google {
	
	const URL = 'http://www.google.com/addurl/';
	
	private $browser = null;
	
	public function __construct($cookiesStringBase64 = '')
	{
		$this->browser  = new Zend_Browser_Console($cookiesStringBase64);
	}
	
	public function getBrowser()
	{
		return $this->browser;
	}
	
	public function init($submitSite) 
	{
		$response = $this->getBrowser()->doRequest(self::URL);
		return $this->getForm($response);
	}
	
	public function getForm($html, $default = true) 
	{
		$form     = $this->getBrowser()->getForm($response, 'form[@action="Captcha"]');
		// default
		if ($default) {
			
		}
		$form['cookie'] = $this->getBrowser()->getCookieAsStringBase64();
		return $form;
	}
	
	public function doPost($form, $cookiesStringBase64)
	{
		$response = $this->getBrowser()->doRequest($form['action'], $form['method'], $form['data'], false, 0, self::URL);
		if ($this->isSuccess($response)) {
			return true;
		} else {
			// error get page again
			return $this->getForm($response, false);
		}
	}
	
	
	public function isSuccess($html) 
	{
		return preg_match('##uis', $html);
	}
	
	
}