<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib\Generator;

/**
 * Class that implements a zend extension generator.
 */
class ZendPHP extends \Peg\Lib\Generator\Base
{
    public function __construct(
        $templates, 
        $output, 
        \Peg\Lib\Definitions\Symbols &$symbols
    )
    {
        parent::__construct($templates, $output, $symbols);
        
        $this->generator_name = "zend_php";
        
        $this->output_path .= $this->generator_name . "/";
    }
    
    public function Start()
    {
        // Create includes directory if doesn't exists.
        if(!file_exists($this->output_path . "includes"))
            \Peg\Lib\Utilities\FileSystem::MakeDir(
                $this->output_path . "includes",
                0755,
                true
            );

        // Create src directory if doesn't exists.
        if(!file_exists($this->output_path . "src"))
            \Peg\Lib\Utilities\FileSystem::MakeDir(
                $this->output_path . "src",
                0755,
                true
            );

        foreach($this->symbols->headers as $header_name=>$header_object)
        {
            // Skip disabled headers
            if(!$header_object->enabled)
            {
                $this->RemoveHeader($header_name);
                continue;
            }

            // Generate header file
            $header_content = $this->GenerateHeader($header_name);

            $this->AddHeader($header_name, $header_content);

            // Generate source file
            $source_content = $this->GenerateSource($header_name);

            $this->AddSource($header_name, $source_content);
        }
    }

    /**
     * Generates a specific header file.
     * @param string $header_name
     * @return string Source code.
     */
    public function GenerateHeader($header_name)
    {
        // Variables used by some template files.
        $authors = \Peg\Lib\Settings::GetAuthors();
        $contributors = \Peg\Lib\Settings::GetContributors();
        $extension = \Peg\Lib\Settings::GetExtensionName();
        $version = \Peg\Lib\Settings::GetVersion();

        $header_object = $this->symbols->headers[$header_name];
        $header_define = $this->GetHeaderDefine($header_name);

        $header_content = "";

        // Get heading of header file
        ob_start();
            include($this->GetTemplatePath(
                $header_name, 
                "headers", 
                "header", 
                "helpers", 
                "header"
            ));
            $header_content .= ob_get_contents();
        ob_end_clean();

        // Get constants function template content
        if($header_object->HasConstants() || $header_object->HasGlobalVariables())
        {
            ob_start();
                include($this->GetTemplatePath(
                    $header_name, 
                    "constants", 
                    "function_decl", 
                    "helpers", 
                    "constant"
                ));
                $header_content .= ob_get_contents();
            ob_end_clean();

            $header_content .= "\n";
        }

        // Get enums function template content
        if($header_object->HasEnumerations())
        {
            ob_start();
                include($this->GetTemplatePath(
                    $header_name, 
                    "enums", 
                    "function_decl", 
                    "helpers", 
                    "enum"
                ));
                $header_content .= ob_get_contents();
            ob_end_clean();

            $header_content .= "\n";
        }

        // Get functions function template content
        if($header_object->HasFunctions())
        {
            ob_start();
                include($this->GetTemplatePath(
                    $header_name, 
                    "functions", 
                    "function_decl", 
                    "helpers", 
                    "function"
                ));
                $header_content .= ob_get_contents();
            ob_end_clean();

            $header_content .= "\n";
        }

        foreach($header_object->namespaces as $namespace_name=>$namespace_object)
        {
            // Do something here.
        }

        // Get footer of header file
        ob_start();
            include($this->GetTemplatePath(
                $header_name, 
                "headers", 
                "footer", 
                "helpers", 
                "header"
            ));
            $header_content .= ob_get_contents();
        ob_end_clean();

        return $header_content;
    }

