<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Definitions\Element;

/**
 * Represents an enumeration declared independently on a header file or as
 * part of a class.
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
     * Initializes the enumeration element.
     * @param string $name
     * @param array $options
     * @param string $description
     */
    public function __construct($name, array $options, $description="")
    {
        $this->name = $name;
        
        $this->$options = $options;
        
        $this->description = $description;
    }

}

?>
