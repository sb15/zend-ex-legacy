<?php

require_once 'Zend/Controller/Router/Route/Static.php';

class Zend_Controller_Router_Route_Redirector extends Zend_Controller_Router_Route_Static
{

    protected function redirect()
    {       
        $front = Zend_Controller_Front::getInstance();
        $defaults = $this->getDefaults();
        if (!array_key_exists('newRoute', $defaults)) {
            throw new Exception('No new route defined');
        }
        
        $redirectRoute = $this->getDefault('newRoute');                           
        $front->getResponse()->setRedirect($redirectRoute, 301)
                             ->sendResponse();
        exit(0);
    }
    
    public function match($path, $partial = false)
    {
        $path = urldecode($path);
        if ($partial) {
            if ((empty($path) && empty($this->_route)) || (mb_substr($path, 0, mb_strlen($this->_route, 'UTF-8'), 'UTF-8') === $this->_route)) {
                $this->setMatchedPath($this->_route);
                $this->redirect();
            }
        } else {
            if (trim($path, '/') == $this->_route) {
                $this->redirect();
            }
        }

        return false;
    }

}