<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib\Generator;

/**
 * Base class to implement a source code generator.
 */
abstract class Base
{

    /**
     * Reference to the symbols object with all definitions required to generate the code.
     * @var \Peg\Lib\Definitions\Symbols 
     */
    public $symbols;
    
    /**
     * Path where template files reside.
     * @var string
     */
    public $templates_path;
    
    /**
     * Path where the generated source code is going to be saved.
     * @var string 
     */
    public $output_path;
    
    /**
     * The symbols object with all definitions required to generate the code.
     * @param string $templates Path where template files reside.
     * @param string $output Path where the generated source code is going to be saved.
     * @param \Peg\Lib\Definitions\Symbols $symbols
     */
    public function __construct(
        $templates,
        $output,
        \Peg\Lib\Definitions\Symbols &$symbols
    )
    {
        $this->symbols =& $symbols;
        $this->templates_path = $templates;
        $this->output_path = $output;
    }
    
    abstract public function Start();
}