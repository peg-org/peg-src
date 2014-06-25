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
class Parameter
{
    
    /**
     * Holds the name of the element
     * @var string
     */
    public $name;
    
    /**
     * Type of the parameter without modifiers (eg: int, double, char, etc...)
     * @var string
     */
    public $type;
    
    /**
     * The default value of the parameter.
     * @var string 
     */
    public $default_value;
    
    /**
     * Flag that indicates if the parameter is constant (const).
     * @var bool
     */
    public $is_const;
    
    /**
     * Flag that indicates if the parameter is a reference (&).
     * @var bool
     */
    public $is_reference;
    
    /**
     * Flag that indicates if the parameter is a reference (*).
     * @var bool
     */
    public $is_pointer;
    
    /**
     * Amount of pointers indirection (*).
     * @var int
     */
    public $indirection_level;
    
    /**
     * Flag that indicates if the parameter is an array ([]).
     * @var bool
     */
    public $is_array;
    
    /**
     * Description of the element.
     * @var string
     */
    public $description;
    
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
        $this->name = $name;
        
        $this->type = str_replace(
            array("const ", "&", "*"), 
            "", 
            $type
        );
        
        $this->default_value = $default_value;
        
        if(substr_count($type, "const ") > 0)
        {
            $this->is_const = true;
        }
        else
        {
            $this->is_const = false;
        }
        
        if(substr_count($type, "&") > 0)
        {
            $this->is_reference = true;
        }
        else
        {
            $this->is_reference = false;
        }
        
        if($indirection = substr_count($type, "*") > 0)
        {
            $this->is_pointer = true;
            $this->$indirection = $indirection;
        }
        else
        {
            $this->is_pointer = false;
            $this->indirection_level = 0;
        }
        
        if(substr_count($type, "[]") > 0)
        {
            $this->is_array = true;
        }
        else
        {
            $this->is_array = false;
        }
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
