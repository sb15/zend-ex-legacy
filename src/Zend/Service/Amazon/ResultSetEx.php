<?class Zend_Service_Amazon_ResultSetEx extends Zend_Service_Amazon_ResultSet{    public function __construct(DOMDocument $dom)
    {
        $this->_dom = $dom;
        $this->_xpath = new DOMXPath($dom);
        $this->_xpath->registerNamespace('az', 'http://webservices.amazon.com/AWSECommerceService/2011-08-01');
        $this->_results = $this->_xpath->query('//az:Item');
    }	public function current()    {        return new Zend_Service_Amazon_ItemEx($this->_results->item($this->_currentIndex));    }}