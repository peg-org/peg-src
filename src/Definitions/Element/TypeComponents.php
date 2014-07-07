<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib\Definitions\Element;

/**
 * Class to identify the components of a type seperated by the scope operator,
 * eg: Something::Someclass::Sometype. This class shouldn't be used by
 * it self but with the help of the Symbols class.
 * @see \Peg\Lib\Definitions\Symbols::GetComponents($type)
 */
class TypeComponents
{
    public $namespace;
    public $class;
    public $type;
    
    public function __construct()
    {
        $this->namespace = "\\";
    }
    
    /**
     * Returns true if theres a namespace on the 
     * components of a type declaration.
     * @return boolean
     */
    public function HasNamespace()
    {
        if($this->namespace && $this->namespace != "\\")
            return true;
        
        return false;
    }
    
    /**
     * Returns true if theres a class on the 
     * components of a type declaration.
     * @return boolean
     */
    public function HasClass()
    {
        if($this->class)
            return true;
        
        return false;
    }
}

