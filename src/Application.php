<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib;

/**
 * Holds global options and objects.
 */
class Application
{
    
    /**
     * Reference to the definition's symbols object.
     * @var \Peg\Lib\Definitions\Symbols
     */
    private static $symbols;

    // Disable constructor
    private function __construct(){}

    /**
     * Initialize all variables used by Peg. Has to be called before any usage
     * of peg.
     */
    public static function Initialize()
    {
        // Initialize the plugin loader and try to load any plugins.
        self::$plugin_loader = new Plugins\Loader();

        if(self::ValidExtension())
        {
            self::$plugin_loader->Start(self::GetCwd() . "/plugins");
            
            if(file_exists(self::GetCwd() . "/peg.conf"))
            {
                Settings::SetBackEnd(new Config\INI);
                Settings::Load(self::GetCwd(), "peg.conf");
            }
            else
            {
                Settings::SetBackEnd(new Config\JSON);
                Settings::Load(self::GetCwd(), "peg.json");
            }
        }
    }

    /**
     * Check if the current directory is of a valid extension.
     * @return boolean
     */
    public static function ValidExtension()
    {
        $dir = self::GetCwd();

        if(
            // Templates
            is_dir($dir . "/templates") &&
            
            // Peg configuration file
            (file_exists($dir . "/peg.conf") || file_exists($dir . "/peg.json"))
        )
        {
            return true;
        }

        return false;
    }

    /**
     * Gets the current working directory.
     * @return string
     */
    public static function GetCwd()
    {
        return $_SERVER["PWD"];
    }

    /**
     * Retreieve the skeleton path from PEG_SKELETON_PATH or throws
     * an exception if not exists.
     * @return string
     * @throws Exception
     */
    public static function GetSkeletonPath()
    {
        if(file_exists(PEG_SKELETON_PATH))
            return PEG_SKELETON_PATH;

        throw new Exception("Skeleton path not found.");
    }
    
    /**
     * Loads definitions files if not yet loaded and returns a reference to a
     * symbols object that can be used throught the application.
     * @return \Peg\Lib\Definitions\Symbols
     */
    public static function &GetDefinitions()
    {
        if(!self::ValidExtension())
        {
            CommandLine\Error::Show(t("Invalid extension directory, definitions could not be loaded."));
        }

        if(!is_object(self::$symbols))
        {
            self::$symbols = new Definitions\Symbols;
            
            $importer = new Definitions\Importer(
                self::$symbols, 
                "definitions",
                Definitions\Type::JSON
            );
            
            $importer->Start();
        }

        return self::$symbols;
    }

    /**
     * Get reference to the plugin loader currently used by peg.
     * @return \Peg\Lib\Plugins\Loader
     */
    public static function &GetPluginLoader()
    {
        return self::$plugin_loader;
    }
}
