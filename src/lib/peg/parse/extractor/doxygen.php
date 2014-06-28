<?php
/**
 * A doxygen definitions extractor that is divided into different methods.
 *
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Parse\Extractor;

use Peg\Parse\DefinitionsType;
use Peg\Application;
use \DOMDocument;
use \DOMXPath;

/**
 * Implements a doxygen xml extractor of definitions.
 */
class Doxygen extends \Peg\Parse\Extractor
{

    /**
     * Reference to the index.xml file initialized on the Start() method.
     * @var \DOMDocument
     */
    private $document;

    /**
     * Path to the doxygen xml documentation
     * @var string
     */
    private $path;

    /**
     * Initialize this action to be of input type doxygen.
     */
    public function __construct()
    {
        parent::__construct("doxygen");
    }

    /**
     * Initializes the parsing process
     * @param string $path Were the doxygen xml documentation resides.
     */
    public function Start($path)
    {
        $path = rtrim($path, "/\\");

        $this->path = $path;

        $this->document = new DOMDocument();
        $this->document->load($path . "/index.xml");

        $this->ExtractConstants($this->document);
        $this->ExtractEnumerations($this->document);
        $this->ExtractVariables($this->document);
        $this->ExtractTypeDefinitions($this->document);
        $this->ExtractFunctions($this->document);
        $this->ExtractClasses($this->document);

        print "--------------------------------------------------------------\n";

        $this->SaveDefinitions(Application::GetCwd() . "/definitions", DefinitionsType::INCLUDES);
        $this->SaveDefinitions(Application::GetCwd() . "/definitions", DefinitionsType::CONSTANTS);
        $this->SaveDefinitions(Application::GetCwd() . "/definitions", DefinitionsType::ENUMERATIONS);
        $this->SaveDefinitions(Application::GetCwd() . "/definitions", DefinitionsType::VARIABLES);
        $this->SaveDefinitions(Application::GetCwd() . "/definitions", DefinitionsType::TYPE_DEFINITIONS);
        $this->SaveDefinitions(Application::GetCwd() . "/definitions", DefinitionsType::FUNCTIONS);
        $this->SaveDefinitions(Application::GetCwd() . "/definitions", DefinitionsType::CLASSES);
        $this->SaveDefinitions(Application::GetCwd() . "/definitions", DefinitionsType::CLASS_ENUMERATIONS);
        $this->SaveDefinitions(Application::GetCwd() . "/definitions", DefinitionsType::CLASS_VARIABLES);

        print "--------------------------------------------------------------\n";
    }

    /**
     * Extract #defines and anonymous enumerations.
     * @param \DOMDocument $document
     * @param string $namespace
     */
    private function ExtractConstants(\DOMDocument $document, $namespace = "")
    {
        $xpath = new DOMXPath($document);

        $entries = $xpath->evaluate("//compound[@kind='file'] | //compound[@kind='namespace']", $document);

        for($i = 0; $i < $entries->length; $i++)
        {
            $kind = $entries->item($i)->getAttribute("kind");
            $refid = $entries->item($i)->getAttribute("refid");
            $name = $entries->item($i)->childNodes->item(0)->nodeValue;

            if($kind == "namespace")
                $namespace = str_replace("::", "\\", $name);
            else
                $namespace = "\\";

            $file_doc = new DOMDocument();
            $file_doc->load($this->path . "/$refid.xml");

            $file_xpath = new DOMXPath($file_doc);

            $file_members = $file_xpath->evaluate("//memberdef[@kind='define'] | //memberdef[@kind='enum']", $file_doc);

            for($member = 0; $member < $file_members->length; $member++)
            {
                $kind = $file_members->item($member)->getAttribute("kind");

                $location = str_replace(
                    $this->headers_path, "", str_replace(
                        "\\", "/", $file_xpath->evaluate("location", $file_members->item($member))->item(0)->getAttribute("file")
                    )
                );

                $this->includes[$location] = true;

                if($kind == "define")
                {
                    $define_name = $file_xpath->evaluate("name", $file_members->item($member))->item(0)->nodeValue;

                    $define_initializer = true;

                    if(is_object($file_xpath->evaluate("initializer", $file_members->item($member))->item(0)))
                        $define_initializer = $file_xpath->evaluate("initializer", $file_members->item($member))->item(0)->nodeValue;
                    elseif($this->verbose)
                        print t("Warning:") . " " . t("no initializer value for") . " #" . $define_name . "\n";

                    //Skip macro function defines
                    if($file_xpath->evaluate("param", $file_members->item($member))->length > 0)
                    {
                        continue;
                    }

                    //Skip defines used for compiler
                    if($define_name{0} == "_" && $define_name{1} == "_")
                    {
                        continue;
                    }

                    $this->constants[$location][$namespace][$define_name] = "$define_initializer";
                }

                // Also add anonymous enumerations as constants
                elseif($kind == "enum")
                {
                    $enum_name = $file_xpath->evaluate("name", $file_members->item($member))->item(0)->nodeValue;

                    if($enum_name{0} != "@")
                        continue;

                    $enum_values = $file_xpath->evaluate("enumvalue", $file_members->item($member));

                    for($enum_value = 0; $enum_value < $enum_values->length; $enum_value++)
                    {
                        $this->constants[$location][$namespace][$file_xpath->evaluate("name", $enum_values->item($enum_value))->item(0)->nodeValue] = true;
                    }
                }
            }
        }
    }

