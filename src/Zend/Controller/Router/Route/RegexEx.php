<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Controller
 * @subpackage Router
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: Regex.php 23772 2011-02-28 21:35:29Z ralph $
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Controller_Router_Route_Abstract */
require_once 'Zend/Controller/Router/Route/Abstract.php';
require_once 'Zend/Controller/Router/Route/Regex.php';

/**
 * Regex Route
 *
 * @package    Zend_Controller
 * @subpackage Router
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Controller_Router_Route_RegexEx extends Zend_Controller_Router_Route_Regex
{
    
    public function match($path, $partial = false)
    {
        if (!$partial) {
            $path = trim(urldecode($path), '/');
            $regex = '#^' . $this->_regex . '$#ui';
        } else {
            $regex = '#^' . $this->_regex . '#ui';
        }

        $res = preg_match($regex, $path, $values);

        if ($res === 0) {
            return false;
        }

        if ($partial) {
            $this->setMatchedPath($values[0]);
        }

        // array_filter_key()? Why isn't this in a standard PHP function set yet? :)
        foreach ($values as $i => $value) {
            if (!is_int($i) || $i === 0) {
                unset($values[$i]);
            }
        }

        $this->_values = $values;

        $values   = $this->_getMappedValues($values);
        $defaults = $this->_getMappedValues($this->_defaults, false, true);
        $return   = $values + $defaults;

        return $return;
    }

}
