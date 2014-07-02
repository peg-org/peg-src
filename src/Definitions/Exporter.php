<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Definitions;

use Peg\Utilities\Json;
use Peg\Utilities\FileSystem;

/**
 * Export definitions from a symbols object into cache files.
 */
class Exporter extends \Peg\Signals\Signal
{
    /**
     * Reference to the symbols object that is going to be cached into files.
     * @var \Peg\Definitions\Symbols 
     */
    public $symbols;
    
    /**
     * Directory where the symbols cache files will be saved.
     * @var string
     */
    public $definitions_path;
    
    /**
     * Format used to store the cached symbols.
     * @see \Peg\Definitions\Type
     * @var string
     */
    public $export_type;
    
    /**
     * Data that is send each time a signal is launched.
     * @var \Peg\Signals\Data\Definitions\ExportMessage 
     */
    private $signal_data;
    
    /**
     * Constructor.
     * @param \Peg\Definitions\Symbols $symbols The table to populate.
     * @param string $path The path where will be stored the cache files.
     * @param string $export_type The type of cache files to create.
     */
    public function __construct(
        \Peg\Definitions\Symbols &$symbols,
        $path = null, 
        $export_type=\Peg\Definitions\Type::JSON
    )
    {
        $this->symbols =& $symbols;
        $this->definitions_path = $path;
        $this->export_type = $export_type;
        $this->signal_data = new \Peg\Signals\Data\Definitions\ExportMessage;
    }
    
    /**
     * Begin exporting definitions from the symbols object specified on constructor.
     */
    public function Start()
    {
        if(!$this->definitions_path)
        {
            throw new \Exception(
                t("Your are trying to export definitions without setting a path first.")
            );
        }
        elseif(!file_exists($this->definitions_path))
        {
            FileSystem::MakeDir(
                $this->definitions_path, 
                0755, 
                true
            );
        }
        
        $this->definitions_path = rtrim($this->definitions_path, "\\/") . "/";
        
        $this->SendMessage(
            sprintf(
                t("Starting export of definitions to %s format."),
                $this->export_type
            )
        );
        
        if($this->export_type == Type::JSON)
        {
            $this->SaveToJSON($this->definitions_path);
        }
        else
        {
            $this->SaveToPHP($this->definitions_path);
        }
        
        $this->SendMessage(t("Export completed."));
    }
    
    /**
     * Saves all symbols into php files.
     * @param string $path Directory where files will be stored.
     */
    private function SaveToPHP($path)
    {
        $this->definitions_path = rtrim($path, "/\\") . "/";
        $this->export_type = Type::PHP;
        
        $this->SendMessage(t("Creating constants.php"));
        $this->SaveConstantsToPHP();
        
        $this->SendMessage(t("Creating enumerations.php"));
        $this->SaveEnumerationsToPHP();
        
        $this->SendMessage(t("Creating type_definitions.php"));
        $this->SaveTypeDefToPHP();
        
        $this->SendMessage(t("Creating variables.php"));
        $this->SaveGloablVariablesToPHP();
        
        $this->SendMessage(t("Creating functions.php"));
        $this->SaveFunctionsToPHP();
        
        $this->SendMessage(t("Creating classes.php"));
        $this->SaveClassesToPHP();
    }
    
    /**
     * Save constant/#define symbols into a constants.php file.
     */
    private function SaveConstantsToPHP()
    {
        $output_file = fopen(
            $this->definitions_path . "constants.php", 
            "w"
        );
        
        fwrite($output_file, "<?php\n\n");
        
        fwrite($output_file, "use Peg\Definitions\Element\Constant;\n");
        
        foreach($this->symbols->headers as $header)
        {
            if(!$header->HasConstants())
                continue;
            
            foreach($header->namespaces as $namespace)
            {
                if(!$namespace->HasConstants())
                    continue;
                
                foreach($namespace->constants as $constant)
                {
                    $value = addslashes($constant->value);
                    $description = addslashes($constant->description);
                    $namespace_name = addslashes($namespace->name);
                    
                    $output = "\n";
                    $output .= '$symbols->AddConstant(' . "\n";
                    $output .= '    new Constant(' . "\n";
                    $output .= '        "'.$constant->name.'",' . "\n";
                    $output .= '        "'.$value.'",' . "\n";
                    $output .= '        "'.$description.'"' . "\n";
                    $output .= '    ),' . "\n";
                    $output .= '    "'.$header->name.'",' . "\n";
                    $output .= '    "'.$namespace_name.'"' . "\n";
                    $output .= ');' . "\n";
                    
                    fwrite($output_file, $output);
                }
            }
        }
        
        fclose($output_file);
    }
    
