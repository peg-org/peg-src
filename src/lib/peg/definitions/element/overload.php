<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Definitions\Element;

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
     * The return type of the overload.
     * @var \Peg\Definitions\Element\ReturnType
     */
    public $return_type;
    
    /**
     * Holds the list of parameters for the overload.
     * @var \Peg\Definitions\Element\Parameter[]
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
    public $is_deprecated;
    
    /**
     * Reference to the parent function element.
     * @var \Peg\Definitions\Element\FunctionElement
     */
    public $function;
    
    /**
     * Create a function or method overload.
     * @param string $description
     */
    public function __construct($description="")
    {
        $this->description = $description;
    }
    
    /**
     * Helper function to set the overload return type.
     * @param \Peg\Definitions\Element\ReturnType $return_type
     * @return \Peg\Definitions\Element\Overload
     */
    public function SetReturnType(\Peg\Definitions\Element\ReturnType $return_type)
    {
        $return_type->overload =& $this;
        
        $this->return_type = $return_type;
        
        return $this;
    }
    
    /**
     * Adds a new parameter.
     * @param \Peg\Definitions\Element\Parameter $parameter
     * @return \Peg\Definitions\Element\Overload
     */
    public function AddParameter(\Peg\Definitions\Element\Parameter $parameter)
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

}

?>
