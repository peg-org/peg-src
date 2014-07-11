<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib\Config;

/**
 * Json implementation to manage configuration files.
 */
class JSON extends \Peg\Lib\Config\Base
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
            $this->preferences = \Peg\Lib\Utilities\Json::Decode(
                file_get_contents($this->file)
            );
        }
    }

    /**
     * Writes a configuration file using the settings array.
     */
    public function Write()
    {
        file_put_contents(
            $this->file, 
            \Peg\Lib\Utilities\Json::Encode($this->preferences)
        );
    }

}