<?
    {
        $this->_dom = $dom;
        $this->_xpath = new DOMXPath($dom);
        $this->_xpath->registerNamespace('az', 'http://webservices.amazon.com/AWSECommerceService/2011-08-01');
        $this->_results = $this->_xpath->query('//az:Item');
    }