    /**
     * Generates a specific header source file.
     * @param string $header_name
     * @return string Source code.
     */
    public function GenerateSource($header_name)
    {
        // Variables used by some template files.
        $authors = \Peg\Lib\Settings::GetAuthors();
        $contributors = \Peg\Lib\Settings::GetContributors();
        $extension = \Peg\Lib\Settings::GetExtensionName();
        $version = \Peg\Lib\Settings::GetVersion();

        $header_object = $this->symbols->headers[$header_name];
        $header_define = $this->GetHeaderDefine($header_name);
        $php_header_name = $this->GetHeaderNamePHP($header_name);

        $source_content = "";

        // Get heading of source file
        ob_start();
            include($this->GetTemplatePath(
                $header_name, 
                "sources", 
                "header", 
                "helpers", 
                "source"
            ));
            $source_content .= ob_get_contents();
        ob_end_clean();

        // Get constants function template content
        if($header_object->HasConstants() || $header_object->HasGlobalVariables())
        {
            // Constants function heading
            ob_start();
                include($this->GetTemplatePath(
                    $header_name, 
                    "constants", 
                    "function_header", 
                    "helpers", 
                    "constant"
                ));
                $source_content .= ob_get_contents();
            ob_end_clean();

            foreach($header_object->namespaces as $namespace_name=>$namespace_object)
            {
                if($namespace_name == "\\")
                    $namespace_name = "";

                $namespace_name_cpp = str_replace(
                    "\\",
                    "::",
                    $namespace_name
                );

                $namespace_name_var = str_replace(
                    "\\",
                    "_",
                    $namespace_name
                );

                foreach($namespace_object->constants as $constant_name=>$constant_object)
                {
                    ob_start();
                        include($this->GetRegisterConstantTemplate($constant_name));
                        $source_content .= $this->Indent(ob_get_contents(), 4);
                    ob_end_clean();
                }

                foreach($namespace_object->global_variables as $constant_name=>$constant_object)
                {
                    ob_start();
                        include($this->GetRegisterVarConstantTemplate($constant_object));
                        $source_content .= $this->Indent(ob_get_contents(), 4);
                    ob_end_clean();
                }
            }

            // Constants function footer
            ob_start();
                include($this->GetTemplatePath(
                    $header_name, 
                    "constants", 
                    "function_footer", 
                    "helpers", 
                    "constant"
                ));
                $source_content .= ob_get_contents();
            ob_end_clean();

            $source_content .= "\n";
        }

        // Get enums function template content
        if($header_object->HasEnumerations())
        {
            // Enums function heading
            ob_start();
                include($this->GetTemplatePath(
                    $header_name, 
                    "enums", 
                    "function_header", 
                    "helpers", 
                    "enum"
                ));
                $source_content .= ob_get_contents();
            ob_end_clean();

            foreach($header_object->namespaces as $namespace_name=>$namespace_object)
            {
                if($namespace_name == "\\")
                    $namespace_name = "";

                $namespace_name_cpp = str_replace(
                    "\\",
                    "::",
                    $namespace_name
                );

                $namespace_name_var = str_replace(
                    "\\",
                    "_",
                    $namespace_name
                );

                foreach($namespace_object->enumerations as $enum_name=>$enum_object)
                {
                    ob_start();
                        include($this->GetTemplatePath(
                            $enum_name, 
                            "enums", 
                            "declare_class", 
                            "helpers", 
                            "enum",
                            $namespace_name
                        ));
                        $source_content .= $this->Indent(ob_get_contents(), 4);
                    ob_end_clean();

                    foreach($enum_object->options as $enum_option)
                    {
                        ob_start();
                            include($this->GetTemplatePath(
                                $enum_name, 
                                "enums", 
                                "declare_constant", 
                                "helpers", 
                                "enum",
                                $namespace_name
                            ));
                            $source_content .= $this->Indent(ob_get_contents(), 4);
                        ob_end_clean();
                    }
                }
            }

            // Enums function footer
            ob_start();
                include($this->GetTemplatePath(
                    $header_name, 
                    "enums", 
                    "function_footer", 
                    "helpers", 
                    "enum"
                ));
                $source_content .= ob_get_contents();
            ob_end_clean();

            $source_content .= "\n";
        }

        // Get functions function template content
        if($header_object->HasFunctions())
        {
            // Generate function wrappers
            foreach($header_object->namespaces as $namespace_name=>$namespace_object)
            {
                foreach($namespace_object->functions as $function_name=>$function_object)
                {
                    $source_content .= $this->GenerateFunction($function_object);
                }
            }

            // Generate functions registration code
            
            // Functions registration heading
            ob_start();
                include($this->GetTemplatePath(
                    $header_name, 
                    "functions", 
                    "function_header", 
                    "helpers", 
                    "function"
                ));
                $source_content .= ob_get_contents();
            ob_end_clean();

            ob_start();
                include($this->GetTemplatePath(
                    $header_name, 
                    "functions", 
                    "table_begin", 
                    "helpers", 
                    "function"
                ));
                $source_content .= $this->Indent(ob_get_contents(), 4);
            ob_end_clean();

            foreach($header_object->namespaces as $namespace_name=>$namespace_object)
            {
                if($namespace_name == "\\")
                    $namespace_name = "";

                $namespace_name_cpp = str_replace(
                    "\\",
                    "::",
                    $namespace_name
                );

                $namespace_name_var = str_replace(
                    "\\",
                    "_",
                    $namespace_name
                );

                foreach($namespace_object->functions as $function_name=>$function_object)
                {
                    ob_start();
                        include($this->GetTemplatePath(
                            $header_name, 
                            "functions", 
                            "table_entry", 
                            "helpers", 
                            "function"
                        ));
                        $source_content .= $this->Indent(ob_get_contents(), 8);
                    ob_end_clean();
                }
            }

            ob_start();
                include($this->GetTemplatePath(
                    $header_name, 
                    "functions", 
                    "table_end", 
                    "helpers", 
                    "function"
                ));
                $source_content .= $this->Indent(ob_get_contents(), 4);
            ob_end_clean();

            ob_start();
                include($this->GetTemplatePath(
                    $header_name, 
                    "functions", 
                    "table_register", 
                    "helpers", 
                    "function"
                ));
                $source_content .= $this->Indent(ob_get_contents(), 4);
            ob_end_clean();

            // Functions registration footer
            ob_start();
                include($this->GetTemplatePath(
                    $header_name, 
                    "functions", 
                    "function_footer", 
                    "helpers", 
                    "function"
                ));
                $source_content .= ob_get_contents();
            ob_end_clean();

            $source_content .= "\n";
        }

        // Get footer of source file
        ob_start();
            include($this->GetTemplatePath(
                $header_name, 
                "sources", 
                "footer", 
                "helpers", 
                "source"
            ));
            $source_content .= ob_get_contents();
        ob_end_clean();

        return $source_content;
    }

