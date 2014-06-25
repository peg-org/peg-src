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
class ReturnType extends VariableType
{
    
    /**
     * Reference to the overload owner.
     * @var \Peg\Definitions\Element\Overload
     */
    public $overload;

}

?>
