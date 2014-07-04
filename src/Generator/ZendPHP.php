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
        
        // Variables used by some template files.
        $authors = \Peg\Lib\Settings::GetAuthors();
        $contributors = \Peg\Lib\Settings::GetContributors();
        $extension = \Peg\Lib\Settings::GetExtensionName();
        
        foreach($this->symbols->headers as $header_name=>$header_object)
        {
            // Skip disabled headers
            if(!$header_object->enabled)
                continue;
            
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
            
            file_put_contents(
                $this->output_path . "includes/" . $this->GetHeaderNamePHP($header_name), 
                $header_content
            );
        }
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
}