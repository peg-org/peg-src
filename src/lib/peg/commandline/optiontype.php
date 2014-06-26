<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\CommandLine;

/**
 * Enumeration used to declare a \Peg\CommandLine\Option type
 */
class OptionType
{

    /**
     * Accepts any type of string.
     */
    const STRING = 1;

    /**
     * Only accept numbers.
     */
    const INTEGER = 2;

    /**
     * Doesn't needs a value, just to be present.
     */
    const FLAG = 3;

}

?>
