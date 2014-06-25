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
     * Holds the name of the element
     * @var \Peg\Definitions\Element\Parameter[]
     */
    public $parameters;
    
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
     * Reference to the function.
     * @var \Peg\Definitions\Element\FunctionElement
     */
    public $function;

}

?>
