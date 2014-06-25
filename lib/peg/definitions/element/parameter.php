<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Definitions\Element;

/**
 * Represents a function or class method parameter.
 */
class Parameter extends VariableType
{
    
    /**
     * Holds the name of the element
     * @var string
     */
    public $name;
    
    /**
     * The default value of the parameter.
     * @var string 
     */
    public $default_value;
    
    /**
     * Reference to the overload owner.
     * @var \Peg\Definitions\Element\Overload
     */
    public $overload;
    
    /**
     * Create a parameter element from a declaration specification, 
     * 
     * @param string $name Name of the parameter.
     * @param string $type Parameter type by specification, eg: const int*
     * @param string $default_value Default value of the parameter.
     */
    public function __construct($name, $type, $default_value="")
    {
        parent::__construct($type);
        
        $this->name = $name;
        
        $this->default_value = $default_value;
    }
    
    /**
     * Converts the parameter specifications to c/c++ code that can be 
     * added when generating a function/method declaration, 
     * eg: const int* num = 0
     * @return string
     */
    public function GetDeclarationCode()
    {
        $code = "";
        
        if($this->is_const)
            $code .= "const ";
        
        $code .= $this->type;
        
        if($this->is_reference)
            $code .= "&";
        
        if($this->is_pointer)
        {
            for($i=0; $i<$this->indirection_level; $i++)
            {
                $code .= "*";
            }
        }
        
        $code .= " " . $this->name;
        
        if($this->is_array)
            $code .= "[]";
        
        if(trim($this->default_value) != "")
            $code .= " = " . $this->default_value;
    }

}

?>
