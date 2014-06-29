<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
*/

namespace Peg\Plugins;

/**
 * Base interface to write plugins. All plugins should inherit from this class.
 */
class Base
{

    /**
     * Class name of the plugin.
     * This variable is set by the plugin loader.
     * @var string
     */
    public $name;

    /**
     * Path to directory where the plugin resides.
     * This variable is set by the plugin loader.
     * @var string
     */
    public $path;

    /**
     * Called when the system is ready to initialize the plugin.
     * On this method you can listen for signals, etc...
     */
    public function OnInit(){}

}