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
     * Adds a new constant
     * @param string $namespace
     * @param \Peg\Definitions\Element\Constant $constant
     */
    public function AddConstant($namespace, \Peg\Definitions\Element\Constant $constant)
    {
        $this->CreateNamespace($namespace);

        $this->namespaces[$namespace]->constants[$constant->name] = $constant;
    }

    /**
     * Adds a namespace to the namespaces array if not already listed.
     * @param string $name
     */
    private function CreateNamespace($name)
    {
        if($name == "")
            $name = "\\";

        if(!isset($this->namespaces[$name]))
            $this->namespaces[$name] = new NamespaceElement($name);
    }

}

?>
