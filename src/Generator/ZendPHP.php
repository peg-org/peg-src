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
    public function Start()
    {
        // Remove old header files or create includes directory.
        if(!file_exists($this->output_path . "includes"))
            \Peg\Lib\Utilities\FileSystem::MakeDir(
                $this->output_path . "includes"
            );
        else
            \Peg\Lib\Utilities\FileSystem::RecursiveRemoveDir(
                $this->output_path . "includes",
                true
            );

        // Remove old source files or create src directory.
        if(!file_exists($this->output_path . "src"))
            \Peg\Lib\Utilities\FileSystem::MakeDir(
                $this->output_path . "src"
            );
        else
            \Peg\Lib\Utilities\FileSystem::RecursiveRemoveDir(
                $this->output_path . "src",
                true
            );

        foreach($this->symbols->headers as $header_name=>$header_object)
        {
            // Skip disabled headers
            if(!$header_object->enabled)
                continue;

            // Generate header file
            $header_content = $this->GenerateHeader($header_name);

            file_put_contents(
                $this->output_path . "includes/" . $this->GetHeaderNamePHP($header_name),
                $header_content
            );

            // Generate source file
            $source_content = $this->GenerateSource($header_name);

            file_put_contents(
                $this->output_path . "src/" . $this->GetSourceNamePHP($header_name),
                $source_content
            );
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
            include($this->GetHeaderTemplate($header_name));
            $header_content .= ob_get_contents();
        ob_end_clean();

        // Get constants function template content
        if($header_object->HasConstants() || $header_object->HasGlobalVariables())
        {
            ob_start();
                include($this->GetConstantsFunctionTemplate($header_name));
                $header_content .= ob_get_contents();
            ob_end_clean();

            $header_content .= "\n";
        }

        // Get enums function template content
        if($header_object->HasEnumerations())
        {
            ob_start();
                include($this->GetEnumsFunctionTemplate($header_name));
                $header_content .= ob_get_contents();
            ob_end_clean();

            $header_content .= "\n";
        }

        // Get functions function template content
        if($header_object->HasFunctions())
        {
            ob_start();
                include($this->GetFunctionsFunctionTemplate($header_name));
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
            include($this->GetHeaderTemplate($header_name, "footer"));
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
            include($this->GetSourceTemplate($header_name));
            $source_content .= ob_get_contents();
        ob_end_clean();

        // Get constants function template content
        if($header_object->HasConstants() || $header_object->HasGlobalVariables())
        {
            ob_start();
                include($this->GetConstantsFunctionTemplate($header_name, "header"));
                $source_content .= ob_get_contents();
            ob_end_clean();

            $source_content .= "    ";

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

            ob_start();
                include($this->GetConstantsFunctionTemplate($header_name, "footer"));
                $source_content .= ob_get_contents();
            ob_end_clean();

            $source_content .= "\n";
        }

        // Get enums function template content
        if($header_object->HasEnumerations())
        {
            ob_start();
                include($this->GetEnumsFunctionTemplate($header_name, "header"));
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
                        include($this->GetRegisterEnumTemplate($enum_name, $namespace_name, "class"));
                        $source_content .= $this->Indent(ob_get_contents(), 4);
                    ob_end_clean();

                    foreach($enum_object->options as $enum_option)
                    {
                        ob_start();
                            include($this->GetRegisterEnumTemplate($enum_name, $namespace_name, "constant"));
                            $source_content .= $this->Indent(ob_get_contents(), 4);
                        ob_end_clean();
                    }
                }
            }

            ob_start();
                include($this->GetEnumsFunctionTemplate($header_name, "footer"));
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
                    $source_content .= $this->GenerateFunction($function_object);
                }
            }

            // Generate functions registration code
            ob_start();
                include($this->GetFunctionsFunctionTemplate($header_name, "header"));
                $source_content .= ob_get_contents();
            ob_end_clean();

            ob_start();
                include($this->GetFunctionsTableTemplate($header_name, "", "begin"));
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
                        include($this->GetFunctionsTableTemplate($header_name, "", "entry"));
                        $source_content .= $this->Indent(ob_get_contents(), 8);
                    ob_end_clean();
                }
            }

            ob_start();
                include($this->GetFunctionsTableTemplate($header_name, "", "end"));
                $source_content .= $this->Indent(ob_get_contents(), 4);
            ob_end_clean();

            ob_start();
                include($this->GetFunctionsTableTemplate($header_name, "", "register"));
                $source_content .= $this->Indent(ob_get_contents(), 4);
            ob_end_clean();

            ob_start();
                include($this->GetFunctionsFunctionTemplate($header_name, "footer"));
                $source_content .= ob_get_contents();
            ob_end_clean();

            $source_content .= "\n";
        }

        // Get footer of source file
        ob_start();
            include($this->GetSourceTemplate($header_name, "footer"));
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

        $function_content = "";

        $parameters_code = "";
        $parse_code = "";
        $call_code = "";
        $return_code = "";

        $proto_header = "";
        $proto_footer = "";

        // Get header of function
        ob_start();
            include($this->GetFunctionTemplate($function_name, $namespace_name, "head"));
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
            
            $clause = $required_parameters == $parameters_count ? 
                "arguments_received == $required_parameters" 
                : 
                "arguments_received >= $required_parameters  && arguments_received <= $parameters_count"
            ;
            
            $parse_code .= "//Overload $overload\n";
            $parse_code .= "overload_{$overload}:\n";
            $parse_code .= "if(!already_called && $clause)\n";
            $parse_code .= "{\n";
            
            $parse_string = "";
            $parameters_optional = false;
            $parse_parameters = "";
            
            $parse_string_ref = "";
            $parse_reference = "";
            $references_found = false;
            
            $object_retrieve_code = "";
            
            foreach($overload_object->parameters as $parameter_name=>$parameter_object)
            {
                if($parameter_object->default_value && !$parameters_optional)
                {
                    $parse_string .= "|";
                    $parameters_optional = true;
                }
            
                ob_start();
                    include($this->GetParameterTemplate($parameter_object, $namespace_name, "parse_string"));
                    $parse_string .= ob_get_contents();
                ob_end_clean();
                
                ob_start();
                    include($this->GetParameterTemplate($parameter_object, $namespace_name, "parse"));
                    $parse_parameters .= ob_get_contents() . ", ";
                ob_end_clean();
                
                if(!$parameter_object->is_const && $parameter_object->is_reference)
                    $references_found = true;
                
                ob_start();
                    include($this->GetParameterTemplate($parameter_object, $namespace_name, "parse_string_ref"));
                    $parse_string_ref .= ob_get_contents();
                ob_end_clean();
                
                ob_start();
                    include($this->GetParameterTemplate($parameter_object, $namespace_name, "parse_reference"));
                    $parse_reference .= ob_get_contents() . ", ";
                ob_end_clean();
            }
            
            // No need to parse parameters
            if($required_parameters == 0 && $parameters_count < 1)
            {
                $parse_code .= $this->Indent(
                    "overload_{$overload}_called = true;\n", 
                    4
                );
                    
                $parse_code .= $this->Indent(
                    "already_called = true;\n", 
                    4
                );
            }
            // Theres a need to parse parameters
            else
            {
                $parse_code .= $this->Indent(
                    "char parse_parameters_string[] = \"$parse_string\";\n", 
                    4
                );
                
                $parse_code .= $this->Indent(
                    "if(zend_parse_parameters_ex("
                    . "ZEND_PARSE_PARAMS_QUIET, "
                    . "arguments_received TSRMLS_CC, "
                    . "parse_parameters_string, "
                    . trim($parse_parameters, ", ").") == SUCCESS)\n", 
                    4
                );
                
                $parse_code .= $this->Indent(
                    "{\n", 
                    4
                );
                
                $parse_code .= $object_retrieve_code;
                
                $parse_code .= $this->Indent(
                    "overload_{$overload}_called = true;\n", 
                    8
                );
                
                $parse_code .= $this->Indent(
                    "already_called = true;\n", 
                    8
                );

                // Just get variables not originally parsed as 
                // zvals since they are references
                if($references_found)
                {
                    $parse_code .= "\n";
                    
                    $parse_code .= $this->Indent(
                        "char parse_references_string[] = "
                        . "\"$parse_string_ref\";\n", 
                        8
                    );
                    
                    $parse_code .= $this->Indent(
                        "zend_parse_parameters_ex("
                        . "ZEND_PARSE_PARAMS_QUIET, "
                        . "arguments_received TSRMLS_CC, "
                        . "parse_references_string, "
                        . trim($parse_reference, ", ").");\n", 
                        8
                    );
                }

                $parse_code .= $this->Indent("}\n", 4);
            }

            $parse_code .= "}\n";
            $parse_code .= "\n";
        }

        $function_content .= "    ";

        // Get body of function
        ob_start();
            include($this->GetFunctionTemplate($function_name, $namespace_name, "body"));
            $function_content .= $this->Indent(ob_get_contents(), 4);
        ob_end_clean();

        // Get footer of function
        ob_start();
            include($this->GetFunctionTemplate($function_name, $namespace_name, "footer"));
            $function_content .= ob_get_contents();
        ob_end_clean();

        return $function_content;
    }

    /**
     * Retrieve the template path for a header, also checks if a valid override
     * exists and returns that instead.
     * @param string $header_name Name of header.
     * @param string $type Can be header or footer.
     * @return string Path to template file.
     */
    public function GetHeaderTemplate($header_name, $type="header")
    {
        $override = $this->templates_path
            . "zend_php/helpers/header_overrides/"
            . "{$type}_" . strtolower(
                str_replace(
                    array("/", "-", "."),
                    "_",
                    $header_name
                )
            )
            . ".php"
        ;

        if(file_exists($override))
        {
            return $override;
        }

        return $this->templates_path
            . "zend_php/helpers/"
            . "headers_$type.php"
        ;
    }

    /**
     * Retrieve the template path for a source, also checks if a valid override
     * exists and returns that instead.
     * @param string $header_name Name of header.
     * @param string $type Can be header or footer.
     * @return string Path to template file.
     */
    public function GetSourceTemplate($header_name, $type="header")
    {
        $override = $this->templates_path
            . "zend_php/helpers/source_overrides/"
            . "{$type}_" . strtolower(
                str_replace(
                    array("/", "-", "."),
                    "_",
                    $header_name
                )
            )
            . ".php"
        ;

        if(file_exists($override))
        {
            return $override;
        }

        return $this->templates_path
            . "zend_php/helpers/"
            . "sources_$type.php"
        ;
    }

    /**
     * Retrieve the template path for constants registration function,
     * also checks if a valid override exists and returns that instead.
     * @param string $header_name Name of header file.
     * @param string $type Can be header, footer or decl.
     * @return string Path to template file.
     */
    public function GetConstantsFunctionTemplate($header_name, $type="decl")
    {
        $override = $this->templates_path
            . "zend_php/helpers/constant_overrides/"
            . "{$type}_" . strtolower(
                str_replace(
                    array("/", "-", "."),
                    "_",
                    $header_name
                )
            )
            . ".php"
        ;

        if(file_exists($override))
        {
            return $override;
        }

        return $this->templates_path
            . "zend_php/helpers/"
            . "constants_function_$type.php"
        ;
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
     * Retrieve the template path for enums registration function,
     * also checks if a valid override exists and returns that instead.
     * @param string $header_name Name of header file.
     * @param string $type Can be header, footer or decl.
     * @return string Path to template file.
     */
    public function GetEnumsFunctionTemplate($header_name, $type="decl")
    {
        $override = $this->templates_path
            . "zend_php/helpers/enum_overrides/"
            . "function_{$type}_" . strtolower(
                str_replace(
                    array("/", "-", "."),
                    "_",
                    $header_name
                )
            )
            . ".php"
        ;

        if(file_exists($override))
        {
            return $override;
        }

        return $this->templates_path
            . "zend_php/helpers/"
            . "enums_function_$type.php"
        ;
    }

    /**
     * Retrieve the template path for registering enums, also checks
     * if a valid override exists and returns that instead.
     * @param string $name Name of the enumaration.
     * @param string $namespace Namespace where resides the enum.
     * @param string $type Can be class or constant.
     * @return string Path to template file.
     */
    public function GetRegisterEnumTemplate($name, $namespace="", $type="class")
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
            . "zend_php/helpers/enum_overrides/"
            . "declare_{$type}_" . $namespace . strtolower(
                str_replace(
                    array("/", "-", "."),
                    "_",
                    $name
                )
            )
            . ".php"
        ;

        if(file_exists($override))
        {
            return $override;
        }

        return $this->templates_path
            . "zend_php/helpers/"
            . "enums_declare_$type.php"
        ;
    }

    /**
     * Retrieve the template path for functions registration function,
     * also checks if a valid override exists and returns that instead.
     * @param string $header_name Name of header file.
     * @param string $type Can be header, footer or decl.
     * @return string Path to template file.
     */
    public function GetFunctionsFunctionTemplate($header_name, $type="decl")
    {
        $override = $this->templates_path
            . "zend_php/helpers/function_overrides/"
            . "function_{$type}_" . strtolower(
                str_replace(
                    array("/", "-", "."),
                    "_",
                    $header_name
                )
            )
            . ".php"
        ;

        if(file_exists($override))
        {
            return $override;
        }

        return $this->templates_path
            . "zend_php/helpers/"
            . "functions_function_$type.php"
        ;
    }

    /**
     * Retrieve the template path for registering functions, also checks
     * if a valid override exists and returns that instead.
     * @param string $name Name of the function.
     * @param string $namespace Namespace where resides the enum.
     * @param string $type Can be begin, end, entry or register.
     * @return string Path to template file.
     */
    public function GetFunctionsTableTemplate($name, $namespace="", $type="entry")
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
            . "zend_php/helpers/function_overrides/"
            . "table_{$type}_" . $namespace . strtolower(
                str_replace(
                    array("/", "-", "."),
                    "_",
                    $name
                )
            )
            . ".php"
        ;

        if(file_exists($override))
        {
            return $override;
        }

        return $this->templates_path
            . "zend_php/helpers/"
            . "functions_table_$type.php"
        ;
    }

    /**
     * Retrieve the template path for generating functions, also checks
     * if a valid override exists and returns that instead.
     * @param string $name Name of the function.
     * @param string $namespace Namespace where resides the enum.
     * @param string $type Can be head, footer or body.
     * @return string Path to template file.
     */
    public function GetFunctionTemplate($name, $namespace="", $type="body")
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
            . "zend_php/functions/overrides/"
            . "{$type}_" . $namespace . strtolower(
                str_replace(
                    array("/", "-", "."),
                    "_",
                    $name
                )
            )
            . ".php"
        ;

        if(file_exists($override))
        {
            return $override;
        }

        return $this->templates_path
            . "zend_php/functions/"
            . "function_$type.php"
        ;
    }

    /**
     * Retrieve the template path for parameters, also checks
     * if a valid override exists and returns that instead.
     * @param string $parameter Name of the function.
     * @param string $namespace Namespace where resides the enum.
     * @param string $type Can be declare, parse_string, parse, parse_reference...
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
}