    /**
     * Save enumeration symbols into a enumerations.php file.
     */
    private function SaveEnumerationsToPHP()
    {
        $output_file = fopen(
            $this->definitions_path . "enumerations.php", 
            "w"
        );
        
        fwrite($output_file, "<?php\n\n");
        
        fwrite($output_file, "use Peg\Definitions\Element\Enumeration;\n");
        
        foreach($this->symbols->headers as $header)
        {
            if(!$header->HasEnumerations())
                continue;
            
            foreach($header->namespaces as $namespace)
            {
                if(!$namespace->HasEnumerations())
                    continue;
                
                foreach($namespace->enumerations as $enumeration)
                {
                    $description = addslashes($enumeration->description);
                    $namespace_name = addslashes($namespace->name);
                    
                    $output = "\n";
                    $output .= '$symbols->AddEnumeration(' . "\n";
                    $output .= '    new Enumeration(' . "\n";
                    $output .= '        "'.$enumeration->name.'",' . "\n";
                    
                    $output .= '        [' . "\n";
                    foreach($enumeration->options as $option)
                    {
                        $output .= '            "'.$option.'",' . "\n";
                    }
                    $output = rtrim($output, ",\n") . "\n";
                    $output .= '        ],' . "\n";
                    
                    $output .= '        "'.$description.'"' . "\n";
                    $output .= '    ),' . "\n";
                    $output .= '    "'.$header->name.'",' . "\n";
                    $output .= '    "'.$namespace_name.'"' . "\n";
                    $output .= ');' . "\n";
                    
                    fwrite($output_file, $output);
                }
            }
        }
        
        fclose($output_file);
    }
    
    /**
     * Save typedef symbols into a type_definitions.php file.
     */
    private function SaveTypeDefToPHP()
    {
        $output_file = fopen(
            $this->definitions_path . "type_definitions.php", 
            "w"
        );
        
        fwrite($output_file, "<?php\n\n");
        
        fwrite($output_file, "use Peg\Definitions\Element\TypeDef;\n");
        
        foreach($this->symbols->headers as $header)
        {
            if(!$header->HasTypeDefs())
                continue;
            
            foreach($header->namespaces as $namespace)
            {
                if(!$namespace->HasTypeDefs())
                    continue;
                
                foreach($namespace->type_definitions as $typedef)
                {
                    $type = addslashes($typedef->original_type);
                    $description = addslashes($typedef->description);
                    $namespace_name = addslashes($namespace->name);
                    
                    $output = "\n";
                    $output .= '$symbols->AddTypeDef(' . "\n";
                    $output .= '    new TypeDef(' . "\n";
                    $output .= '        "'.$typedef->name.'",' . "\n";
                    $output .= '        "'.$type.'",' . "\n";
                    $output .= '        "'.$description.'"' . "\n";
                    $output .= '    ),' . "\n";
                    $output .= '    "'.$header->name.'",' . "\n";
                    $output .= '    "'.$namespace_name.'"' . "\n";
                    $output .= ');' . "\n";
                    
                    fwrite($output_file, $output);
                }
            }
        }
        
        fclose($output_file);
    }
    
