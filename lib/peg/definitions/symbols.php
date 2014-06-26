<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Definitions;

use Peg\Utilities\Json;

class Symbols
{

    /**
     * List of header files
     * @var \Peg\Definitions\Element\Header[]
     */
    public $headers;

    /**
     * Directory path to definition files.
     * @var string
     */
    public $path;
    
    /**
     * Mechanism used to load the symbols.
     * @see \Peg\Definitions\Symbols
     * @var string
     */
    public $load_type;

    /**
     * Initializes
     * @param string $path The path where resides the 
     * definition files that represent the library.
     */
    public function __construct(
        $path = null, 
        $load_type=\Peg\Definitions\SymbolsType::JSON
    )
    {
        $this->headers = array();
        $this->load_type = $load_type;

        if($path != null)
        {
            if($load_type == SymbolsType::JSON)
            {
                $this->LoadFromJSON($path);
            }
            else
            {
                $this->LoadFromPHP($path);
            }
        }
    }
    
    /**
     * Empty this symbols table.
     */
    public function Clear()
    {
        unset($this->headers);
        
        $this->headers = array();
    }
    
    public function LoadFromPHP($path)
    {
        $this->path = rtrim($path, "/\\") . "/";
        $this->load_type = SymbolsType::PHP;
        
        // This variable is used by the php definition files to reference
        // this object and populate it with elements.
        $symbols =& $this;
        
        include($this->path . "constants.php");
        include($this->path . "enumerations.php");
        include($this->path . "type_definitions.php");
        include($this->path . "variables.php");
        include($this->path . "functions.php");
        include($this->path . "classes.php");
    }

    public function LoadFromJSON($path)
    {
        $this->path = rtrim($path, "/\\") . "/";
        $this->load_type = SymbolsType::JSON;

        //Populate headers array
        $includes = Json::Decode(
            file_get_contents($this->path . "includes.json")
        );

        foreach($includes as $header_name => $header_enabled)
        {
            $file = new Element\Header($header_name);
            $file->enabled = $header_enabled;

            $this->headers[$header_name] = $file;
        }

        unset($includes);

        $this->LoadConstantsFromJson();
        
        $this->LoadEnumerationsFromJson();
        
        $this->LoadTypeDefFromJson();
        
        $this->LoadGlobalVariablesFromJson();
    }

    /**
     * Helper function to load all constants as symbol elements into a
     * header namespace.
     */
    private function LoadConstantsFromJson()
    {
        $constants_def = Json::Decode(
            file_get_contents($this->path . "constants.json")
        );

        foreach($constants_def as $header => $namespaces)
        {
            foreach($namespaces as $namespace => $constants)
            {
                foreach($constants as $constant_name => $constant_value)
                {
                    $constant = new Element\Constant(
                        $constant_name,
                        $constant_value
                    );

                    $this->headers[$header]->AddConstant(
                        $constant,
                        $namespace
                    );
                }
            }
        }

        unset($constants_def);
    }

    /**
     * Helper function to load all enumerations as symbol elements into a
     * header namespace.
     */
    private function LoadEnumerationsFromJson()
    {
        $enumerations_def = Json::Decode(
            file_get_contents($this->path . "enumerations.json")
        );

        foreach($enumerations_def as $header => $namespaces)
        {
            foreach($namespaces as $namespace => $enumerations)
            {
                foreach($enumerations as $enumeration_name => $enumeration_options)
                {
                    $enumeration = new Element\Enumeration(
                        $enumeration_name,
                        $enumeration_options
                    );

                    $this->headers[$header]->AddEnumeration(
                        $enumeration,
                        $namespace
                    );
                }
            }
        }

        unset($enumerations_def);
    }

    /**
     * Helper function to load all type definitions as symbol elements into a
     * header namespace.
     */
    private function LoadTypeDefFromJson()
    {
        $typedef_def = Json::Decode(file_get_contents(
            $this->path . "type_definitions.json")
        );

        foreach($typedef_def as $header => $namespaces)
        {
            foreach($namespaces as $namespace => $typedefs)
            {
                foreach($typedefs as $typedef_name => $typedef_type)
                {
                    $typedef = new Element\TypeDef(
                        $typedef_name,
                        $typedef_type
                    );

                    $this->headers[$header]->AddTypeDef(
                        $typedef,
                        $namespace
                    );
                }
            }
        }

        unset($typedef_def);
    }
    
    /**
     * Helper function to load all type definitions as symbol elements into a
     * header namespace.
     */
    private function LoadGlobalVariablesFromJson()
    {
        $variables_def = Json::Decode(file_get_contents(
            $this->path . "variables.json")
        );

        foreach($variables_def as $header => $namespaces)
        {
            foreach($namespaces as $namespace => $variables)
            {
                foreach($variables as $variable_name => $variable_type)
                {
                    $variable = new Element\GlobalVariable(
                        $variable_name,
                        $variable_type
                    );

                    $this->headers[$header]->AddGlobalVariable(
                        $variable,
                        $namespace
                    );
                }
            }
        }

        unset($variables_def);
    }

    /**
     * Add a header file.
     * @param string $name  Name of header file.
     * @param bool $enabled Flag to inidicate if the header should be included
     * as part of the code generation.
     */
    public function AddHeader($name, $enabled=true)
    {
        if(!isset($this->headers[$name]))
        {
            $header = new Element\Header($name, $enabled);

            $this->headers[$name] = $header;
        }
    }

    /**
     * Adds a constant to the symbols table.
     * @param \Peg\Definitions\Element\Constant $constant
     * @param string $header Name of header file where the constant resides.
     * @param string $namespace If omitted the constant is added at a global scope.
     */
    public function AddConstant(
        \Peg\Definitions\Element\Constant $constant,
        $header,
        $namespace="\\"
    )
    {
        $this->AddHeader($header);

        $this->headers[$header]->AddConstant($namespace, $constant);
    }

    /**
     * Adds an enumeration to the symbols table.
     * @param \Peg\Definitions\Element\Enumeration $enumeration
     * @param string $header Name of header file where the constant resides.
     * @param string $namespace If omitted the constant is added at a global scope.
     */
    public function AddEnumeration(
        \Peg\Definitions\Element\Enumeration $enumeration,
        $header,
        $namespace="\\"
    )
    {
        $this->AddHeader($header);

        $this->headers[$header]->AddEnumeration($namespace, $enumeration);
    }

    /**
     * Adds a type definition to the symbols table.
     * @param \Peg\Definitions\Element\TypeDef $typedef
     * @param string $header Name of header file where the constant resides.
     * @param string $namespace If omitted the constant is added at a global scope.
     */
    public function AddTypeDef(
        \Peg\Definitions\Element\TypeDef $typedef, 
        $header, 
        $namespace="\\"
    )
    {
        $this->AddHeader($header);

        $this->headers[$header]->AddTypeDef($namespace, $typedef);
    }

    /**
     * Adds a global variable to the symbols table.
     * @param \Peg\Definitions\Element\GlobalVariable $global_variable
     * @param string $header Name of header file where the constant resides.
     * @param string $namespace If omitted the constant is added at a global scope.
     */
    public function AddGlobalVar(
        \Peg\Definitions\Element\GlobalVariable $global_variable, 
        $header, 
        $namespace="\\"
    )
    {
        $this->AddHeader($header);

        $this->headers[$header]->AddTypeDef($namespace, $global_variable);
    }

}

?>
