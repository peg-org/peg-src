<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib\Definitions\Element;

/**
 * Represents a variable type.
 */
class VariableType
{
    
    /**
     * Type of the variable without modifiers (eg: int, double, char, etc...)
     * @var string
     */
    public $type;
    
    /**
     * Original type of the variable with all modifiers as passed on constructor.
     * @var string
     */
    public $original_type;
    
    /**
     * Flag that indicates if the parameter is constant (const).
     * @var bool
     */
    public $is_const;
    
    /**
     * Flag that indicates if the variable is unsigned.
     * @var bool
     */
    public $is_unsigned;
    
    /**
     * Flag that indicates if the variable is a reference (&).
     * @var bool
     */
    public $is_reference;
    
    /**
     * Flag that indicates if the variable is a pointer reference (*).
     * @var bool
     */
    public $is_pointer;
    
    /**
     * Amount of pointers indirection (*).
     * @var int
     */
    public $indirection_level;
    
    /**
     * Flag that indicates if the variable is an array ([]).
     * @var bool
     */
    public $is_array;
    
    /**
     * Description of the element.
     * @var string
     */
    public $description;
    
    
    /**
     * Create a variable type.
     * @param string $type Parameter type by specification, eg: const int*
     * @param string $description
     */
    public function __construct($type, $description="")
    {   
        $this->original_type = $type;
        
        $this->type = str_replace(
            array("const ", "&", "*"), 
            "", 
            $type
        );
        
        $this->description = $description;
        
        if(substr_count($type, "const ") > 0)
        {
            $this->is_const = true;
        }
        else
        {
            $this->is_const = false;
        }
        
        if(substr_count($type, "unsigned ") > 0)
        {
            $this->is_unsigned = true;
        }
        else
        {
            $this->is_unsigned = false;
        }
        
        if(substr_count($type, "&") > 0)
        {
            $this->is_reference = true;
        }
        else
        {
            $this->is_reference = false;
        }
        
        if(($indirection = substr_count($type, "*")) > 0)
        {
            $this->is_pointer = true;
            $this->indirection_level = $indirection;
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
     * Converts the type specifications to c/c++ code that can be 
     * added when generating code. 
     * eg: const int*
     * @return string
     */
    public function GetDeclarationCode()
    {
        $code = "";
        
        if($this->is_const)
            $code .= "const ";
        
        if($this->is_unsigned)
            $code .= "unsigned ";
        
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
        
        if($this->is_array)
            $code .= "[]";
    }

}