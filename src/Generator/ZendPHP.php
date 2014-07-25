<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib\Generator;

use Peg\Lib\Utilities\FileSystem;

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
        parent::__construct($templates, $output, "zend_php", $symbols);
    }
    
    /**
     * Generate all the files that needed to build a zend php extension.
     */
    public function Start()
    {
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
            
            // Generate classes header file
            $classes_header_content = $this->GenerateClassesHeader($header_name);

            $this->AddHeader(
                $header_name, 
                $classes_header_content, 
                "includes/classes"
            );

            // Generate source file
            $source_content = $this->GenerateSource($header_name);

            $this->AddSource($header_name, $source_content);
        }
        
        $this->GenerateOtherSources();
        
        $this->GenerateCustomSources();
        
        $this->GenerateConfigs();
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
        
        // Get classes function template content
        if($header_object->HasClasses())
        {
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
                
                foreach($namespace_object->classes as $class_name=>$class_object)
                {
                    ob_start();
                        include($this->GetTemplatePath(
                            $header_name, 
                            "classes", 
                            "function_decl", 
                            "helpers", 
                            "class"
                        ));
                        $header_content .= ob_get_contents();
                    ob_end_clean();

                    $header_content .= "\n";
                }
            }
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
     * Generates a specific header file for classes.
     * @param string $header_name
     * @return string Source code.
     */
    public function GenerateClassesHeader($header_name)
    {
        // Variables used by some template files.
        $authors = \Peg\Lib\Settings::GetAuthors();
        $contributors = \Peg\Lib\Settings::GetContributors();
        $extension = \Peg\Lib\Settings::GetExtensionName();
        $version = \Peg\Lib\Settings::GetVersion();

        $header_object = $this->symbols->headers[$header_name];
        $header_define = $this->GetHeaderDefine($header_name);

        $header_content = "";
        
        if($header_object->HasClasses())
        {
            // Get heading of header file
            ob_start();
                include($this->GetTemplatePath(
                    $header_name, 
                    "headers", 
                    "header", 
                    "classes", 
                    "header"
                ));
                $header_content .= ob_get_contents();
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
                
                foreach($namespace_object->classes as $class_name=>$class_object)
                {
                    $constructor_code = "";
                    $virtual_methods_code = "";
                    $properties_init_code = "";
                    $properties_uninit_code = "";
                    
                    // Get class declaration code
                    ob_start();
                        include($this->GetTemplatePath(
                            $class_name, 
                            "", 
                            "declaration", 
                            "classes", 
                            "declaration"
                        ));
                        $header_content .= ob_get_contents();
                    ob_end_clean();

                    $header_content .= "\n";
                }
            }
            
            // Get footer of header file
            ob_start();
                include($this->GetTemplatePath(
                    $header_name, 
                    "headers", 
                    "footer", 
                    "classes", 
                    "header"
                ));
                $header_content .= ob_get_contents();
            ob_end_clean();
        }

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

    /**
     * Generates the PHP wrapping code from a C/C++ function.
     * @param \Peg\Lib\Definitions\Element\FunctionElement $function_object
     * @return string PHP C/C++ code.
     */
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
            // Overload parameters declaration header
            ob_start();
                include($this->GetTemplatePath(
                    $function_name, 
                    "overloads", 
                    "parameters_header", 
                    "helpers", 
                    "overload"
                ));
                $parameters_code .= ob_get_contents();
            ob_end_clean();

            foreach($overload_object->parameters as $parameter_name=>$parameter_object)
            {
                // Overload parameter declarations
                ob_start();
                    include($this->GetParameterTemplate($parameter_object, $namespace_name, "declare"));
                    $parameters_code .= ob_get_contents();
                ob_end_clean();
            }
            
            // Overload parameters declaration footer
            ob_start();
                include($this->GetTemplatePath(
                    $function_name, 
                    "overloads", 
                    "parameters_footer", 
                    "helpers", 
                    "overload"
                ));
                $parameters_code .= ob_get_contents();
            ob_end_clean();
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
     * Generates other sources required to build the extension, eg:
     * php_extension.h, extension.c
     */
    public function GenerateOtherSources()
    {
        // Variables used by some template files.
        $authors = \Peg\Lib\Settings::GetAuthors();
        $contributors = \Peg\Lib\Settings::GetContributors();
        $extension = \Peg\Lib\Settings::GetExtensionName();
        $version = \Peg\Lib\Settings::GetVersion();
        
        // Variable to temporarily store a template file content.
        $content = "";
        
        // Generate all_headers.h
        $headers = "";
        foreach($this->symbols->headers as $header_name=>$header_object)
        {
            if($header_object->enabled)
            {
                $headers .= '#include "'
                    . $this->GetHeaderNamePHP($header_name)
                    . '"' 
                    . "\n"
                ;
            }
        }
        
        ob_start();
            include($this->GetGenericTemplate("all_headers.h", "sources"));
            $content = ob_get_contents();
        ob_end_clean();
        
        $this->AddGenericFile("all_headers.h", $content, "includes");
        
        // Generate functions.h/cpp
        ob_start();
            include($this->GetGenericTemplate("functions.h", "sources"));
            $content = ob_get_contents();
        ob_end_clean();
        
        $this->AddGenericFile("functions.h", $content, "includes");
        
        ob_start();
            include($this->GetGenericTemplate("functions.cpp", "sources"));
            $content = ob_get_contents();
        ob_end_clean();
        
        $this->AddGenericFile("functions.cpp", $content, "src");
        
        // Generate enums.h/c
        ob_start();
            include($this->GetGenericTemplate("enums.h", "sources"));
            $content = ob_get_contents();
        ob_end_clean();
        
        $this->AddGenericFile("enums.h", $content, "includes");
        
        ob_start();
            include($this->GetGenericTemplate("enums.c", "sources"));
            $content = ob_get_contents();
        ob_end_clean();
        
        $this->AddGenericFile("enums.c", $content, "src");
        
        // Generate references.h/cpp
        ob_start();
            include($this->GetGenericTemplate("references.h", "sources"));
            $content = ob_get_contents();
        ob_end_clean();
        
        $this->AddGenericFile("references.h", $content, "includes");
        
        ob_start();
            include($this->GetGenericTemplate("references.cpp", "sources"));
            $content = ob_get_contents();
        ob_end_clean();
        
        $this->AddGenericFile("references.cpp", $content, "src");
        
        // Generate php_extension.h/c
        $constants_register = "";
        $enums_register = "";
        $functions_register = "";
        $classes_register = "";
        
        foreach($this->symbols->headers as $header_name=>$header_object)
        {
            $header_define = $this->GetHeaderDefine($header_name);
            
            if($header_object->enabled)
            {
                if($header_object->HasConstants())
                {
                    ob_start();
                        include($this->GetTemplatePath(
                            $header_name, 
                            "constants", 
                            "function_call", 
                            "helpers", 
                            "constant"
                        ));
                        $constants_register .= ob_get_contents();
                    ob_end_clean();
                }
                
                if($header_object->HasEnumerations())
                {
                    ob_start();
                        include($this->GetTemplatePath(
                            $header_name, 
                            "enums", 
                            "function_call", 
                            "helpers", 
                            "enum"
                        ));
                        $enums_register .= ob_get_contents();
                    ob_end_clean();
                }
                
                if($header_object->HasEnumerations())
                {
                    ob_start();
                        include($this->GetTemplatePath(
                            $header_name, 
                            "functions", 
                            "function_call", 
                            "helpers", 
                            "function"
                        ));
                        $functions_register .= ob_get_contents();
                    ob_end_clean();
                }
                
                /*if($header_object->HasClasses())
                {
                    ob_start();
                        include($this->GetTemplatePath(
                            $header_name, 
                            "classes", 
                            "function_call", 
                            "helpers", 
                            "class"
                        ));
                        $enums_register .= ob_get_contents();
                    ob_end_clean();
                }*/
            }
        }
        
        $constants_register = $this->Indent($constants_register, 8);
        $enums_register = $this->Indent($enums_register, 8);
        $functions_register = $this->Indent($functions_register, 8);
        
        ob_start();
            include($this->GetGenericTemplate("php_extension.h", "sources"));
            $content = ob_get_contents();
        ob_end_clean();
        
        $this->AddGenericFile("php_" . strtolower($extension) . ".h", $content);
        
        ob_start();
            include($this->GetGenericTemplate("extension.c", "sources"));
            $content = ob_get_contents();
        ob_end_clean();
        
        $this->AddGenericFile(strtolower($extension) . ".c", $content);
        
        // Generate object_types.h
        $object_types = "";
        foreach($this->symbols->headers as $header_name=>$header_object)
        {
            foreach($header_object->namespaces as $namespace_name=>$namespace_object)
            {
                if($namespace_name == "\\")
                    $namespace_name = "";

                $namespace_name_var = strtoupper(
                        str_replace(
                        "\\",
                        "_",
                        $namespace_name
                    )
                );
        
                foreach($namespace_object->classes as $class_name=>$class_object)
                {
                    if($namespace_name)
                    {
                        $object_types .= "PHP_" 
                            . $namespace_name_var . "_"
                            . strtoupper($class_name) 
                            . "_TYPE,"
                            . "\n    "
                        ;
                    }
                    else
                    {
                        $object_types .= "PHP_" 
                            . strtoupper($class_name) 
                            . "_TYPE,"
                            . "\n    "
                        ;
                    }
                }
            }
        }
        
        $object_types = rtrim($object_types, "\n, ") . "\n";
        
        ob_start();
            include($this->GetGenericTemplate("object_types.h", "sources"));
            $content = ob_get_contents();
        ob_end_clean();
        
        $this->AddGenericFile("object_types.h", $content, "includes");
    }
    
    /**
     * Generates config.m4 and config.w32.
     */
    public function GenerateConfigs()
    {
        // Variables used by some template files.
        $authors = \Peg\Lib\Settings::GetAuthors();
        $contributors = \Peg\Lib\Settings::GetContributors();
        $extension = \Peg\Lib\Settings::GetExtensionName();
        $version = \Peg\Lib\Settings::GetVersion();
        
        // Variable to temporarily store a template file content.
        $content = "";
        
        // Generate sources list
        $source_files = array(
            "enums.c",
            "functions.cpp",
            "references.cpp"
        );
        
        foreach($this->symbols->headers as $header_name=>$header_object)
        {
            if($header_object->enabled)
            {
                $source_files[] = $this->GetSourceNamePHP($header_name);
            }
        }
        
        // Generate custom sources list
        $custom_sources = $this->GetCustomSources();
        
        if(count($custom_sources) > 0)
        {
            foreach($custom_sources as $custom_source)
            {
                if($custom_source != "." && $custom_source != "..")
                {
                    if(
                        (strpos($custom_source, ".c") !== false)
                        ||
                        (strpos($custom_source, ".cpp") !== false)
                    )
                    {
                        $source_files[] = $custom_source;
                    }
                }
            }
        }
        
        $source_files = implode(" ", $source_files);
        
        // Generate config.m4
        ob_start();
            include($this->GetGenericTemplate("config.m4", "configs"));
            $content = ob_get_contents();
        ob_end_clean();
        
        $this->AddGenericFile("config.m4", $content);
        
        // Generate config.w32
        ob_start();
            include($this->GetGenericTemplate("config.w32", "configs"));
            $content = ob_get_contents();
        ob_end_clean();
        
        $this->AddGenericFile("config.w32", $content);
    }
    
    /**
     * Generates sources from the custom_sources templates directory.
     */
    public function GenerateCustomSources()
    {
        // Variables used by some template files.
        $authors = \Peg\Lib\Settings::GetAuthors();
        $contributors = \Peg\Lib\Settings::GetContributors();
        $extension = \Peg\Lib\Settings::GetExtensionName();
        $version = \Peg\Lib\Settings::GetVersion();
        
        // Variable to temporarily store a template file content.
        $content = "";
        
        // Generate custom sources list
        $custom_sources = $this->GetCustomSources();
        
        if(count($custom_sources) > 0)
        {
            foreach($custom_sources as $custom_source)
            {
                if($custom_source != "." && $custom_source != "..")
                {
                    ob_start();
                        include($this->GetGenericTemplate(
                            $custom_source, "custom_sources"
                        ));
                        $content = ob_get_contents();
                    ob_end_clean();
        
                    if(
                        (strpos($custom_source, ".c") !== false)
                        ||
                        (strpos($custom_source, ".cpp") !== false)
                    )
                    {
                        $this->AddGenericFile(
                            $custom_source, 
                            $content, 
                            "src"
                        );
                    }
                    elseif(
                        (strpos($custom_source, ".h") !== false)
                        ||
                        (strpos($custom_source, ".hpp") !== false)
                    )
                    {
                        $this->AddGenericFile(
                            $custom_source, 
                            $content, 
                            "includes"
                        );
                    }
                }
            }
        }
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
            . "constants/overrides/"
            . "define_{$name}"
            . ".php"
        ;

        if(file_exists($override))
        {
            return $override;
        }

        return $this->templates_path
            . "constants/"
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
            . "constants/overrides/"
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
            . "constants/"
            . $standard_type
            . $ptr
            . $ref
            . $array
            . ".php"
        ;

        if(!file_exists($template))
        {
            return $this->templates_path
                . "constants/"
                . "default.php"
            ;
        }

        return $template;
    }
}