    /**
     * Save global variable symbols into a variables.php file.
     */
    private function SaveGloablVariablesToPHP()
    {
        $output_file = fopen(
            $this->definitions_path . "variables.php", 
            "w"
        );
        
        fwrite($output_file, "<?php\n\n");
        
        fwrite($output_file, "use Peg\Definitions\Element\GlobalVariable;\n");
        
        foreach($this->symbols->headers as $header)
        {
            if(!$header->HasGlobalVariables())
                continue;
            
            foreach($header->namespaces as $namespace)
            {
                if(!$namespace->HasGlobalVariables())
                    continue;
                
                foreach($namespace->global_variables as $variable)
                {
                    $type = addslashes($variable->original_type);
                    $description = addslashes($variable->description);
                    $namespace_name = addslashes($namespace->name);
                    
                    $output = "\n";
                    $output .= '$symbols->AddGlobalVar(' . "\n";
                    $output .= '    new GlobalVariable(' . "\n";
                    $output .= '        "'.$variable->name.'",' . "\n";
                    $output .= '        "'.$type.'",' . "\n";
                    $output .= '        "'.$description.'"' . "\n";
                    $output .= '    ),' . "\n";
                    $output .= '    "'.$header->name.'",' . "\n";
                    $output .= '    "'.$namespace_name.'"' . "\n";
                    $output .= ');' . "\n";
                    
                    fwrite($output_file, $output);
                }
            }
        }
        
        fclose($output_file);
    }
    
    /**
     * Save function symbols into a functions.php file.
     */
    private function SaveFunctionsToPHP()
    {
        $output_file = fopen(
            $this->definitions_path . "functions.php", 
            "w"
        );
        
        fwrite($output_file, "<?php\n\n");
        
        fwrite($output_file, "use Peg\Definitions\Element\FunctionElement;\n");
        fwrite($output_file, "use Peg\Definitions\Element\Overload;\n");
        fwrite($output_file, "use Peg\Definitions\Element\ReturnType;\n");
        fwrite($output_file, "use Peg\Definitions\Element\Parameter;\n");
        
        foreach($this->symbols->headers as $header)
        {
            if(!$header->HasFunctions())
                continue;
            
            foreach($header->namespaces as $namespace)
            {
                if(!$namespace->HasFunctions())
                    continue;
                
                foreach($namespace->functions as $function)
                {
                    $description = addslashes($function->description);
                    $namespace_name = addslashes($namespace->name);
                    
                    $output = "\n";
                    
                    $output .= '// Function ' . $function->name . "\n";
                    $output .= '$function = new FunctionElement(' . "\n";
                    $output .= '    "'.$function->name.'",' . "\n";
                    $output .= '    "'.$description.'"' . "\n";
                    $output .= ');' . "\n\n";
                    
                    foreach($function->overloads as $overload_index=>$overload)
                    {
                        $overload_description = addslashes($overload->description);
                        
                        $output .= '// Overload ' . $overload_index . "\n";
                        $output .= '$overload = new Overload(' . "\n";
                        $output .= '    "'.$overload_description.'"' . "\n";
                        $output .= ');' . "\n";
                        
                        $output .= '$overload->SetReturnType(' . "\n";
                        $output .= '    new ReturnType(' . "\n";
                        $output .= '        "'.$overload->return_type->original_type.'"' . "\n";
                        $output .= '    )' . "\n";
                        $output .= ');' . "\n";
                        
                        if($overload->HasParameters())
                        {
                            foreach($overload->parameters as $parameter)
                            {
                                $parameter_value = addslashes($parameter->default_value);
                                $parameter_description = addslashes($parameter->description);

                                $output .= '$parameter = new Parameter(' . "\n";
                                $output .= '    "'.$parameter->name.'",' . "\n";
                                $output .= '    "'.$parameter->original_type.'",' . "\n";
                                $output .= '    "'.$parameter_value.'",' . "\n";
                                $output .= '    "'.$parameter_description.'"' . "\n";
                                $output .= ');' . "\n";
                                
                                if($parameter->is_array)
                                {
                                    $output .= '$parameter->is_array = true;' . "\n";
                                }
                                
                                $output .= '$overload->AddParameter(' . "\n";
                                $output .= '    $parameter' . "\n";
                                $output .= ');' . "\n";
                            }
                        }
                        
                        $output .= '$function->AddOverload($overload);' . "\n\n";
                    }
                    
                    $output .= '$symbols->AddFunction(' . "\n";
                    $output .= '    $function,' . "\n";
                    $output .= '    "'.$header->name.'",' . "\n";
                    $output .= '    "'.$namespace_name.'"' . "\n";
                    $output .= ');' . "\n\n";
                    
                    fwrite($output_file, $output);
                }
            }
        }
        
        fclose($output_file);
    }
    
