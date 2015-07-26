<?

class Zend_Crypt_Aes {

	private $_cipher = null;
	private $_iv = null;
	private $_key = null;
	
	public function __construct($key) 
	{
		if (strlen($key) != 32) {
			throw new Exception('Key length invalid, must be 32 chars', '0');
		}
		$this->_key = $key;
		$this->_cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_256, '', MCRYPT_MODE_CBC, '');
		$this->_iv  = str_repeat("5", mcrypt_enc_get_iv_size($this->_cipher));
	}
	
	private function initEncription()
	{
		if (mcrypt_generic_init($this->_cipher, $this->_key, $this->_iv) == -1) {
			throw new Exception('init fail', 0);
		}
	}
	
	public function encrypt($text) 
	{
		$this->initEncription();
		return mcrypt_generic($this->_cipher, $text);
	}
	
	public function decript($text) 
	{
		$this->initEncription();
		return rtrim( mdecrypt_generic($this->_cipher, $text));
	}
}