<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib\Definitions\Element;

/**
 * Represents a function or class method return type.
 */
class ReturnType extends VariableType
{
    
    /**
     * Reference to the overload owner.
     * @var \Peg\Lib\Definitions\Element\Overload
     */
    public $overload;

}

?>
