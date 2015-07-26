<?php

/** Zend_Controller_Router_Route_Abstract */
require_once 'Zend/Controller/Router/Route.php';

class Zend_Controller_Router_RouteEx extends Zend_Controller_Router_Route
{
    /**
     * Matches a user submitted path with parts defined by a map. Assigns and
     * returns an array of variables on a successful match.
     *
     * @param string $path Path used to match against this routing map
     * @return array|false An array of assigned values or a false on a mismatch
     */
    public function match($path, $partial = false)
    {
        if ($this->_isTranslated) {
            $translateMessages = $this->getTranslator()->getMessages();
        }

        $pathStaticCount = 0;
        $values          = array();
        $matchedPath     = '';

        if (!$partial) {
            $path = trim($path, $this->_urlDelimiter);
        }

        if ($path !== '') {
            $path = explode($this->_urlDelimiter, $path);

            foreach ($path as $pos => $pathPart) {
                // Path is longer than a route, it's not a match
                if (!array_key_exists($pos, $this->_parts)) {
                    if ($partial) {
                        break;
                    } else {
                        return false;
                    }
                }

                $matchedPath .= $pathPart . $this->_urlDelimiter;

                // If it's a wildcard, get the rest of URL as wildcard data and stop matching
                if ($this->_parts[$pos] == '*') {
                    $count = count($path);
                    for($i = $pos; $i < $count; $i+=2) {
                        $var = urldecode($path[$i]);
                        if (!isset($this->_wildcardData[$var]) && !isset($this->_defaults[$var]) && !isset($values[$var])) {
                            $this->_wildcardData[$var] = (isset($path[$i+1])) ? urldecode($path[$i+1]) : null;
                        }
                    }

                    $matchedPath = implode($this->_urlDelimiter, $path);
                    break;
                }

                $name     = isset($this->_variables[$pos]) ? $this->_variables[$pos] : null;
                $pathPart = urldecode($pathPart);

                // Translate value if required
                $part = $this->_parts[$pos];
                if ($this->_isTranslated && (mb_substr($part, 0, 1, 'UTF-8') === '@' && mb_substr($part, 1, 1, 'UTF-8') !== '@' && $name === null) || $name !== null && in_array($name, $this->_translatable)) {
                    if (mb_substr($part, 0, 1, 'UTF-8') === '@') {
                        $part = mb_substr($part, 1, null, 'UTF-8');
                    }

                    if (($originalPathPart = array_search($pathPart, $translateMessages)) !== false) {
                        $pathPart = $originalPathPart;
                    }
                }

                if (mb_substr($part, 0, 2, 'UTF-8') === '@@') {
                    $part = mb_substr($part, 1, null, 'UTF-8');
                }

                // If it's a static part, match directly
                if ($name === null && $part != $pathPart) {
                    return false;
                }

                // If it's a variable with requirement, match a regex. If not - everything matches
                if ($part !== null && !preg_match($this->_regexDelimiter . '^' . $part . '$' . $this->_regexDelimiter . 'iu', $pathPart)) {
                    return false;
                }

                // If it's a variable store it's value for later
                if ($name !== null) {
                    $values[$name] = $pathPart;
                } else {
                    $pathStaticCount++;
                }
            }
        }

        // Check if all static mappings have been matched
        if ($this->_staticCount != $pathStaticCount) {
            return false;
        }

        $return = $values + $this->_wildcardData + $this->_defaults;

        // Check if all map variables have been initialized
        foreach ($this->_variables as $var) {
            if (!array_key_exists($var, $return)) {
                return false;
            } elseif ($return[$var] == '' || $return[$var] === null) {
                // Empty variable? Replace with the default value.
                $return[$var] = $this->_defaults[$var];
            }
        }

        $this->setMatchedPath(rtrim($matchedPath, $this->_urlDelimiter));

        $this->_values = $values;

        return $return;

    }
}