    /**
     * Extract named enumerations.
     * @param \DOMDocument $document
     * @param string $namespace
     */
    private function ExtractEnumerations(\DOMDocument $document, $namespace = "")
    {
        $xpath = new DOMXPath($document);

        $entries = $xpath->evaluate("//compound[@kind='file'] | //compound[@kind='namespace']", $document);

        for($i = 0; $i < $entries->length; $i++)
        {
            $kind = $entries->item($i)->getAttribute("kind");
            $refid = $entries->item($i)->getAttribute("refid");
            $name = $entries->item($i)->childNodes->item(0)->nodeValue;

            if($kind == "namespace")
                $namespace = str_replace("::", "\\", $name);
            else
                $namespace = "\\";

            $file_doc = new DOMDocument();
            $file_doc->load($this->path . "/$refid.xml");

            $file_xpath = new DOMXPath($file_doc);

            $file_members = $file_xpath->evaluate("//memberdef[@kind='enum']", $file_doc);

            for($member = 0; $member < $file_members->length; $member++)
            {
                $location = str_replace(
                        $this->headers_path, "", str_replace(
                                "\\", "/", $file_xpath->evaluate("location", $file_members->item($member))->item(0)->getAttribute("file")
                        )
                );

                $this->includes[$location] = true;

                $enum_name = $file_xpath->evaluate("name", $file_members->item($member))->item(0)->nodeValue;

                // Just extract named enumerations
                // Anonymous enumerations go on constants.json
                if($enum_name{0} != "@")
                {
                    $this->enumerations[$location][$namespace][$enum_name] = array();

                    $enum_values = $file_xpath->evaluate("enumvalue", $file_members->item($member));

                    for($enum_value = 0; $enum_value < $enum_values->length; $enum_value++)
                    {
                        $this->enumerations[$location][$namespace][$enum_name][] = $file_xpath->evaluate("name", $enum_values->item($enum_value))->item(0)->nodeValue;
                    }
                }
            }
        }
    }