    function GenerateFunction(
        \Peg\Lib\Definitions\Element\FunctionElement $function_object
    )
    {
        // Variables used by some template files.
        $authors = \Peg\Lib\Settings::GetAuthors();
        $contributors = \Peg\Lib\Settings::GetContributors();
        $extension = \Peg\Lib\Settings::GetExtensionName();
        $version = \Peg\Lib\Settings::GetVersion();

        $namespace_name = $function_object->namespace->name;

        if($namespace_name == "\\")
            $namespace_name = "";

        $namespace_name_cpp = str_replace(
            "\\",
            "::",
            $namespace_name
        );

        $namespace_name_var = str_replace(
            "\\",
            "_",
            $namespace_name
        );

        $function_name = $function_object->name;
        $overloads_count = count($function_object->overloads);

        $function_content = "";

        $parameters_code = "";
        $parse_code = "";
        $return_code = "";

        $proto_header = $this->GetProtoHeader($function_object);
        $proto_footer = $this->GetProtoFooter();

        // Get header of function
        ob_start();
            include($this->GetTemplatePath(
                $function_name, 
                "function", 
                "head", 
                "functions", 
                "",
                $namespace_name
            ));
            $function_content .= ob_get_contents();
        ob_end_clean();

         // Parameters declaration 
        foreach($function_object->overloads as $overload=>$overload_object)
        {
            $parameters_code .= "/* Parameters for overload $overload */\n";

            foreach($overload_object->parameters as $parameter_name=>$parameter_object)
            {
               
                ob_start();
                    include($this->GetParameterTemplate($parameter_object, $namespace_name, "declare"));
                    $parameters_code .= ob_get_contents();
                ob_end_clean();

                $parameters_code .= "\n";
            }
            
            $parameters_code .= "bool overload_{$overload}_called = false;\n";
                
            $parameters_code .= "\n";
        }
        
        // Parse parameters code
        foreach($function_object->overloads as $overload=>$overload_object)
        {
            $parameters_count = $overload_object->GetParametersCount();
            $required_parameters = $overload_object->GetRequiredParametersCount();
            
            // Overload parse header
            ob_start();
                include($this->GetTemplatePath(
                    $function_name, 
                    "overloads", 
                    "parse_header", 
                    "helpers", 
                    "overload"
                ));
                $parse_code .= ob_get_contents();
            ob_end_clean();
            
            $parse_string = "";
            $parameters_optional = false;
            $parse_parameters = "";
            
            $parse_references = "";
            $parse_string_ref = "";
            $parse_reference = "";
            $references_found = false;
            
            $object_validate_code = "";
            
            $parameter_index = 0;
            foreach($overload_object->parameters as $parameter_name=>$parameter_object)
            {
                if($parameter_object->default_value && !$parameters_optional)
                {
                    $parse_string .= "|";
                    $parse_string_ref = "|";
                    $parameters_optional = true;
                }
            
                ob_start();
                    include($this->GetParameterTemplate($parameter_object, $namespace_name, "parse_string"));
                    $parse_string .= ob_get_contents();
                ob_end_clean();
                
                ob_start();
                    include($this->GetParameterTemplate($parameter_object, $namespace_name, "parse"));
                    $parse_parameters .= ob_get_contents();
                ob_end_clean();
                
                if(!$parameter_object->is_const && $parameter_object->is_reference)
                    $references_found = true;
                
                ob_start();
                    include($this->GetParameterTemplate($parameter_object, $namespace_name, "parse_string_ref"));
                    $parse_string_ref .= ob_get_contents();
                ob_end_clean();
                
                ob_start();
                    include($this->GetParameterTemplate($parameter_object, $namespace_name, "parse_reference"));
                    $parse_reference .= ob_get_contents();
                ob_end_clean();
                
                if(
                    $this->symbols->GetStandardType($parameter_object)
                    ==
                    \Peg\Lib\Definitions\StandardType::OBJECT
                )
                {
                    ob_start();
                        include($this->GetParameterTemplate($parameter_object, $namespace_name, "object_validate"));
                        $object_validate_code .= $this->Indent(ob_get_contents(), 4);
                    ob_end_clean();
                }
                
                $parameter_index++;
            }
            
            // No parameters to parse
            if($required_parameters == 0 && $parameters_count < 1)
            {
                // Overload no parse body
                ob_start();
                    include($this->GetTemplatePath(
                        $function_name, 
                        "overloads", 
                        "no_parse_body", 
                        "helpers", 
                        "overload"
                    ));
                    $parse_code .= $this->Indent(ob_get_contents(), 4);
                ob_end_clean();
            }
            // There is parameters to parse
            else
            {
                if($references_found)
                {
                    // Overload parse references
                    ob_start();
                        include($this->GetTemplatePath(
                            $function_name, 
                            "overloads", 
                            "parse_references", 
                            "helpers", 
                            "overload"
                        ));
                        $parse_references .= $this->Indent(ob_get_contents(), 4);
                    ob_end_clean();
                }
                
                // Overload parse body
                ob_start();
                    include($this->GetTemplatePath(
                        $function_name, 
                        "overloads", 
                        "parse_body", 
                        "helpers", 
                        "overload"
                    ));
                    $parse_code .= $this->Indent(ob_get_contents(), 4);
                ob_end_clean();
            }

            // Overload parse footer
            ob_start();
                include($this->GetTemplatePath(
                    $function_name, 
                    "overloads", 
                    "parse_footer", 
                    "helpers", 
                    "overload"
                ));
                $parse_code .= ob_get_contents();
            ob_end_clean();
        }
        
        // Return code
        foreach($function_object->overloads as $overload=>$overload_object)
        {
            $parameters_count = $overload_object->GetParametersCount();
            $required_parameters = $overload_object->GetRequiredParametersCount();
            
            $call_declare_code = "";
            
            // Optional declarations before proceeding to call an overload
            $parameter_index = 0;
            foreach($overload_object->parameters as $parameter_name=>$parameter_object)
            {
                ob_start();
                    include($this->GetParameterTemplate($parameter_object, "", "call_declare"));
                    $call_declare_code .= $this->Indent(ob_get_contents(), 4);
                ob_end_clean();
                
                $parameter_index++;
            }
            
            // Generate the overload return heading
            ob_start();
                include($this->GetTemplatePath(
                    $function_name, 
                    "overloads", 
                    "return_header", 
                    "helpers", 
                    "overload"
                ));
                $return_code .= ob_get_contents();
            ob_end_clean();
            
            // Variables used by after call object template.
            $class_name = "";
            $is_constructor = false;
            
            for(
                $required_parameters; 
                $required_parameters<=$parameters_count; 
                $required_parameters++
            )
            {
                // Overload return body header
                ob_start();
                    include($this->GetTemplatePath(
                        $function_name, 
                        "overloads", 
                        "return_body_header", 
                        "helpers", 
                        "overload"
                    ));
                    $return_code .= $this->Indent(ob_get_contents(), 8);
                ob_end_clean();
            
                $parameter_index = 0;
                $parameters_string = "";
                $after_call = "";
                
                foreach($overload_object->parameters as $parameter_name=>$parameter_object)
                {
                    if($parameter_index<$required_parameters)
                    {
                        ob_start();
                            include($this->GetParameterTemplate($parameter_object, "", "before_call"));
                            $before_call = $this->Indent(ob_get_contents(), 12);
                        ob_end_clean();
                        
                        if(trim($before_call))
                            $return_code .= $before_call;
                        
                        ob_start();
                            include($this->GetParameterTemplate($parameter_object, "", "call"));
                            $parameters_string .= ob_get_contents();
                        ob_end_clean();
                        
                        ob_start();
                            include($this->GetParameterTemplate($parameter_object, "", "after_call"));
                            $after_call .= ob_get_contents();
                        ob_end_clean();
                    }
                    else
                    {
                        break;
                    }
                    
                    $parameter_index++;
                }
                
                ob_start();
                    include($this->GetReturnTemplate($overload_object->return_type, "", "function"));
                    $return_code .= $this->Indent(ob_get_contents(), 12);
                ob_end_clean();
                
                $return_code .= $after_call;
                
                // Overload return body footer
                ob_start();
                    include($this->GetTemplatePath(
                        $function_name, 
                        "overloads", 
                        "return_body_footer", 
                        "helpers", 
                        "overload"
                    ));
                    $return_code .= $this->Indent(ob_get_contents(), 8);
                ob_end_clean();
            }

            // Generate the overload return footer
            ob_start();
                include($this->GetTemplatePath(
                    $function_name, 
                    "overloads", 
                    "return_footer", 
                    "helpers", 
                    "overload"
                ));
                $return_code .= ob_get_contents();
            ob_end_clean();
        }

        // Get body of function
        ob_start();
            include($this->GetTemplatePath(
                $function_name, 
                "function", 
                "body", 
                "functions", 
                "",
                $namespace_name
            ));
            $function_content .= $this->Indent(ob_get_contents(), 4);
        ob_end_clean();

        // Get footer of function
        ob_start();
            include($this->GetTemplatePath(
                $function_name, 
                "function", 
                "footer", 
                "functions", 
                "",
                $namespace_name
            ));
            $function_content .= ob_get_contents();
        ob_end_clean();

        return $function_content;
    }
    
