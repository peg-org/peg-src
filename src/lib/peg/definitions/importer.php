<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Definitions;

use Peg\Utilities\Json;

/**
 * Loads definitions into a symbols table.
 */
class Importer extends \Signals\Signal
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
    public $import_type;
    
    /**
     * Data that is send each time a signal is launched.
     * @var \Peg\Signals\Definitions\ImportMessage 
     */
    private $signal_data;
    
    /**
     * Initializes
     * @param \Peg\Definitions\Symbols $symbols The table to populate.
     * @param string $path The path where resides the cached
     * definition files that represent the library.
     * @param string $import_type
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
        $this->signal_data = new \Peg\Signals\Definitions\ImportMessage;
    }
    
    /**
     * Begin importing definitions to the symbols object specified on constructor.
     */
    public function Start()
    {
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
        
        /*include($this->definitions_path . "functions.php");
        include($this->definitions_path . "classes.php");*/
    }

    private function LoadFromJSON($path)
    {
        $this->definitions_path = rtrim($path, "/\\") . "/";
        $this->import_type = Type::JSON;

        if(file_exists($this->definitions_path . "includes.json"))
        {
            $this->SendMessage(t("Loading includes.json"));
            
            //Populate headers array
            $includes = Json::Decode(
                file_get_contents($this->definitions_path . "includes.json")
            );

            foreach($includes as $header_name => $header_enabled)
            {
                $file = new Element\Header($header_name);
                $file->enabled = $header_enabled;

                $this->symbols->headers[$header_name] = $file;
            }

            unset($includes);
        }

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
    }

    /**
     * Helper function to load all constants as symbol elements into a
     * header namespace.
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
                foreach($constants as $constant_name => $constant_value)
                {
                    $constant = new Element\Constant(
                        $constant_name,
                        $constant_value
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
     * Helper function to load all enumerations as symbol elements into a
     * header namespace.
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
                foreach($enumerations as $enumeration_name => $enumeration_options)
                {
                    $enumeration = new Element\Enumeration(
                        $enumeration_name,
                        $enumeration_options
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
     * Helper function to load all type definitions as symbol elements into a
     * header namespace.
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
                foreach($typedefs as $typedef_name => $typedef_type)
                {
                    $typedef = new Element\TypeDef(
                        $typedef_name,
                        $typedef_type
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
     * Helper function to load all type definitions as symbol elements into a
     * header namespace.
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
                foreach($variables as $variable_name => $variable_type)
                {
                    $variable = new Element\GlobalVariable(
                        $variable_name,
                        $variable_type
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
     * Sends a signal with message of current task being performed.
     * @param string $message
     */
    private function SendMessage($message)
    {
        $this->signal_data->message = $message;
        
        $this->Send(
            \Peg\Signals\Definitions::IMPORT_MESSAGE,
            $this->signal_data
        );
    }

}

?>
