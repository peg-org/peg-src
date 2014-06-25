<?php
/**
 * @author Jefferson González
 * @license MIT
 */

namespace Signals;

/**
 * Container of variable references that is passed to every signal listener.
 */
class SignalData
{
    /**
     * Associative array with references to stored arguments.
     * @var array
     */
    public $arguments;
    
    /**
     * Default constructor.
     */
    public function __construct()
    {
        $this->arguments = array();
    }
    
    /**
     * Store a reference to a variable.
     * @param string $name Name of variable.
     * @param mixed $value Current variable.
     */
    public function Add($name, &$value)
    {
        $this->arguments[$name] = &$value;
    }
    
    /**
     * Override default getter so we can get stored references.
     * @param type $name
     * @return mixed Returns null if property isn't found.
     */
    public function &__get($name)
    {
        if(!isset($this->arguments[$name]))
            return null;
        
        return $this->arguments[$name];
    }
}
?>
