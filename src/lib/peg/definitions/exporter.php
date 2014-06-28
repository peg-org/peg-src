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
 * Export definitions from a symbols table loaded in memory into files.
 */
class Exporter extends \Signals\Signal
{
    /**
     * Reference to the symbols object that is going to be 
     * populated by the importer.
     * @var \Peg\Definitions\Symbols 
     */
    public $symbols;
    
    /**
     * Directory path to definition files.
     * @var string
     */
    public $definitions_path;
    
    /**
     * Mechanism used to load the symbols.
     * @see \Peg\Definitions\Type
     * @var string
     */
    public $export_type;
    
    /**
     * Data that is send each time a signal is launched.
     * @var \Peg\Signals\Definitions\ExportMessage 
     */
    private $signal_data;
    
    /**
     * Initializes
     * @param \Peg\Definitions\Symbols $symbols The table to populate.
     * @param string $path The path where resides the cached
     * definition files that represent the library.
     * @param string $export_type
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
        $this->signal_data = new \Signals\SignalData;
    }
    
    /**
     * Begin exporting definitions to the symbols object specified on constructor.
     */
    public function Start()
    {
        if(!file_exists($this->definitions_path))
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
    
    private function SaveToPHP($path)
    {
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
    }
    
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

                                $output .= '$overload->AddParameter(' . "\n";
                                $output .= '    new Parameter(' . "\n";
                                $output .= '        "'.$parameter->name.'",' . "\n";
                                $output .= '        "'.$parameter->original_type.'",' . "\n";
                                $output .= '        "'.$parameter_value.'"' . "\n";
                                $output .= '    )' . "\n";
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

    private function SaveToJSON($path)
    {
        $this->definitions_path = rtrim($path, "/\\") . "/";
        $this->export_type = Type::JSON;

        $includes = array();
        
        $this->SendMessage(t("Creating includes.json"));
        foreach($this->symbols->headers as $header)
        {
            $includes[$header->name] = $header->enabled;
        }
        
        file_put_contents(
            $this->definitions_path . "includes.json", 
            \Peg\Utilities\Json::Encode($includes)
        );
        
        unset($includes);
        
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
    }

    /**
     * Helper function to load all constants as symbol elements into a
     * header namespace.
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
                    $constants[$header->name][$namespace->name]
                        [$constant->name] = $constant->value
                    ;
                }
            }
        }
        
        file_put_contents(
            $this->definitions_path . "constants.json", 
            Json::Encode($constants)
        );
        
        unset($constants);
    }

    /**
     * Helper function to load all enumerations as symbol elements into a
     * header namespace.
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
                    $enumerations[$header->name][$namespace->name]
                        [$enumeration->name] = $enumeration->options
                    ;
                }
            }
        }
        
        file_put_contents(
            $this->definitions_path . "enumerations.json", 
            Json::Encode($enumerations)
        );
        
        unset($enumerations);
    }

    /**
     * Helper function to load all type definitions as symbol elements into a
     * header namespace.
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
                    $typedefs[$header->name][$namespace->name]
                        [$typedef->name] = $typedef->original_type
                    ;
                }
            }
        }
        
        file_put_contents(
            $this->definitions_path . "type_definitions.json", 
            Json::Encode($typedefs)
        );
        
        unset($typedefs);
    }
    
    /**
     * Helper function to load all type definitions as symbol elements into a
     * header namespace.
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
                    $variables[$header->name][$namespace->name]
                        [$variable->name] = $variable->original_type
                    ;
                }
            }
        }
        
        file_put_contents(
            $this->definitions_path . "variables.json", 
            Json::Encode($variables)
        );
        
        unset($variables);
    }
    
    /**
     * Helper function to load all type definitions as symbol elements into a
     * header namespace.
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
                                $parameters[] = [
                                    "name" => $parameter->name,
                                    "type" => $parameter->original_type,
                                    "is_array" => $parameter->is_array
                                ];
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
        
        file_put_contents(
            $this->definitions_path . "functions.json", 
            Json::Encode($functions)
        );
        
        unset($functions);
    }

    /**
     * Sends a signal with message of current task being performed.
     * @param string $message
     */
    private function SendMessage($message)
    {
        $this->signal_data->message = $message;
        
        $this->Send(
            \Peg\Signals\Definitions::EXPORT_MESSAGE,
            $this->signal_data
        );
    }
}

?>