    /**
     * Save classes symbols into a classes.php file.
     */
    private function SaveClassesToPHP()
    {
        $output_file = fopen(
            $this->definitions_path . "classes.php", 
            "w"
        );
        
        fwrite($output_file, "<?php\n\n");
        
        fwrite($output_file, "use Peg\Definitions\Element\ClassElement;\n");
        fwrite($output_file, "use Peg\Definitions\Element\FunctionElement;\n");
        fwrite($output_file, "use Peg\Definitions\Element\Overload;\n");
        fwrite($output_file, "use Peg\Definitions\Element\ReturnType;\n");
        fwrite($output_file, "use Peg\Definitions\Element\Parameter;\n");
        fwrite($output_file, "use Peg\Definitions\Element\Enumeration;\n");
        fwrite($output_file, "use Peg\Definitions\Element\ClassVariable;\n");
        
        foreach($this->symbols->headers as $header)
        {
            if(!$header->HasClasses())
                continue;
            
            foreach($header->namespaces as $namespace)
            {
                if(!$namespace->HasClasses())
                    continue;
                
                foreach($namespace->classes as $class)
                {
                    $description = addslashes($class->description);
                    $namespace_name = addslashes($namespace->name);
                    
                    $output = "\n";
                    $output .= '// Class ' . $class->name . "\n";
                    $output .= '$class = new ClassElement(' . "\n";
                    $output .= '    "'.$class->name.'",' . "\n";
                    $output .= '    "'.$description.'"' . "\n";
                    $output .= ');' . "\n\n";
                    
                    // Write class methods
                    foreach($class->methods as $method)
                    {
                        $description = addslashes($method->description);

                        $output .= "\n";

                        $output .= '// Method ' . $class->name . '::' . $method->name . "\n";
                        $output .= '$method = new FunctionElement(' . "\n";
                        $output .= '    "'.$method->name.'",' . "\n";
                        $output .= '    "'.$description.'"' . "\n";
                        $output .= ');' . "\n\n";

                        foreach($method->overloads as $overload_index=>$overload)
                        {
                            $overload_description = addslashes($overload->description);

                            $output .= '// Overload ' . $overload_index . "\n";
                            $output .= '$overload = new Overload(' . "\n";
                            $output .= '    "'.$overload_description.'"' . "\n";
                            $output .= ');' . "\n";

                            $output .= '$overload->SetReturnType(' . "\n";
                            $output .= '    new ReturnType(' . "\n";
                            $output .= '        "'.$overload->return_type->original_type.'"' . "\n";
                            $output .= '    )' . "\n";
                            $output .= ');' . "\n";
                            
                            if($overload->static)
                                $output .= '$overload->static = true;' . "\n";
                            
                            if($overload->protected)
                                $output .= '$overload->protected = true;' . "\n";
                            
                            if($overload->virtual)
                                $output .= '$overload->virtual = true;' . "\n";
                            
                            if($overload->pure_virtual)
                                $output .= '$overload->pure_virtual = true;' . "\n";
                            
                            if($overload->constant)
                                $output .= '$overload->constant = true;' . "\n";

                            if($overload->HasParameters())
                            {
                                foreach($overload->parameters as $parameter)
                                {
                                    $parameter_value = addslashes($parameter->default_value);
                                    $parameter_description = addslashes($parameter->description);
                                    
                                    $output .= '$parameter = new Parameter(' . "\n";
                                    $output .= '    "'.$parameter->name.'",' . "\n";
                                    $output .= '    "'.$parameter->original_type.'",' . "\n";
                                    $output .= '    "'.$parameter_value.'",' . "\n";
                                    $output .= '    "'.$parameter_description.'"' . "\n";
                                    $output .= ');' . "\n";
                                    
                                    if($parameter->is_array)
                                    {
                                        $output .= '$parameter->is_array = true;' . "\n";
                                    }

                                    $output .= '$overload->AddParameter(' . "\n";
                                    $output .= '    $parameter' . "\n";
                                    $output .= ');' . "\n";
                                }
                            }

                            $output .= '$method->AddOverload($overload);' . "\n\n";
                        }

                        $output .= '$class->AddMethod(' . "\n";
                        $output .= '    $method' . "\n";
                        $output .= ');' . "\n\n";
                    }
                    
                    // Write class enumerations
                    foreach($class->enumerations as $enumeration)
                    {
                        $description = addslashes($enumeration->description);

                        $output .= "\n";
                        $output .= '// Enumeration ' . $class->name . '::' . $enumeration->name . "\n";
                        $output .= '$class->AddEnumeration(' . "\n";
                        $output .= '    new Enumeration(' . "\n";
                        $output .= '        "'.$enumeration->name.'",' . "\n";

                        $output .= '        [' . "\n";
                        foreach($enumeration->options as $option)
                        {
                            $output .= '            "'.$option.'",' . "\n";
                        }
                        $output = rtrim($output, ",\n") . "\n";
                        $output .= '        ],' . "\n";

                        $output .= '        "'.$description.'"' . "\n";
                        $output .= '    )' . "\n";
                        $output .= ');' . "\n";

                        fwrite($output_file, $output);
                    }
                    
                    // Write class variables
                    foreach($class->variables as $variable)
                    {
                        $type = addslashes($variable->original_type);
                        $description = addslashes($variable->description);

                        $output .= "\n";
                        $output .= '$class->AddVariable(' . "\n";
                        $output .= '    new ClassVariable(' . "\n";
                        $output .= '        "'.$variable->name.'",' . "\n";
                        $output .= '        "'.$type.'",' . "\n";
                        $output .= '        "'.$description.'"' . "\n";
                        $output .= '    )' . "\n";
                        $output .= ');' . "\n";

                        fwrite($output_file, $output);
                    }
                    
                    $output .= '$symbols->AddClass(' . "\n";
                    $output .= '    $class,' . "\n";
                    $output .= '    "'.$header->name.'",' . "\n";
                    $output .= '    "'.$namespace_name.'"' . "\n";
                    $output .= ');' . "\n\n";
                    
                    fwrite($output_file, $output);
                }
            }
        }
        
        fclose($output_file);
    }

