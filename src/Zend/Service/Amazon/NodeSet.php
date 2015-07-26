<?

class Zend_Service_Amazon_NodeSet
{
	public $nodes;

	public function __construct($dom)
    {
        if (null === $dom) {
            require_once 'Zend/Service/Amazon/Exception.php';
            throw new Zend_Service_Amazon_Exception('Item element is empty');
        }
		//var_dump(get_class($dom));
        if (!$dom instanceof DOMDocument) { //DOMDocument
            require_once 'Zend/Service/Amazon/Exception.php';
            throw new Zend_Service_Amazon_Exception('Item is not a valid DOMDocument');
        }
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('az', 'http://webservices.amazon.com/AWSECommerceService/2011-08-01');

		$nodes = $xpath->query('//az:BrowseNodes/az:BrowseNode/az:Children/az:BrowseNode', $dom);

        //print_r($nodes->length);
		//$result = $xpath->query("./az:BrowseNode/text()", $dom);
		//print_r($result);
		foreach ($nodes as $node) {
			//Zend_Debug::dump($node);
			$temp = array();
			$temp['id'] = (string) $node->getElementsByTagName('BrowseNodeId')->item(0)->nodeValue;
			$temp['name'] = (string) $node->getElementsByTagName('Name')->item(0)->nodeValue;
			//$temp['name'] = $node->Name;
			$this->nodes[] = $temp;
		}
    }
}