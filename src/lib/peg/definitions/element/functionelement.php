<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Definitions\Element;

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
     * List of overloads for this function/method
     * @var \Peg\Definitions\Element\Overload[]
     */
    public $overloads;
    
    /**
     * Description of the element.
     * @var string
     */
    public $description;
    
    /**
     * Reference to the class containing this element if applicable.
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
     * Creates a function element.
     * @param string $name
     * @param string $description
     */
    public function __construct($name, $description="")
    {
        $this->name = $name;
        $this->description = $description;
    }
    
    public function AddOverload(\Peg\Definitions\Element\Overload $overload)
    {
        $overload->function =& $this;
        $this->overloads[] = $overload;
    }

}

?>
