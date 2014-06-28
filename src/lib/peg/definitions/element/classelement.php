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
    
    /**
     * Flag that indicates if this class should be treated as a struct.
     * @var bool
     */
    public $struct;
    
    /**
     * Flag that indicates if this class should be treated as a forward
     * declaration.
     * @var bool
     */
    public $forward_declaration;
    
    /**
     * Flag that indicates if this class has parents.
     * @var bool
     */
    public $has_parent;
    
    /**
     * List of parents.
     * @var string[]
     */
    public $parents;
    
    /**
     * List of member variables.
     * @var \Peg\Definitions\Element\ClassVariable[]
     */
    public $variables;
    
    /**
     * List of enumerations.
     * @var \Peg\Definitions\Element\Enumeration[]
     */
    public $enumerations;
    
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
     * Reference to the namespace containing this element.
     * @var \Peg\Definitions\Element\NamespaceElement
     */
    public $namespace;
    
    /**
     * Creates a class element.
     * @param string $name
     */
    public function __construct($name, $description="")
    {
        $this->name = $name;
        $this->description = $description;
        
        $this->parents = array();
        $this->variables = array();
        $this->enumerations = array();
        $this->methods = array();
    }
    
    /**
     * Add a parent class.
     * @param string $parent Name of parent class which may also include its namespace.
     * @return \Peg\Definitions\Element\ClassElement
     */
    public function AddParent($parent)
    {
        $this->parents[$parent] = $parent;
        $this->has_parent = true;
        
        return $this;
    }
    
    /**
     * Adds an array of parent classes.
     * @param array $parents Name of parent classes which may also include its namespace.
     * @return \Peg\Definitions\Element\ClassElement
     */
    public function AddParents(array $parents)
    {
        foreach($parents as $parent)
            $this->AddParent($parent);
        
        return $this;
    }
    
    /**
     * Adds a variable to the class.
     * @param \Peg\Definitions\Element\ClassVariable $variable
     * @return \Peg\Definitions\Element\ClassElement
     */
    public function AddVariable(\Peg\Definitions\Element\ClassVariable $variable)
    {
        $variable->parent_class =& $this;
        $this->variables[$variable->name] = $variable;
        
        return $this;
    }
    
    /**
     * Adds an enumeration to the class.
     * @param \Peg\Definitions\Element\Enumeration $enumeration
     * @return \Peg\Definitions\Element\ClassElement
     */
    public function AddEnumeration(\Peg\Definitions\Element\Enumeration $enumeration)
    {
        $enumeration->parent_class =& $this;
        $this->enumerations[$enumeration->name] = $enumeration;
        
        return $this;
    }
    
    /**
     * Adds a new method/function to the class.
     * @param \Peg\Definitions\Element\FunctionElement $method
     * @return \Peg\Definitions\Element\ClassElement
     */
    public function AddMethod(\Peg\Definitions\Element\FunctionElement $method)
    {
        $method->parent_class =& $this;
        $this->methods[$method->name] = $method;
        
        return $this;
    }

}

?>
