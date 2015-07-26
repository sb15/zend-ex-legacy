<?php
  
require_once 'Zend/Http/Client.php';
require_once 'Zend/Http/CookieJar.php';
require_once 'Zend/Http/Client/Adapter/Curl.php';
require_once 'Zend/Registry.php';
require_once 'Zend/Debug.php';
require_once 'Zend/Browser/CookieJar.php';

class Zend_Browser_Console {
    
    protected $_client   = null;

    protected $_referer = '';
    protected $_reqNum = 0;
    protected $_debug = false;

    protected $lastResponseBody = null;
    protected $lastResponseUrl = null;

    const STORAGE_FILE = 'file';
    const STORAGE_STRING = 'string';
    
    protected static $_instance = null;

    public function setLastResponseUrl($lastResponseUrl)
    {
        $this->lastResponseUrl = $lastResponseUrl;
    }

    public function getLastResponseUrl()
    {
        return $this->lastResponseUrl;
    }

    public function setLastResponseBody($lastResponseBody)
    {
        $this->lastResponseBody = $lastResponseBody;
    }

    public function getLastResponseBody()
    {
        return $this->lastResponseBody;
    }

    public static function getInstance()
    {
        if (!self::$_instance instanceof Zend_Browser_Console) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function setDebug()
    {
        $this->_debug = true;
    }
    
    public function trace($text)
    {
        if (!$this->_debug) {
            return;
        }
        //file_put_contents("D:\\weferferf", $text . "\n", FILE_APPEND);
    }
    
    public function __construct($cookies = '', $storageEngine = self::STORAGE_STRING) 
    {
        $client = new Zend_Http_Client();
        $client->setConfig(array('maxredirects' => 0,
                                 'timeout'      => 120,
                                 //'ssltransport' => 'tls',                                 
                                 'useragent'    => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.0.3) Gecko/2008092417 Firefox/3.0.3 WebMoney Advisor'));

        $this->setClient($client);
        
        if ($storageEngine == self::STORAGE_STRING) {
        	$this->loadCookieFromStringBase64($cookies);
        } elseif ($storageEngine == self::STORAGE_FILE) {
        	$this->loadCookie();
        }
        $this->getClient()->getCookieJar()->setUnencodeCookie();
        
    }
        
    public function getCookieFile()
    {
        $tempDir = sys_get_temp_dir();
        //$tempDir = '';
        return $tempDir . 'Zend_Browser_Console_v1.0';
    }
    
    public function saveCookie() 
    {
    	$this->trace('save cookie file');
        //file_put_contents($this->getCookieFile(), serialize($this->getClient()->getCookieJar()));
    }
    
    public function getReferer()
    {
        return $this->_referer;
    }
    
    public function setReferer($url)
    {
        $this->_referer = $url;
    }
    
    public function loadCookie()
    {
        if (is_file($this->getCookieFile())) {
            $cookie  = file_get_contents($this->getCookieFile());
            $cookie  = unserialize($cookie);            
        } else {
            $cookie = new Zend_Browser_CookieJar();
        }
        $this->getClient()->setCookieJar($cookie);
    }
    
    public function loadCookieFromStringBase64($cookies)
    {
    	if (empty($cookies)) {
    		$cookieJar = new Zend_Browser_CookieJar();
    	} else {
    		$cookieJar  = unserialize(base64_decode($cookies));
    	}        
    	$this->getClient()->setCookieJar($cookieJar);
    }
       
    /**
    * @return Zend_Http_Client
    */
    public function getClient() 
    {
        return $this->_client;
    }
    
    public function setClient($client) 
    {
        $this->_client = $client;
    }
    
    public function getCookie() 
    {
        return $this->getClient()->getCookieJar();
    }
    
	public function getCookieAsStringBase64() 
    {
        return base64_encode(serialize($this->getClient()->getCookieJar()));
    }
    
    public function getImages($html)
    {
    	require_once 'Zend/Dom/Query.php';
        $dom = new Zend_Dom_Query($html);
        $imgs = $dom->query('img');
        $result = array();
        foreach ($imgs as $img) {
        	$result[] = array('src' => $this->getAbsoluteUrl($img->getAttribute('src')),
        					  'id'  => $img->getAttribute('id'));        	
        }
        return $result;
    }
    
	public function getIFrames($html)
    {
    	require_once 'Zend/Dom/Query.php';
        $dom = new Zend_Dom_Query($html);
        $frames = $dom->query('iframe');
        $result = array();
        foreach ($frames as $frame) {
        	$result[] = array('src' => $frame->getAttribute('src'));        	
        }
        return $result;
    }
    
    public function getForm($html, $xpath, $withImages = false)
    {

        if (!$html) {
            $html = $this->getLastResponseBody();
        }

        require_once 'Zend/Dom/Query.php';
        $dom = new Zend_Dom_Query($html);
        //'form[@id="addForm"]'
        $form = $dom->query($xpath)->current();
        if (!$form instanceof DOMElement) {
            return false;
        }
        return $this->getFormDataAsArray($form, $withImages);
    }
	
	public function getFormDataAsArray(DOMElement $form, $withImages = false) 
	{
		$action = $form->getAttribute('action');
		
		if ($action) {
			$action = $this->getAbsoluteUrl($action);
		} else {
			$action = $this->getClient()->getUri()->__toString();
			
		}
		// to-do
		// относительный адрес определяется неправильно 
				
		$formData = array ('action' => $action, 
						   'method' => $form->getAttribute('method'),
                           'enctype' => $form->getAttribute('enctype') ? $form->getAttribute('enctype') : Zend_Http_Client::ENC_URLENCODED);
		
		$formInputData = array();
		
		$inputs = $form->getElementsByTagName ( 'input' );
		foreach ( $inputs as $input ) {
			$inputName = $input->getAttribute ( 'name' );
			if (! empty ( $inputName )) {
				$formInputData [$inputName] = $input->getAttribute ( 'value' );
			}
		}
		
		$selects = $form->getElementsByTagName ( 'select' );
		foreach ( $selects as $select ) {
			
			$options = $select->getElementsByTagName ( 'option' );
			$optionsData = array ();
			foreach ( $options as $option ) {
				$optionsData [] = $option->getAttribute ( 'value' );
			}
			
			$formInputData [$select->getAttribute ( 'name' )] = $optionsData;
		}
		
		if ($withImages) {
			$imgs = $form->getElementsByTagName ( 'img' );				
			foreach ( $imgs as $img ) {
				$imageSrc = $img->getAttribute ( 'src' );
				$image = array('src' => $imageSrc);
				$image['data'] = base64_encode($this->doRequest($imageSrc, 'GET', true));
				$formData['_images'][] = $image; 
			}
		}
		
		$formData ['fields'] = $formInputData;
		return $formData;
	}
    
    public function getForms($html) 
    {
        if (!$html) {
            $html = $this->getLastResponseBody();
        }

        require_once 'Zend/Dom/Query.php';
        $dom = new Zend_Dom_Query($html);
        $forms = $dom->query('form');
        $result = array();
        $i = 0;
        foreach ($forms as $form) {
            $formName = $form->getAttribute('name');
            if (empty($formName)) {
                $formName = $i;
                $i++;
            }
            $result[$formName] = $this->getFormDataAsArray($form);
        }
        return $result;
    }
    
    public function getRootDomain($url = null) 
    {
    	if (!$url) {
    		$url = $this->getClient()->getUri();
    	}
    	$url = parse_url($url);
    	// to do with path
    	return $url['scheme'] . '://' . $url['host'] . '/';
    }
    
    public function getAbsoluteUrl($url) 
    {
    	if (strpos($url, "http") === 0) {
    		return $url;
    	} else {
    		$rootDomain = $this->getRootDomain();
    		return $rootDomain . ltrim($url, '/');
    	}
    }
       
    public function getDomainFromUrl($url) 
    {
    	$parts = parse_url($url);
    	return $parts['scheme'] . '://' . $parts['host'] . '/';
    }
    
    public function doFrameRequest($url, $method = 'GET', $data = array(), $isChild = false, $redirects = 0, $referer = null) 
    {
    	$referer = $this->getReferer();
    	$this->doRequest($url);
    	$this->getClient()->setHeaders('Referer', $referer);
    	$this->setReferer($referer);
    }

    public function sentForm($form)
    {
        return $this->doRequest($form['action'], $form['method'], $form['fields'], false, 0, null, $form['enctype']);
    }

    public function doRequest($url, $method = 'GET', $data = array(), $isChild = false, $redirects = 0, $referer = null, $postEncoding = Zend_Http_Client::ENC_URLENCODED)
    {
        $traceLog = "\n\nRequest url: {$url}\n".
                    "Method: {$method}\n" .
                    "Data: " . print_r($data, true) .
                    "Is Child: " . (int) $isChild . "\n" .
                    "Redirects: {$redirects}\n".
                    "Referer: {$referer}\n".
                    "Post Encoding: {$postEncoding}\n";

		//$this->trace($traceLog);

    	$this->_reqNum++;
    	$client = $this->getClient();

    	$method = strtoupper($method);
    	if ($redirects > 5) {
    		throw new Exception('Max Redirects');
    	}
    	
        $url = str_replace("&amp;", "&", $url);
        
    	if (is_null($url)) {
    		throw new Exception('No url');
    	}
    	
    	$client->setUri($url);
    	$client->setMethod($method);
    	$client->resetParameters();

    	if (strtoupper($method) == 'GET') {
    		$client->setParameterGet($data);
    		// url fix
    	} else {    	
    		$client->setParameterPost($data);
    		$client->setEncType($postEncoding);
    	}

    	if (!is_null($referer)) {
    		$client->setHeaders('Referer', $referer);
    		$this->setReferer($referer);
    	}

    	if ($isChild) {
    		$this->trace('Child process');
    	}
    	
    	try {
    		$response = $client->request($method);
    	} catch (Exception $e) {
    		$this->trace('exception req: ' . print_r($e, true));
			throw $e;
    	}

        $code = $response->getStatus();

        $this->trace("Client Request: \n" . $client->getLastRequest());
        $this->trace("Client Response: \n" . preg_replace("#\n\n.*#is", "", $client->getLastResponse()) . "\n\n");

    	if ($code == 200) {
		
			if ($isChild == false) {
				$client->setHeaders('Referer', $url);
				$this->setReferer($url);
			}

    		$body = $response->getBody();

			if (strpos($response->getHeader('Content-type'), 'text/html') === 0) {

				// to-do detect not html
				require_once 'Zend/Dom/Query.php';
				$dom = new Zend_Dom_Query($body);
				$metaData = $dom->query('meta');
				if ($metaData) {
					for ($i = 0; $i < $metaData->count(); $i++) {
						$current = $metaData->current();
						$httpEquiv = $current->getAttribute('http-equiv');

						if ($httpEquiv == 'refresh') {
							$content = $current->getAttribute('content');
							$contentPart = explode(";", $content);
							$contentPart = explode("=", trim($contentPart[1]), 2);
							if ($contentPart[0] == 'url') {
								$url = $this->getAbsoluteUrl($contentPart[1]);	        				
							}
							$this->trace("Meta redirect: " . $url);

							return $this->doRequest($url, 'GET', array(), $isChild, $redirects + 1);
						}	
						$metaData->next();
					}
				}
			} else {
				$this->trace('not in array type ' . $response->getHeader('Content-type'));
			}			

            $this->setLastResponseBody($body);
            $this->setLastResponseUrl($url);
        	return $body;

    	}

    	if ($code >= 300 && $code < 400) {

    		$redirectUrl = $response->getHeader('Location');
    		if (strpos($redirectUrl, 'http') !== 0) {
    			// относит url
    			$redirectUrl = $this->getDomainFromUrl($url) . ltrim($redirectUrl,'/'); 
    		}

			$this->trace("{$code} Redirect To " . $redirectUrl);
    		return $this->doRequest($redirectUrl, 'GET', array(), false, $redirects + 1);
    	}
        
        if ($code > 400) {

            $body = $response->getBody();

            $this->setLastResponseBody($body);
            $this->setLastResponseUrl($url);

            return $body;
        }
    }


    public function getDomParserNokogiri($content = null, $fromEncoding = null)
    {
        if (!$content) {
            $content = $this->getLastResponseBody();
        }
        $content = preg_replace('#<head\b[^>]*>#isu', "<head>\r\n<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />", $content);
        return new Zend_Dom_Nokogiri($content);
    }
}  
