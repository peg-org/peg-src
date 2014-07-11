<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib\Config;

/**
 * Base structure for managing configuration files. Every configuration manager
 * should implement this class.
 */
abstract class Base
{

    /**
     * All preferences stored on a configuration file. 
     * 
     * Format of this array for single options:
     * $settings["option"] = "value" 
     * 
     * Format of this array for a group of options:
     * $settings["group"] = array(
     *     "option" => "value",
     *     "option2" => "value2"
     * )
     * @var array
     */
    protected $preferences;

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
     * Writes a configuration file using the settings array.
     */
    abstract public function Write();

    /**
     * Get an option value.
     * @param string $option
     * @return boolean
     */
    public function Get($option)
    {
        if(isset($this->preferences[$option]))
        {
            return $this->preferences[$option];
        }

        return false;
    }

    /**
     * Get all preferences.
     * @return array
     */
    public function GetAll()
    {
        return $this->preferences;
    }

    /**
     * Get the value of an option on a specific configuration group.
     * @param string $group
     * @param string $option
     * @return boolean|string
     */
    public function GetGroupValue($group, $option)
    {
        if(isset($this->preferences[$group]))
        {
            if(isset($this->preferences[$group][$option]))
            {
                return $this->preferences[$group][$option];
            }
        }

        return false;
    }

    /**
     * Modifies or adds an option value and writes it to the configuration
     * file immediately.
     * @param type $option
     * @param type $value
     */
    public function Set($option, $value)
    {
        $this->preferences[$option] = $value;

        $this->Write();
    }

    /**
     * Edits or creates a new group and option and writes it to the 
     * configuration file immediately.
     * @param string $group
     * @param string $option
     * @param string $value
     */
    public function SetGroupValue($group, $option, $value)
    {
        $this->preferences[$group][$option] = $value;

        $this->Write();
    }

}