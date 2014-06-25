<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Definitions\Element;

/**
 * Represents a class element.
 */
class ClassElement
{

    /**
     * Holds the name of the class
     * @var string
     */
    public $name;
    
    public $has_parent;
    
    public $parents;
    
    public $enumerations;
    
    public $variables;
    
    /**
     * List of methods.
     * @var \Peg\Definitions\Element\FunctionElement[]
     */
    public $methods;
    
    /**
     * Description of the element.
     * @var string
     */
    public $description;
    
    /**
     * Reference to the header containing this element.
     * @var \Peg\Definitions\Element\Header
     */
    public $header;
    
    /**
     * Reference to the header containing this element.
     * @var \Peg\Definitions\Element\NamespaceElement
     */
    public $namespace;

}

?>
