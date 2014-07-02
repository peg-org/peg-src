<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Definitions;

use Peg\Utilities\Json;

/**
 * Loads cached definitions into a symbols object.
 */
class Importer extends \Peg\Signals\Signal
{
    /**
     * Reference to the symbols object that is going to be 
     * populated by the importer.
     * @var \Peg\Definitions\Symbols 
     */
    public $symbols;
    
    /**
     * Path where reside the cached files.
     * @var string
     */
    public $definitions_path;
    
    /**
     * Format used to load the symbols.
     * @see \Peg\Definitions\Type
     * @var string
     */
    public $import_type;
    
    /**
     * Data that is send each time a signal is launched.
     * @var \Peg\Signals\Data\Definitions\ImportMessage 
     */
    private $signal_data;
    
    /**
     * Constructor.
     * @param \Peg\Definitions\Symbols $symbols The table to populate.
     * @param string $path The path where resides the cached files.
     * @param string $import_type The type of cache files to import.
     */
    public function __construct(
        \Peg\Definitions\Symbols &$symbols,
        $path = null, 
        $import_type=\Peg\Definitions\Type::JSON
    )
    {
        $this->symbols =& $symbols;
        $this->definitions_path = $path;
        $this->import_type = $import_type;
        $this->signal_data = new \Peg\Signals\Data\Definitions\ImportMessage;
    }
    
    /**
     * Begin importing definitions to the symbols object specified on constructor.
     */
    public function Start()
    {
        if(!file_exists($this->definitions_path))
        {
            throw new \Exception(
                t("Trying to import symbols from a non existent directory.")
            );
        }
        
        $this->SendMessage(
            sprintf(
                t("Starting import of definitions stored in %s format."),
                $this->import_type
            )
        );
        
        if($this->import_type == Type::JSON)
        {
            $this->LoadFromJSON($this->definitions_path);
        }
        else
        {
            $this->LoadFromPHP($this->definitions_path);
        }
        
        $this->SendMessage(t("Import completed."));
    }
    
    /**
     * Load all kind of symbols from php files previously created
     * by \Peg\Definitions\Exporter.
     * @param string $path Directory where the php files reside.
     */
    private function LoadFromPHP($path)
    {
        $this->definitions_path = rtrim($path, "/\\") . "/";
        $this->import_type = Type::PHP;
        
        // This variable is used by the php definition files to reference
        // this object and populate it with elements.
        $symbols =& $this->symbols;
        
        if(file_exists($this->definitions_path . "constants.php"))
        {
            $this->SendMessage(t("Loading constants.php"));
            include($this->definitions_path . "constants.php");
        }
        
        if(file_exists($this->definitions_path . "enumerations.php"))
        {
            $this->SendMessage(t("Loading enumerations.php"));
            include($this->definitions_path . "enumerations.php");
        }
        
        if(file_exists($this->definitions_path . "type_definitions.php"))
        {
            $this->SendMessage(t("Loading type_definitions.php"));
            include($this->definitions_path . "type_definitions.php");
        }
        
        if(file_exists($this->definitions_path . "variables.php"))
        {
            $this->SendMessage(t("Loading variables.php"));
            include($this->definitions_path . "variables.php");
        }
        
        if(file_exists($this->definitions_path . "functions.php"))
        {
            $this->SendMessage(t("Loading functions.php"));
            include($this->definitions_path . "functions.php");
        }
        
        if(file_exists($this->definitions_path . "classes.php"))
        {
            $this->SendMessage(t("Loading classes.php"));
            include($this->definitions_path . "classes.php");
        }
    }

    /**
     * Load all kind of symbols from json files previously created
     * by \Peg\Definitions\Exporter.
     * @param string $path Directory where the json files reside.
     */
    private function LoadFromJSON($path)
    {
        $this->definitions_path = rtrim($path, "/\\") . "/";
        $this->import_type = Type::JSON;

        if(file_exists($this->definitions_path . "constants.json"))
        {
            $this->SendMessage(t("Loading constants.json"));
            $this->LoadConstantsFromJson();
        }
        
        if(file_exists($this->definitions_path . "enumerations.json"))
        {
            $this->SendMessage(t("Loading enumerations.json"));
            $this->LoadEnumerationsFromJson();
        }
        
        if(file_exists($this->definitions_path . "type_definitions.json"))
        {
            $this->SendMessage(t("Loading type_definitions.json"));
            $this->LoadTypeDefFromJson();
        }
        
        if(file_exists($this->definitions_path . "variables.json"))
        {
            $this->SendMessage(t("Loading variables.json"));
            $this->LoadGlobalVariablesFromJson();
        }
        
        if(file_exists($this->definitions_path . "functions.json"))
        {
            $this->SendMessage(t("Loading functions.json"));
            $this->LoadFunctionsFromJson();
        }
        
        if(file_exists($this->definitions_path . "classes.json"))
        {
            $this->SendMessage(t("Loading classes.json"));
            $this->LoadClassesFromJson();
        }
    }

