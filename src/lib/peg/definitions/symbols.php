<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Definitions;

/**
 * Represents a symbols table with all the definitions required to generate
 * a PHP extension.
 */
class Symbols
{

    /**
     * List of header files (#include)
     * @var \Peg\Definitions\Element\Header[]
     */
    public $headers;
    
    /**
     * Default constructor.
     */
    public function __construct()
    {
        $this->headers = array();
    }
    
    /**
     * Removes all the symbols stored on the container.
     */
    public function Clear()
    {
        unset($this->headers);
        
        $this->headers = array();
    }

    /**
     * Add a header file.
     * @param string $name  Name of header file.
     * @param bool $enabled Flag to inidicate if the header should be included
     * as part of the code generation.
     */
    public function AddHeader($name, $enabled=true)
    {
        if(!isset($this->headers[$name]))
        {
            $header = new Element\Header($name, $enabled);

            $this->headers[$name] = $header;
        }
    }

    /**
     * Adds a constant to the symbols table.
     * @param \Peg\Definitions\Element\Constant $constant
     * @param string $header Name of header file where the constant resides.
     * @param string $namespace If omitted the constant is added at a global scope.
     */
    public function AddConstant(
        \Peg\Definitions\Element\Constant $constant,
        $header,
        $namespace="\\"
    )
    {
        $this->AddHeader($header);

        $this->headers[$header]->AddConstant($constant, $namespace);
    }

    /**
     * Adds an enumeration to the symbols table.
     * @param \Peg\Definitions\Element\Enumeration $enumeration
     * @param string $header Name of header file where the constant resides.
     * @param string $namespace If omitted the constant is added at a global scope.
     */
    public function AddEnumeration(
        \Peg\Definitions\Element\Enumeration $enumeration,
        $header,
        $namespace="\\"
    )
    {
        $this->AddHeader($header);

        $this->headers[$header]->AddEnumeration($enumeration, $namespace);
    }

    /**
     * Adds a type definition to the symbols table.
     * @param \Peg\Definitions\Element\TypeDef $typedef
     * @param string $header Name of header file where the constant resides.
     * @param string $namespace If omitted the constant is added at a global scope.
     */
    public function AddTypeDef(
        \Peg\Definitions\Element\TypeDef $typedef, 
        $header, 
        $namespace="\\"
    )
    {
        $this->AddHeader($header);

        $this->headers[$header]->AddTypeDef($typedef, $namespace);
    }

    /**
     * Adds a global variable to the symbols table.
     * @param \Peg\Definitions\Element\GlobalVariable $global_variable
     * @param string $header Name of header file where the constant resides.
     * @param string $namespace If omitted the constant is added at a global scope.
     */
    public function AddGlobalVar(
        \Peg\Definitions\Element\GlobalVariable $global_variable, 
        $header, 
        $namespace="\\"
    )
    {
        $this->AddHeader($header);

        $this->headers[$header]->AddGlobalVariable($global_variable, $namespace);
    }
    
    /**
     * Adds a function to the symbols table.
     * @param \Peg\Definitions\Element\FunctionElement $function
     * @param string $header Name of header file where the constant resides.
     * @param string $namespace If omitted the constant is added at a global scope.
     */
    public function AddFunction(
        \Peg\Definitions\Element\FunctionElement $function, 
        $header, 
        $namespace="\\"
    )
    {
        $this->AddHeader($header);

        $this->headers[$header]->AddFunction($function, $namespace);
    }

}

?>
