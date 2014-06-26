<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Definitions\Element;

/**
 * Represents a constant defined on some include file.
 */
class Constant
{

    /**
     * Holds the name of the constant
     * @var string
     */
    public $name;

    /**
     * Value of the constant
     * @var string
     */
    public $value;
    
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
    
    /**
     * Create a constant element.
     * @param string $name
     * @param string $value
     * @param string $description
     */
    public function __construct($name, $value, $description="")
    {
        $this->name = $name;
        $this->value = $value;
        $this->description = $description;
    }

}

?>
