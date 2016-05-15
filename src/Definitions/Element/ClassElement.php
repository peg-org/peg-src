<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib\Definitions\Element;

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
     * List of platforms where the element is supported.
     * @var array 
     */
    public $platforms;
    
    /**
     * Flag that indicates if this is a generic class, eg: class_name<T>.
     * @var bool
     */
    public $generic;
    
    /**
     * The generic expression that goes after class name, eg: <T>.
     * @var string
     */
    public $generic_expression;
    
    /**
     * List of arguments on the generic expression.
     * @var array
     */
    public $generic_arguments;
    
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
     * @var \Peg\Lib\Definitions\Element\ClassVariable[]
     */
    public $variables;
    
    /**
     * List of enumerations.
     * @var \Peg\Lib\Definitions\Element\Enumeration[]
     */
    public $enumerations;
    
    /**
     * List of methods.
     * @var \Peg\Lib\Definitions\Element\FunctionElement[]
     */
    public $methods;
    
    /**
     * Description of the element.
     * @var string
     */
    public $description;
    
    /**
     * Reference to the header containing this element.
     * @var \Peg\Lib\Definitions\Element\Header
     */
    public $header;
    
    /**
     * Reference to the namespace containing this element.
     * @var \Peg\Lib\Definitions\Element\NamespaceElement
     */
    public $namespace;
    
    /**
     * Creates a class element.
     * @param string $name
     * @param string $description
     */
    public function __construct($name, $description="")
    {
        if(strstr($name, "<"))
        {
            $this->generic = true;
            $name_parts = explode("<", $name);
            $name = trim($name_parts[0]);
            
            $this->generic_expression = "<" . trim($name_parts[1], " >") . ">";
            $this->generic_arguments = explode(",", trim($name_parts[1], " >"));
            
            array_walk($this->generic_arguments, 'trim');
        }
        else
        {
            $this->generic = false;
        }
        
        $this->name = $name;
        $this->description = $description;
        
        $this->parents = array();
        $this->variables = array();
        $this->enumerations = array();
        $this->methods = array();
        $this->platforms = array();
    }
    
    /**
     * Add a parent class.
     * @param string $parent Name of parent class which may also include its namespace.
     * @return \Peg\Lib\Definitions\Element\ClassElement
     */
    public function AddParent($parent)
    {
        $parent = str_replace("::", "\\", $parent);
        
        $this->parents[$parent] = $parent;
        $this->has_parent = true;
        
        return $this;
    }
    
    /**
     * Adds an array of parent classes.
     * @param array $parents Name of parent classes which may also include its namespace.
     * @return \Peg\Lib\Definitions\Element\ClassElement
     */
    public function AddParents(array $parents)
    {
        foreach($parents as $parent)
            $this->AddParent($parent);
        
        return $this;
    }
    
    /**
     * Adds a variable to the class.
     * @param \Peg\Lib\Definitions\Element\ClassVariable $variable
     * @return \Peg\Lib\Definitions\Element\ClassElement
     */
    public function AddVariable(\Peg\Lib\Definitions\Element\ClassVariable $variable)
    {
        $variable->parent_class =& $this;
        $this->variables[$variable->name] = $variable;
        
        return $this;
    }
    
    /**
     * Adds an enumeration to the class.
     * @param \Peg\Lib\Definitions\Element\Enumeration $enumeration
     * @return \Peg\Lib\Definitions\Element\ClassElement
     */
    public function AddEnumeration(\Peg\Lib\Definitions\Element\Enumeration $enumeration)
    {
        $enumeration->parent_class =& $this;
        $this->enumerations[$enumeration->name] = $enumeration;
        
        return $this;
    }
    
    /**
     * Adds a new method/function to the class.
     * @param \Peg\Lib\Definitions\Element\FunctionElement $method
     * @return \Peg\Lib\Definitions\Element\ClassElement
     */
    public function AddMethod(\Peg\Lib\Definitions\Element\FunctionElement $method)
    {
        $method->parent_class =& $this;
        $this->methods[$method->name] = $method;
        
        return $this;
    }
    
    /**
     * Check of the class as any properties.
     * @return bool
     */
    public function HasProperties()
    {
        if(count($this->variables) > 0)
            return true;
        
        return false;
    }

}