    /**
     * Generates proto doc comments header for a function.
     * @param \Peg\Lib\Definitions\Element\FunctionElement $function
     * @return string
     */
    public function GetProtoHeader(
        \Peg\Lib\Definitions\Element\FunctionElement $function
    )
    {
        $proto = "/* {{{ ";
        
        foreach($function->overloads as $overload)
        {
            $proto .= "proto ";
            
            $proto .= $this->symbols->GetPHPStandardType($overload->return_type);
            
            $proto .= " " . $overload->function->name . "(";
            
            foreach($overload->parameters as $parameter)
            {
                $proto .= $this->symbols->GetPHPStandardType($parameter);
                $proto .= " " . $parameter->name;
                
                if($parameter->default_value)
                {
                    $proto .= " = " . $parameter->default_value; 
                }
                
                $proto .= ", ";
            }
            
            $proto = rtrim($proto, ", ");
            
            $proto .= ")";
            
            if($overload->description)
                $proto .= "\n       " . $overload->description;
            
            $proto .= "\n       ";
        }
        
        $proto = rtrim($proto, " \n");
        
        $proto .= " */\n";
        
        return $proto;
    }
    
    /**
     * Generates proto doc comments footer.
     * @return string
     */
    public function GetProtoFooter()
    {
        return "/* }}} */\n";
    }

