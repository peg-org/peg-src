<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib\Definitions\Element;

/**
 * Represents a function or class method element.
 */
class FunctionElement
{

    /**
     * Holds the name of the element
     * @var string
     */
    public $name;
    
    /**
     * Description of the element.
     * @var string
     */
    public $description;
    
    /**
     * List of overloads for this function/method
     * @var \Peg\Lib\Definitions\Element\Overload[]
     */
    public $overloads;
    
    /**
     * Reference to the class containing this element if applicable.
     * @var \Peg\Lib\Definitions\Element\ClassElement
     */
    public $parent_class;
    
    /**
     * Reference to the header containing this element.
     * @var \Peg\Lib\Definitions\Element\Header
     */
    public $header;
    
    /**
     * Reference to the namespace containing this element.
     * @var \Peg\Lib\Definitions\Element\NamespaceElement
     */
    public $namespace;
    
    /**
     * Creates a function element.
     * @param string $name
     * @param string $description
     */
    public function __construct($name, $description="")
    {
        $this->name = $name;
        $this->description = $description;
    }
    
    /**
     * Adds a new overload for the function.
     * @param \Peg\Lib\Definitions\Element\Overload $overload
     * @return \Peg\Lib\Definitions\Element\FunctionElement
     */
    public function AddOverload(\Peg\Lib\Definitions\Element\Overload $overload)
    {
        $overload->function =& $this;
        $this->overloads[] = $overload;
        
        return $this;
    }

}