    /**
     * Extract global variables.
     * @param \DOMDocument $document
     * @param string $namespace
     */
    private function ExtractVariables(\DOMDocument $document, $namespace = "")
    {
        $xpath = new DOMXPath($document);

        $entries = $xpath->evaluate("//compound[@kind='file'] | //compound[@kind='namespace']", $document);

        for($i = 0; $i < $entries->length; $i++)
        {
            $kind = $entries->item($i)->getAttribute("kind");
            $refid = $entries->item($i)->getAttribute("refid");
            $name = $entries->item($i)->childNodes->item(0)->nodeValue;

            if($kind == "namespace")
                $namespace = str_replace("::", "\\", $name);
            else
                $namespace = "\\";

            $file_doc = new DOMDocument();
            $file_doc->load($this->path . "/$refid.xml");

            $file_xpath = new DOMXPath($file_doc);

            $file_members = $file_xpath->evaluate("//memberdef[@kind='variable']", $file_doc);

            for($member = 0; $member < $file_members->length; $member++)
            {
                $location = str_replace(
                        $this->headers_path, "", str_replace(
                                "\\", "/", $file_xpath->evaluate("location", $file_members->item($member))->item(0)->getAttribute("file")
                        )
                );

                $this->includes[$location] = true;

                $global_variable_name = $file_xpath->evaluate("name", $file_members->item($member))->item(0)->nodeValue;
                $global_variable_type = $file_xpath->evaluate("type", $file_members->item($member))->item(0)->nodeValue;

                $this->variables[$location][$namespace][$global_variable_name] = str_replace(array(" *", " &"), array("*", "&"), $global_variable_type);
            }
        }
    }

    /**
     * Extract typedef declared on the source files.
     * @param \DOMDocument $document
     * @param type $namespace
     */
    private function ExtractTypeDefinitions(\DOMDocument $document, $namespace = "")
    {
        $xpath = new DOMXPath($document);

        $entries = $xpath->evaluate("//compound[@kind='file'] | //compound[@kind='namespace']", $document);

        for($i = 0; $i < $entries->length; $i++)
        {
            $kind = $entries->item($i)->getAttribute("kind");
            $refid = $entries->item($i)->getAttribute("refid");
            $name = $entries->item($i)->childNodes->item(0)->nodeValue;

            if($kind == "namespace")
                $namespace = str_replace("::", "\\", $name);
            else
                $namespace = "\\";

            $file_doc = new DOMDocument();
            $file_doc->load($this->path . "/$refid.xml");

            $file_xpath = new DOMXPath($file_doc);

            $file_members = $file_xpath->evaluate("//memberdef[@kind='typedef']", $file_doc);

            for($member = 0; $member < $file_members->length; $member++)
            {
                $location = str_replace(
                    $this->headers_path, "", str_replace(
                        "\\", "/", $file_xpath->evaluate("location", $file_members->item($member))->item(0)->getAttribute("file")
                    )
                );

                $this->includes[$location] = true;

                $typedef_name = $file_xpath->evaluate("name", $file_members->item($member))->item(0)->nodeValue;
                $typedef_type = $file_xpath->evaluate("type", $file_members->item($member))->item(0)->nodeValue;

                $this->type_definitions[$location][$namespace][$typedef_name] = $typedef_type;
            }
        }
    }