    /**
     * Saves all symbols into json files.
     * @param string $path Directory where files will be stored.
     */
    private function SaveToJSON($path)
    {
        $this->definitions_path = rtrim($path, "/\\") . "/";
        $this->export_type = Type::JSON;
        
        $this->SendMessage(t("Creating constants.json"));
        $this->SaveConstantsToJson();
        
        $this->SendMessage(t("Creating enumerations.json"));
        $this->SaveEnumerationsToJson();
        
        $this->SendMessage(t("Creating type_definitions.json"));
        $this->SaveTypeDefToJson();
        
        $this->SendMessage(t("Creating variables.json"));
        $this->SaveGlobalVariablesToJson();
        
        $this->SendMessage(t("Creating functions.json"));
        $this->SaveFunctionsToJson();
        
        $this->SendMessage(t("Creating classes.json"));
        $this->SaveClassesToJson();
    }

    /**
     * Save constant/#define symbols into constants.json.
     */
    private function SaveConstantsToJson()
    {
        $constants = array();
        
        foreach($this->symbols->headers as $header)
        {
            if(!$header->HasConstants())
                continue;
            
            foreach($header->namespaces as $namespace)
            {
                if(!$namespace->HasConstants())
                    continue;
                
                foreach($namespace->constants as $constant)
                {
                    if(trim($constant->description))
                    {
                        $constants[$header->name][$namespace->name]
                            [$constant->name] = [
                                "value" => $constant->value,
                                "description" => $constant->description
                            ]
                        ;
                    }
                    else
                    {
                        $constants[$header->name][$namespace->name]
                            [$constant->name] = [
                                "value" => $constant->value
                            ]
                        ;
                    }
                }
            }
        }
        
        if(count($constants) > 0)
        {
            file_put_contents(
                $this->definitions_path . "constants.json", 
                Json::Encode($constants)
            );

            unset($constants);
        }
    }

