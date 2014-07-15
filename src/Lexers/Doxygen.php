<?php
/**
 * A doxygen definitions extractor that is divided into different methods.
 *
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib\Lexers;

use \DOMDocument;
use \DOMXPath;

/**
 * Implements a doxygen xml extractor of definitions.
 */
class Doxygen extends \Peg\Lib\Lexers\Base
{

    /**
     * Reference to the index.xml file initialized on the Start() method.
     * @var \DOMDocument
     */
    private $document;

    /**
     * Initializes the parsing process
     * @param string $path Were the doxygen xml documentation resides.
     */
    public function Start()
    {
        $this->definitions_path = rtrim($this->definitions_path, "/\\");

        $this->document = new DOMDocument();
        $this->document->load($this->definitions_path . "/index.xml");

        $this->ExtractConstants($this->document);
        $this->ExtractEnumerations($this->document);
        $this->ExtractVariables($this->document);
        $this->ExtractTypeDefinitions($this->document);
        $this->ExtractFunctions($this->document);
        $this->ExtractClasses($this->document);
    }

    /**
     * Extract #defines and anonymous enumerations.
     * @param \DOMDocument $document
     * @param string $namespace
     */
    private function ExtractConstants(\DOMDocument $document, $namespace = "")
    {
        $xpath = new DOMXPath($document);

        $entries = $xpath->evaluate(
            "//compound[@kind='file'] | //compound[@kind='namespace']", 
            $document
        );

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
            $file_doc->load($this->definitions_path . "/$refid.xml");

            $file_xpath = new DOMXPath($file_doc);

            $file_members = $file_xpath->evaluate(
                "//memberdef[@kind='define'] | //memberdef[@kind='enum']", 
                $file_doc
            );

            for($member = 0; $member < $file_members->length; $member++)
            {
                $kind = $file_members->item($member)->getAttribute("kind");

                $location = str_replace(
                    $this->headers_path, 
                    "", 
                    str_replace(
                        "\\", 
                        "/", 
                        $file_xpath->evaluate(
                            "location", 
                            $file_members->item($member)
                        )->item(0)->getAttribute("file")
                    )
                );

                if($kind == "define")
                {
                    $define_name = $file_xpath->evaluate(
                        "name", 
                        $file_members->item($member)
                    )->item(0)->nodeValue;
                    
                    $define_description = $file_xpath->evaluate(
                        "briefdescription", 
                        $file_members->item($member)
                    )->item(0)->nodeValue;

                    $define_initializer = true;

                    if(is_object($file_xpath->evaluate("initializer", $file_members->item($member))->item(0)))
                        $define_initializer = $file_xpath->evaluate(
                            "initializer", 
                            $file_members->item($member)
                        )->item(0)->nodeValue;
                    
                    else
                        $this->SendMessage(
                            t("Warning:") . " " 
                            . t("no initializer value for") 
                            . " #" . $define_name 
                        );

                    // Skip macro function defines
                    if($file_xpath->evaluate("param", $file_members->item($member))->length > 0)
                    {
                        continue;
                    }

                    // Skip defines used for compiler
                    if($define_name{0} == "_" && $define_name{1} == "_")
                    {
                        continue;
                    }

                    $constant = new \Peg\Lib\Definitions\Element\Constant(
                        $define_name,
                        $define_initializer,
                        $define_description
                    );
                    
                    $this->symbols->AddConstant(
                        $constant, 
                        $location, 
                        $namespace
                    );
                }

                // Also add anonymous enumerations as constants
                elseif($kind == "enum")
                {
                    $enum_name = $file_xpath->evaluate(
                        "name", 
                        $file_members->item($member)
                    )->item(0)->nodeValue;
                    
                    $enum_description = $file_xpath->evaluate(
                        "briefdescription", 
                        $file_members->item($member)
                    )->item(0)->nodeValue;

                    // Skip non anonymous enumerations
                    if($enum_name{0} != "@")
                        continue;

                    $enum_values = $file_xpath->evaluate(
                        "enumvalue", 
                        $file_members->item($member)
                    );

                    for($enum_value = 0; $enum_value < $enum_values->length; $enum_value++)
                    {
                        $enum_option = $file_xpath->evaluate(
                            "name", 
                            $enum_values->item($enum_value)
                        )->item(0)->nodeValue;
                        
                        $constant = new \Peg\Lib\Definitions\Element\Constant(
                            $enum_option,
                            1,
                            $enum_description
                        );

                        $this->symbols->AddConstant(
                            $constant, 
                            $location, 
                            $namespace
                        );
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

        $entries = $xpath->evaluate(
            "//compound[@kind='file'] | //compound[@kind='namespace']", 
            $document
        );

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
            $file_doc->load($this->definitions_path . "/$refid.xml");

            $file_xpath = new DOMXPath($file_doc);

            $file_members = $file_xpath->evaluate(
                "//memberdef[@kind='enum']", 
                $file_doc
            );

            for($member = 0; $member < $file_members->length; $member++)
            {
                $location = str_replace(
                    $this->headers_path, 
                    "", 
                    str_replace(
                        "\\", 
                        "/", 
                        $file_xpath->evaluate(
                            "location", 
                            $file_members->item($member)
                        )->item(0)->getAttribute("file")
                    )
                );

                $enum_name = $file_xpath->evaluate(
                    "name", 
                    $file_members->item($member)
                )->item(0)->nodeValue;
                
                $enum_description = $file_xpath->evaluate(
                    "briefdescription", 
                    $file_members->item($member)
                )->item(0)->nodeValue;

                // Just extract named enumerations
                // Anonymous enumerations go on constants.json
                if($enum_name{0} != "@")
                {
                    $enum_values = $file_xpath->evaluate(
                        "enumvalue", 
                        $file_members->item($member)
                    );
                    
                    $enum_options = array();

                    for(
                        $enum_value = 0; 
                        $enum_value < $enum_values->length; 
                        $enum_value++
                    )
                    {
                        $enum_options[] = $file_xpath->evaluate(
                            "name", 
                            $enum_values->item($enum_value)
                        )->item(0)->nodeValue;
                    }
                    
                    $enumeration = new \Peg\Lib\Definitions\Element\Enumeration(
                        $enum_name,
                        $enum_options,
                        $enum_description
                    );
                    
                    $this->symbols->AddEnumeration(
                        $enumeration, 
                        $location, 
                        $namespace
                    );
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

        $entries = $xpath->evaluate(
            "//compound[@kind='file'] | //compound[@kind='namespace']", 
            $document
        );

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
            $file_doc->load($this->definitions_path . "/$refid.xml");

            $file_xpath = new DOMXPath($file_doc);

            $file_members = $file_xpath->evaluate(
                "//memberdef[@kind='variable']", 
                $file_doc
            );

            for($member = 0; $member < $file_members->length; $member++)
            {
                $location = str_replace(
                    $this->headers_path, 
                    "", 
                    str_replace(
                        "\\", 
                        "/", 
                        $file_xpath->evaluate(
                            "location", 
                            $file_members->item($member)
                        )->item(0)->getAttribute("file")
                    )
                );

                $global_variable_name = $file_xpath->evaluate(
                    "name", 
                    $file_members->item($member)
                )->item(0)->nodeValue;
                
                $global_variable_type = $file_xpath->evaluate(
                    "type", 
                    $file_members->item($member)
                )->item(0)->nodeValue;
                
                $global_variable_description = $file_xpath->evaluate(
                    "briefdescription", 
                    $file_members->item($member)
                )->item(0)->nodeValue;

                $variable = new \Peg\Lib\Definitions\Element\GlobalVariable(
                    $global_variable_name, 
                    str_replace(
                        array(" *", " &"), 
                        array("*", "&"), 
                        $global_variable_type
                    ),
                    $global_variable_description
                );
                
                $this->symbols->AddGlobalVar($variable, $location, $namespace);
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

        $entries = $xpath->evaluate(
            "//compound[@kind='file'] | //compound[@kind='namespace']", 
            $document
        );

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
            $file_doc->load($this->definitions_path . "/$refid.xml");

            $file_xpath = new DOMXPath($file_doc);

            $file_members = $file_xpath->evaluate(
                "//memberdef[@kind='typedef']", 
                $file_doc
            );

            for($member = 0; $member < $file_members->length; $member++)
            {
                $location = str_replace(
                    $this->headers_path, 
                    "", 
                    str_replace(
                        "\\", 
                        "/", 
                        $file_xpath->evaluate(
                            "location", 
                            $file_members->item($member)
                        )->item(0)->getAttribute("file")
                    )
                );

                $this->includes[$location] = true;

                $typedef_name = $file_xpath->evaluate(
                    "name", 
                    $file_members->item($member)
                )->item(0)->nodeValue;
                
                $typedef_type = $file_xpath->evaluate(
                    "type", 
                    $file_members->item($member)
                )->item(0)->nodeValue;
                
                $typedef_description = $file_xpath->evaluate(
                    "briefdescription", 
                    $file_members->item($member)
                )->item(0)->nodeValue;

                $typedef = new \Peg\Lib\Definitions\Element\TypeDef(
                    $typedef_name, 
                    $typedef_type, 
                    $typedef_description
                );
                
                $this->symbols->AddTypeDef(
                    $typedef, 
                    $location, 
                    $namespace
                );
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

        $entries = $xpath->evaluate(
            "//compound[@kind='file'] | //compound[@kind='namespace']", 
            $document
        );
        
        // First we store all functions and their overloads, after
        // we use this array to create FunctionElement objects and add
        // them to the symbols object.
        $functions = array();

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
            $file_doc->load($this->definitions_path . "/$refid.xml");

            $file_xpath = new DOMXPath($file_doc);

            $file_members = $file_xpath->evaluate(
                "//memberdef[@kind='function']", 
                $file_doc
            );

            for($member = 0; $member < $file_members->length; $member++)
            {
                $location = str_replace(
                    $this->headers_path, "", str_replace(
                        "\\", 
                        "/", 
                        $file_xpath->evaluate(
                            "location", 
                            $file_members->item($member)
                        )->item(0)->getAttribute("file")
                    )
                );

                $function_name = $file_xpath->evaluate(
                    "name", 
                    $file_members->item($member)
                )->item(0)->nodeValue;

                $function_type = $file_xpath->evaluate(
                    "type", 
                    $file_members->item($member)
                )->item(0)->nodeValue;
                
                $function_description = trim(
                    $file_xpath->evaluate(
                        "briefdescription", 
                        $file_members->item($member)
                    )->item(0)->nodeValue
                );
                
                $function_type = str_replace(
                    array(" *", " &"), 
                    array("*", "&"), 
                    $function_type
                );

                $function_overload = new \Peg\Lib\Definitions\Element\Overload(
                    $function_description
                );
                
                $function_overload->SetReturnType(
                    new \Peg\Lib\Definitions\Element\ReturnType(
                        $function_type
                    )
                );
                
                // Retrieve all function parameters
                $function_parameters = $file_xpath->evaluate(
                    "param", 
                    $file_members->item($member)
                );

                $parameters = array();

                for(
                    $function_parameter = 0; 
                    $function_parameter < $function_parameters->length; 
                    $function_parameter++
                )
                {
                    // Default generated param name in case it doesn't have one.
                   $param_name = "param" . $function_parameter;

                    if(is_object($file_xpath->evaluate("declname", $function_parameters->item($function_parameter))->item(0)))
                    {
                        $param_name = $file_xpath->evaluate(
                            "declname", 
                            $function_parameters->item(
                                $function_parameter
                            ))->item(0)->nodeValue
                        ;
                    }
                    else
                    {
                        $this->SendMessage(t("Skipping:") . " " . t("function") 
                            . " '" . $function_name . "' " 
                            . t("seems to be a macro with undocumented parameter types.") 
                        );

                        continue 2;
                    }

                    $param_type = str_replace(
                        array(" *", " &"), 
                        array("*", "&"), 
                        $file_xpath->evaluate(
                            "type", 
                            $function_parameters->item($function_parameter)
                        )->item(0)->nodeValue
                    );

                    // Check if parameter is array
                    $param_array = false;
                    if($file_xpath->evaluate("array", $function_parameters->item($function_parameter))->length > 0)
                    {
                        $array_value = $file_xpath->evaluate(
                            "array", 
                            $function_parameters->item(
                                $function_parameter)
                            )->item(0)->nodeValue
                        ;

                        if($array_value == "[]")
                        {
                            $param_array = true;
                        }
                        else
                        {
                            //$parameters[$function_parameter]["extra"] = $array_value;
                        }
                    }

                    // Get default value
                    $param_value = "";
                    if($file_xpath->evaluate("defval", $function_parameters->item($function_parameter))->length > 0)
                    {
                        $param_value = $file_xpath->evaluate(
                            "defval", 
                            $function_parameters->item(
                                $function_parameter)
                            )->item(0)->nodeValue
                        ;
                    }
                    
                    $parameter = new \Peg\Lib\Definitions\Element\Parameter(
                        $param_name, 
                        $param_type, 
                        $param_value
                    );
                    
                    $parameter->is_array = $param_array;
                    
                    $function_overload->AddParameter($parameter);
                }

                $functions[$function_name][] = [
                    $location,
                    $namespace,
                    $function_overload
                ];
            }
        }
        
        foreach($functions as $function_name=>$function_overloads)
        {
            $function = new \Peg\Lib\Definitions\Element\FunctionElement(
                $function_name
            );
            
            $header = "";
            $namespace = "";
            
            foreach($function_overloads as $overload)
            {
                if(!$header)
                {
                    $header = $overload[0];
                    $namespace = $overload[1];
                }
                
                $function->AddOverload($overload[2]);
            }
            
            $this->symbols->AddFunction(
                $function, 
                $header,
                $namespace
            );
        }
    }

    /**
     * Extracts classes and its properties, methods, methods overloads, paramter
     * details, enumerations and member variables.
     * @param \DOMDocument $document
     * @param string $namespace
     * @todo Also extract a class unnamed enums and handle array parameters
     * with non bracket array elements.
     */
    private function ExtractClasses(\DOMDocument $document, $namespace = "")
    {
        $xpath = new DOMXPath($document);

        $entries = $xpath->evaluate(
            "//compound[@kind='class'] | //compound[@kind='struct']", 
            $document
        );

        for($i = 0; $i < $entries->length; $i++)
        {
            $kind = $entries->item($i)->getAttribute("kind");
            $refid = $entries->item($i)->getAttribute("refid");
            
            $class_name = $entries->item($i)->childNodes->item(0)->nodeValue;

            // Check if class is part of a namespace
            $name_parts = explode("::", $class_name);

            if(count($name_parts) > 0)
            {
                $class_name = $name_parts[count($name_parts) - 1];
                unset($name_parts[count($name_parts) - 1]);
                $namespace = implode("\\", $name_parts);
            }
            else
            {
                $namespace = "\\";
            }

            $class_doc = new DOMDocument();
            $class_doc->load($this->definitions_path . "/$refid.xml");

            $class_xpath = new DOMXPath($class_doc);
            
            // Retreive class description
            $class_description = $class_xpath->evaluate(
                "/doxygen/compounddef/briefdescription", 
                $class_doc
            )->item(0)->nodeValue;

            // Retreive include file
            $header_file = $class_xpath->evaluate(
                "//includes", 
                $class_doc
            )->item(0)->nodeValue;

            $class_is_struct = false;
            if($kind == "struct")
            {
                $class_is_struct = true;
            }

            // Check from which classes this one inherits
            $class_parents = array();
            
            $class_inherits = $class_xpath->evaluate(
                "//inheritancegraph", 
                $class_doc
            );

            if($class_inherits->length > 0)
            {
                $class_inherit_nodes = $class_xpath->evaluate(
                    "node", 
                    $class_inherits->item(0)
                );
                
                for($node = 0; $node < $class_inherit_nodes->length; $node++)
                {
                    if($class_inherit_nodes->item($node)->childNodes->item(1)->nodeValue == $class_name)
                    {
                        $class_inherit_childnodes = $class_xpath->evaluate(
                            "childnode", 
                            $class_inherit_nodes->item($node)
                        );

                        if($class_inherit_nodes->length > 0)
                        {
                            for(
                                $childnode = 0; 
                                $childnode < $class_inherit_childnodes->length; 
                                $childnode++
                            )
                            {
                                $parent_class_id = $class_inherit_childnodes
                                    ->item($childnode)
                                    ->attributes
                                    ->getNamedItem("refid")
                                    ->value
                                ;
                                
                                $parent_class_node = $class_xpath->evaluate(
                                    '//node[@id="' . $parent_class_id . '"]', 
                                    $class_doc
                                );

                                if($parent_class_node->length > 0)
                                {
                                    $parent_class_name = $parent_class_node
                                        ->item(0)
                                        ->childNodes
                                        ->item(1)
                                        ->nodeValue
                                    ;
                                    
                                    $class_parents[] = str_replace(
                                        "::",
                                        "\\",
                                        $parent_class_name
                                    );
                                }
                            }
                        }

                        break;
                    }
                }
            }

            // If class is implemented only on some platforms we store them.
            // This feature is wxWidgets specific as far as I know.
            $class_platforms = array();
            
            $class_availability = $class_xpath->evaluate(
                "/doxygen/compounddef/detaileddescription/para/onlyfor", 
                $class_doc
            );
            
            if($class_availability->length > 0)
            {
                $class_platforms = explode(
                    ",", 
                    $class_availability->item(0)->nodeValue
                );
            }
            
            // Initialize class object
            $class = new \Peg\Lib\Definitions\Element\ClassElement(
                $class_name, 
                $class_description
            );
            
            $class->struct = $class_is_struct;
            
            $class->AddParents($class_parents);
            
            // First we store all methods and their overloads, after,
            // we use this array to create FunctionElement objects and add
            // them to the class object.
            $methods = array();

            // Get the member functions of the class
            $class_member = $class_xpath->evaluate("//memberdef", $class_doc);
            for($member = 0; $member < $class_member->length; $member++)
            {

                // Class functions
                if($class_member->item($member)->getAttribute("kind") == "function")
                {
                    // Retreive function name
                    $function_name = $class_xpath->evaluate(
                        "name", 
                        $class_member->item($member)
                    )->item(0)->nodeValue;

                    // skip destructor
                    if($function_name{0} == "~")
                        continue;
                    
                    // Retrieve brief description
                    $function_description = trim(
                        $class_xpath->evaluate(
                            "briefdescription", 
                            $class_member->item($member)
                        )->item(0)->nodeValue
                    );
                    
                    // Retrieve member return type
                    $function_type = str_replace(
                        array(" *", " &"), 
                        array("*", "&"), 
                        $class_xpath->evaluate(
                            "type", 
                            $class_member->item($member)
                        )->item(0)->nodeValue
                    );
                    
                    // Store type base class to later check which bases classes aren't documented
                    if("" . stristr($function_type, "Base") . "" != "")
                    {
                        $base_classes[
                            str_replace(
                                array("&", " ", "*", "const"), 
                                "", 
                                $function_type
                            )
                        ] = 1;
                    }
                    
                    // Create method overload
                    $function_overload = new \Peg\Lib\Definitions\Element\Overload(
                        $function_description
                    );

                    $function_overload->SetReturnType(
                        new \Peg\Lib\Definitions\Element\ReturnType(
                            $function_type
                        )
                    );
                    
                    // Check if a method has been deprecated.
                    $function_deprecated = false;

                    if(
                        strstr(
                            $class_xpath->evaluate(
                                "detaileddescription", 
                                $class_member->item($member)
                            )->item(0)->nodeValue, 
                            "Deprecated"
                        ) !== false
                    )
                    {
                        $function_deprecated = true;
                    }

                    // If method is implemented only on some platforms we store them
                    // This seems to be wxWidgets specific.
                    $function_platforms = array();
                    
                    $member_availability = $class_xpath->evaluate(
                        "detaileddescription/para/onlyfor", 
                        $class_member->item($member)
                    );
                    
                    if($member_availability->length > 0)
                    {
                        $function_platforms = explode(
                            ",", 
                            $member_availability->item(0)->nodeValue
                        );
                    }

                    // Check if member is constant
                    if($class_member->item($member)->getAttribute("const") == "yes")
                        $function_overload->constant = true;

                    // Check if member is static
                    if($class_member->item($member)->getAttribute("static") == "yes")
                        $function_overload->static = true;

                    // Check if member is virtual
                    if($class_member->item($member)->getAttribute("virt") == "virtual")
                        $function_overload->virtual = true;

                    // Check if member is pure virtual
                    if($class_member->item($member)->getAttribute("virt") == "pure-virtual")
                        $function_overload->pure_virtual = true;

                    if($class_member->item($member)->getAttribute("prot") == "protected")
                        $function_overload->protected = true;


                    // Check all member parameters
                    $function_parameters = $class_xpath->evaluate(
                        "param", 
                        $class_member->item($member)
                    );
                    
                    if($function_parameters->length > 0)
                    {
                        for(
                            $parameter = 0; 
                            $parameter < $function_parameters->length; 
                            $parameter++
                        )
                        {
                            // Get param name
                            $param_name = "param" . $parameter;

                            if(is_object($class_xpath->evaluate("declname", $function_parameters->item($parameter))->item(0)))
                                $param_name = $class_xpath->evaluate(
                                    "declname", 
                                    $function_parameters->item($parameter)
                                )->item(0)->nodeValue;
                            else
                                $this->SendMessage(
                                    t("Warning:") . " " . t("parameter") . " " 
                                    . ($parameter + 1) . " " . t("on method") 
                                    . " " . $class_name . "::" . $function_name 
                                    . " " . t("does not have a name.")
                                );

                            // Get param type
                            $param_type = str_replace(
                                array(" *", " &"), 
                                array("*", "&"), 
                                $class_xpath->evaluate(
                                    "type", 
                                    $function_parameters->item($parameter)
                                )->item(0)->nodeValue
                            );

                            // Check if parameter is array
                            $param_array = false;
                            if($class_xpath->evaluate("array", $function_parameters->item($parameter))->length > 0)
                            {
                                $array_value = $class_xpath->evaluate(
                                    "array", 
                                    $function_parameters->item($parameter)
                                )->item(0)->nodeValue;

                                if($array_value == "[]")
                                {
                                    $param_array = true;
                                }
                                else
                                {
                                    //TODO: Handle non bracket array elements
                                    //$parameters[$parameter]["extra"] = $array_value;
                                }
                            }

                            // Store type base class to later check which bases classes aren't documented
                            $the_type = str_replace(
                                array(" *", " &"), 
                                array("*", "&"), 
                                $class_xpath->evaluate(
                                    "type", 
                                    $function_parameters->item($parameter)
                                )->item(0)->nodeValue
                            );
                            
                            if("" . stristr($the_type, "Base") . "" != "")
                            {
                                $base_classes[str_replace(array("&", " ", "*", "const"), "", $the_type)] = 1;
                            }

                            // Get param default value
                            $param_value = "";
                            if($class_xpath->evaluate("defval", $function_parameters->item($parameter))->length > 0)
                            {
                                $param_value = $class_xpath->evaluate(
                                    "defval", 
                                    $function_parameters->item($parameter)
                                )->item(0)->nodeValue;
                            }
                            
                            $param = new \Peg\Lib\Definitions\Element\Parameter( 
                                $param_name, 
                                $param_type, 
                                $param_value
                            );
                            
                            $param->is_array = $param_array;
                            
                            $function_overload->AddParameter($param);
                        }
                    }

                    $methods[$function_name][] = $function_overload;
                }

                // Class enumerations
                elseif($class_member->item($member)->getAttribute("kind") == "enum")
                {
                    $enum_name = $class_xpath->evaluate(
                        "name", 
                        $class_member->item($member)
                    )->item(0)->nodeValue;
                    
                    $enum_description = $class_xpath->evaluate(
                        "briefdescription", 
                        $class_member->item($member)
                    )->item(0)->nodeValue;

                    //Skip unnamed enums TODO: implement them
                    if($enum_name{0} == "@")
                        continue;

                    $enum_values = $class_xpath->evaluate("enumvalue", $class_member->item($member));

                    $enum_options = array();
                    
                    for(
                        $enum_value = 0; 
                        $enum_value < $enum_values->length; 
                        $enum_value++
                    )
                    {
                        $enum_options[] = $class_xpath->evaluate(
                            "name", 
                            $enum_values->item($enum_value)
                        )->item(0)->nodeValue;
                    }
                    
                    $enumeration = new \Peg\Lib\Definitions\Element\Enumeration(
                        $enum_name,
                        $enum_options,
                        $enum_description
                    );
                    
                    $class->AddEnumeration(
                        $enumeration
                    );
                }
                // Class variables
                elseif($class_member->item($member)->getAttribute("kind") == "variable")
                {
                    $variable_name = $class_xpath->evaluate(
                        "name", 
                        $class_member->item($member)
                    )->item(0)->nodeValue;

                    $variable_type = $class_xpath->evaluate(
                        "type", 
                        $class_member->item($member)
                    )->item(0)->nodeValue;
                    
                    $variable_description = $class_xpath->evaluate(
                        "briefdescription", $class_member->item($member)
                    )->item(0)->nodeValue;
                    
                    $variable = new \Peg\Lib\Definitions\Element\ClassVariable(
                        $variable_name, 
                        $variable_type, 
                        $variable_description
                    );

                    if($class_member->item($member)->getAttribute("static") != "no")
                        $variable->static = true;

                    if($class_member->item($member)->getAttribute("mutable") != "no")
                        $variable->mutable = true;

                    if($class_member->item($member)->getAttribute("prot") == "protected")
                        $variable->protected = true;

                    if($class_member->item($member)->getAttribute("prot") == "public")
                        $variable->public = true;
                    
                    $class->AddVariable($variable);
                }
                //Store not handled members of the class
                else
                {
                    //$class_not_handle[$class_member->item($member)->getAttribute("kind")]++;
                }
            }
            
            foreach($methods as $method_name=>$method_overloads)
            {
                $function = new \Peg\Lib\Definitions\Element\FunctionElement(
                    $method_name
                );

                foreach($method_overloads as $overload)
                {
                    $function->AddOverload($overload);
                }

                $class->AddMethod($function);
            }
            
            $this->symbols->AddClass($class, $header_file, $namespace);
        }
    }

}