<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib\CommandLine;

/**
 * Class that represents an action executed when a specific command is called.
 */
abstract class Action
{

    /**
     * Method called by the command if it was executed.
     */
    abstract public function OnCall(Command $command);
}