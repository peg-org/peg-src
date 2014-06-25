<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg;

/**
 * For managing the configuration options and settings of a valid peg
 * managed extension directory.
 */
class Settings extends Utilities\Configuration
{

    // Disable constructor
    private function __construct(){}

    /**
     * Loads the peg.conf file on the given directory.
     * @param type $directory
     */
    public static function Load($directory)
    {
        parent::Load($directory, "peg.conf");
    }

    public static function GetAuthors()
    {
        return self::Get("authors");
    }

    public static function GetContributors()
    {
        return self::Get("contributors");
    }

    public static function GetExtensionName()
    {
        $name = self::Get("name");

        if(strlen($name) <= 0)
        {
            $dir_parts = explode("/", str_replace("\\", "/", self::$directory));
            $name = $dir_parts[count($dir_parts) - 1];
        }

        return $name;
    }

    public static function GetVersion()
    {
        return self::Get("version");
    }

    public static function SetAuthors($authors)
    {
        $authors = trim($authors);
        $authors = trim($authors, ",");

        self::Set("authors", $authors);
    }

    public static function SetContributors($contributors)
    {
        $contributors = trim($contributors);
        $contributors = trim($contributors, ",");

        self::Set("contributors", $contributors);
    }

    public static function SetExtensionName($name)
    {
        $name = trim($name);

        self::Set("name", $name);
    }

    public static function SetVersion($number)
    {
        $number = trim($number);

        self::Set("version", $number);
    }

}

?>
