<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib;

/**
 * For managing the configuration options of a valid peg
 * managed extension directory.
 */
class Settings
{

    /**
     * The configurations object used to manipulate the settings.
     * @var \Peg\Lib\Config\Base
     */
    private static $backend;
    
    // Disable constructor
    private function __construct(){}
    
    /**
     * Sets the back-end or implementation that will be used to manage
     * the retreival and storage of settings.
     * @param \Peg\Lib\Config\Base $backend
     */
    public static function SetBackEnd(\Peg\Lib\Config\Base $backend)
    {
        self::$backend = $backend;
    }

    /**
     * Loads the configuration file on the given directory.
     * @param string $directory
     * @param string $file
     */
    public static function Load($directory, $file)
    {
        self::CheckBackend();
        
        self::$backend->Load($directory, $file);
    }
    
    /**
     * Gets the value of a specific option.
     * @param string $option
     * @return string|bool
     */
    public static function Get($option)
    {
        self::CheckBackend();
        
        return self::$backend->Get($option);
    }
    
    /**
     * Gets the value of a specific option inside a group.
     * @param string $group Eg: parser
     * @param string $option Eg: input_format
     * @return string|bool
     */
    public static function GetGroupValue($group, $option)
    {
        self::CheckBackend();
        
        return self::$backend->GetGroupValue($group, $option);
    }

    /**
     * Get a comma separated list of authors.
     * @return string
     */
    public static function GetAuthors()
    {
        self::CheckBackend();
        
        return self::$backend->Get("authors");
    }

    /**
     * Get a comma separated list of contributors.
     * @return string
     */
    public static function GetContributors()
    {
        self::CheckBackend();
        
        return self::$backend->Get("contributors");
    }

    /**
     * Get the current extension name.
     * @return string
     */
    public static function GetExtensionName()
    {
        self::CheckBackend();
        
        $name = self::$backend->Get("name");

        if(strlen($name) <= 0)
        {
            $dir_parts = explode(
                "/", 
                str_replace("\\", "/", self::$backend->directory)
            );
            
            $name = $dir_parts[count($dir_parts) - 1];
        }

        return $name;
    }

    /**
     * Get the current extension version.
     * @return string
     */
    public static function GetVersion()
    {
        self::CheckBackend();
        
        return self::$backend->Get("version");
    }
    
    /**
     * Modify or add a new option.
     * @param string $option Option to add or modify.
     * @param string $value Value of the option.
     */
    public static function Set($option, $value)
    {
        self::CheckBackend();

        self::$backend->Set($option, $value);
    }
    
    /**
     * Modify or add a new group with an option.
     * @param string $group Name of group to modify or create.
     * @param string $option Name of option to add or modify in the group.
     * @param string $value Value of the option.
     */
    public static function SetGroupValue($group, $option, $value)
    {
        self::CheckBackend();

        self::$backend->SetGroupValue($group, $option, $value);
    }

    /**
     * Set the authors of the extension. This should be a comma 
     * seperated list with the names of the authors.
     * @param string $authors
     */
    public static function SetAuthors($authors)
    {
        self::CheckBackend();
        
        $authors = trim($authors);
        $authors = trim($authors, ",");

        self::$backend->Set("authors", $authors);
    }

    /**
     * Set the contributors of the extension. This should be a comma 
     * seperated list with the names of the contributors.
     * @param string $contributors
     */
    public static function SetContributors($contributors)
    {
        self::CheckBackend();
        
        $contributors = trim($contributors);
        $contributors = trim($contributors, ",");

        self::$backend->Set("contributors", $contributors);
    }

    /**
     * Set the extension name, which is used in some important parts of the
     * code generator.
     * @param string $name
     */
    public static function SetExtensionName($name)
    {
        self::CheckBackend();
        
        $name = trim($name);

        self::$backend->Set("name", $name);
    }

    /**
     * Sets the version of the extension which is used in some important
     * parts of the code generator.
     * @param string $number
     */
    public static function SetVersion($number)
    {
        self::CheckBackend();
        
        $number = trim($number);

        self::$backend->Set("version", $number);
    }
    
    /**
     * Helper called by all other methods to make sure that a backend 
     * was set before calling them.
     * @throws \Exception
     */
    private static function CheckBackend()
    {   
        if(!is_object(self::$backend))
            throw new \Exception(
                t("The back-end to manage the settings is not set.")
            );
    }

}