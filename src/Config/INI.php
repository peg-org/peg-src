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
        $this->preferences = array();

        $this->directory = $directory;

        $this->file = $this->directory . "/$configuration_file";

        if(file_exists($this->file))
        {
            $this->preferences = parse_ini_file($this->file, true);
        }
    }

    /**
     * Writes a configuration file using the settings array.
     */
    public function Write()
    {
        $content = "";

        foreach($this->preferences as $key => $data)
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