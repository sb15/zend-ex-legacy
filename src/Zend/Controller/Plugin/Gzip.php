<?php

class Zend_Controller_Plugin_Gzip extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopShutdown()
    {
        $content = $this->getResponse()->getBody();
        /*$content = preg_replace(
                    array('/(\x20{2,})/',   '/\t/', '/\n\r/'),
                    array(' ', ' ', ' '),
                    $content
                );
		*/
        if (@strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') === FALSE) {
            $this->getResponse()->setBody($content);
        } else {
            header('Content-Encoding: gzip');
            $this->getResponse()->setBody(gzencode($content, 5));
        }
    }
}
