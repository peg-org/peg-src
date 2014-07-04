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

    public function GenerateHeader($header_name)
    {
        // Variables used by some template files.
        $authors = \Peg\Lib\Settings::GetAuthors();
        $contributors = \Peg\Lib\Settings::GetContributors();
        $extension = \Peg\Lib\Settings::GetExtensionName();

        $header_object = $this->symbols->headers[$header_name];
        $header_define = $this->GetHeaderDefine($header_name);

        $header_content = "";

        foreach($header_object->namespaces as $namespace_name=>$namespace_object)
        {
            // Get heading of header file
            ob_start();
                include($this->GetHeaderTemplate($header_name));
                $header_content .= ob_get_contents();
            ob_end_clean();

            // Get constants function template content
            if($header_object->HasConstants())
            {
                // Name used for the constants_function_decl template
                $function_name = $this->GetConstantsFunctionName($header_name);

                ob_start();
                    include($this->GetConstantsFunctionTemplate($header_name));
                    $header_content .= ob_get_contents();
                ob_end_clean();
            }

            // Get footer of header file
            ob_start();
                include($this->GetHeaderTemplate($header_name, "footer"));
                $header_content .= ob_get_contents();
            ob_end_clean();
        }

        return $header_content;
    }
    
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
        
        // Get heading of header file
        ob_start();
            include($this->GetSourceTemplate($header_name));
            $source_content .= ob_get_contents();
        ob_end_clean();

        // Get constants function template content
        if($header_object->HasConstants())
        {
            // Name used for the constants_function_decl template
            $function_name = $this->GetConstantsFunctionName($header_name);

            ob_start();
                include($this->GetConstantsFunctionTemplate($header_name, "header"));
                $source_content .= ob_get_contents();
            ob_end_clean();
            
            $source_content .= "    ";

            foreach($header_object->namespaces as $namespace_name=>$namespace_object)
            {
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
        }

        // Get footer of header file
        ob_start();
            include($this->GetSourceTemplate($header_name, "footer"));
            $source_content .= ob_get_contents();
        ob_end_clean();

        return $source_content;
    }

    /**
     * Retrieve the template path for a header, also checks if a valid override
     * exists and returns that instead.
     * @param string $name
     * @param string $type Can be header or footer.
     * @return string Path to template file.
     */
    public function GetHeaderTemplate($name, $type="header")
    {
        $override = $this->templates_path
            . "zend_php/helpers/header_overrides/"
            . "{$type}_" . strtolower(
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
            . "headers_$type.php"
        ;
    }
    
    /**
     * Retrieve the template path for a source, also checks if a valid override
     * exists and returns that instead.
     * @param string $name
     * @param string $type Can be header or footer.
     * @return string Path to template file.
     */
    public function GetSourceTemplate($name, $type="header")
    {
        $override = $this->templates_path
            . "zend_php/helpers/source_overrides/"
            . "{$type}_" . strtolower(
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
            . "sources_$type.php"
        ;
    }

    /**
     * Retrieve the template path for constant functions, also checks
     * if a valid override exists and returns that instead.
     * @param string $name
     * @param string $type Can be header, footer or decl.
     * @return string Path to template file.
     */
    public function GetConstantsFunctionTemplate($name, $type="decl")
    {
        $override = $this->templates_path
            . "zend_php/helpers/constants_function_overrides/"
            . "{$type}_" . strtolower(
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
            . "constants_function_$type.php"
        ;
    }
    
    /**
     * Retrieve the template path for constant functions, also checks
     * if a valid override exists and returns that instead.
     * @todo Improve this to handle various types.
     * @param string $name
     * @return string Path to template file.
     */
    public function GetRegisterConstantTemplate($name)
    {
        return $this->templates_path
            . "zend_php/constants/"
            . "integer.php"
        ;
    }
}