    /**
     * Save enumeration symbols into enumerations.json.
     */
    private function SaveEnumerationsToJson()
    {
        $enumerations = array();
        
        foreach($this->symbols->headers as $header)
        {
            if(!$header->HasEnumerations())
                continue;
            
            foreach($header->namespaces as $namespace)
            {
                if(!$namespace->HasEnumerations())
                    continue;
                
                foreach($namespace->enumerations as $enumeration)
                {
                    if(trim($enumeration->description))
                    {
                        $enumerations[$header->name][$namespace->name]
                            [$enumeration->name] = [
                                "options" => $enumeration->options,
                                "description" => $enumeration->description
                            ]
                        ;
                    }
                    else
                    {
                        $enumerations[$header->name][$namespace->name]
                            [$enumeration->name] = [
                                "options" => $enumeration->options
                            ]
                        ;
                    }
                }
            }
        }
        
        if(count($enumerations) > 0)
        {
            file_put_contents(
                $this->definitions_path . "enumerations.json", 
                Json::Encode($enumerations)
            );

            unset($enumerations);
        }
    }

    /**
     * Save typedef symbols into type_definitions.json.
     */
    private function SaveTypeDefToJson()
    {
        $typedefs = array();
        
        foreach($this->symbols->headers as $header)
        {
            if(!$header->HasTypeDefs())
                continue;
            
            foreach($header->namespaces as $namespace)
            {
                if(!$namespace->HasTypeDefs())
                    continue;
                
                foreach($namespace->type_definitions as $typedef)
                {
                    if(trim($typedef->description))
                    {
                        $typedefs[$header->name][$namespace->name]
                            [$typedef->name] = [
                                "type" => $typedef->original_type,
                                "description" => $typedef->description
                            ]
                        ;
                    }
                    else
                    {
                        $typedefs[$header->name][$namespace->name]
                            [$typedef->name] = [
                                "type" => $typedef->original_type
                            ]
                        ;
                    }
                }
            }
        }
        
        if(count($typedefs) > 0)
        {
            file_put_contents(
                $this->definitions_path . "type_definitions.json", 
                Json::Encode($typedefs)
            );

            unset($typedefs);
        }
    }
    
    /**
     * Save global variable symbols into variables.json.
     */
    private function SaveGlobalVariablesToJson()
    {
        $variables = array();
        
        foreach($this->symbols->headers as $header)
        {
            if(!$header->HasGlobalVariables())
                continue;
            
            foreach($header->namespaces as $namespace)
            {
                if(!$namespace->HasGlobalVariables())
                    continue;
                
                foreach($namespace->global_variables as $variable)
                {
                    if(trim($variable->description))
                    {
                        $variables[$header->name][$namespace->name]
                            [$variable->name] = [
                                "type" => $variable->original_type,
                                "description" => $variable->description
                            ]
                        ;
                    }
                    else
                    {
                        $variables[$header->name][$namespace->name]
                            [$variable->name] = [
                                "type" => $variable->original_type
                            ]
                        ;
                    }
                }
            }
        }
        
        if(count($variables) > 0)
        {
            file_put_contents(
                $this->definitions_path . "variables.json", 
                Json::Encode($variables)
            );

            unset($variables);
        }
    }
    
    /**
     * Save function symbols into functions.json.
     */
    private function SaveFunctionsToJson()
    {
        $functions = array();
        
        foreach($this->symbols->headers as $header)
        {
            if(!$header->HasFunctions())
                continue;
            
            foreach($header->namespaces as $namespace)
            {
                if(!$namespace->HasFunctions())
                    continue;
                
                foreach($namespace->functions as $function)
                {
                    foreach($function->overloads as $overload)
                    {
                        $parameters = array();
                        
                        if($overload->HasParameters())
                        {
                            foreach($overload->parameters as $parameter)
                            {
                                if(trim($parameter->description))
                                {
                                    $parameters[] = [
                                        "name" => $parameter->name,
                                        "type" => $parameter->original_type,
                                        "is_array" => $parameter->is_array,
                                        "description" => $parameter->description
                                    ];
                                }
                                else
                                {
                                    $parameters[] = [
                                        "name" => $parameter->name,
                                        "type" => $parameter->original_type,
                                        "is_array" => $parameter->is_array
                                    ];
                                }
                            }
                            
                            $functions[$header->name][$namespace->name]
                                [$function->name][] = [
                                    "description" => $overload->description,
                                    "return_type" => $overload->return_type->original_type,
                                    "parameters" => $parameters
                                ]
                            ;
                        }
                        else
                        {
                            $functions[$header->name][$namespace->name]
                                [$function->name][] = [
                                    "description" => $overload->description,
                                    "return_type" => $overload->return_type->original_type
                                ]
                            ;
                        }
                    }
                }
            }
        }
        
        if(count($functions) > 0)
        {
            file_put_contents(
                $this->definitions_path . "functions.json", 
                Json::Encode($functions)
            );

            unset($functions);
        }
    }
    