    /**
     * Extract functions its overloads and parameters information.
     * @param \DOMDocument $document
     * @param string $namespace
     */
    private function ExtractFunctions(\DOMDocument $document, $namespace = "")
    {
        $xpath = new DOMXPath($document);

        $entries = $xpath->evaluate("//compound[@kind='file'] | //compound[@kind='namespace']", $document);

        for($i = 0; $i < $entries->length; $i++)
        {
            $kind = $entries->item($i)->getAttribute("kind");
            $refid = $entries->item($i)->getAttribute("refid");
            $name = $entries->item($i)->childNodes->item(0)->nodeValue;

            if($kind == "namespace")
                $namespace = str_replace("::", "\\", $name);
            else
                $namespace = "\\";

            $file_doc = new DOMDocument();
            $file_doc->load($this->path . "/$refid.xml");

            $file_xpath = new DOMXPath($file_doc);

            $file_members = $file_xpath->evaluate("//memberdef[@kind='function']", $file_doc);

            for($member = 0; $member < $file_members->length; $member++)
            {
                $location = str_replace(
                        $this->headers_path, "", str_replace(
                                "\\", "/", $file_xpath->evaluate("location", $file_members->item($member))->item(0)->getAttribute("file")
                        )
                );

                $this->includes[$location] = true;

                $function_name = $file_xpath->evaluate("name", $file_members->item($member))->item(0)->nodeValue;

                $function_type = $file_xpath->evaluate("type", $file_members->item($member))->item(0)->nodeValue;
                $function_brief_description = trim($file_xpath->evaluate("briefdescription", $file_members->item($member))->item(0)->nodeValue);
                $function_type = str_replace(array(" *", " &"), array("*", "&"), $function_type);

                //Check all function parameters
                $function_parameters = $file_xpath->evaluate("param", $file_members->item($member));

                $parameters = array();

                for($function_parameter = 0; $function_parameter < $function_parameters->length; $function_parameter++)
                {
                    $parameters[$function_parameter] = array();

                    $parameters[$function_parameter]["name"] = "param" . $function_parameter;

                    if(is_object($file_xpath->evaluate("declname", $function_parameters->item($function_parameter))->item(0)))
                    {
                        $parameters[$function_parameter]["name"] = $file_xpath->evaluate("declname", $function_parameters->item($function_parameter))->item(0)->nodeValue;
                    }
                    else
                    {
                        if($this->verbose)
                            print t("Skipping:") . " " . t("function") . " '" . $function_name . "' " . t("seems to be a macro with undocumented parameter types.") . "\n";

                        continue 2;
                    }

                    $parameters[$function_parameter]["type"] = str_replace(array(" *", " &"), array("*", "&"), $file_xpath->evaluate("type", $function_parameters->item($function_parameter))->item(0)->nodeValue);

                    //Check if parameter is array
                    if($file_xpath->evaluate("array", $function_parameters->item($function_parameter))->length > 0)
                    {
                        $array_value = $file_xpath->evaluate("array", $function_parameters->item($function_parameter))->item(0)->nodeValue;

                        if($array_value == "[]")
                        {
                            $parameters[$function_parameter]["is_array"] = true;
                        }
                        else
                        {
                            $parameters[$function_parameter]["extra"] = $array_value;
                        }
                    }

                    if($file_xpath->evaluate("defval", $function_parameters->item($function_parameter))->length > 0)
                    {
                        $parameters[$function_parameter]["value"] = $file_xpath->evaluate("defval", $function_parameters->item($function_parameter))->item(0)->nodeValue;
                    }
                }

                if(count($parameters) > 0)
                {
                    $this->functions[$location][$namespace][$function_name][] = array(
                        "return_type"       => $function_type,
                        "brief_description" => $function_brief_description,
                        "parameters"        => $parameters
                    );
                }
                else
                {
                    $this->functions[$location][$namespace][$function_name][] = array(
                        "return_type"       => $function_type,
                        "brief_description" => $function_brief_description
                    );
                }
            }
        }
    }

