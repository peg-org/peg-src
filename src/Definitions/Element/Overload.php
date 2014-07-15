<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib\Definitions\Element;

/**
 * Represents a function or class method overload element.
 */
class Overload
{

    /**
     * Description of the element.
     * @var string
     */
    public $description;
    
    /**
     * List of platforms where the element is supported.
     * @var array 
     */
    public $platforms;
    
    /**
     * The return type of the overload.
     * @var \Peg\Lib\Definitions\Element\ReturnType
     */
    public $return_type;
    
    /**
     * Holds the list of parameters for the overload.
     * @var \Peg\Lib\Definitions\Element\Parameter[]
     */
    public $parameters;
    
    /**
     * Flag that indicate if the method is protected.
     * @var bool
     */
    public $protected;
    
    /**
     * Flag that indicate if the method is const.
     * @var bool
     */
    public $constant;
    
    /**
     * Flag that indicate if the method is static.
     * @var bool
     */
    public $static;
    
    /**
     * Flag that indicate if the method is virtual.
     * @var bool
     */
    public $virtual;
    
    /**
     * Flag that indicate if the method is pure virtual.
     * @var bool
     */
    public $pure_virtual;
    
    /**
     * Flag that indicate if the method/function is deprecated.
     * @var bool
     */
    public $deprecated;
    
    /**
     * Reference to the parent function element.
     * @var \Peg\Lib\Definitions\Element\FunctionElement
     */
    public $function;
    
    /**
     * Create a function or method overload.
     * @param string $description
     */
    public function __construct($description="")
    {
        $this->description = $description;
        
        $this->parameters = array();
        $this->platforms = array();
    }
    
    /**
     * Helper function to set the overload return type.
     * @param \Peg\Lib\Definitions\Element\ReturnType $return_type
     * @return \Peg\Lib\Definitions\Element\Overload
     */
    public function SetReturnType(\Peg\Lib\Definitions\Element\ReturnType $return_type)
    {
        $return_type->overload =& $this;
        
        $this->return_type = $return_type;
        
        return $this;
    }
    
    /**
     * Adds a new parameter.
     * @param \Peg\Lib\Definitions\Element\Parameter $parameter
     * @return \Peg\Lib\Definitions\Element\Overload
     */
    public function AddParameter(\Peg\Lib\Definitions\Element\Parameter $parameter)
    {
        $parameter->overload =& $this;
        
        if(!isset($this->parameters[$parameter->name]))
        {
            $this->parameters[$parameter->name] = $parameter;
        }
        else
        {
            /*throw new \Exception(
                sprintf(
                    t("You are trying to add a parameter which is already listed: %s"),
                    $parameter->name
                )
            );*/
        }
        
        return $this;
    }
    
    /**
     * Check if the overload has parameters.
     * @return bool
     */
    public function HasParameters()
    {
        return count($this->parameters) > 0 ? true : false;
    }
    
    public function GetParametersCount()
    {
        return count($this->parameters);
    }
    
    public function GetRequiredParametersCount()
    {
        $required = 0;
        
        foreach($this->parameters as $parameter)
        {
            if($parameter->default_value)
                $required++;
        }
        
        return $required;
    }

}
