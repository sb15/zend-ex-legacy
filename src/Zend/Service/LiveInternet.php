<?php
require_once 'Zend/Browser/Console.php';

class Zend_Service_LiveInternet 
{
	private $_client = null;
	private $_urls = array(
		'login' => 'http://www.liveinternet.ru/stat/%SITE%/index.html',
		'attendance' => 'http://www.liveinternet.ru/stat/%SITE%/index.html'
	);
	
	private $_data = array();
	
	/**
     * @return Zend_Browser_Console
     */
    public function getClient()
    {
        return $this->_client;
    }
	
	public function __construct($site, $password)
    {
        $this->_client = new Zend_Browser_Console();
		foreach ($this->_urls as &$url) {
			$url = str_replace('%SITE%', $site, $url);
		}
		$this->_login($password);
    }
	
	private function _login($password) 
	{
		$url = $this->_urls['login'];
		$html = $this->getClient()->doRequest($url);

		$form = $this->getClient()->getForm($html, 'form');
		$form['fields']['password'] = $password;

		$html = $this->getClient()->doRequest($form['action'], $form['method'], $form['fields'], false, 0, null, $form['enctype']);
	}
	
	public function getAttendance()
	{
		$url = $this->_urls['attendance'];
		$html = $this->getClient()->doRequest($url);
		
		require_once 'Zend/Dom/Nokogiri.php';
		$nokogiri = Zend_Dom_Nokogiri::fromHtml($html);
		$table = $nokogiri->get('table[bgcolor=#e8e8e8]')->toArray();

		if (!$table) {
			throw new Exception('Table bot found');
		}
		
		$_data['attendance']['views'] = is_array($table[0]['tr'][1]['td'][2]['#text']) ? $table[0]['tr'][1]['td'][2]['#text'][0] : $table[0]['tr'][1]['td'][2]['#text'];
		$_data['attendance']['viewsDiff'] = @$table[0]['tr'][1]['td'][2]['a'][0]['font'][0]['#text'] ?: 0;

		$_data['attendance']['users'] = $table[0]['tr'][3]['td'][2]['#text'][0];
		$_data['attendance']['usersDiff'] = @$table[0]['tr'][3]['td'][2]['a'][0]['font'][0]['#text'] ?: 0;
		
		return $_data['attendance'];
	}
}