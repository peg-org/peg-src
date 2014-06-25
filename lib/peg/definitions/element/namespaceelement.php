<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Definitions\Element;

/**
 * Represents a namespace.
 */
class NamespaceElement
{

    /**
     * Holds the name of the namespace
     * @var string
     */
    public $name;
    
    /**
     * Flag that indicates if this is the global scope namespace ::
     * @var bool
     */
    public $is_global;

    /**
     * Array of functions declared on the namespace.
     * @var \Peg\Definitions\Element\Function[]
     */
    public $functions;

    /**
     * Array of variables declared on the namespace.
     * @var \Peg\Definitions\Element\Variable[]
     */
    public $variables;

    /**
     * Array of classes declared on the namespace.
     * @var \Peg\Definitions\Element\ClassElement[]
     */
    public $classes;

    /**
     * Array of constants declared on the namespace.
     * @var \Peg\Definitions\Element\Constant[]
     */
    public $constants;

    /**
     * Array of enumerations declared on the namespace.
     * @var \Peg\Definitions\Element\Enumeration[]
     */
    public $enumerations;

    /**
     * Array of type definitions declared on the file.
     * @var \Peg\Definitions\Element\TypeDefinition[]
     */
    public $type_definitions;

    /**
     * Initializes a namespace element
     * @param string $name
     */
    public function __construct($name)
    {
        if($name == "")
            $name = "\\";
        
        $this->name = $name;
        
        if($name == "\\")
        {
            $this->is_global = true;
        }
        else
        {
            $this->is_global = false;
        }
    }

}

?>