    /**
     * Extracts classes and its properties, methods, methods overloads, paramter
     * details, enumerations and member variables.
     * @param \DOMDocument $document
     * @param string $namespace
     */
    private function ExtractClasses(\DOMDocument $document, $namespace = "")
    {
        $xpath = new DOMXPath($document);

        $entries = $xpath->evaluate("//compound[@kind='class'] | //compound[@kind='struct']", $document);

        for($i = 0; $i < $entries->length; $i++)
        {
            $kind = $entries->item($i)->getAttribute("kind");
            $refid = $entries->item($i)->getAttribute("refid");
            $name = $entries->item($i)->childNodes->item(0)->nodeValue;

            // Check if class is part of a namespace
            $name_parts = explode("::", $name);

            if(count($name_parts) > 0)
            {
                $name = $name_parts[count($name_parts) - 1];
                unset($name_parts[count($name_parts) - 1]);
                $namespace = implode("\\", $name_parts);
            }
            else
            {
                $namespace = "\\";
            }

            $class_doc = new DOMDocument();
            $class_doc->load($this->path . "/$refid.xml");

            $class_xpath = new DOMXPath($class_doc);

            // Retreive include file
            $header_file = $class_xpath->evaluate("//includes", $class_doc)->item(0)->nodeValue;

            $this->includes[$header_file] = true;

            $this->classes[$header_file][$namespace][$name] = array();

            if($kind == "struct")
            {
                $this->classes[$header_file][$namespace][$name]["_struct"] = true;
            }

            // Check from which classes this one inherits
            $class_inherits = $class_xpath->evaluate("//inheritancegraph", $class_doc);

            if($class_inherits->length > 0)
            {
                $class_inherit_nodes = $class_xpath->evaluate("node", $class_inherits->item(0));
                for($node = 0; $node < $class_inherit_nodes->length; $node++)
                {
                    if($class_inherit_nodes->item($node)->childNodes->item(1)->nodeValue == $name)
                    {
                        $class_inherit_childnodes = $class_xpath->evaluate("childnode", $class_inherit_nodes->item($node));

                        if($class_inherit_nodes->length > 0)
                        {
                            for($childnode = 0; $childnode < $class_inherit_childnodes->length; $childnode++)
                            {
                                $parent_class_id = $class_inherit_childnodes->item($childnode)->attributes->getNamedItem("refid")->value;
                                $parent_class_node = $class_xpath->evaluate('//node[@id="' . $parent_class_id . '"]', $class_doc);

                                if($parent_class_node->length > 0)
                                {
                                    $parent_class_name = $parent_class_node->item(0)->childNodes->item(1)->nodeValue;
                                    $this->classes[$header_file][$namespace][$name]["_implements"][] = $parent_class_name;
                                }
                            }
                        }

                        break;
                    }
                }
            }

            // If class is implemented only on some platforms we store them
            $class_availability = $class_xpath->evaluate("/doxygen/compounddef/detaileddescription/para/onlyfor", $class_doc);
            if($class_availability->length > 0)
            {
                $this->classes[$header_file][$namespace][$name]["_platforms"] = explode(",", $class_availability->item(0)->nodeValue);
            }

            // Get the member functions of the class
            $class_member = $class_xpath->evaluate("//memberdef", $class_doc);
            for($member = 0; $member < $class_member->length; $member++)
            {

                // Class functions
                if($class_member->item($member)->getAttribute("kind") == "function")
                {
                    $function_name = $class_xpath->evaluate("name", $class_member->item($member))->item(0)->nodeValue;

                    // skip destructor
                    if($function_name{0} == "~")
                        continue;

                    // Store all function properties
                    $function = array();

                    // If method is implemented only on some platforms we store them
                    $member_availability = $class_xpath->evaluate("detaileddescription/para/onlyfor", $class_member->item($member));
                    if($member_availability->length > 0)
                    {
                        $platforms = explode(",", $member_availability->item(0)->nodeValue);
                    }

                    // Check if member is constant
                    if($class_member->item($member)->getAttribute("const") == "yes")
                        $function["constant"] = true;

                    // Check if member is static
                    if($class_member->item($member)->getAttribute("static") == "yes")
                        $function["static"] = true;

                    // Check if member is virtual
                    if($class_member->item($member)->getAttribute("virt") == "virtual")
                        $function["virtual"] = true;

                    // Check if member is pure virtual
                    if($class_member->item($member)->getAttribute("virt") == "pure-virtual")
                        $function["pure_virtual"] = true;

                    if($class_member->item($member)->getAttribute("prot") == "protected")
                        $function["protected"] = true;

                    // Retrieve member type
                    $function["return_type"] = str_replace(array(" *", " &"), array("*", "&"), $class_xpath->evaluate("type", $class_member->item($member))->item(0)->nodeValue);

                    // Retrieve brief description
                    $function["brief_description"] = trim($class_xpath->evaluate("briefdescription", $class_member->item($member))->item(0)->nodeValue);

                    // Store type base class to later check which bases classes aren't documented
                    if("" . stristr($function["return_type"], "Base") . "" != "")
                    {
                        $base_classes[str_replace(array("&", " ", "*", "const"), "", $function["return_type"])] = 1;
                    }

                    // Initialize array that will store parameters
                    $parameters = array();

                    // Check all member parameters
                    $function_parameters = $class_xpath->evaluate("param", $class_member->item($member));
                    if($function_parameters->length > 0)
                    {
                        for($parameter = 0; $parameter < $function_parameters->length; $parameter++)
                        {
                            $parameters[$parameter]["name"] = "param" . $parameter;

                            if(is_object($class_xpath->evaluate("declname", $function_parameters->item($parameter))->item(0)))
                                $parameters[$parameter]["name"] = $class_xpath->evaluate("declname", $function_parameters->item($parameter))->item(0)->nodeValue;
                            elseif($this->verbose)
                                print t("Warning:") . " " . t("parameter") . " " . ($parameter + 1) . " " . t("on method") . " " . $name . "::" . $function_name . " " . t("does not have a name.") . "\n";

                            $parameters[$parameter]["type"] = str_replace(array(" *", " &"), array("*", "&"), $class_xpath->evaluate("type", $function_parameters->item($parameter))->item(0)->nodeValue);

                            // Check if parameter is array
                            if($class_xpath->evaluate("array", $function_parameters->item($parameter))->length > 0)
                            {
                                $array_value = $class_xpath->evaluate("array", $function_parameters->item($parameter))->item(0)->nodeValue;

                                if($array_value == "[]")
                                {
                                    $parameters[$parameter]["is_array"] = true;
                                }
                                else
                                {
                                    //TODO: Handle non bracket array elements
                                    $parameters[$parameter]["extra"] = $array_value;
                                }
                            }

                            // Store type base class to later check which bases classes aren't documented
                            $the_type = str_replace(array(" *", " &"), array("*", "&"), $class_xpath->evaluate("type", $function_parameters->item($parameter))->item(0)->nodeValue);
                            if("" . stristr($the_type, "Base") . "" != "")
                            {
                                $base_classes[str_replace(array("&", " ", "*", "const"), "", $the_type)] = 1;
                            }

                            if($class_xpath->evaluate("defval", $function_parameters->item($parameter))->length > 0)
                            {
                                $parameters[$parameter]["value"] = $class_xpath->evaluate("defval", $function_parameters->item($parameter))->item(0)->nodeValue;
                            }
                        }
                    }

                    if(count($parameters) > 0)
                    {
                        $function["parameters"] = $parameters;
                    }

                    if(!isset($this->classes[$header_file][$namespace][$name][$function_name]))
                        $this->classes[$header_file][$namespace][$name][$function_name] = array();

                    $this->classes[$header_file][$namespace][$name][$function_name][] = $function;
                }

                //Class enumerations
                elseif($class_member->item($member)->getAttribute("kind") == "enum")
                {
                    $enum_name = $class_xpath->evaluate("name", $class_member->item($member))->item(0)->nodeValue;

                    //Skip unnamed enums TODO: implement them
                    if($enum_name{0} == "@")
                        continue;

                    $this->class_enumerations[$header_file][$namespace][$name][$enum_name] = array();

                    $enum_values = $class_xpath->evaluate("enumvalue", $class_member->item($member));

                    for($enum_value = 0; $enum_value < $enum_values->length; $enum_value++)
                    {
                        $this->class_enumerations[$header_file][$namespace][$name][$enum_name][] = $class_xpath->evaluate("name", $enum_values->item($enum_value))->item(0)->nodeValue;
                    }
                }
                //Class variables
                elseif($class_member->item($member)->getAttribute("kind") == "variable")
                {
                    $variable_name = $class_xpath->evaluate("name", $class_member->item($member))->item(0)->nodeValue;

                    $variable = array();

                    $variable["type"] = $class_xpath->evaluate("type", $class_member->item($member))->item(0)->nodeValue;

                    if($class_member->item($member)->getAttribute("static") != "no")
                        $variable["static"] = true;

                    if($class_member->item($member)->getAttribute("mutable") != "no")
                        $variable["mutable"] = true;

                    if($class_member->item($member)->getAttribute("prot") == "protected")
                        $variable["protected"] = true;

                    if($class_member->item($member)->getAttribute("prot") == "public")
                        $variable["public"] = true;

                    $this->class_variables[$header_file][$namespace][$name][$variable_name] = $variable;
                }
                //Store not handled members of the class
                else
                {
                    //$class_not_handle[$class_member->item($member)->getAttribute("kind")]++;
                }
            }
        }
    }

}

?>
