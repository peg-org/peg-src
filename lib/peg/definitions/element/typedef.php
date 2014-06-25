<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Definitions\Element;

/**
 * Represents a C type definition (typedef).
 */
class TypeDef extends VariableType
{
    
    /**
     * Holds the name of the element
     * @var string
     */
    public $name;
    
    /**
     * Reference to the header containing this element.
     * @var \Peg\Definitions\Element\Header
     */
    public $header;
    
    /**
     * Reference to the namespace containing this element.
     * @var \Peg\Definitions\Element\NamespaceElement
     */
    public $namespace;
    
    /**
     * Create a parameter element from a declaration specification, 
     * 
     * @param string $name Name of the parameter.
     * @param string $type Parameter type by specification, eg: const int*
     * @param string $default_value Default value of the parameter.
     */
    public function __construct($name, $type)
    {
        parent::__construct($type);
        
        $this->name = $name;
    }

}

?>