    /**
     * Load all constant symbols from constants.json
     */
    private function LoadConstantsFromJson()
    {
        $constants_def = Json::Decode(
            file_get_contents($this->definitions_path . "constants.json")
        );

        foreach($constants_def as $header => $namespaces)
        {
            foreach($namespaces as $namespace => $constants)
            {
                foreach($constants as $constant_name => $constant_data)
                {
                    if(!isset($constant_data["description"]))
                        $constant_data["description"] = "";
                    
                    $constant = new Element\Constant(
                        $constant_name,
                        $constant_data["value"],
                        $constant_data["description"]
                    );

                    $this->symbols->headers[$header]->AddConstant(
                        $constant,
                        $namespace
                    );
                }
            }
        }

        unset($constants_def);
    }

    /**
     * Load all enumeration symbols from enumerations.json
     */
    private function LoadEnumerationsFromJson()
    {
        $enumerations_def = Json::Decode(
            file_get_contents($this->definitions_path . "enumerations.json")
        );

        foreach($enumerations_def as $header => $namespaces)
        {
            foreach($namespaces as $namespace => $enumerations)
            {
                foreach($enumerations as $enumeration_name => $enumeration_data)
                {
                    if(!isset($enumeration_data["description"]))
                        $enumeration_data["description"] = "";
                    
                    $enumeration = new Element\Enumeration(
                        $enumeration_name,
                        $enumeration_data["options"],
                        $enumeration_data["description"]
                    );

                    $this->symbols->headers[$header]->AddEnumeration(
                        $enumeration,
                        $namespace
                    );
                }
            }
        }

        unset($enumerations_def);
    }

    /**
     * Load all typedef symbols from type_definitions.json
     */
    private function LoadTypeDefFromJson()
    {
        $typedef_def = Json::Decode(file_get_contents(
            $this->definitions_path . "type_definitions.json")
        );

        foreach($typedef_def as $header => $namespaces)
        {
            foreach($namespaces as $namespace => $typedefs)
            {
                foreach($typedefs as $typedef_name => $typedef_data)
                {
                    if(!isset($typedef_data["description"]))
                        $typedef_data["description"] = "";
                    
                    $typedef = new Element\TypeDef(
                        $typedef_name,
                        $typedef_data["type"],
                        $typedef_data["description"]
                    );

                    $this->symbols->headers[$header]->AddTypeDef(
                        $typedef,
                        $namespace
                    );
                }
            }
        }

        unset($typedef_def);
    }
    
    /**
     * Load all global variable symbols from variables.json
     */
    private function LoadGlobalVariablesFromJson()
    {
        $variables_def = Json::Decode(file_get_contents(
            $this->definitions_path . "variables.json")
        );

        foreach($variables_def as $header => $namespaces)
        {
            foreach($namespaces as $namespace => $variables)
            {
                foreach($variables as $variable_name => $variable_data)
                {
                    if(!isset($variable_data["description"]))
                        $variable_data["description"] = "";
                    
                    $variable = new Element\GlobalVariable(
                        $variable_name,
                        $variable_data["type"],
                        $variable_data["description"]
                    );

                    $this->symbols->headers[$header]->AddGlobalVariable(
                        $variable,
                        $namespace
                    );
                }
            }
        }

        unset($variables_def);
    }
    
    /**
     * Load all function symbols from functions.json
     */
    private function LoadFunctionsFromJson()
    {
        $functions_def = Json::Decode(
            file_get_contents(
                $this->definitions_path . "functions.json"
            )
        );

        foreach($functions_def as $header => $namespaces)
        {
            foreach($namespaces as $namespace => $functions)
            {
                foreach($functions as $function_name => $function_overloads)
                {
                    $function = new Element\FunctionElement($function_name);
                    
                    foreach($function_overloads as $index=>$function_overload)
                    {
                        $overload = new Element\Overload(
                            $function_overload["description"]
                        );
                        
                        $overload->SetReturnType(
                            new Element\ReturnType(
                                $function_overload["return_type"]
                            )
                        );
                        
                        if(isset($function_overload["parameters"]))
                        {
                            foreach($function_overload["parameters"] as $parameter)
                            {
                                if(!isset($parameter["value"]))
                                    $parameter["value"] = "";
                                
                                if(!isset($parameter["description"]))
                                    $parameter["description"] = "";

                                $param = new Element\Parameter(
                                    $parameter["name"], 
                                    $parameter["type"], 
                                    $parameter["value"],
                                    $parameter["description"]
                                );
                                
                                if(isset($parameter["is_array"]))
                                {
                                    $param->is_array = true;
                                }
                                
                                $overload->AddParameter($param);
                            }
                        }
                        
                        $function->AddOverload($overload);
                    }
                    
                    $this->symbols->headers[$header]->AddFunction(
                        $function,
                        $namespace
                    );
                }
            }
        }

        unset($functions_def);
    }
    
