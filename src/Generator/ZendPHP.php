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
     * @todo Handle namespaces.
     * @param string $header_name
     * @return string Source code.
     */
    public function GenerateHeader($header_name)
    {
        // Variables used by some template files.
        $authors = \Peg\Lib\Settings::GetAuthors();
        $contributors = \Peg\Lib\Settings::GetContributors();
        $extension = \Peg\Lib\Settings::GetExtensionName();

        $header_object = $this->symbols->headers[$header_name];
        $header_define = $this->GetHeaderDefine($header_name);

        $header_content = "";
        
        // Get heading of header file
        ob_start();
            include($this->GetHeaderTemplate($header_name));
            $header_content .= ob_get_contents();
        ob_end_clean();

        // Get constants function template content
        if($header_object->HasConstants())
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
     * @todo Handle namespaces.
     * @param string $header_name
     * @return string Source code.
     */
    public function GenerateSource($header_name)
    {
        // Variables used by some template files.
        $authors = \Peg\Lib\Settings::GetAuthors();
        $contributors = \Peg\Lib\Settings::GetContributors();
        $extension = \Peg\Lib\Settings::GetExtensionName();

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
        if($header_object->HasConstants())
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
                
                foreach($namespace_object->constants as $constant_name=>$constant_object)
                {
                    ob_start();
                        include($this->GetRegisterConstantTemplate($constant_name));
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

        // Get footer of source file
        ob_start();
            include($this->GetSourceTemplate($header_name, "footer"));
            $source_content .= ob_get_contents();
        ob_end_clean();

        return $source_content;
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
            . "{$type}"
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
}