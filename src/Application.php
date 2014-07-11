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
     * Reference to the global parser.
     * @var \Peg\Lib\CommandLine\Parser
     */
    private static $cli_parser;

    /**
     * Reference to the plugins loader.
     * @var \Peg\Lib\Plugins\Loader;
     */
    private static $plugin_loader;

    /**
     * Reference to the help command.
     * @var \Peg\Lib\Command\Help
     */
    private static $help_command;

    /**
     * Reference to the init command.
     * @var \Peg\Lib\Command\Init
     */
    private static $init_command;

    /**
     * Reference to the parse command.
     * @var \Peg\Lib\Command\Parse
     */
    private static $parse_command;

    /**
     * Reference to the generate command.
     * @var \Peg\Lib\Command\Generate
     */
    private static $generate_command;

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
        self::$cli_parser = new CommandLine\Parser;

        // Set Application details
        self::$cli_parser->application_name = "peg";
        self::$cli_parser->application_version = "1.0";
        self::$cli_parser->application_description = t("PHP Extension Generator (http://github.com/peg-org/peg-src)");

        // Create commands
        self::$help_command = new Command\Help;
        self::$init_command = new Command\Init;
        self::$parse_command = new Command\Parse;
        self::$generate_command = new Command\Generate;

        // Register command operations
        self::$cli_parser->RegisterCommand(self::$help_command);
        self::$cli_parser->RegisterCommand(self::$init_command);
        self::$cli_parser->RegisterCommand(self::$parse_command);
        self::$cli_parser->RegisterCommand(self::$generate_command);

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
     * Gets the global command line parser.
     * @return \Peg\Lib\CommandLine\Parser
     */
    public static function &GetCLIParser()
    {
        return self::$cli_parser;
    }

    /**
     * Gets a reference to init command currently used by peg.
     * @return \Peg\Lib\Command\Init
     */
    public static function &GetInitCommand()
    {
        return self::$init_command;
    }

    /**
     * Gets a reference to help command currently used by peg.
     * @return \Peg\Lib\Command\Help
     */
    public static function &GetHelpCommand()
    {
        return self::$help_command;
    }

    /**
     * Gets a reference to parse command currently used by peg.
     * @return \Peg\Lib\Command\Parse
     */
    public static function &GetParseCommand()
    {
        return self::$parse_command;
    }

    /**
     * Gets a reference to generate command currently used by peg.
     * @return \Peg\Lib\Command\Parse
     */
    public static function &GetGenerateCommand()
    {
        return self::$generate_command;
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