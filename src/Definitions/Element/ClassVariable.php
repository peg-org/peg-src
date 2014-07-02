<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Definitions\Element;

/**
 * Represents a class member variable/constant element.
 */
class ClassVariable extends VariableType
{
    
    /**
     * Holds the name of the element
     * @var string
     */
    public $name;
    
    /**
     * Flag that indicates if the variable is static.
     * @var bool
     */
    public $static;
    
    /**
     * Flag that indicates if the variable is mutable.
     * @var bool
     */
    public $mutable;
    
    /**
     * Flag that indicates if the variable is protected.
     * @var bool
     */
    public $protected;
    
    /**
     * Flag that indicates if the variable is public.
     * @var bool
     */
    public $public;
    
    /**
     * Reference to the class containing this element.
     * @var \Peg\Definitions\Element\ClassElement
     */
    public $parent_class;
    
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
     * Create a global variable element using a declaration specification
     * for its type.
     * @param string $name Name of the variable.
     * @param string $type Parameter type by specification, eg: const int*
     */
    public function __construct($name, $type, $description="")
    {
        parent::__construct($type, $description);
        
        $this->name = $name;
    }

}

?>
