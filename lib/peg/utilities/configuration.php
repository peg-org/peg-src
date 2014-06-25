<?php
/**
 * Defines a class for easily working with ini files.
 *
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Utilities;

/**
 * Manage configuration files.
 */
class Configuration
{

    /**
     * All settings stored on a configuration file.
     * @var array
     */
    protected static $settings;

    /**
     * Directory of configuration file.
     * @var string
     */
    protected static $directory;

    /**
     * Full path of configuration file.
     * @var string
     */
    protected static $file;

    /**
     * Dont permit instantiation of this class.
     * @param string $directory Directory where the configuration file resides.
     * @param string $configuration_file Name of the configuration file.
     */
    private function __construct(){}

    /**
     * Loads all configuration options of a give file and creates it if does
     * not exists.
     * @param string $directory
     * @param string $configuration_file
     */
    static function Load($directory, $configuration_file)
    {
        self::$settings = array();

        self::$directory = $directory;

        self::$file = self::$directory . "/$configuration_file";

        if(file_exists(self::$file))
        {
            self::$settings = parse_ini_file(self::$file, true);
        }
    }

    /**
     * Get a setting value.
     * @param string $valueName
     * @return boolean
     */
    static function Get($valueName)
    {
        if(isset(self::$settings[$valueName]))
        {
            return self::$settings[$valueName];
        }

        return false;
    }

    /**
     * Get all settings.
     * @return array
     */
    static function GetAll()
    {
        return self::$settings;
    }

    /**
     * Get the value of a setting on a specific configuration section.
     * @param string $sectionName
     * @param string $valueName
     * @return boolean|string
     */
    static function GetSectionValue($sectionName, $valueName)
    {
        if(isset(self::$settings[$sectionName]))
        {
            if(isset(self::$settings[$sectionName][$valueName]))
            {
                return self::$settings[$sectionName][$valueName];
            }
        }

        return false;
    }

    /**
     * Modifies or adds a global value.
     * @param type $valueName
     * @param type $value
     */
    static function Set($valueName, $value)
    {
        self::$settings[$valueName] = $value;

        self::WriteINI();
    }

    /**
     * Edits or creates a new section and value.
     * @param string $sectionName
     * @param string $valueName
     * @param string $value
     */
    static function SetSectionValue($sectionName, $valueName, $value)
    {
        self::$settings[$sectionName][$valueName] = $value;

        self::WriteINI();
    }

    /**
     * Writes a configuration file using the settings array.
     */
    static function WriteINI()
    {
        $content = "";

        foreach(self::$settings as $key => $data)
        {
            if(is_array($data))
            {
                $is_section = true;

                foreach($data as $dataKey => $dataValues)
                {
                    if(is_long($dataKey))
                    {
                        $is_section = false;
                        break;
                    }
                }

                $content .= "\n";

                //Write global array value
                if(!$is_section)
                {
                    foreach($data as $dataKey => $dataValue)
                    {
                        $content .= $key . '[] = "' . $dataValue . '"' . "\n";
                    }
                }

                //Write section
                else
                {
                    $content .= "[" . $key . "]\n";

                    foreach($data as $dataKey => $dataValue)
                    {
                        if(is_array($dataValue))
                        {
                            foreach($dataValue as $dataInnerValue)
                            {
                                $content .= $dataKey . '[] = "' . $dataInnerValue . '"' . "\n";
                            }
                        }
                        else
                        {
                            $content .= $dataKey . ' = "' . $dataValue . '"' . "\n";
                        }
                    }
                }

                $content .= "\n";
            }

            //Write global value
            else
            {
                $content .= $key . ' = "' . $data . '"' . "\n";
            }
        }

        file_put_contents(self::$file, $content);
    }

}

?>