    /**
     * Load all class symbols from classes.json, its enumerations from
     * class_enumerations.json and variables from class_variables.json.
     */
    private function LoadClassesFromJson()
    {
        $classes_def = Json::Decode(
            file_get_contents(
                $this->definitions_path . "classes.json"
            )
        );
        
        $enumerations_def = array();
        
        if(file_exists($this->definitions_path . "class_enumerations.json"))
        {
            $enumerations_def = Json::Decode(
                file_get_contents(
                    $this->definitions_path . "class_enumerations.json"
                )
            );
        }
        
        $variables_def = array();
        
        if(file_exists($this->definitions_path . "class_variables.json"))
        {
            $variables_def = Json::Decode(
                file_get_contents(
                    $this->definitions_path . "class_variables.json"
                )
            );
        }

        foreach($classes_def as $header => $namespaces)
        {
            foreach($namespaces as $namespace => $classes)
            {
                foreach($classes as $class_name => $methods)
                {   
                    $class = new Element\ClassElement(
                        $class_name
                    );
                    
                    // Set class details
                    if(isset($methods["_description"]))
                    {
                        $class->description = $methods["_description"];
                        unset($methods["_description"]);
                    }
                    
                    if(isset($methods["_parents"]))
                    {
                        $class->AddParents($methods["_parents"]);
                        unset($methods["_parents"]);
                    }
                    
                    if(isset($methods["_struct"]))
                    {
                        $class->struct = true;
                        unset($methods["_struct"]);
                    }
                    
                    if(isset($methods["_forward_declaration"]))
                    {
                        $class->forward_declaration = true;
                        unset($methods["_forward_declaration"]);
                    }
                    
                    if(isset($methods["_platforms"]))
                    {
                        unset($methods["_platforms"]);
                    }
                    
                    // Add methods
                    foreach($methods as $method_name => $method_overloads)
                    {
                        $method = new Element\FunctionElement($method_name);
                        
                        foreach($method_overloads as $method_overload)
                        {
                            $overload = new Element\Overload(
                                $method_overload["description"]
                            );

                            $overload->SetReturnType(
                                new Element\ReturnType(
                                    $method_overload["return_type"]
                                )
                            );
                            
                            if(isset($method_overload["constant"]))
                                $overload->constant = $method_overload["constant"];

                            if(isset($method_overload["static"]))
                                $overload->static = $method_overload["static"];

                            if(isset($method_overload["virtual"]))
                                $overload->virtual = $method_overload["virtual"];

                            if(isset($method_overload["pure_virtual"]))
                                $overload->pure_virtual = $method_overload["pure_virtual"];

                            if(isset($method_overload["protected"]))
                                $overload->protected = $method_overload["protected"];

                            if(isset($method_overload["parameters"]))
                            {
                                foreach($method_overload["parameters"] as $parameter)
                                {
                                    if(!isset($parameter["value"]))
                                        $parameter["value"] = "";
                                    
                                    if(!isset($parameter["description"]))
                                        $parameter["description"] = "";

                                    $param = new Element\Parameter(
                                        $parameter["name"], 
                                        $parameter["type"], 
                                        $parameter["value"],
                                        $parameter["description"]
                                    );

                                    if(isset($parameter["is_array"]))
                                    {
                                        $param->is_array = true;
                                    }

                                    $overload->AddParameter($param);
                                }
                            }

                            $method->AddOverload($overload);
                        }
                        
                        $class->AddMethod($method);
                    }
                    
                    // Add enumerations
                    if(isset($enumerations_def[$header][$namespace][$class_name]))
                    {
                        foreach($enumerations_def[$header][$namespace][$class_name] as $enumeration_name=>$enumeration_data)
                        {
                            if(!isset($enumeration_data["description"]))
                                $enumeration_data["description"] = "";
                            
                            $class->AddEnumeration(
                                new Element\Enumeration(
                                    $enumeration_name,
                                    $enumeration_data["options"],
                                    $enumeration_data["description"]
                                )
                            );
                        }
                    }
                    
                    // Add variables
                    if(isset($variables_def[$header][$namespace][$class_name]))
                    {
                        foreach($variables_def[$header][$namespace][$class_name] as $variable_name=>$variable_options)
                        {
                            $variable = new Element\ClassVariable(
                                $variable_name, 
                                $variable_options["type"]
                            );
                            
                            if(isset($variable_options["static"]))
                                $variable->static = $variable_options["static"];

                            if(isset($variable_options["mutable"]))
                                $variable->mutable = $variable_options["mutable"];

                            if(isset($variable_options["protected"]))
                                $variable->protected = $variable_options["protected"];

                            if(isset($variable_options["public"]))
                                $variable->public = $variable_options["public"];
                            
                            if(isset($variable_options["description"]))
                                $variable->description = $variable_options["description"];
                            
                            $class->AddVariable($variable);
                        }
                    }
                    
                    $this->symbols->headers[$header]->AddClass(
                        $class,
                        $namespace
                    );
                }
            }
        }

        unset($classes_def);
        unset($enumerations_def);
        unset($variables_def);
    }
    
    /**
     * Sends a signal with message of current task being performed.
     * @param string $message
     */
    private function SendMessage($message)
    {
        $this->signal_data->message = $message;
        
        $this->Send(
            \Peg\Signals\Type\Definitions::IMPORT_MESSAGE,
            $this->signal_data
        );
    }

}

?>
