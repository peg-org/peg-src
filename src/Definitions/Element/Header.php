<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib\Definitions\Element;

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
     * List of namespaces declared on the file.
     * @var \Peg\Lib\Definitions\Element\NamespaceElement[]
     */
    public $namespaces;

    /**
     * Initializes a header element.
     * @param string $name
     * @param bool $enabled
     */
    public function __construct($name, $enabled=true)
    {
        $this->name = $name;
        
        $this->enabled = $enabled;

        $this->namespaces = array();
    }

    /**
     * Adds a new constant.
     * @param \Peg\Lib\Definitions\Element\Constant $constant
     * @param string $namespace
     */
    public function AddConstant(
        \Peg\Lib\Definitions\Element\Constant $constant, 
        $namespace = "\\"
    )
    {
        $this->CreateNamespace($namespace);
        
        $constant->header =& $this;
        $constant->namespace =& $this->namespaces[$namespace];

        $this->namespaces[$namespace]->constants[$constant->name] = $constant;
    }
    
    /**
     * Adds a new enumeration.
     * @param \Peg\Lib\Definitions\Element\Enumeration $enumeration
     * @param string $namespace
     */
    public function AddEnumeration(
        \Peg\Lib\Definitions\Element\Enumeration $enumeration, 
        $namespace = "\\"
    )
    {
        $this->CreateNamespace($namespace);
        
        $enumeration->header =& $this;
        $enumeration->namespace =& $this->namespaces[$namespace];

        $this->namespaces[$namespace]
            ->enumerations[$enumeration->name] = $enumeration
        ;
    }
    
    /**
     * Adds a new enumeration.
     * @param \Peg\Lib\Definitions\Element\Enumeration $typedef
     * @param string $namespace
     */
    public function AddTypeDef(
        \Peg\Lib\Definitions\Element\TypeDef $typedef, 
        $namespace = "\\"
    )
    {
        $this->CreateNamespace($namespace);
        
        $typedef->header =& $this;
        $typedef->namespace =& $this->namespaces[$namespace];

        $this->namespaces[$namespace]
            ->type_definitions[$typedef->name] = $typedef
        ;
    }
    
    /**
     * Adds a new global variable.
     * @param \Peg\Lib\Definitions\Element\GlobalVariable $global_variable
     * @param string $namespace
     */
    public function AddGlobalVariable(
        \Peg\Lib\Definitions\Element\GlobalVariable $global_variable, 
        $namespace = "\\"
    )
    {
        $this->CreateNamespace($namespace);
        
        $global_variable->header =& $this;
        $global_variable->namespace =& $this->namespaces[$namespace];

        $this->namespaces[$namespace]
            ->global_variables[$global_variable->name] = $global_variable
        ;
    }
    
    /**
     * Adds a new function.
     * @param \Peg\Lib\Definitions\Element\FunctionElement $function
     * @param string $namespace
     */
    public function AddFunction(
        \Peg\Lib\Definitions\Element\FunctionElement $function, 
        $namespace = "\\"
    )
    {
        $this->CreateNamespace($namespace);
        
        $function->header =& $this;
        $function->namespace =& $this->namespaces[$namespace];

        $this->namespaces[$namespace]
            ->functions[$function->name] = $function
        ;
    }
    
    /**
     * Adds a new class.
     * @param \Peg\Lib\Definitions\Element\ClassElement $class
     * @param string $namespace
     */
    public function AddClass(
        \Peg\Lib\Definitions\Element\ClassElement $class, 
        $namespace = "\\"
    )
    {
        $this->CreateNamespace($namespace);
        
        $class->header =& $this;
        $class->namespace =& $this->namespaces[$namespace];

        $this->namespaces[$namespace]
            ->classes[$class->name] = $class
        ;
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
    
    /**
     * Check if the header has constants.
     * @return bool
     */
    public function HasConstants()
    {
        foreach($this->namespaces as $namespace)
        {
            if($namespace->HasConstants())
                return true;
        }
        
        return false;
    }
    
    /**
     * Check if the header has enumerations.
     * @return bool
     */
    public function HasEnumerations()
    {
        foreach($this->namespaces as $namespace)
        {
            if($namespace->HasEnumerations())
                return true;
        }
        
        return false;
    }
    
    /**
     * Check if the header has type definitions.
     * @return bool
     */
    public function HasTypeDefs()
    {
        foreach($this->namespaces as $namespace)
        {
            if($namespace->HasTypeDefs())
                return true;
        }
        
        return false;
    }
    
    /**
     * Check if the header has global variables.
     * @return bool
     */
    public function HasGlobalVariables()
    {
        foreach($this->namespaces as $namespace)
        {
            if($namespace->HasGlobalVariables())
                return true;
        }
        
        return false;
    }
    
    /**
     * Check if the header has functions.
     * @return bool
     */
    public function HasFunctions()
    {
        foreach($this->namespaces as $namespace)
        {
            if($namespace->HasFunctions())
                return true;
        }
        
        return false;
    }
    
    /**
     * Check if the header has classes.
     * @return bool
     */
    public function HasClasses()
    {
        foreach($this->namespaces as $namespace)
        {
            if($namespace->HasClasses())
                return true;
        }
        
        return false;
    }

}