<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Config;

/**
 * Base structure for managing configuration files. Every configuration manager
 * should implement this class.
 */
abstract class Base
{

    /**
     * All settings stored on a configuration file.
     * @var array
     */
    protected $settings;

    /**
     * Directory of configuration file.
     * @var string
     */
    public $directory;

    /**
     * Full path of configuration file.
     * @var string
     */
    public $file;

    /**
     * Loads all configuration options of a given file and creates it if does
     * not exists.
     * @param string $directory
     * @param string $configuration_file
     */
    abstract public function Load($directory, $configuration_file);

    /**
     * Get a setting value.
     * @param string $valueName
     * @return boolean
     */
    abstract public function Get($valueName);

    /**
     * Get all settings.
     * @return array
     */
    abstract public function GetAll();

    /**
     * Get the value of a setting on a specific configuration section.
     * @param string $sectionName
     * @param string $valueName
     * @return boolean|string
     */
    abstract public function GetSectionValue($sectionName, $valueName);

    /**
     * Modifies or adds a global value and writes it to the configuration
     * file immediately.
     * @param type $valueName
     * @param type $value
     */
    abstract public function Set($valueName, $value);

    /**
     * Edits or creates a new section and value and writes it to the 
     * configuration file immediately.
     * @param string $sectionName
     * @param string $valueName
     * @param string $value
     */
    abstract public function SetSectionValue($sectionName, $valueName, $value);

    /**
     * Writes a configuration file using the settings array.
     */
    abstract public function Write();

}

?>
