<?

require_once 'Zend/Controller/Router/Route/Static.php';

class Zend_Controller_Router_Route_StaticEx extends Zend_Controller_Router_Route_Static
{

	public function match($path, $partial = false)
    {
		$path = urldecode($path);
        if ($partial) {
            if ((empty($path) && empty($this->_route))
                || (mb_substr($path, 0, mb_strlen($this->_route, 'UTF-8'), 'UTF-8') === $this->_route)
            ) {
                $this->setMatchedPath($this->_route);
                return $this->_defaults;
            }
        } else { 
            if (trim($path, '/') == $this->_route) {
                return $this->_defaults;
            }
        }

        return false;
    }

}