    /**
     * Save class symbols into classes.json, the class
     * enumerations into class_enumerations.json and its variables into
     * class_variables.json.
     */
    private function SaveClassesToJson()
    {
        $classes = array();
        $enumerations = array();
        $variables = array();
        
        foreach($this->symbols->headers as $header)
        {
            if(!$header->HasClasses())
                continue;
            
            foreach($header->namespaces as $namespace)
            {
                if(!$namespace->HasClasses())
                    continue;
                
                foreach($namespace->classes as $class)
                {   
                    // Get methods
                    foreach($class->methods as $function)
                    {
                        foreach($function->overloads as $overload)
                        {
                            $parameters = array();

                            if($overload->HasParameters())
                            {
                                foreach($overload->parameters as $parameter)
                                {
                                    if(trim($parameter->description))
                                    {
                                        $parameters[] = [
                                            "name" => $parameter->name,
                                            "type" => $parameter->original_type,
                                            "is_array" => $parameter->is_array,
                                            "description" => $parameter->description
                                        ];
                                    }
                                    else
                                    {
                                        $parameters[] = [
                                            "name" => $parameter->name,
                                            "type" => $parameter->original_type,
                                            "is_array" => $parameter->is_array
                                        ];
                                    }
                                }

                                $classes[$header->name][$namespace->name]
                                    [$class->name][$function->name][] = [
                                        "description" => $overload->description,
                                        "return_type" => $overload->return_type->original_type,
                                        "parameters" => $parameters
                                    ]
                                ;
                            }
                            else
                            {
                                $classes[$header->name][$namespace->name]
                                    [$class->name][$function->name][] = [
                                        "description" => $overload->description,
                                        "return_type" => $overload->return_type->original_type
                                    ]
                                ;
                            }
                        }
                    }
                    
                    // Get enumerations
                    foreach($class->enumerations as $enumeration)
                    {
                        if(trim($enumeration->description))
                        {
                            $enumerations[$header->name][$namespace->name]
                                [$class->name][$enumeration->name] = [
                                    "options" => $enumeration->options,
                                    "description" => $enumeration->description
                                ]
                            ;
                        }
                        else
                        {
                            $enumerations[$header->name][$namespace->name]
                                [$class->name][$enumeration->name] = [
                                    "options" => $enumeration->options
                                ]
                            ;
                        }
                    }
                    
                    // Get variables
                    foreach($class->variables as $variable)
                    {
                        $variable_array = array();
                        
                        if(trim($variable->type))
                            $variable_array["type"] = $variable->type;
                        
                        if(trim($variable->static))
                            $variable_array["static"] = $variable->static;
                        
                        if(trim($variable->protected))
                            $variable_array["protected"] = $variable->protected;
                        
                        if(trim($variable->mutable))
                            $variable_array["mutable"] = $variable->mutable;
                        
                        if(trim($variable->description))
                            $variable_array["description"] = $variable->description;
                        
                        $variables[$header->name][$namespace->name]
                            [$class->name][$variable->name] = $variable_array
                        ;
                    }
                }
            }
        }
        
        if(count($classes) > 0)
        {
            file_put_contents(
                $this->definitions_path . "classes.json", 
                Json::Encode($classes)
            );
            
            unset($classes);
        }
        
        if(count($enumerations) > 0)
        {
            file_put_contents(
                $this->definitions_path . "class_enumerations.json", 
                Json::Encode($enumerations)
            );
            
            unset($enumerations);
        }
        
        if(count($variables) > 0)
        {
            file_put_contents(
                $this->definitions_path . "class_variables.json", 
                Json::Encode($variables)
            );

            unset($variables);
        }
    }

    /**
     * Sends a signal with message of current task being performed.
     * @param string $message
     */
    private function SendMessage($message)
    {
        $this->signal_data->message = $message;
        
        $this->Send(
            \Peg\Signals\Type\Definitions::EXPORT_MESSAGE,
            $this->signal_data
        );
    }
}

?>
