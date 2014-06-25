<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Definitions\Element;

/**
 * Represents an enumeration.
 */
class Enumeration
{

    /**
     * Holds the name of the enumeration.
     * @var string
     */
    public $name;

    /**
     * List of options.
     * @var array
     */
    public $options;
    
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
     * Initializes the enumeration element.
     * @param string $name
     * @param array $options
     */
    public function __construct($name, array $options)
    {
        $this->name = $name;
        
        $this->$options = $options;
    }

}

?>
