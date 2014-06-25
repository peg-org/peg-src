<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Definitions\Element;

/**
 * Represents an include file with all its elements.
 */
class Header
{

    /**
     * Holds the name of the file
     * @var string
     */
    public $name;

    /**
     * Flag to exclude/include this file when generating the source code.
     * @var bool
     */
    public $enabled;

    /**
     * Array of constants declared on the file.
     * @var \Peg\Definitions\Element\NamespaceElement[]
     */
    public $namespaces;

    /**
     * Initializes a file element
     * @param string $name
     */
    public function __construct($name, $enabled=true)
    {
        $this->name = $name;
        
        $this->enabled = $enabled;

        $this->namespaces = array();
    }

    /**
     * Adds a new constant.
     * @param \Peg\Definitions\Element\Constant $constant
     * @param string $namespace
     */
    public function AddConstant(\Peg\Definitions\Element\Constant $constant, $namespace = "\\")
    {
        $this->CreateNamespace($namespace);
        
        $constant->header =& $this;
        $constant->namespace =& $this->namespaces[$namespace];

        $this->namespaces[$namespace]->constants[$constant->name] = $constant;
    }
    
    /**
     * Adds a new enumeration.
     * @param \Peg\Definitions\Element\Enumeration $enumeration
     * @param string $namespace
     */
    public function AddEnumeration(\Peg\Definitions\Element\Enumeration $enumeration, $namespace = "\\")
    {
        $this->CreateNamespace($namespace);
        
        $enumeration->header =& $this;
        $enumeration->namespace =& $this->namespaces[$namespace];

        $this->namespaces[$namespace]->enumerations[$enumeration->name] = $enumeration;
    }
    
    /**
     * Adds a new enumeration.
     * @param \Peg\Definitions\Element\Enumeration $typedef
     * @param string $namespace
     */
    public function AddTypeDef(\Peg\Definitions\Element\TypeDef $typedef, $namespace = "\\")
    {
        $this->CreateNamespace($namespace);
        
        $typedef->header =& $this;
        $typedef->namespace =& $this->namespaces[$namespace];

        $this->namespaces[$namespace]->type_definitions[$typedef->name] = $typedef;
    }

    /**
     * Adds a namespace to the namespaces array if not already listed.
     * @param string $name
     */
    private function CreateNamespace(&$name)
    {
        if($name == "")
            $name = "\\";

        if(!isset($this->namespaces[$name]))
            $this->namespaces[$name] = new NamespaceElement($name);
    }

}

?>
