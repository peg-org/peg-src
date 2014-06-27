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
     * Description of the element.
     * @var string
     */
    public $description;
    
    /**
     * Reference to the parent function element.
     * @var \Peg\Definitions\Element\FunctionElement
     */
    public $function;
    
    /**
     * Helper function to set the overload return type.
     * @param \Peg\Definitions\Element\ReturnType $return_type
     * @return \Peg\Definitions\Element\Overload
     */
    public function SetReturnType(\Peg\Definitions\Element\ReturnType $return_type)
    {
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
        if(!isset($this->parameters[$parameter->name]))
        {
            $this->parameters[$parameter->name] = $parameter;
        }
        else
        {
            throw new Exception(
                t("You are trying to add a parameter which is already listed")
            );
        }
        
        return $this;
    }

}

?>
