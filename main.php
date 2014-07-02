<?php
/**
 * Basic cli implementation used for testing and development of the library. 
 * If your are planning to use peg please use http://github.com/peg-org/peg-cli 
 * which is the official and full featured cli interface of the project.
 *
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

// Set the path to peg files by using environment variables if available,
// if not, it uses current path
if(isset($_SERVER["PEG_SKELETON_PATH"]))
    define("PEG_SKELETON_PATH", $_SERVER["PEG_SKELETON_PATH"]);
else
    define("PEG_SKELETON_PATH", __DIR__ . "/skeleton");

if(isset($_SERVER["PEG_LIBRARY_PATH"]))
    define("PEG_LIBRARY_PATH", $_SERVER["PEG_LIBRARY_PATH"]);
else
    define("PEG_LIBRARY_PATH", __DIR__ . "/");

if(isset($_SERVER["PEG_LOCALE_PATH"]))
    define("PEG_LOCALE_PATH", $_SERVER["PEG_LOCALE_PATH"]);
else
    define("PEG_LOCALE_PATH", __DIR__ . "/locale");


if(!file_exists(PEG_LIBRARY_PATH . "src"))
    throw new Exception("Peg lib path not found.");

if(!file_exists(PEG_SKELETON_PATH))
    throw new Exception("Peg skeleton files path not found.");

// Register class auto-loader
function peg_autoloader($class_name)
{
    $file = str_replace("\\", "/", $class_name) . ".php";
    $file = str_replace("Peg/", "", $file);

    include(PEG_LIBRARY_PATH . "src/" . $file);
}

spl_autoload_register("peg_autoloader");

// Register global function for translating and to facilitate automatic
// generation of po files.
function t($text)
{
    static $language_object;

    if(!$language_object)
    {
        $language_object = new Peg\Localization\Language(PEG_LOCALE_PATH);
    }

    return $language_object->Translate($text);
}

// Set a default timezone to prevent warnings
date_default_timezone_set("UTC");

// Initialize the application
Peg\Application::Initialize();

// Retrieve a reference of main command line parser
$parser = Peg\Application::GetCLIParser();

// Start the command line parser
$parser->Start($argc, $argv);


?>