    /**
     * Retrieve the template path for registering constants, also checks
     * if a valid override exists and returns that instead.
     * @todo Improve this to handle various types.
     * @param string $name Name of constant.
     * @param string $namespace Namespace where resides the constant.
     * @return string Path to template file.
     */
    public function GetRegisterConstantTemplate($name, $namespace="", $type="")
    {
        if($namespace)
        {
            $namespace = str_replace(
                array("\\", "::"),
                "_",
                $namespace
            ) . "_";
        }

        $override = $this->templates_path
            . "zend_php/constants/overrides/"
            . "define_{$name}"
            . ".php"
        ;

        if(file_exists($override))
        {
            return $override;
        }

        return $this->templates_path
            . "zend_php/constants/"
            . "integer.php"
        ;
    }

    /**
     * Retrieve the template path for registering constants registered as global
     * variables, also checks if a valid override exists and returns that instead.
     * @param \Peg\Lib\Definitions\Element\GlobalVariable $variable
     * @param string $namespace Namespace where resides the constant.
     * @return string Path to template file.
     */
    public function GetRegisterVarConstantTemplate(
        \Peg\Lib\Definitions\Element\GlobalVariable $variable,
        $namespace=""
    )
    {
        if($namespace)
        {
            $namespace = str_replace(
                array("\\", "::"),
                "_",
                $namespace
            ) . "_";
        }

        $ptr = "";
        if($variable->is_pointer)
        {
            for($i=0; $i<$variable->indirection_level; $i++)
            {
                $ptr .= "_ptr";
            }
        }

        $ref = "";
        if($variable->is_reference)
        {
            $ref .= "_ref";
        }

        $array = "";
        if($variable->is_array)
        {
            $array .= "_arr";
        }

        $override = $this->templates_path
            . "zend_php/constants/overrides/"
            . $variable->type
            . $ptr
            . $ref
            . $array
            . ".php"
        ;

        if(file_exists($override))
        {
            return $override;
        }

        $standard_type = $this->symbols->GetStandardType($variable);

        $template = $this->templates_path
            . "zend_php/constants/"
            . $standard_type
            . $ptr
            . $ref
            . $array
            . ".php"
        ;

        if(!file_exists($template))
        {
            return $this->templates_path
                . "zend_php/constants/"
                . "default.php"
            ;
        }

        return $template;
    }

    /**
     * Retrieve the template path for parameters, also checks
     * if a valid override exists and returns that instead.
     * @param string $parameter Name of the function.
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
            . "zend_php/parameters/{$type}/overrides/"
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
            . "zend_php/parameters/{$type}/overrides/"
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
            . "zend_php/parameters/{$type}/"
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
                . "zend_php/parameters/{$type}/"
                . "default.php"
            ;
        }

        return $template;
    }
    
    /**
     * Retrieve the template path for return, also checks
     * if a valid override exists and returns that instead.
     * @param string $return Name of the function.
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
            . "zend_php/return/{$type}/overrides/"
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
            . "zend_php/return/{$type}/overrides/"
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
            . "zend_php/return/{$type}/"
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
                . "zend_php/return/{$type}/"
                . "default.php"
            ;
        }

        return $template;
    }
}