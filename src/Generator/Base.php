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
     * Reference to the symbols object with all definitions required 
     * to generate the code.
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
     * Name of the generator implementing this base class. Useful to determine
     * the directory where generated source code is going to be stored.
     * @var string
     */
    public $generator_name;
    
    /**
     * The symbols object with all definitions required to generate the code.
     * @param string $templates Path where template files reside.
     * @param string $output Path where the generated source code is going to be saved.
     * @param string $generator_name Name of the generator being instantiated.
     * Should be all lower case.
     * @param \Peg\Lib\Definitions\Symbols $symbols
     */
    public function __construct(
        $templates,
        $output,
        $generator_name,
        \Peg\Lib\Definitions\Symbols &$symbols
    )
    {
        $this->symbols =& $symbols;
        $this->templates_path = rtrim($templates, "\\/") . "/";
        $this->output_path = rtrim($output, "\\/") . "/";
        $this->generator_name = $generator_name;
        
        // Strip generator name if passed by user since it should be
        // appended automatically by this constructor.
        $this->output_path = str_replace(
            "/$generator_name/", 
            "/", 
            $this->output_path
        ) . $generator_name . "/";
        
        $this->templates_path = str_replace(
            "/$generator_name/", 
            "/", 
            $this->templates_path
        ) . $generator_name . "/";
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
        
        if(substr_count($code, "\n") <= 1)
            return $indent . $code;
        
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
     * Gets a list of custom source files on the custom_sources directory
     * that resides in the templates/$generator_name directory with the .php
     * extension stripped out.
     */
    public function GetCustomSources()
    {
        $sources = scandir($this->templates_path . "custom_sources");
        
        foreach($sources as $index=>$source)
        {
            // Skip non .php files
            if(strpos($source, ".php") === false)
            {
                unset($sources[$index]);
                continue;
            }
            
            $sources[$index] = str_replace(".php", "", $source);
        }
        
        return $sources;
    }
    
    /**
     * Get a php template file path based on the given parameters.
     * @param string $name Name of element.
     * @param string $type Main type of template.
     * @param string $subtype Subtype of template.
     * @param string $dir Relative dir to templates path where template resides.
     * @param string $overrides_prefix In case $dir is shared with other template types.
     * @param string $namespace Namespace of the $name element.
     * @return string
     */
    public function GetTemplatePath(
        $name, $type, $subtype, $dir, $overrides_prefix="", $namespace=""
    )
    {   
        if($namespace)
        {
            $namespace = strtolower(
                str_replace(
                    array("\\", "::"),
                    "_",
                    $namespace
                )
            ) . "_";
        }
        
        if($overrides_prefix)
        {
            $overrides_prefix = rtrim($overrides_prefix, "_") . "_";
        }

        $override = $this->templates_path
            . "{$dir}/{$overrides_prefix}overrides/"
            . "{$subtype}_" . $namespace . strtolower(
                str_replace(
                    array("/", "-", "."),
                    "_",
                    $name
                )
            )
            . ".php"
        ;

        if(file_exists($override))
        {
            return $override;
        }
        
        if($type)
            $type .= "_";

        return $this->templates_path
            . "{$dir}/"
            . "{$type}{$subtype}.php"
        ;
    }
    
    /**
     * Get a php template file for a given file name.
     * @param string $name Filename of source file, eg: php_extension.h, config.m4
     * @param string $subdir Relative path to the templates_path where the file resides.
     * @return string Path to template file.
     */
    public function GetGenericTemplate($name, $subdir="")
    {
        if($subdir)
            $subdir = trim($subdir, "\\/") . "/";
        
        return $this->templates_path
            . $subdir
            . "{$name}.php"
        ;
    }
    
    /**
     * Retrieve the template path for parameters, also checks
     * if a valid override exists and returns that instead.
     * @param \Peg\Lib\Definitions\Element\Parameter $parameter Name of the function.
     * @param string $namespace
     * @param string $type Can be declare, parse_string, parse, 
     * parse_string_ref, parse_reference or object_validate.
     * @return string Path to template file.
     */
    public function GetParameterTemplate(
        \Peg\Lib\Definitions\Element\Parameter $parameter,
        $namespace="",
        $type="declare"
    )
    {
        if(!$this->generator_name)
        {
            throw new \Exception(t("The generator name wasn't set."));
        }
        
        if($namespace)
        {
            $namespace = str_replace(
                array("\\", "::"),
                "_",
                $namespace
            ) . "_";
        }

        $function_name = strtolower($parameter->overload->function->name);

        $parameter_type = strtolower($parameter->type);

        $const = "";
        if($parameter->is_const)
        {
            $const .= "_const";
        }

        $ptr = "";
        if($parameter->is_pointer)
        {
            for($i=0; $i<$parameter->indirection_level; $i++)
            {
                $ptr .= "_ptr";
            }
        }

        $ref = "";
        if($parameter->is_reference)
        {
            $ref .= "_ref";
        }

        $array = "";
        if($parameter->is_array)
        {
            $array .= "_arr";
        }

        $override_function = $this->templates_path
            . "parameters/{$type}/overrides/"
            . $function_name . "_" . $parameter_type
            . $const
            . $ptr
            . $ref
            . $array
            . ".php"
        ;

        if(file_exists($override_function))
        {
            return $override_function;
        }

        $override = $this->templates_path
            . "parameters/{$type}/overrides/"
            . $parameter_type
            . $const
            . $ptr
            . $ref
            . $array
            . ".php"
        ;

        if(file_exists($override))
        {
            return $override;
        }

        $standard_type = $this->symbols->GetStandardType($parameter);

        $template = $this->templates_path
            . "parameters/{$type}/"
            . $standard_type
            . $const
            . $ptr
            . $ref
            . $array
            . ".php"
        ;

        if(!file_exists($template))
        {    
            return $this->templates_path
                . "parameters/{$type}/"
                . "default.php"
            ;
        }

        return $template;
    }
    
    /**
     * Retrieve the template path for return, also checks
     * if a valid override exists and returns that instead.
     * @param \Peg\Lib\Definitions\Element\ReturnType $return
     * @param string $namespace
     * @param string $type Can be function, method or static_method.
     * @return string Path to template file.
     */
    public function GetReturnTemplate(
        \Peg\Lib\Definitions\Element\ReturnType $return,
        $namespace="",
        $type="function"
    )
    {
        if(!$this->generator_name)
        {
            throw new \Exception(t("The generator name wasn't set."));
        }
        
        if($namespace)
        {
            $namespace = str_replace(
                array("\\", "::"),
                "_",
                $namespace
            ) . "_";
        }

        $function_name = strtolower($return->overload->function->name);

        $return_type = strtolower($return->type);

        $const = "";
        if($return->is_const)
        {
            $const .= "_const";
        }

        $ptr = "";
        if($return->is_pointer)
        {
            for($i=0; $i<$return->indirection_level; $i++)
            {
                $ptr .= "_ptr";
            }
        }

        $ref = "";
        if($return->is_reference)
        {
            $ref .= "_ref";
        }

        $array = "";
        if($return->is_array)
        {
            $array .= "_arr";
        }

        $override_function = $this->templates_path
            . "return/{$type}/overrides/"
            . $function_name . "_" . $return_type
            . $const
            . $ptr
            . $ref
            . $array
            . ".php"
        ;

        if(file_exists($override_function))
        {
            return $override_function;
        }

        $override = $this->templates_path
            . "return/{$type}/overrides/"
            . $return_type
            . $const
            . $ptr
            . $ref
            . $array
            . ".php"
        ;

        if(file_exists($override))
        {
            return $override;
        }

        $standard_type = $this->symbols->GetStandardType($return);

        $template = $this->templates_path
            . "return/{$type}/"
            . $standard_type
            . $const
            . $ptr
            . $ref
            . $array
            . ".php"
        ;

        if(!file_exists($template))
        {
            return $this->templates_path
                . "return/{$type}/"
                . "default.php"
            ;
        }

        return $template;
    }
    
    /**
     * Deletes a generated header declarations file and its source file
     * @param string $header_name Original name of header.
     */
    public function RemoveHeader($header_name)
    {
        $header = $this->output_path 
            . "includes/" 
            . $this->GetHeaderNamePHP($header_name)
        ;
        
        $source = $this->output_path 
            . "src/" 
            . $this->GetSourceNamePHP($header_name)
        ;
        
        if(file_exists($header))
            unlink($header);
        
        if(file_exists($source))
            unlink($source);
    }
    
    /**
     * Adds or updates a header file if neccessary.
     * @param string $header_name Original name of header.
     * @param string $content
     * @param string $subdir
     */
    public function AddHeader($header_name, &$content, $subdir="includes")
    {
        if($subdir)
        {
            $subdir = trim($subdir, "\\/") . "/";
            
            if(!file_exists($this->output_path . $subdir))
                \Peg\Lib\Utilities\FileSystem::MakeDir(
                    $this->output_path . $subdir, 
                    0755, 
                    true
                );
        }
        
        $header = $this->output_path 
            . $subdir
            . $this->GetHeaderNamePHP($header_name)
        ;
        
        \Peg\Lib\Utilities\FileSystem::WriteFileIfDifferent($header, $content);
    }
    
    /**
     * Adds or updates a header source file if neccessary.
     * @param string $header_name Original name of header.
     * @param string $content
     * @param string $subdir
     */
    public function AddSource($header_name, &$content, $subdir="src")
    {
        if($subdir)
        {
            $subdir = trim($subdir, "\\/") . "/";
            
            if(!file_exists($this->output_path . $subdir))
                \Peg\Lib\Utilities\FileSystem::MakeDir(
                    $this->output_path . $subdir, 
                    0755, 
                    true
                );
        }
        
        $header = $this->output_path 
            . $subdir
            . $this->GetSourceNamePHP($header_name)
        ;
        
        \Peg\Lib\Utilities\FileSystem::WriteFileIfDifferent($header, $content);
    }
    
    /**
     * Adds or updates a generic file if neccessary.
     * @param string $file_name Original name of header.
     * @param string $content
     * @param string $subdir
     */
    public function AddGenericFile($file_name, &$content, $subdir="")
    {
        if($subdir)
        {
            $subdir = trim($subdir, "\\/") . "/";
            
            if(!file_exists($this->output_path . $subdir))
                \Peg\Lib\Utilities\FileSystem::MakeDir(
                    $this->output_path . $subdir, 
                    0755, 
                    true
                );
        }
        
        $output_file = $this->output_path 
            . $subdir
            . $file_name
        ;
        
        \Peg\Lib\Utilities\FileSystem::WriteFileIfDifferent(
            $output_file, 
            $content
        );
    }
    
    /**
     * Generate all the sources.
     */
    abstract public function Start();
    
    /**
     * Generate the header source of a specific header file.
     * @param string $header_name
     * @return string Source code.
     */
    abstract public function GenerateHeader($header_name);
    
    /**
     * Generate the source file of a specific header file.
     * @param string $header_name
     * @return string Source code.
     */
    abstract public function GenerateSource($header_name);
    
    /**
     * Generate the wrapping code of a specific C/C++ function.
     * @param \Peg\Lib\Definitions\Element\FunctionElement $function_object
     * @return string Source code.
     */
    abstract public function GenerateFunction(
        \Peg\Lib\Definitions\Element\FunctionElement $function_object
    );
}