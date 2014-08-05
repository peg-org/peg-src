<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Config;

/**
 * Base configuration class. Every frontend's configuration helper should extend this.
 */
class Settings
{
    
    // Disable constructor
    private function __construct(){}
    
    
    // The array where all settings are kept.
    protected $settings = [
        
        "ExtensionName" => null,
        "ExtensionVersion" => null,
        "Authors" => null,
        "Contributors" => null,
        
        ];
    
    /**
     * Gets the value of a specific option.
     * @param string $option
     * @return string|bool
     */
    public static function Get($option)
    {
        return $settings[$option];
    }
    
    /**
     * Get the current extension name.
     * @return string
     */
    public static function GetExtensionName()
    {
        return $settings["ExtensionVersion"];
    }
    
    /**
     * Get the current extension version.
     * @return string
     */
    public static function GetExtensionVersion()
    {
        return $settings["ExtensionVersion"];
    }
    
    /**
     * Get a comma separated list of authors.
     * @return string
     */
    public static function GetAuthors()
    {
        return $settings["Authors"];
    }
    
    /**
     * Get a comma separated list of contributors.
     * @return string
     */
    public static function GetContributors()
    {
        return $settings["Contributors"];
    }
    
    /**
     * Set the extension name, which is used in some important parts of the
     * code generator.
     * @param string $name
     */
    public static function SetExtensionName($name)
    {
        $settings["ExtensionName"] = trim($name);
    }
    
    /**
     * Sets the version of the extension, which is used in some important
     * parts of the code generator.
     * @param string $number
     */
    public static function SetVersion($number)
    {
        $settings["ExtensionVersion"] = trim($number);
    }
    
    /**
     * Set the authors of the extension. This should be a comma 
     * seperated list with the names of the authors.
     * @param string $authors
     */
    public static function SetAuthors($authors)
    {
        $authors = trim($authors);
        $authors = trim($authors, ",");
        
        $settings["Authors"] = $authors;
    }

    /**
     * Set the contributors of the extension. This should be a comma 
     * seperated list with the names of the contributors.
     * @param string $contributors
     */
    public static function SetContributors($contributors)
    {
        $authors = trim($contributors);
        $authors = trim($contributors, ",");
        
        $settings["Contributors"] = $contributors;
    }
}
