<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib\Config;

/**
 * Ini implementation to manage configuration files.
 */
class INI extends \Peg\Lib\Config\Base
{

    /**
     * Loads all configuration options of a given file and creates it if does
     * not exists.
     * @param string $directory
     * @param string $configuration_file
     */
    public function Load($directory, $configuration_file)
    {
        $this->settings = array();

        $this->directory = $directory;

        $this->file = $this->directory . "/$configuration_file";

        if(file_exists($this->file))
        {
            $this->settings = parse_ini_file($this->file, true);
        }
    }

    /**
     * Get a setting value.
     * @param string $valueName
     * @return boolean
     */
    public function Get($valueName)
    {
        if(isset($this->settings[$valueName]))
        {
            return $this->settings[$valueName];
        }

        return false;
    }

    /**
     * Get all settings.
     * @return array
     */
    public function GetAll()
    {
        return $this->settings;
    }

    /**
     * Get the value of a setting on a specific configuration section.
     * @param string $sectionName
     * @param string $valueName
     * @return boolean|string
     */
    public function GetSectionValue($sectionName, $valueName)
    {
        if(isset($this->settings[$sectionName]))
        {
            if(isset($this->settings[$sectionName][$valueName]))
            {
                return $this->settings[$sectionName][$valueName];
            }
        }

        return false;
    }

    /**
     * Modifies or adds a global value.
     * @param type $valueName
     * @param type $value
     */
    public function Set($valueName, $value)
    {
        $this->settings[$valueName] = $value;

        self::Write();
    }

    /**
     * Edits or creates a new section and value.
     * @param string $sectionName
     * @param string $valueName
     * @param string $value
     */
    public function SetSectionValue($sectionName, $valueName, $value)
    {
        $this->settings[$sectionName][$valueName] = $value;

        self::Write();
    }

    /**
     * Writes a configuration file using the settings array.
     */
    public function Write()
    {
        $content = "";

        foreach($this->settings as $key => $data)
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

        file_put_contents($this->file, $content);
    }

}

?>
