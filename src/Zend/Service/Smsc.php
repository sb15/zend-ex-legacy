<?php

class Zend_Service_Smsc
{
	private $__method = "POST";
	private $__scheme = "https";
	private $__user = null;
	private $__password = null;
	
	public function __construct($user, $password)
	{
		$this->__user = $user;
		$this->__password = $password;
	}
	
	private function readUrl($url)
	{
		$ret = null;
		$c = curl_init();

		if ($this->__method == 'POST') {
			list($url, $post) = explode('?', $url, 2);
			curl_setopt($c, CURLOPT_POST, true);
			curl_setopt($c, CURLOPT_POSTFIELDS, $post);
		}

		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($c, CURLOPT_TIMEOUT, 10);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);

		$ret = curl_exec($c);
		curl_close($c);
		return $ret;
	}
	
	public function getSmsCost($phones, $message, $translit = 0, $sender = false, $charset = "UTF-8")
	{
		return $this->sendCmd("send", "cost=1&phones=".urlencode($phones)."&mes=".urlencode($message).($sender === false ? "" : "&sender=".urlencode($sender))."&charset=$charset&translit=$translit");		
	}
	
	public function sendSms($phones, $message, $sender = null, $translit = 0, $time = 0, $id = 0, $flash = 0, $charset = "UTF-8", $timezone = "", $query = "")
	{
		if (APPLICATION_ENV == 'development') {
			return json_decode('{"id":1,"cnt":1,"cost":"1"}');
		}
		$result = $this->sendCmd("send", "cost=3&phones=".urlencode($phones)."&mes=".urlencode($message).
						"&translit=$translit&id=$id&flash=$flash".($sender === null ? "" : "&sender=".urlencode($sender)).
						"&charset=$charset".($time ? "&time=".urlencode($time)."&tz=$timezone" : "").($query ? "&$query" : ""));
	 	
		if (isset($result->error)) {
			throw new Exception("Send error " . serialize($result));
		}
		
		// (id, cnt, cost, balance) или (id, -error)
		
		return $result;
	}
	
	private function sendCmd($cmd, $arg = "") 
	{
		$url = ($this->__scheme ? "https" : "http")."://smsc.ru/sys/{$cmd}.php?login=".urlencode($this->__user)."&psw=".urlencode($this->__password)."&fmt=3&".$arg;
	
		$i = 0;
		$ret = "";
		do {
			if ($i) {
				sleep(2);
			}	
			$ret = $this->readUrl($url);
		}
		while ($ret == "" && ++$i < 3);
	
		if ($ret == "") {
			throw new Exception("Error send");
			$ret = null; 
		}
	
		return json_decode($ret);
	}
}