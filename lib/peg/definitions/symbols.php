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
     * Array of include files
     * @var array
     */
    private $includes;

    /**
     * Array of constants
     * @var array
     */
    private $constants;

    /**
     * Array of enumerations
     * @var array
     */
    private $enumerations;

    /**
     * Array of functions
     * @var array
     */
    private $functions;

    /**
     * Array of global variables
     * @var array
     */
    private $variables;

    /**
     * Array of classes
     * @var array
     */
    private $classes;

    /**
     * Array of class enumerations
     * @var array
     */
    private $classes_enunmerations;

    /**
     * Array of class variables
     * @var array
     */
    private $classes_variables;

    /**
     * Array of type definitions
     * @var array
     */
    private $type_definitions;

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
     * Initializes
     * @param type $path The path where resides the definition files that represent
     * the library.
     */
    public function __construct($path = null)
    {
        $this->includes = array();
        $this->constants = array();
        $this->enumerations = array();
        $this->functions = array();
        $this->variables = array();
        $this->classes = array();
        $this->classes_enunmerations = array();
        $this->classes_variables = array();
        $this->type_definitions = array();
        
        $this->headers = array();

        $this->Load($path);
    }

    public function Load($path = null)
    {
        if($path)
        {
            $this->path = rtrim($path, "/\\") . "/";

            //Load definition files
            $this->includes = Json::Decode(file_get_contents($this->path . "includes.json"));
            $this->constants = Json::Decode(file_get_contents($this->path . "constants.json"));
            $this->enumerations = Json::Decode(file_get_contents($this->path . "enumerations.json"));
            $this->functions = Json::Decode(file_get_contents($this->path . "functions.json"));
            $this->variables = Json::Decode(file_get_contents($this->path . "variables.json"));
            $this->classes = Json::Decode(file_get_contents($this->path . "classes.json"));
            $this->classes_enunmerations = Json::Decode(file_get_contents($this->path . "class_enumerations.json"));
            $this->classes_variables = Json::Decode(file_get_contents($this->path . "class_variables.json"));
            $this->type_definitions = Json::Decode(file_get_contents($this->path . "type_definitions.json"));

            //Populate files array
            foreach($this->includes as $file_name => $file_enabled)
            {
                $file = new Element\Header($file_name);
                $file->enabled = $file_enabled;

                $this->LoadConstants($file);

                $this->headers[$file_name] = $file;
            }
        }
    }

    /**
     * Helper function to load all constants as symbol elements into a
     * file tree namespaces.
     * @param \Peg\Definitions\Element\Header $file
     * @return \Peg\Definitions\Element\Constant
     */
    private function LoadConstants($file)
    {
        if(isset($this->constants[$file->name]))
        {
            foreach($this->constants[$file->name] as $namespace => $constants)
            {
                foreach($constants as $constant_name => $constant_value)
                {
                    $constant = new \Peg\Definitions\Element\Constant;

                    $constant->name = $constant_name;
                    $constant->value = $constant_value;
                    $constant->namespace = $namespace;

                    $file->AddConstant($namespace, $constant);
                }
            }
        }
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
     * Add Constant to the symbols table.
     * @param \Peg\Definitions\Element\Constant $constant
     * @param string $header Name of header file where the constant resides.
     * @param string $namespace If omitted the constant is added at a global scope.
     */
    public function AddConstant(\Peg\Definitions\Element\Constant $constant, $header, $namespace="\\")
    {
        $this->AddHeader($header);
        
        $this->headers[$header]->AddConstant($namespace, $constant);
    }
    
    /**
     * Add Constant to the symbols table.
     * @param \Peg\Definitions\Element\Enumeration $enumeration
     * @param string $header Name of header file where the constant resides.
     * @param string $namespace If omitted the constant is added at a global scope.
     */
    public function AddEnumeration(\Peg\Definitions\Element\Enumeration $enumeration, $header, $namespace="\\")
    {
        $this->AddHeader($header);
        
        $this->headers[$header]->AddEnumeration($namespace, $enumeration);
    }

}

?>
