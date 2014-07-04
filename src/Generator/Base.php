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
        $this->templates_path = rtrim($templates, "\\/") . "/";
        $this->output_path = rtrim($output, "\\/") . "/";
    }
    
    /**
     * Useful to indent the output of a template.
     * @param string $code Output code of template.
     * @param int $spaces Amount of spaces to indent the code.
     * @return string Code indented.
     */
    public function Indent($code, $spaces)
    {
        $indent = "";
        
        for($i=0; $i<$spaces; $i++)
        {
            $indent .= " ";
        }
        
        $code = str_replace("\n", "\n$indent", $code);
        
        return $code;
    }
    
    /**
     * Converts a header file name into a suitable flag for the compiler 
     * pre-processor. Eg: header.h -> PHP_HEADER_H this can be used for
     * #ifndef checks on the generated code to prevent double inclusions.
     * @param string $name
     */
    public function GetHeaderDefine($name)
    {
        return "PHP_" . strtoupper(
            str_replace(
                array("/", ".", "-"), 
                "_", 
                $name
            )
        );
    }
    
    /**
     * Converts a header filename into one that doesn't conflicts with
     * original which can be used to store the generated code. Eg:
     * header.h -> php_header.h or lib/header.h -> php_lib_header.h
     * @param string $name
     */
    public function GetHeaderNamePHP($name)
    {
        return "php_" . strtolower(
            str_replace(
                array("/", "-"), 
                "_", 
                $name
            )
        );
    }
    
    /**
     * Converts a header filename into a source file name which can be used 
     * to store the generated code. 
     * Eg: header.h -> php_header.cpp or lib/header.h -> php_lib_header.cpp
     * @param string $name
     */
    public function GetSourceNamePHP($name)
    {
        return str_replace(
            ".h", 
            ".cpp", 
            $this->GetHeaderNamePHP($name)
        );
    }
    
    /**
     * Converts a header file into a valid function name that should be
     * used when generating the function which registers constants. 
     * Eg: header.h -> php_header_h_constants
     * @param string $name Name of the header file.
     */
    public function GetConstantsFunctionName($name)
    {
        return strtolower(
            $this->GetHeaderDefine($name)
        ) . "_constants";
    }
    
    /**
     * Generate all the sources.
     */
    abstract public function Start();
    
    /**
     * Generate the header source of a specific header file.
     * @param string $header_name
     */
    abstract public function GenerateHeader($header_name);
}