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
     * Holds the name of the namespace.
     * @var string
     */
    public $name;
    
    /**
     * Flag that indicates if this is the global scope namespace (\).
     * @var bool
     */
    public $is_global;
    
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
     * @var \Peg\Definitions\Element\TypeDef[]
     */
    public $type_definitions;

    /**
     * Array of variables declared on the namespace.
     * @var \Peg\Definitions\Element\GlobalVariable[]
     */
    public $global_variables;
    
    /**
     * Array of functions declared on the namespace.
     * @var \Peg\Definitions\Element\FunctionElement[]
     */
    public $functions;

    /**
     * Array of classes declared on the namespace.
     * @var \Peg\Definitions\Element\ClassElement[]
     */
    public $classes;

    /**
     * Initializes a namespace element.
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
        
        $this->constants = array();

        $this->enumerations = array();

        $this->type_definitions = array();

        $this->global_variables = array();

        $this->functions = array();

        $this->classes = array();
    }
    
    /**
     * Check if the namespace has constants.
     * @return bool
     */
    public function HasConstants()
    {
        return count($this->constants) > 0;
    }
    
    /**
     * Check if the namespace has enumerations.
     * @return bool
     */
    public function HasEnumerations()
    {
        return count($this->enumerations) > 0;
    }
    
    /**
     * Check if the namespace has type definitions.
     * @return bool
     */
    public function HasTypeDefs()
    {
        return count($this->type_definitions) > 0;
    }
    
    /**
     * Check if the namespace has global variables.
     * @return bool
     */
    public function HasGlobalVariables()
    {
        return count($this->global_variables) > 0;
    }
    
    /**
     * Check if the namespace has functions.
     * @return bool
     */
    public function HasFunctions()
    {
        return count($this->functions) > 0;
    }
    
    /**
     * Check if the namespace has classes.
     * @return bool
     */
    public function HasClasses()
    {
        return count($this->classes) > 0;
    }

}

?>
