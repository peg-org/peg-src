<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg;

/**
 * For managing the configuration options of a valid peg
 * managed extension directory.
 */
class Settings
{

    /**
     * The configurations object used to manipulate the settings.
     * @var \Peg\Config\Base
     */
    private static $backend;
    
    // Disable constructor
    private function __construct(){}
    
    /**
     * Sets the back-end or implementation that will be used to manage
     * the retreival and storage of settings.
     * @param \Peg\Config\Base $backend
     */
    public static function SetBackEnd(\Peg\Config\Base $backend)
    {
        self::$backend = $backend;
    }

    /**
     * Loads the peg.conf file on the given directory.
     * @param type $directory
     */
    public static function Load($directory)
    {
        self::CheckBackend();
        
        self::$backend->Load($directory, "peg.conf");
    }
    
    public static function Get($option)
    {
        self::CheckBackend();
        
        return self::$backend->Get($option);
    }

    public static function GetAuthors()
    {
        self::CheckBackend();
        
        return self::$backend->Get("authors");
    }

    public static function GetContributors()
    {
        self::CheckBackend();
        
        return self::$backend->Get("contributors");
    }

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

    public static function GetVersion()
    {
        self::CheckBackend();
        
        return self::$backend->Get("version");
    }
    
    public static function Set($option, $value)
    {
        self::CheckBackend();

        self::$backend->Set($option, $value);
    }

    public static function SetAuthors($authors)
    {
        self::CheckBackend();
        
        $authors = trim($authors);
        $authors = trim($authors, ",");

        self::$backend->Set("authors", $authors);
    }

    public static function SetContributors($contributors)
    {
        self::CheckBackend();
        
        $contributors = trim($contributors);
        $contributors = trim($contributors, ",");

        self::$backend->Set("contributors", $contributors);
    }

    public static function SetExtensionName($name)
    {
        self::CheckBackend();
        
        $name = trim($name);

        self::$backend->Set("name", $name);
    }

    public static function SetVersion($number)
    {
        self::CheckBackend();
        
        $number = trim($number);

        self::$backend->Set("version", $number);
    }
    
    private static function CheckBackend()
    {   
        if(!is_object(self::$backend))
            throw new \Exception(
                t("The back-end to manage the settings is not set.")
            );
    }

}

?>
